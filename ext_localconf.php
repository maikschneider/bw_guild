<?php

defined('TYPO3_MODE') || die();

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
    'Blueways.BwGuild',
    'Userlist',
    [
        'User' => 'list, show, edit, update, new',
    ],
    // non-cacheable actions
    [
    ]
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
    'Blueways.BwGuild',
    'Offerlist',
    [
        'Offer' => 'list, show, edit, update, new, delete',
    ],
    // non-cacheable actions
    [
    ]
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::makeCategorizable(
    'bw_guild',
    'fe_users'
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::makeCategorizable(
    'bw_guild',
    'tx_bwguild_domain_model_offer'
);

// Define state cache, if not already defined
if (!is_array($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['bwguild'])) {
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['bwguild'] = [
        'frontend' => \TYPO3\CMS\Core\Cache\Frontend\VariableFrontend::class,
        'backend' => \TYPO3\CMS\Core\Cache\Backend\Typo3DatabaseBackend::class,
    ];
}

// Register geo coding task
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks'][\Blueways\BwGuild\Task\GeocodingTask::class] = [
    'extension' => 'bw_guild',
    'title' => 'Geocoding of fe_user records',
    'description' => 'Check all fe_user records for geocoding information and write them into the fields'
];
