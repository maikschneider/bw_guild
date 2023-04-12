<?php

namespace Blueways\BwGuild\Domain\Repository;

use Blueways\BwGuild\Domain\Model\Dto\OfferDemand;
use Doctrine\DBAL\DBALException;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class OfferRepository
 */
class OfferRepository extends AbstractDemandRepository
{
    public function getGroupedOffers()
    {
        $query = $this->createQuery();
        $query->getQuerySettings()->setIgnoreEnableFields(true);

        $allOffers = $query->execute();

        $offers = [];

        foreach ($allOffers as $offer) {
            $offers[$offer->getRecordType()][] = $offer;
        }

        return $offers;
    }

    public function setConstraints($demand): void
    {
        parent::setConstraints($demand);

        /** @var OfferDemand $demand */
        $this->setPublicOfferConstraint();
    }

    private function setPublicOfferConstraint(): void
    {
        $this->queryBuilder->andWhere(
            $this->queryBuilder->expr()->eq(
                'public',
                $this->queryBuilder->createNamedParameter(1, \PDO::PARAM_BOOL)
            )
        );
    }

    /**
     * @param int[] $sysFileReferenceUids
     * @throws DBALException
     */
    public function deleteImagesByUids(array $sysFileReferenceUids): void
    {
        if (!count($sysFileReferenceUids)) {
            return;
        }

        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getConnectionForTable('sys_file_reference')->createQueryBuilder();

        $queryBuilder->getRestrictions()->removeAll();
        $queryBuilder
            ->update('sys_file_reference')
            ->set('deleted', 1)
            ->where($queryBuilder->expr()->in('uid',
                $queryBuilder->quoteArrayBasedValueListToIntegerList($sysFileReferenceUids)))
            ->execute();
    }
}
