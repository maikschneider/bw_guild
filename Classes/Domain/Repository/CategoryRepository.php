<?php

namespace Blueways\BwGuild\Domain\Repository;

use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;
use TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException;
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
}
