<?php

namespace Blueways\BwGuild\Domain\Repository;

use Blueways\BwGuild\Domain\Model\Dto\BaseDemand;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Expression\ExpressionBuilder;
use TYPO3\CMS\Core\Database\Query\Restriction\FrontendGroupRestriction;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapper;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\Repository;

class AbstractDemandRepository extends Repository
{

    /**
     * @var \TYPO3\CMS\Core\Database\Query\QueryBuilder $queryBuilder
     */
    protected $queryBuilder;

    /**
     * @param \Blueways\BwGuild\Domain\Model\Dto\BaseDemand $demand
     * @return int
     * @throws \TYPO3\CMS\Extbase\Persistence\Generic\Exception
     */
    public function countDemanded($demand): int
    {
        $records = $this->findDemanded($demand);
        return $records->count();
    }

    /**
     * @param \Blueways\BwGuild\Domain\Model\Dto\BaseDemand $demand
     * @return array|\TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     * @throws \TYPO3\CMS\Extbase\Persistence\Generic\Exception
     */
    public function findDemanded($demand)
    {
        $this->createQueryBuilder();

        $this->queryBuilder->select('*');

        $dataMapper = $this->objectManager->get(DataMapper::class);
        $dataMap = $dataMapper->getDataMap($this->objectType);

        if ($dataMap->getTableName() === 'fe_users') {
            $this->queryBuilder->setParameter('dcValue1', [0]);
        }

        $this->setConstraints($demand);

        $result = $this->queryBuilder->execute()->fetchAll();

        return $dataMapper->map(
            $dataMap->getClassName(),
            $result
        );
    }

    /**
     * Create queryBuilder for current repository table + add filter for correct subclass (record_type)
     *
     * @throws \TYPO3\CMS\Extbase\Persistence\Generic\Exception
     * @see https://gist.github.com/Nemo64/d6bf6561fc4b32d490b1b39966107ff5
     */
    private function createQueryBuilder(): void
    {
        $dataMapper = $this->objectManager->get(DataMapper::class);
        $dataMap = $dataMapper->getDataMap($this->objectType);
        /** @var \TYPO3\CMS\Core\Database\Query\QueryBuilder $queryBuilder */
        $qb = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($dataMap->getTableName());
        $qb->from($dataMap->getTableName());

        $recordTypeColumnName = $dataMap->getRecordTypeColumnName();
        if ($recordTypeColumnName) {
            $typeNames = [$dataMap->getRecordType()];

            foreach ($dataMap->getSubclasses() as $subclass) {
                $typeNames[] = $dataMapper->getDataMap($subclass)->getRecordType();
            }

            $typeNameParameter = $qb->createNamedParameter(
                $typeNames,
                Connection::PARAM_STR_ARRAY
            );
            $qb->andWhere($qb->expr()->in($recordTypeColumnName, $typeNameParameter));
        }

        $this->queryBuilder = $qb;
    }

    /**
     * @param BaseDemand $demand
     */
    protected function setConstraints($demand): void
    {
        $this->setSearchFilterConstraints($demand);
        $this->setCategoryConstraints($demand);
        $this->setOrderConstraints($demand);
        $this->setLimitConstraint($demand);
        $this->setGeoCodeConstraint($demand);
        $this->setRestritions($demand);
        $this->setLanguageConstraint($demand);
    }

    /**
     * @param BaseDemand $demand
     */
    private function setSearchFilterConstraints($demand): void
    {
        if (empty($demand->getSearch())) {
            return;
        }

        $constraints = [];
        $searchSplittedParts = $demand->getSearchParts();

        $tcaSearchFields = $GLOBALS['TCA'][$demand::TABLE]['ctrl']['searchFields'];
        $searchFields = GeneralUtility::trimExplode(',', $tcaSearchFields, true);

        foreach ($searchSplittedParts as $searchSplittedPart) {
            $subConstraints = [];

            foreach ($searchFields as $cleanProperty) {
                $searchSplittedPart = trim($searchSplittedPart);
                if ($searchSplittedPart) {
                    $subConstraints[] = $this->queryBuilder->expr()->like(
                        $cleanProperty,
                        $this->queryBuilder->createNamedParameter('%' . $searchSplittedPart . '%')
                    );
                }
            }
            $constraints[] = $this->queryBuilder->expr()->orX(...$subConstraints);
        }

        $this->queryBuilder->andWhere($this->queryBuilder->expr()->andX(...$constraints));
    }

    /**
     * @param \Blueways\BwGuild\Domain\Model\Dto\BaseDemand $demand
     */
    private function setCategoryConstraints(BaseDemand $demand): void
    {
        $categories = $demand->getCategories();
        $categoryConjunction = $demand->getCategoryConjunction();

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
        $this->queryBuilder->groupBy('uid');
    }

    /**
     * @param \Blueways\BwGuild\Domain\Model\Dto\BaseDemand $demand
     */
    private function setOrderConstraints(BaseDemand $demand): void
    {
        $orderField = $demand->getOrder() ?: 'crdate';
        $orderFields = GeneralUtility::trimExplode(',', $orderField, true);
        $orderDirection = $demand->getOrderDirection() ?: QueryInterface::ORDER_ASCENDING;

        foreach ($orderFields as $orderField) {
            $this->queryBuilder->addOrderBy($orderField, $orderDirection);
        }
    }

    /**
     * @param \Blueways\BwGuild\Domain\Model\Dto\BaseDemand $demand
     */
    private function setLimitConstraint(BaseDemand $demand): void
    {
        if ($demand->getLimit() && $demand->getLimit() > 0) {
            $this->queryBuilder->setMaxResults($demand->getLimit());
        }
    }

    /**
     * @param BaseDemand $demand
     */
    private function setGeoCodeConstraint($demand): void
    {
        if (!$demand->getSearchDistanceAddress()) {
            return;
        }

        // return no results if search string could not be geo coded
        if (!$demand->geoCodeSearchString()) {
            $this->queryBuilder->setMaxResults(0);
            return;
        }

        $earthRadius = 6378.1;
        $maxDistance = $demand->getMaxDistance() ?: 999;

        $distanceSqlCalc = 'ACOS(SIN(RADIANS(' . $this->queryBuilder->quoteIdentifier('latitude') . ')) * SIN(RADIANS(' . $demand->getLatitude() . ')) + COS(RADIANS(' . $this->queryBuilder->quoteIdentifier('latitude') . ')) * COS(RADIANS(' . $demand->getLatitude() . ')) * COS(RADIANS(' . $this->queryBuilder->quoteIdentifier('longitude') . ') - RADIANS(' . $demand->getLongitude() . '))) * ' . $earthRadius;

        $this->queryBuilder->addSelectLiteral($distanceSqlCalc . ' AS `distance`');
        $this->queryBuilder->andWhere(
            $this->queryBuilder->expr()->comparison($distanceSqlCalc, ExpressionBuilder::LT, $maxDistance)
        );
        $this->queryBuilder->orderBy('distance');
    }

    /**
     * Change DefaultRestrictions to FrontendRestrictions in order to respect fe_group
     *
     * @param \Blueways\BwGuild\Domain\Model\Dto\BaseDemand $demand
     */
    private function setRestritions(BaseDemand $demand)
    {
        $this->queryBuilder->getRestrictions()
            ->add(GeneralUtility::makeInstance(FrontendGroupRestriction::class));
    }

    /**
     * @param \Blueways\BwGuild\Domain\Model\Dto\BaseDemand $demand
     * @throws \TYPO3\CMS\Core\Context\Exception\AspectNotFoundException
     */
    protected function setLanguageConstraint(BaseDemand $demand)
    {
        $languageAspect = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Context\Context::class)->getAspect('language');
        $sysLanguageUid = $languageAspect->getId();

        $this->queryBuilder->andWhere(
            $this->queryBuilder->expr()->eq(
                'sys_language_uid',
                $this->queryBuilder->createNamedParameter($sysLanguageUid, \PDO::PARAM_INT)
            )
        );
    }

    /**
     * @param array $settings
     * @param string $class
     * @return \Blueways\BwGuild\Domain\Model\Dto\BaseDemand
     * @deprecated
     */
    public function createDemandObjectFromSettings(
        array $settings,
        string $class = BaseDemand::class
    ): BaseDemand {
        // @TODO: check if this typoscript demandClass setting makes sense
        $class = isset($settings['demandClass']) && !empty($settings['demandClass']) ? $settings['demandClass'] : $class;

        /** @var \Blueways\BwGuild\Domain\Model\Dto\BaseDemand $demand */
        $demand = new $class();

        $demand->setCategories(GeneralUtility::trimExplode(',', $settings['categories'], true));
        $demand->setCategoryConjunction($settings['categoryConjunction'] ?? '');
        $demand->setIncludeSubCategories($settings['includeSubCategories'] ?? false);
        $demand->setOrder($settings['order'] ?? '');
        $demand->setOrderDirection($settings['orderDirection'] ?? '');
        $demand->setItemsPerPage((int)$settings['itemsPerPage']);

        if ($settings['limit']) {
            $demand->setLimit((int)$settings['limit']);
        }

        if ($settings['maxItems']) {
            $demand->setLimit((int)$settings['maxItems']);
        }

        return $demand;
    }
}
