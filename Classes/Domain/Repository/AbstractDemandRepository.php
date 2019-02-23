<?php

namespace Blueways\BwGuild\Domain\Repository;

use Blueways\BwGuild\Domain\Model\Dto\BaseDemand;
use ReflectionClass;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\Repository;

class AbstractDemandRepository extends Repository
{

    /**
     * @param \Blueways\BwGuild\Domain\Model\Dto\BaseDemand $demand
     * @return array|\TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     */
    public function findDemanded($demand)
    {
        $query = $this->createQuery();

        // filter constraints
        $constraints = $this->createConstraintsFromDemand($query, $demand);
        if (!empty($constraints)) {
            $query->matching(
                $query->logicalAnd($constraints)
            );
        }

        // orderings
        $orderings = $this->createOrderingsFromDemand($query, $demand);
        if ($orderings) {
            $query->setOrderings($orderings);
        }

        // limit
        $limit = $this->createLimitFromDemand($query, $demand);
        if ($limit != null) {
            $query->setLimit($limit);
        }

        return $query->execute();
    }

    /**
     * @param \TYPO3\CMS\Extbase\Persistence\QueryInterface $query
     * @param \Blueways\BwGuild\Domain\Model\Dto\BaseDemand $demand
     * @return array<\TYPO3\CMS\Extbase\Persistence\Generic\Qom\ConstraintInterface>
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     */
    protected function createConstraintsFromDemand(
        QueryInterface $query,
        \Blueways\BwGuild\Domain\Model\Dto\BaseDemand $demand
    ) {
        $constraints = [];

        // category filter
        if ($demand->getCategories() && $demand->getCategories() !== '0') {
            $constraints['categories'] = $this->createCategoryConstraint(
                $query,
                $demand->getCategories(),
                $demand->getCategoryConjunction(),
                $demand->isIncludeSubCategories()
            );
        }

        // string search
        $searchConstraint = $this->getSearchConstraint($query, $demand);
        if ($searchConstraint) {
            $constraints['search'] = $searchConstraint;
        }

        // Clean not used constraints
        foreach ($constraints as $key => $value) {
            if (null === $value) {
                unset($constraints[$key]);
            }
        }

        return $constraints;
    }

    /**
     * @param \TYPO3\CMS\Extbase\Persistence\QueryInterface $query
     * @param array|string $categories
     * @param string $conjunction
     * @param boolean $includeSubCategories
     * @return \TYPO3\CMS\Extbase\Persistence\Generic\Qom\AndInterface|\TYPO3\CMS\Extbase\Persistence\Generic\Qom\NotInterface|\TYPO3\CMS\Extbase\Persistence\Generic\Qom\OrInterface|null
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     */
    protected function createCategoryConstraint(
        QueryInterface $query,
        $categories,
        string $conjunction,
        bool $includeSubCategories
    ) {
        $constraint = null;
        $categoryConstraints = [];

        // If "ignore category selection" is used, nothing needs to be done
        if (empty($conjunction)) {
            return $constraint;
        }

        if (!is_array($categories)) {
            $categories = GeneralUtility::intExplode(',', $categories, true);
        }
        foreach ($categories as $category) {
            if ($includeSubCategories) {
                // @TODO see news extension
                // need to find child categories and add them to the constraint
            }
            $categoryConstraints[] = $query->contains('categories', $category);
        }

        if ($categoryConstraints) {
            switch (strtolower($conjunction)) {
                case 'or':
                    $constraint = $query->logicalOr($categoryConstraints);
                    break;
                case 'notor':
                    $constraint = $query->logicalNot($query->logicalOr($categoryConstraints));
                    break;
                case 'notand':
                    $constraint = $query->logicalNot($query->logicalAnd($categoryConstraints));
                    break;
                case 'and':
                default:
                    $constraint = $query->logicalAnd($categoryConstraints);
            }
        }

        return $constraint;
    }

    /**
     * @param \TYPO3\CMS\Extbase\Persistence\QueryInterface $query
     * @param \Blueways\BwGuild\Domain\Model\Dto\BaseDemand
     * @return \TYPO3\CMS\Extbase\Persistence\Generic\Qom\OrInterface|null
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     */
    protected function getSearchConstraint(QueryInterface $query, BaseDemand $demand)
    {
        $constraint = null;

        // abort if empty search string
        if (empty($demand->getSearch())) {
            return $constraint;
        }

        $searchConstraints = [];

        $searchSplitted = str_getcsv($demand->getSearch(), ' ');
        $searchFields = $this->getObjectSearchFields($demand);

        foreach ($searchFields as $cleanProperty) {

            $subConstraints = [];

            foreach ($searchSplitted as $searchSplittedPart) {
                $searchSplittedPart = trim($searchSplittedPart);
                if ($searchSplittedPart) {
                    $subConstraints[] = $query->like($cleanProperty, '%' . $searchSplittedPart . '%');
                }
            }
            $searchConstraints[] = $query->logicalOr($subConstraints);
        }

        if (sizeof($searchConstraints)) {
            $constraint = $query->logicalOr($searchConstraints);
        }

        return $constraint;
    }

    /**
     * @param BaseDemand $demand
     * @return array
     */
    private function getObjectSearchFields($demand)
    {
        // get and merge exclude fields
        $excludeFields = GeneralUtility::trimExplode(',', $demand::EXCLUDE_FIELDS, true);
        $excludeSearchFields = GeneralUtility::trimExplode(',', $demand->getExcludeSearchFields(), true);
        $excludeSearchFields = array_merge($excludeSearchFields, $excludeFields);

        // use reflection class to get all properties of object (e.g. User, Offer,..)
        $reflectionClass = $this->objectManager->get(ReflectionClass::class, $this->objectType);
        $searchFields = $reflectionClass->getProperties();

        // map ReflectionProperty to normal array
        $searchFields = array_map(function ($field) {
            return $field->name;
        }, $searchFields);

        // remove all fields that are excluded by constant or exclude property
        $searchFields = array_filter($searchFields, function ($obj) use ($excludeSearchFields) {
            return !in_array($obj, $excludeSearchFields);
        });

        return $searchFields;
    }

    /**
     * @param \Blueways\BwGuild\Domain\Model\Dto\BaseDemand $demand
     * @return int
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     */
    public function countDemanded($demand)
    {
        $query = $this->createQuery();

        // filter constraints
        $constraints = $this->createConstraintsFromDemand($query, $demand);
        if (!empty($constraints)) {
            $query->matching(
                $query->logicalAnd($constraints)
            );
        }

        // limit
        if ($demand->getLimit() != null) {
            $query->setLimit((int)$demand->getLimit());
        }

        return $query->count();
    }

    /**
     * Create Demand by settings array (see typoscript constants)
     *
     * @param $settings
     * @param string $class
     * @return \Blueways\BwGuild\Domain\Model\Dto\BaseDemand|mixed
     */
    public function createDemandObjectFromSettings(
        $settings,
        $class = 'Blueways\\BwGuild\\Domain\\Model\\Dto\\BaseDemand'
    ) {
        // @TODO: check if this typoscript demandClass setting makes sense
        $class = isset($settings['demandClass']) && !empty($settings['demandClass']) ? $settings['demandClass'] : $class;

        /** @var \Blueways\BwGuild\Domain\Model\Dto\BaseDemand $demand */
        $demand = $this->objectManager->get($class);

        $demand->setCategories(GeneralUtility::trimExplode(',', $settings['categories'], true));
        $demand->setCategoryConjunction($settings['categoryConjunction'] ?? '');
        $demand->setIncludeSubCategories($settings['includeSubCategories'] ?? false);
        $demand->setLimit($settings['limit'] ?? -1);
        $demand->setOrder($settings['order'] ?? '');

        return $demand;
    }

    /**
     * Create order constraints
     *
     * @TODO: use order field to hold comma separated list of order fields
     * @TODO: create new flexform setting for asc/desc
     *
     * @param \TYPO3\CMS\Extbase\Persistence\QueryInterface $query
     * @param \Blueways\BwGuild\Domain\Model\Dto\BaseDemand $demand
     * @return array
     */
    protected function createOrderingsFromDemand(QueryInterface $query, BaseDemand $demand)
    {
        // default ordering displays newest entry first
        $orderings = [];

        if(!$demand->getOrder() || $demand->getOrder() === '') {
            $orderings['crdate'] = QueryInterface::ORDER_ASCENDING;
        }

        return $orderings;
    }

    /**
     * @param \TYPO3\CMS\Extbase\Persistence\QueryInterface $query
     * @param \Blueways\BwGuild\Domain\Model\Dto\BaseDemand $demand
     * @return int|null
     */
    protected function createLimitFromDemand(QueryInterface $query, BaseDemand $demand)
    {
        $limit = null;

        if($demand->getLimit() && $demand->getLimit() !== '' && (int)$demand->getLimit() > 0) {
            $limit = (int)$demand->getLimit();
        }

        return $limit;
    }
}
