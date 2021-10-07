<?php

namespace Blueways\BwGuild\Task;

use Blueways\BwGuild\Service\GeoService;

/**
 * Class to be called by the scheduler to
 * find geocoding coordinates for all fe_users records
 */
class GeocodingTask extends \TYPO3\CMS\Scheduler\Task\AbstractTask
{

    /**
     * Function executed from the Scheduler.
     */
    public function execute()
    {
        /** @var \Blueways\BwGuild\Service\GeoService $geocodingService */
        $geocodingService = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(GeoService::class);
        $geocodingService->calculateCoordinatesForAllRecordsInTable(
            'fe_users',
            'latitude',
            'longitude',
            'address',
            'zip',
            'city',
            'country'
        );
        $geocodingService->calculateCoordinatesForAllRecordsInTable(
            'tx_bwguild_domain_model_offer',
            'latitude',
            'longitude',
            'address',
            'zip',
            'city',
            'country'
        );
        return true;
    }
}
