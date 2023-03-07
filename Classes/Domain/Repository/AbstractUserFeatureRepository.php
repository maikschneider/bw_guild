<?php

namespace Blueways\BwGuild\Domain\Repository;

use TYPO3\CMS\Extbase\Persistence\Repository;
use Blueways\BwGuild\Domain\Model\AbstractUserFeature;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

class AbstractUserFeatureRepository extends Repository
{
    public function getFeaturesGroupedByRecordType(): array
    {
        $query = $this->createQuery();
        $query->setQuerySettings($query->getQuerySettings()->setRespectStoragePage(false));
        $features = $query->execute()->toArray();
        $groupedFeatures = [];

        /** @var AbstractUserFeature $feature */
        foreach ($features as $feature) {
            $groupedFeatures[(int)$feature->getRecordType()] ??= new ObjectStorage();
            $groupedFeatures[(int)$feature->getRecordType()]->attach($feature);
        }

        return $groupedFeatures;
    }

    public function getFeaturesAsJsonGroupedByRecordType(): array
    {
        $groupedFeatures = $this->getFeaturesGroupedByRecordType();

        return array_map(function ($featureGroup) {
            $featureGroup = array_map(function ($feature) {
                return $feature->getApiOutputArray();
            }, [...$featureGroup]);

            return json_encode(array_values($featureGroup));
        }, $groupedFeatures);
    }
}
