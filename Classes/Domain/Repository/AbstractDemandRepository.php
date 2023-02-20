<?php

namespace Blueways\BwGuild\Domain\Repository;

use Blueways\BwGuild\Domain\Model\Dto\BaseDemand;
use Blueways\BwGuild\Event\ModifyQueryBuilderEvent;
use Doctrine\DBAL\Connection as ConnectionAlias;
use Doctrine\DBAL\Result;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Context\Exception\AspectNotFoundException;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Expression\ExpressionBuilder;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Database\Query\Restriction\FrontendGroupRestriction;
use TYPO3\CMS\Core\EventDispatcher\EventDispatcher;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;
use TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapper;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;
use TYPO3\CMS\Extbase\Persistence\Repository;

class AbstractDemandRepository extends Repository
{
    protected QueryBuilder $queryBuilder;

    protected EventDispatcher $eventDispatcher;

    protected DataMapper $dataMapper;

    /**
     * @param \TYPO3\CMS\Core\EventDispatcher\EventDispatcher $eventDispatcher
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        EventDispatcher $eventDispatcher,
        DataMapper $dataMapper
    ) {
        parent::__construct($objectManager);
        $this->eventDispatcher = $eventDispatcher;
        $this->dataMapper = $dataMapper;
    }

    /**
     * @throws \TYPO3\CMS\Extbase\Persistence\Generic\Exception
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\DBALException
     */
    public function countDemanded(BaseDemand $demand): int
    {
        return count($this->findDemanded($demand));
    }

    /**
     * @param array<mixed> $resultArray
     * @return array<mixed>
     * @throws \TYPO3\CMS\Extbase\Persistence\Generic\Exception
     */
    public function mapResultToObjects(array $resultArray): array
    {
        return $this->dataMapper->map(
            $this->dataMapper->getDataMap($this->objectType)->getClassName(),
            $resultArray
        );
    }

    /**
     * @param \Blueways\BwGuild\Domain\Model\Dto\BaseDemand $demand
     * @return array<mixed>|\TYPO3\CMS\Extbase\Persistence\QueryResultInterface<\TYPO3\CMS\Extbase\DomainObject\AbstractEntity>
     * @throws \TYPO3\CMS\Extbase\Persistence\Generic\Exception|\Doctrine\DBAL\DBALException
     * @throws \Doctrine\DBAL\Driver\Exception
     */
    public function findDemanded(BaseDemand $demand): QueryResultInterface|array
    {
        $this->createQueryBuilder();

        $this->queryBuilder->select($demand::TABLE . '.*');

        if ($demand::TABLE === 'fe_users') {
            $this->queryBuilder->setParameter('dcValue1', [0]);
        }

        /** @var ModifyQueryBuilderEvent $event */
        $event = $this->eventDispatcher->dispatch(
            new ModifyQueryBuilderEvent($this->queryBuilder, $demand)
        );
        $this->queryBuilder = $event->getQueryBuilder();
        $demand = $event->getDemand();

        $this->setConstraints($demand);

        $result = $this->queryBuilder->execute();

        if (!$result instanceof Result) {
            return [];
        }

        return $result->fetchAllAssociative();
    }

    /**
     * Create queryBuilder for current repository table + add filter for correct subclass (record_type)
     *
     * @throws \TYPO3\CMS\Extbase\Persistence\Generic\Exception
     * @see https://gist.github.com/Nemo64/d6bf6561fc4b32d490b1b39966107ff5
     */
    private function createQueryBuilder(): void
    {
        $dataMap = $this->dataMapper->getDataMap($this->objectType);
        $qb = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($dataMap->getTableName());
        $qb->from($dataMap->getTableName());

        $recordTypeColumnName = $dataMap->getRecordTypeColumnName();
        if ($recordTypeColumnName) {
            $typeNames = [$dataMap->getRecordType()];

            foreach ($dataMap->getSubclasses() as $subclass) {
                $typeNames[] = $this->dataMapper->getDataMap($subclass)->getRecordType();
            }

            $typeNameParameter = $qb->createNamedParameter(
                $typeNames,
                ConnectionAlias::PARAM_STR_ARRAY
            );
            $qb->andWhere($qb->expr()->in($dataMap->getTableName() . '.' . $recordTypeColumnName, $typeNameParameter));
        }

        $this->queryBuilder = $qb;
    }

    /**
     * @param BaseDemand $demand
     */
    protected function setConstraints(BaseDemand $demand): void
    {
        $this->setSearchFilterConstraints($demand);
        $this->setCategoryConstraints($demand);
        $this->setOrderConstraints($demand);
        $this->setLimitConstraint($demand);
        $this->setGeoCodeConstraint($demand);
        $this->setRestrictions();
        $this->setLanguageConstraint();
    }

    private function setSearchFilterConstraints(BaseDemand $demand): void
    {
        if (empty($demand->search)) {
            return;
        }

        $constraints = [];
        $searchSplittParts = GeneralUtility::trimExplode(' ', $demand->search, true);

        // reset word-wise search if string contains words less than 3 characters
        $wordsLess3Characters = array_filter($searchSplittParts, function ($word) {
            return strlen($word) < 4;
        });
        if (count($searchSplittParts) > 1 && count($wordsLess3Characters)) {
            $searchSplittParts = [$demand->search];
        }

        $searchFields = $demand->getSearchFields();

        foreach ($searchSplittParts as $searchPart) {
            $subConstraints = [];

            foreach ($searchFields as $cleanProperty) {
                $searchPart = trim($searchPart);
                if ($searchPart) {
                    $subConstraints[] = $this->queryBuilder->expr()->like(
                        $demand::TABLE . '.' . $cleanProperty,
                        $this->queryBuilder->createNamedParameter($searchPart . '%')
                    );
                }
            }
            $constraints[] = $this->queryBuilder->expr()->orX(...$subConstraints);
        }

        $this->queryBuilder->andWhere($this->queryBuilder->expr()->andX(...$constraints));
    }

    private function setCategoryConstraints(BaseDemand $demand): void
    {
        $categories = $demand->categories;
        $categoryConjunction = $demand->categoryConjunction;

        // abort if no category settings
        if (!count($categories) || !$categoryConjunction) {
            return;
        }

        switch (strtolower($categoryConjunction)) {
            case 'or':
                // join tables
                $this->queryBuilder->join(
                    $demand::TABLE,
                    'sys_category_record_mm',
                    'c',
                    $this->queryBuilder->expr()->eq(
                        'c.uid_foreign',
                        $this->queryBuilder->quoteIdentifier($demand::TABLE . '.uid')
                    )
                );

                // any match
                $this->queryBuilder->andWhere(
                    $this->queryBuilder->expr()->in('c.uid_local', $categories)
                );
                break;
            case 'notor':
                // join tables
                $this->queryBuilder->join(
                    $demand::TABLE,
                    'sys_category_record_mm',
                    'c',
                    $this->queryBuilder->expr()->eq(
                        'c.uid_foreign',
                        $this->queryBuilder->quoteIdentifier($demand::TABLE . '.uid')
                    )
                );

                // not any match
                $this->queryBuilder->andWhere(
                    $this->queryBuilder->expr()->notIn('c.uid_local', $categories)
                );
                break;
            case 'notand':
                // join for every category - include check for category uid in join statement
                foreach ($categories as $key => $category) {
                    $this->queryBuilder->join(
                        $demand::TABLE,
                        'sys_category_record_mm',
                        'c' . $key,
                        $this->queryBuilder->expr()->andX(
                            $this->queryBuilder->expr()->eq(
                                'c' . $key . '.uid_foreign',
                                $this->queryBuilder->quoteIdentifier($demand::TABLE . '.uid')
                            ),
                            $this->queryBuilder->expr()->neq(
                                'c' . $key . '.uid_local',
                                $this->queryBuilder->createNamedParameter($category, \PDO::PARAM_INT)
                            )
                        )
                    );
                }
                break;
            case 'and':
            default:
                // join for every category - include check for category uid in join statement
                foreach ($categories as $key => $category) {
                    $this->queryBuilder->join(
                        $demand::TABLE,
                        'sys_category_record_mm',
                        'c' . $key,
                        $this->queryBuilder->expr()->andX(
                            $this->queryBuilder->expr()->eq(
                                'c' . $key . '.uid_foreign',
                                $this->queryBuilder->quoteIdentifier($demand::TABLE . '.uid')
                            ),
                            $this->queryBuilder->expr()->eq(
                                'c' . $key . '.uid_local',
                                $this->queryBuilder->createNamedParameter($category, \PDO::PARAM_INT)
                            )
                        )
                    );
                }
        }

        // make result distinct
        // @TODO: Prefix needs to be added here?
        $this->queryBuilder->groupBy('uid');
    }

    private function setOrderConstraints(BaseDemand $demand): void
    {
        $orderField = $demand->order ?: 'crdate';
        $orderFields = GeneralUtility::trimExplode(',', $orderField, true);
        $orderDirection = $demand->orderDirection ?: QueryInterface::ORDER_ASCENDING;

        foreach ($orderFields as $orderField) {
            $this->queryBuilder->addOrderBy($demand::TABLE . '.' . $orderField, $orderDirection);
        }
    }

    private function setLimitConstraint(BaseDemand $demand): void
    {
        if ($demand->limit && $demand->limit > 0) {
            $this->queryBuilder->setMaxResults($demand->limit);
        }
    }

    private function setGeoCodeConstraint(BaseDemand $demand): void
    {
        if (!$demand->searchDistanceAddress) {
            return;
        }

        // return no results if search string could not be geo coded
        if (!$demand->geoCodeSearchString()) {
            $this->queryBuilder->setMaxResults(0);
            return;
        }

        $earthRadius = 6378.1;
        $maxDistance = $demand->maxDistance ?: 999;

        $distanceSqlCalc = 'ACOS(SIN(RADIANS(' . $this->queryBuilder->quoteIdentifier('latitude') . ')) * SIN(RADIANS(' . $demand->getLatitude() . ')) + COS(RADIANS(' . $this->queryBuilder->quoteIdentifier('latitude') . ')) * COS(RADIANS(' . $demand->getLatitude() . ')) * COS(RADIANS(' . $this->queryBuilder->quoteIdentifier('longitude') . ') - RADIANS(' . $demand->getLongitude() . '))) * ' . $earthRadius;

        $this->queryBuilder->addSelectLiteral($distanceSqlCalc . ' AS `distance`');
        $this->queryBuilder->andWhere(
            $this->queryBuilder->expr()->comparison($distanceSqlCalc, ExpressionBuilder::LT, $maxDistance)
        );
        $this->queryBuilder->orderBy('distance');
    }

    private function setRestrictions(): void
    {
        $this->queryBuilder->getRestrictions()
            ->add(GeneralUtility::makeInstance(FrontendGroupRestriction::class));
    }

    protected function setLanguageConstraint(): void
    {
        try {
            $languageAspect = GeneralUtility::makeInstance(Context::class)->getAspect('language');
            $sysLanguageUid = $languageAspect->getId();
        } catch (AspectNotFoundException) {
            $sysLanguageUid = 0;
        }

        $this->queryBuilder->andWhere(
            $this->queryBuilder->expr()->eq(
                'sys_language_uid',
                $this->queryBuilder->createNamedParameter($sysLanguageUid, \PDO::PARAM_INT)
            )
        );
    }

    /**
     * @param array<mixed> $settings
     * @param string $class
     * @return \Blueways\BwGuild\Domain\Model\Dto\BaseDemand
     */
    public function createDemandObjectFromSettings(
        array $settings,
        string $class = BaseDemand::class
    ): BaseDemand {
        // @TODO: check if this typoscript demandClass setting makes sense
        $class = isset($settings['demandClass']) && !empty($settings['demandClass']) ? $settings['demandClass'] : $class;

        /** @var \Blueways\BwGuild\Domain\Model\Dto\BaseDemand $demand */
        $demand = new $class();

        $demand->setCategories(GeneralUtility::trimExplode(',', $settings['categories'] ?? '', true));
        $demand->setCategoryConjunction($settings['categoryConjunction'] ?? '');
        $demand->setIncludeSubCategories($settings['includeSubCategories'] ?? false);
        $demand->setOrder($settings['order'] ?? '');
        $demand->setOrderDirection($settings['orderDirection'] ?? '');
        $demand->setItemsPerPage((int)$settings['itemsPerPage']);

        if ($settings['limit'] ?? 0) {
            $demand->setLimit((int)$settings['limit']);
        }

        if ($settings['maxItems']) {
            $demand->setLimit((int)$settings['maxItems']);
        }

        return $demand;
    }
}
