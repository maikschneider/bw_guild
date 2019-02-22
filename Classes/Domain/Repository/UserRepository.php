<?php

namespace Blueways\BwGuild\Domain\Repository;

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

}
