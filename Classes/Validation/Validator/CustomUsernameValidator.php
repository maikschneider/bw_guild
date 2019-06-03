<?php

namespace Blueways\BwGuild\Validation\Validator;

use Blueways\BwGuild\Domain\Model\User;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;

class CustomUsernameValidator extends \TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator
{

    protected function isValid($value)
    {
        /** @var User $user */
        $user = $value;
        $settings = $this->getTyposcriptSettings();

        if ($settings['useEmailAsUsername'] === '1') {
            if (!$user->getEmail() || strlen($user->getEmail()) < 3) {
                $this->addError('Email is not valid', 1559595750);
            }
        } else {
            if(!$user->getUsername() || strlen($user->getUsername()) < 3) {
                $this->addError('Username is not valid', 1559595750);
            }
        }

        return true;
    }

    /**
     * Merges the typoscript settings with the settings from flexform
     */
    private function getTyposcriptSettings()
    {
        $settings = [];
        $configurationManager = GeneralUtility::makeInstance(ConfigurationManager::class);
        try {
            $typoscript = $configurationManager->getConfiguration(ConfigurationManager::CONFIGURATION_TYPE_FULL_TYPOSCRIPT);
            ArrayUtility::mergeRecursiveWithOverrule($typoscript['plugin.']['tx_bwguild_userlist.']['settings.'],
                $typoscript['plugin.']['tx_bwguild.']['settings.'], true, false, false);
            ArrayUtility::mergeRecursiveWithOverrule($typoscript['plugin.']['tx_bwguild_userlist.']['settings.'],
                $settings, true, false, false);
            $settings = $typoscript['plugin.']['tx_bwguild_userlist.']['settings.'];
            return $settings;
        } catch (\TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException $exception) {
        }
    }

}
