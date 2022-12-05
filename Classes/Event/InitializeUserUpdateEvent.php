<?php

namespace Blueways\BwGuild\Event;

use TYPO3\CMS\Extbase\Mvc\Controller\MvcPropertyMappingConfiguration;

final class InitializeUserUpdateEvent
{
    private MvcPropertyMappingConfiguration $propertyMappingConfiguration;

    public function __construct(MvcPropertyMappingConfiguration $propertyMappingConfiguration)
    {
        $this->propertyMappingConfiguration = $propertyMappingConfiguration;
    }

    public function getPropertyMappingConfiguration(): MvcPropertyMappingConfiguration
    {
        return $this->propertyMappingConfiguration;
    }
}
