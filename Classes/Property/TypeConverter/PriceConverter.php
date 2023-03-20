<?php

namespace Blueways\BwGuild\Property\TypeConverter;

use Error;
use TYPO3\CMS\Extbase\Property\PropertyMappingConfigurationInterface;
use TYPO3\CMS\Extbase\Property\TypeConverter\FloatConverter;

class PriceConverter extends FloatConverter
{
    public function convertFrom($source, string $targetType, array $convertedChildProperties = [], PropertyMappingConfigurationInterface $configuration = null)
    {
        if ($source !== null && (string)$source === '') {
            $source = 0.0;
        }

        return parent::convertFrom($source, $targetType, $convertedChildProperties, $configuration);
    }
}
