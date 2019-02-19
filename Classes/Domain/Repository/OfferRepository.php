<?php

namespace Blueways\BwGuild\Domain\Repository;

use TYPO3\CMS\Extbase\Persistence\Repository;

/**
 * Class OfferRepository
 *
 * @package Blueways\BwGuild\Domain\Repository
 */
class OfferRepository extends Repository
{

    public function getGroupedOffers()
    {
        $allOffers = $this->createQuery()->execute();

        $offers = [];

        foreach ($allOffers as $offer) {
            $offers[$offer->getRecordType()][] = $offer;
        }

        return $offers;
    }
}
