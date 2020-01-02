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

    public function setConstraints($demand): void
    {
        parent::setConstraints($demand);

        $this->setPublicProfileConstraint();
    }

    private function setPublicProfileConstraint(): void
    {
        $this->queryBuilder->andWhere(
            $this->queryBuilder->expr()->eq('public_profile', $this->queryBuilder->createNamedParameter(1, \PDO::PARAM_BOOL))
        );
    }

    public function getUsernames()
    {
        $query = $this->createQuery();
        $query->statement("SELECT username from fe_users");
        $query->setQuerySettings($query->getQuerySettings()->setRespectStoragePage(false));

        return $query->execute(true);
    }

}
