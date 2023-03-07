<?php

defined('TYPO3') || die();

call_user_func(function () {
    /**
     * TypoScript Tempalte
     */
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile(
        'bw_guild',
        'Configuration/TypoScript',
        'Bw Guild Template'
    );
});
