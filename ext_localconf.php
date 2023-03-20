<?php

defined('TYPO3') || die();

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
        \Blueways\BwGuild\Controller\UserController::class => 'list, edit, update',
    ]
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
    'BwGuild',
    'Usersearch',
    [
        \Blueways\BwGuild\Controller\UserController::class => 'search',
    ],
    [
        \Blueways\BwGuild\Controller\UserController::class => 'search',
    ]
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
    'BwGuild',
    'Offerlist',
    [
        \Blueways\BwGuild\Controller\OfferController::class => 'list, edit, update, new, delete',
    ],
    // non-cacheable actions
    [
        \Blueways\BwGuild\Controller\OfferController::class => 'edit, update, delete, new',
    ]
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
    'BwGuild',
    'Offershow',
    [
        \Blueways\BwGuild\Controller\OfferController::class => 'show',
    ],
    []
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
        \Blueways\BwGuild\Controller\ApiController::class => 'userinfo,bookmark,userEditForm,userEditUpdate,offerEditForm,offerEditUpdate,offerDelete',
    ],
    [
        \Blueways\BwGuild\Controller\ApiController::class => 'userinfo,bookmark,userEditForm,userEditUpdate,offerEditForm,offerEditUpdate,offerDelete',
    ]
);

// Define state cache, if not already defined
if (!is_array($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['bwguild'])) {
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['bwguild'] = [
        'frontend' => \TYPO3\CMS\Core\Cache\Frontend\VariableFrontend::class,
        'backend' => \TYPO3\CMS\Core\Cache\Backend\Typo3DatabaseBackend::class,
    ];
}

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

// Register Extbase Controller Argument override to change the dataType
$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][\TYPO3\CMS\Extbase\Mvc\Controller\Argument::class] = [
    'className' => Blueways\BwGuild\Extbase\Controller\Argument::class,
];
\TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\Object\Container\Container::class)
    ->registerImplementation(
        TYPO3\CMS\Extbase\Mvc\Controller\Argument::class,
        \Blueways\BwGuild\Extbase\Controller\Argument::class
    );
