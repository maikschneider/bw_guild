<?php

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

defined('TYPO3_MODE') || die();

call_user_func(function () {

    /**
     * Default PageTS for BwRundfunkgruppeBase
     */
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::registerPageTSConfigFile(
        'bw_guild',
        'Configuration/PageTS/All.txt',
        'Bw Guild PageTS'
    );
    
});