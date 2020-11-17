<?php

namespace Blueways\BwGuild\Domain\Repository;

use Blueways\BwGuild\Domain\Model\Dto\BaseDemand;
use Blueways\BwGuild\Domain\Model\User;
use PDO;
use TYPO3\CMS\Core\Database\ConnectionPool;
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


    public function deleteAllUserLogos(int $userId)
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getConnectionForTable('sys_file_reference')->createQueryBuilder();

        $queryBuilder->getRestrictions()->removeAll();
        $queryBuilder
            ->update('sys_file_reference')
            ->set('deleted', 1)
            ->where(
                $queryBuilder->expr()->andX(
                    $queryBuilder->expr()->eq('tablenames', $queryBuilder->createNamedParameter('fe_users', PDO::PARAM_STR)),
                    $queryBuilder->expr()->eq('fieldname', $queryBuilder->createNamedParameter('logo', PDO::PARAM_STR)),
                    $queryBuilder->expr()->eq('uid_foreign', $queryBuilder->createNamedParameter($userId, PDO::PARAM_INT))
                )
            );

        return $queryBuilder->execute();

    }

    /**
     * Override this constraint since fe_users aren't localized
     *
     * @param \Blueways\BwGuild\Domain\Model\Dto\BaseDemand $demand
     */
    protected function setLanguageConstraint(BaseDemand $demand) {

    }

}
