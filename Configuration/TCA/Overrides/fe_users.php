<?php

use TYPO3\CMS\Core\Resource\AbstractFile;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

defined('TYPO3') || die();

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
                'eval' => 'trim',
            ],
        ],
        'mobile' => [
            'exclude' => false,
            'label' => 'LLL:EXT:bw_guild/Resources/Private/Language/locallang_tca.xlf:user.mobile',
            'config' => [
                'type' => 'input',
                'size' => 20,
                'eval' => 'trim',
            ],
        ],
        'member_nr' => [
            'exclude' => false,
            'label' => 'LLL:EXT:bw_guild/Resources/Private/Language/locallang_tca.xlf:user.member_nr',
            'config' => [
                'type' => 'input',
                'size' => 20,
                'eval' => 'trim',
            ],
        ],
        'offers' => [
            'exclude' => false,
            'label' => 'LLL:EXT:bw_guild/Resources/Private/Language/locallang_tca.xlf:user.offers',
            'config' => [
                'type' => 'inline',
                'foreign_table' => 'tx_bwguild_domain_model_offer',
                'foreign_field' => 'fe_user',
            ],
        ],
        'features' => [
            'exclude' => false,
            'label' => 'LLL:EXT:bw_guild/Resources/Private/Language/locallang_tca.xlf:user.features',
            'config' => [
                'type' => 'group',
                'allowed' => 'tx_bwguild_domain_model_feature',
                'foreign_table' => 'tx_bwguild_domain_model_feature',
                'MM' => 'tx_bwguild_feature_feuser_mm',
                'MM_opposite_field' => 'features',
                'size' => 10,
                'maxitems' => 9999,
                'eval' => 'int',
                'default' => 0,
                'suggestOptions' => [
                    'tx_bwguild_domain_model_feature' => [
                        'searchWholePhrase' => 1,
                        'additionalSearchFields' => 'name',
                    ],
                ],
                'fieldControl' => [
                    'addRecord' => [
                        'disabled' => false,
                    ],
                ],
            ],
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
                'type' => 'passthrough',
            ],
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
            ],
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
                    'overrideChildTca' => [
                        'types' => [
                            AbstractFile::FILETYPE_IMAGE => [
                                'showitem' => '--palette--;;imageoverlayPalette, --palette--;;filePalette',
                            ],
                        ],
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
                'allowed' => 'pages,fe_users,sys_file',
                'minitems' => 0,
                'size' => 5,
            ],
        ],
    ];
    ExtensionManagementUtility::addTCAcolumns('fe_users', $tempColumns);
    ExtensionManagementUtility::addToAllTCAtypes('fe_users', 'short_name', '', 'after:company');
    ExtensionManagementUtility::addToAllTCAtypes('fe_users', 'mobile', '', 'after:telephone');
    ExtensionManagementUtility::addToAllTCAtypes('fe_users', 'member_nr', '', 'before:company');
    ExtensionManagementUtility::addToAllTCAtypes('fe_users', 'latitude', '', 'before:company');
    ExtensionManagementUtility::addToAllTCAtypes('fe_users', 'longitude', '', 'before:company');
    ExtensionManagementUtility::addToAllTCAtypes('fe_users', 'slug', '', 'after:image');
    ExtensionManagementUtility::addToAllTCAtypes('fe_users', 'public_profile', '', 'after:password');
    ExtensionManagementUtility::addToAllTCAtypes('fe_users', 'logo', '', 'after:image');
    ExtensionManagementUtility::addToAllTCAtypes('fe_users', 'bookmarks', '', 'after:slug');
    ExtensionManagementUtility::addToAllTCAtypes('fe_users', 'features', '', 'after:bookmarks');
    ExtensionManagementUtility::addToAllTCAtypes(
        'fe_users',
        '--div--;LLL:EXT:bw_guild/Resources/Private/Language/locallang_tca.xlf:user.offers,offers',
        '',
        'after:description'
    );

    /**
     * Connect to sys_categories
     */
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::makeCategorizable(
        'bw_guild',
        'fe_users'
    );
});
