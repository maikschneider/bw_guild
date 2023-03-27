<?php

namespace Blueways\BwGuild\Domain\Repository;

use Blueways\BwGuild\Domain\Model\Dto\OfferDemand;

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
}
