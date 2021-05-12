<?php

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

defined('TYPO3_MODE') || die();

call_user_func(function () {

    /**
     * Default PageTS for BwGuild
     */
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::registerPageTSConfigFile(
        'bw_guild',
        'Configuration/TSconfig/Page.tsconfig',
        'Bw Guild PageTS'
    );

});
