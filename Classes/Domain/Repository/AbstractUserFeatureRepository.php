<?php

namespace Blueways\BwGuild\Domain\Repository;

class AbstractUserFeatureRepository extends \TYPO3\CMS\Extbase\Persistence\Repository
{
    public function getFeaturesAsJsonGroupedByRecordType(): array
    {
        $query = $this->createQuery();
        $query->setQuerySettings($query->getQuerySettings()->setRespectStoragePage(false));
        $features = $query->execute()->toArray();
        $groupedFeatures = [];

        /** @var \Blueways\BwGuild\Domain\Model\AbstractUserFeature $feature */
        foreach ($features as $feature) {
            $groupedFeatures[(int)$feature->getRecordType()] ??= [];
            $groupedFeatures[(int)$feature->getRecordType()][] = $feature->getApiOutputArray();
        }

        return array_map(function ($featureGroup) {
            return json_encode($featureGroup);
        }, $groupedFeatures);
    }
}
