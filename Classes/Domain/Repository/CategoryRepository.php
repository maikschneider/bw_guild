<?php

namespace Blueways\BwGuild\Domain\Repository;

class CategoryRepository extends \TYPO3\CMS\Extbase\Domain\Repository\CategoryRepository
{
    /**
     * @param $categoryList
     * @return array|\TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
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
     * @return array|\TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
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
}
