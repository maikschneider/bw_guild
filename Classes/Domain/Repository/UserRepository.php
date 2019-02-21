<?php

namespace Blueways\BwGuild\Domain\Repository;

use Blueways\BwGuild\Utility\DemandUtility;
use TYPO3\CMS\Extbase\Persistence\Repository;

/**
 * Class UserRepository
 *
 * @package Blueways\BwGuild\Domain\Repository
 */
class UserRepository extends Repository
{

    public function getUsernames()
    {
        $query = $this->createQuery();
        $query->statement("SELECT username from fe_users");
        $query->setQuerySettings($query->getQuerySettings()->setRespectStoragePage(false));

        return $query->execute(true);
    }

    /**
     * @param \Blueways\BwGuild\Domain\Model\Dto\BaseDemand $demand
     * @return array|\TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     */
    public function findDemanded($demand)
    {
        $query = $this->createQuery();

        $demandUtility = $this->objectManager->get(DemandUtility::class);
        $constraints = $demandUtility->createConstraintsFromDemand($query, $demand);

        if (!empty($constraints)) {
            $query->matching(
                $query->logicalAnd($constraints)
            );
        }

        return $query->execute();
    }

}
