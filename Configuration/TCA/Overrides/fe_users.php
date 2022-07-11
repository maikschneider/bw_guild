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
        ],
        'latitude' => [
            'exclude' => false,
            'label' => 'LAT',
            'config' => [
                'type' => 'passthrough',
            ],
        ],
        'longitude' => [
            'exclude' => false,
            'label' => 'LONG',
            'config' => [
                'type' => 'passthrough'
            ],
        ],
        'sorting_field' => [
            'exclude' => false,
            'label' => 'LLL:EXT:bw_guild/Resources/Private/Language/locallang_tca.xlf:user.sorting_field',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    ['LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.company', 'company'],
                    ['LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.name', 'name'],
                    ['LLL:EXT:bw_guild/Resources/Private/Language/locallang_tca.xlf:user.short_name', 'short_name'],
                    ['LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.first_name', 'first_name'],
                    ['LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.middle_name', 'middle_name'],
                    ['LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.last_name', 'last_name'],
                    ['LLL:EXT:bw_guild/Resources/Private/Language/locallang_tca.xlf:user.member_nr', 'member_nr'],
                    ['LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.title', 'title'],
                    ['LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.email', 'email'],

                ]
            ]
        ],
        'sorting_text' => [
            'exclude' => false,
            'label' => 'SORTING TEXT',
            'config' => [
                'type' => 'passthrough'
            ]
        ],
        'slug' => [
            'label' => 'LLL:EXT:bw_guild/Resources/Private/Language/locallang_tca.xlf:user.slug',
            'exclude' => 1,
            'config' => [
                'type' => 'slug',
                'generatorOptions' => [
                    'fields' => ['company', 'name'],
                    'fieldSeparator' => '-',
                    'prefixParentPageSlug' => true,
                    'replacements' => [
                        '/' => '',
                    ],
                ],
                'fallbackCharacter' => '-',
                'eval' => 'uniqueInSite',
            ],
        ],
        'public_profile' => [
            'label' => 'LLL:EXT:bw_guild/Resources/Private/Language/locallang_tca.xlf:user.publicProfile',
            'exclude' => 1,
            'config' => [
                'type' => 'check',
                'default' => 1,
            ]
        ],
        'logo' => [
            'label' => 'LLL:EXT:bw_guild/Resources/Private/Language/locallang_tca.xlf:user.logo',
            'exclude' => 1,
            'config' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::getFileFieldTCAConfig(
                'logo',
                [
                    'minitems' => 0,
                    'maxitems' => 1,
                    'foreign_match_fields' => [
                        'fieldname' => 'logo',
                        'tablenames' => 'fe_users',
                        'table_local' => 'sys_file',
                    ],
                ],
                $GLOBALS['TYPO3_CONF_VARS']['GFX']['imagefile_ext']
            ),
        ],
        'bookmarks' => [
            'label' => 'Bookmarks',
            'exclude' => 1,
            'config' => [
                'type' => 'group',
                'internal_type' => 'db',
                'allowed' => 'pages,fe_users,sys_file',
                'minitems' => 0,
                'size' => 5,
            ],
        ],
    ];
    ExtensionManagementUtility::addTCAcolumns('fe_users', $tempColumns);
    ExtensionManagementUtility::addToAllTCAtypes('fe_users', 'short_name', '', 'after:company');
    ExtensionManagementUtility::addToAllTCAtypes('fe_users', 'sorting_field', '', 'after:last_name');
    ExtensionManagementUtility::addToAllTCAtypes('fe_users', 'mobile', '', 'after:telephone');
    ExtensionManagementUtility::addToAllTCAtypes('fe_users', 'member_nr', '', 'before:company');
    ExtensionManagementUtility::addToAllTCAtypes('fe_users', 'latitude', '', 'before:company');
    ExtensionManagementUtility::addToAllTCAtypes('fe_users', 'longitude', '', 'before:company');
    ExtensionManagementUtility::addToAllTCAtypes('fe_users', 'slug', '', 'after:image');
    ExtensionManagementUtility::addToAllTCAtypes('fe_users', 'public_profile', '', 'after:password');
    ExtensionManagementUtility::addToAllTCAtypes('fe_users', 'logo', '', 'after:image');
    ExtensionManagementUtility::addToAllTCAtypes('fe_users', 'bookmarks', '', 'after:slug');
    ExtensionManagementUtility::addToAllTCAtypes('fe_users',
        '--div--;LLL:EXT:bw_guild/Resources/Private/Language/locallang_tca.xlf:user.offers,offers', '',
        'after:description');

    /**
     * Connect to sys_categories
     */
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::makeCategorizable(
        'bw_guild',
        'fe_users'
    );
});
