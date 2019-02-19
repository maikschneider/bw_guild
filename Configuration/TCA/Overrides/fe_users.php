<?php

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

defined('TYPO3_MODE') || die();

call_user_func(function () {

    /**
     * Register new fields
     */
    $tempColumns = [
        'short_name' => [
            'exclude' => false,
            'label' => 'LLL:EXT:bw_guild/Resources/Private/Language/locallang_tca.xlf:user.short_name',
            'config' => [
                'type' => 'input',
                'size' => 20,
                'eval' => 'trim'
            ],
        ],
        'mobile' => [
            'exclude' => false,
            'label' => 'LLL:EXT:bw_guild/Resources/Private/Language/locallang_tca.xlf:user.mobile',
            'config' => [
                'type' => 'input',
                'size' => 20,
                'eval' => 'trim'
            ],
        ],
        'member_nr' => [
            'exclude' => false,
            'label' => 'LLL:EXT:bw_guild/Resources/Private/Language/locallang_tca.xlf:user.member_nr',
            'config' => [
                'type' => 'input',
                'size' => 20,
                'eval' => 'trim'
            ],
        ],
        'offers' => [
            'exclude' => false,
            'label' => 'LLL:EXT:bw_guild/Resources/Private/Language/locallang_tca.xlf:user.offers',
            'config' => [
                'type' => 'inline',
                'foreign_table' => 'tx_bwguild_domain_model_offer',
                'foreign_field' => 'fe_user'
            ]
        ]
    ];
    ExtensionManagementUtility::addTCAcolumns('fe_users', $tempColumns);
    ExtensionManagementUtility::addToAllTCAtypes('fe_users', 'short_name', '', 'after:company');
    ExtensionManagementUtility::addToAllTCAtypes('fe_users', 'mobile', '', 'after:telephone');
    ExtensionManagementUtility::addToAllTCAtypes('fe_users', 'member_nr', '', 'before:company');
    ExtensionManagementUtility::addToAllTCAtypes('fe_users', '--div--;LLL:EXT:bw_guild/Resources/Private/Language/locallang_tca.xlf:user.offers,offers', '', 'after:description');

    $GLOBALS['TCA']['fe_users']['ctrl']['label'] = 'company';
    $GLOBALS['TCA']['fe_users']['ctrl']['label_userFunc'] = 'Blueways\\BwGuild\\Utility\\LabelUtility->feUserLabel';
    /* @TODO: organize fields in paletts
     * $GLOBALS['TCA']['fe_users']['palettes'][] = [
     * 'label' => 'LLL:EXT:bw_guild/Resources/Private/Language/locallang.xlf:user.palette.contactPerson',
     * 'showitem' => 'first_name, last_name'
     * ];
     * */
});
