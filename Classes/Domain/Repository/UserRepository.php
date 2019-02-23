<?php

namespace Blueways\BwGuild\Domain\Repository;

use Blueways\BwGuild\Domain\Model\Dto\BaseDemand;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;

/**
 * Class UserRepository
 *
 * @package Blueways\BwGuild\Domain\Repository
 */
class UserRepository extends AbstractDemandRepository
{

    public function getUsernames()
    {
        $query = $this->createQuery();
        $query->statement("SELECT username from fe_users");
        $query->setQuerySettings($query->getQuerySettings()->setRespectStoragePage(false));

        return $query->execute(true);
    }

    /**
     * Set default ordering to order by company name
     *
     * @param \TYPO3\CMS\Extbase\Persistence\QueryInterface $query
     * @param \Blueways\BwGuild\Domain\Model\Dto\BaseDemand $demand
     * @return array
     */
    protected function createOrderingsFromDemand(QueryInterface $query, BaseDemand $demand)
    {
        $orderings = [];

        if (!$demand->getOrder() || $demand->getOrder() === '') {
            $orderings['company'] = QueryInterface::ORDER_ASCENDING;
        }

        return $orderings;
    }

}
