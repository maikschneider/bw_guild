<?php

defined('TYPO3_MODE') || die();

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
    'Blueways.BwGuild',
    'Userlist',
    [
        'User' => 'list, show, edit, update',
    ],
    // non-cacheable actions
    [
    ]
);
