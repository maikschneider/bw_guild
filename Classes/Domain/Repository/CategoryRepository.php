<?php

namespace Blueways\BwGuild\Domain\Repository;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;

class CategoryRepository extends \TYPO3\CMS\Extbase\Domain\Repository\CategoryRepository
{
    /**
     * @param $categoryList
     * @return array|QueryResultInterface
     * @throws InvalidQueryException
     */
    public function findFromUidList($categoryList)
    {
        $query = $this->createQuery();
        $query->matching(
            $query->in('uid', explode(',', $categoryList))
        );
        return $query->execute();
    }

    /**
     * @param $categoryList
     * @return array|QueryResultInterface
     * @throws InvalidQueryException
     */
    public function findFromUidListNot($categoryList)
    {
        $query = $this->createQuery();
        $query->matching(
            $query->logicalNot(
                $query->in('uid', explode(',', $categoryList))
            )
        );
        return $query->execute();
    }

    /**
     * @throws InvalidQueryException
     */
    public function findCategoriesFromSettings(
        string $categories = '',
        string $includeSubCategories = '0',
        $categoryConjunction = ''
    ): QueryResultInterface|array {
        $categorieUids = GeneralUtility::intExplode(',', $categories, true);
        $includeSubCategories = filter_var($includeSubCategories, FILTER_VALIDATE_BOOLEAN);
        $query = $this->createQuery();

        if (!empty($categories)) {
            $constraint = $query->in('uid', $categorieUids);
        }

        if ($includeSubCategories) {
            $constraint = $query->in('parent', $categorieUids);
        }

        if (isset($constraint)) {
            if ($categoryConjunction === 'notor' || $categoryConjunction === 'notand') {
                $constraint = $query->logicalNot($constraint);
            }

            $query->matching($constraint);
        }

        $query->setOrderings(['sorting' => \TYPO3\CMS\Extbase\Persistence\QueryInterface::ORDER_ASCENDING]);

        return $query->execute();
    }
}
