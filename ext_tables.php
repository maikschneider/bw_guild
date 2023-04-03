<?php

defined('TYPO3') || die();

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
    'BwGuild',
    'web',
    'tx_bwguild_admin',
    'bottom',
    [
        \Blueways\BwGuild\Controller\BackendController::class => 'offer,index'
    ],
    [
        'access' => 'admin',
        'icon' => 'EXT:bw_guild/Resources/Public/Images/module_administration.svg',
        'labels' => 'LLL:EXT:bw_guild/Resources/Private/Language/locallang_be.xlf:tx_bwguild_admin',
        'navigationComponentId' => '',
        'inheritNavigationComponentFromMainModule' => false,
    ]
);
