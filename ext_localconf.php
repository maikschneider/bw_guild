<?php

defined('TYPO3_MODE') || die();

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
    'BwGuild',
    'Usershow',
    [
        \Blueways\BwGuild\Controller\UserController::class => 'show',
    ],
    []
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
    'BwGuild',
    'Userlist',
    [
        \Blueways\BwGuild\Controller\UserController::class => 'list, edit, update, new, search',
    ],
    [
        \Blueways\BwGuild\Controller\UserController::class => 'edit, update, list',
    ]
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
    'BwGuild',
    'Offerlist',
    [
        \Blueways\BwGuild\Controller\OfferController::class => 'list, show, edit, update, new, delete',
    ],
    // non-cacheable actions
    [
        \Blueways\BwGuild\Controller\OfferController::class => 'edit, update, delete, new',
    ]
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
    'BwGuild',
    'Offerlatest',
    [
        \Blueways\BwGuild\Controller\OfferController::class => 'latest',
    ],
    [
    ]
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
    'BwGuild',
    'Api',
    [
        \Blueways\BwGuild\Controller\ApiController::class => 'userinfo',
    ],
    [
        \Blueways\BwGuild\Controller\ApiController::class => 'userinfo',
    ]
);

// Define state cache, if not already defined
if (!isset($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['bwguild'])){
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['bwguild'] ??= [];
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['bwguild'] = [
        'frontend' => \TYPO3\CMS\Core\Cache\Frontend\VariableFrontend::class,
        'backend' => \TYPO3\CMS\Core\Cache\Backend\Typo3DatabaseBackend::class,
    ];
}

/* ===========================================================================
  Hooks
=========================================================================== */
// Add wizard with map for setting geo location
$GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['nodeRegistry'][1546531781] = [
    'nodeName' => 'locationMapWizard',
    'priority' => 30,
    'class' => \Blueways\BwGuild\FormEngine\FieldControl\LocationMapWizard::class
];

// Register geo coding task
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks'][\Blueways\BwGuild\Task\GeocodingTask::class] = [
    'extension' => 'bw_guild',
    'title' => 'Geocoding of fe_user & offer records',
    'description' => 'Check all fe_user and offer records for geocoding information and write them into the fields',
];

// Register evaluations for TCA
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tce']['formevals'][\Blueways\BwGuild\Evaluation\LatitudeEvaluation::class] = '';
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tce']['formevals'][\Blueways\BwGuild\Evaluation\LongitudeEvaluation::class] = '';

// Register hook to set sorting field
$GLOBALS ['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass']['bw_guild'] = 'Blueways\\BwGuild\\Hooks\\TCEmainHook';
$GLOBALS ['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processCmdmapClass']['bw_guild'] = 'Blueways\\BwGuild\\Hooks\\TCEmainHook';

// Register SlugUpdate Wizard
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/install']['update']['bwGuildSlugUpdater'] = Blueways\BwGuild\Updates\SlugUpdater::class;

// Register TypeConverter for logo upload via frontend
\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerTypeConverter('Blueways\\BwGuild\\Property\\TypeConverter\\UploadedFileReferenceConverter');
\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerTypeConverter('Blueways\\BwGuild\\Property\\TypeConverter\\ObjectStorageConverter');

// Register ke_search Hook
if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('ke_search')) {
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['ke_search']['registerIndexerConfiguration'][] =
        \Blueways\BwGuild\Hooks\OfferIndexer::class;
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['ke_search']['customIndexer'][] =
        \Blueways\BwGuild\Hooks\OfferIndexer::class;
}
