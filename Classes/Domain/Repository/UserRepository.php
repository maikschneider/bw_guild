<?php

namespace Blueways\BwGuild\Domain\Repository;

use Blueways\BwGuild\Domain\Model\Dto\BaseDemand;
use Blueways\BwGuild\Domain\Model\User;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapper;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;

/**
 * Class UserRepository
 *
 * @package Blueways\BwGuild\Domain\Repository
 */
class UserRepository extends AbstractDemandRepository
{

    public function findDemanded($demand)
    {
        $result = parent::findDemanded($demand);

        $dataMapper = $this->objectManager->get(DataMapper::class);

        return $dataMapper->map(
            User::class,
            $result
        );
    }

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
        $orderings = parent::createOrderingsFromDemand($query, $demand);

        if (!$demand->getOrder() || $demand->getOrder() === '') {
            $orderings['company'] = QueryInterface::ORDER_ASCENDING;
        }

        return $orderings;
    }

}
