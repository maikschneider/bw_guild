<?php

namespace Blueways\BwGuild\Utility;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;

class DemandUtility
{

    /**
     * @param $settings
     * @param string $class
     * @return \Blueways\BwGuild\Domain\Model\Dto\BaseDemand
     */
    public static function createDemandObjectFromSettings(
        $settings,
        $class = 'Blueways\\BwGuild\\Domain\\Model\\Dto\\UserDemand'
    ) {

        $class = isset($settings['demandClass']) && !empty($settings['demandClass']) ? $settings['demandClass'] : $class;

        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        /** @var \Blueways\BwGuild\Domain\Model\Dto\BaseDemand $demand */
        $demand = $objectManager->get($class);

        $demand->setCategories(GeneralUtility::trimExplode(',', $settings['categories'], true));
        $demand->setCategoryConjunction($settings['categoryConjunction']);
        $demand->setIncludeSubCategories($settings['includeSubCategories']);

        return $demand;
    }

    /**
     * @param \TYPO3\CMS\Extbase\Persistence\QueryInterface $query
     * @param \Blueways\BwGuild\Domain\Model\Dto\BaseDemand $demand
     * @return \Blueways\BwGuild\Domain\Model\Dto\BaseDemand
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     */
    public function createConstraintsFromDemand(
        QueryInterface $query,
        \Blueways\BwGuild\Domain\Model\Dto\BaseDemand $demand
    ) {
        /** @var \Blueways\BwGuild\Domain\Model\Dto\BaseDemand $constraints */
        $constraints = [];

        if ($demand->getCategories() && $demand->getCategories() !== '0') {
            $constraints['categories'] = $this->createCategoryConstraint(
                $query,
                $demand->getCategories(),
                $demand->getCategoryConjunction(),
                $demand->isIncludeSubCategories()
            );
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
    private function createCategoryConstraint(
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
}
