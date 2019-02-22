<?php

namespace Blueways\BwGuild\Domain\Repository;

use Blueways\BwGuild\Domain\Model\Dto\BaseDemand;
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

        $constraints = $this->createConstraintsFromDemand($query, $demand);

        if (!empty($constraints)) {
            $query->matching(
                $query->logicalAnd($constraints)
            );
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
        $searchFields = $demand->getSearchFields();

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
     * @param \Blueways\BwGuild\Domain\Model\Dto\BaseDemand $demand
     * @return int
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     */
    public function countDemanded($demand)
    {
        $query = $this->createQuery();

        $constraints = $this->createConstraintsFromDemand($query, $demand);

        if (!empty($constraints)) {
            $query->matching(
                $query->logicalAnd($constraints)
            );
        }

        return $query->count();
    }

    /**
     * @param $settings
     * @param string $class
     * @return \Blueways\BwGuild\Domain\Model\Dto\BaseDemand|mixed
     */
    protected function createDemandObjectFromSettings(
        $settings,
        $class = 'Blueways\\BwGuild\\Domain\\Model\\Dto\\BaseDemand'
    ) {
        // @TODO check if this demandClass setting in typoscript makes sense
        $class = isset($settings['demandClass']) && !empty($settings['demandClass']) ? $settings['demandClass'] : $class;

        /** @var \Blueways\BwGuild\Domain\Model\Dto\BaseDemand $demand */
        $demand = $this->objectManager->get($class);

        $demand->setCategories(GeneralUtility::trimExplode(',', $settings['categories'], true));
        $demand->setCategoryConjunction($settings['categoryConjunction']);
        $demand->setIncludeSubCategories($settings['includeSubCategories']);

        return $demand;
    }
}
