<?php

return [
    'ctrl' => [
        'title' => 'LLL:EXT:bw_guild/Resources/Private/Language/locallang_tca.xlf:tx_bwguild_domain_model_offer',
        'label' => 'title',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'languageField' => 'sys_language_uid',
        'transOrigPointerField' => 'l10n_parent',
        'transOrigDiffSourceField' => 'l10n_diffsource',
        'delete' => 'deleted',
        'hideAtCopy' => true,
        'type' => 'record_type',
        'typeicon_classes' => [
            'default' => 'tx_bwguild_domain_model_offer',
            '0' => 'tx_bwguild_domain_model_offer-0',
            '1' => 'tx_bwguild_domain_model_offer-1',
            '2' => 'tx_bwguild_domain_model_offer-2',
            '3' => 'tx_bwguild_domain_model_offer-3',
        ],
        'useColumnsForDefaultValues' => 'record_type',
        'enablecolumns' => [
            'disabled' => 'hidden',
            'starttime' => 'starttime',
            'endtime' => 'endtime',
            'fe_group' => 'fe_group',
        ],
        'searchFields' => 'record_type, title, address, zip, country, description, start_date, latitude, longitude, fe_user, fe_users, conditions, possibilities, contact_person, contact_mail',
        'iconfile' => 'EXT:bw_guild/Resources/Public/Images/tx_bwguild_domain_model_offer.svg',
    ],
    'interface' => [
        'showRecordFieldList' => 'cruser_id,pid,sys_language_uid,l10n_parent,l10n_diffsource,hidden,starttime,endtime, record_type, title, address, zip, country, description, start_date, latitude, longitude, fe_user, fe_users, conditions, possibilities, contact_person, contact_mail, contact_phone, fe_group',
    ],
    'types' => [
        // Job
        '0' => [
            'showitem' => 'fe_user, fe_users, record_type, title, slug, description, start_date, contact_person, contact_mail, contact_phone, --div--;LLL:EXT:bw_guild/Resources/Private/Language/locallang_tca.xlf:tx_bwguild_domain_model_offer.more, conditions, possibilities, --div--;LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.address,address,zip,city,country, latitude, longitude,
            --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access,
                    --palette--;;access,',
        ],
        // Education
        '1' => [
            'showitem' => 'fe_user, fe_users, record_type, title, slug, description, start_date, contact_person, contact_mail, contact_phone, --div--;LLL:EXT:bw_guild/Resources/Private/Language/locallang_tca.xlf:tx_bwguild_domain_model_offer.more, conditions, possibilities, --div--;LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.address,address,zip,city,country, latitude, longitude,
            --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access,
                    --palette--;;access,',
        ],
        // Internship
        '2' => [
            'showitem' => 'fe_user, fe_users, record_type, title, slug, description, start_date, contact_person, contact_mail, contact_phone, --div--;LLL:EXT:bw_guild/Resources/Private/Language/locallang_tca.xlf:tx_bwguild_domain_model_offer.more, conditions, possibilities, --div--;LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.address,address,zip,city,country, latitude, longitude,
            --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access,
                    --palette--;;access,',
        ],
        // Help
        '3' => [
            'showitem' => 'fe_user, record_type, title, slug, description, start_date, contact_person, contact_mail, contact_phone, --div--;LLL:EXT:bw_guild/Resources/Private/Language/locallang_tca.xlf:tx_bwguild_domain_model_offer.more, conditions, possibilities, --div--;LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.address,address,zip,city,country, latitude, longitude,
            --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access,
                    --palette--;;access,',
        ],
    ],
    'palettes' => [
        'access' => [
            'label' => 'LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:palette.access',
            'showitem' => '
                starttime;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:starttime_formlabel,
                endtime;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:endtime_formlabel,
                --linebreak--,
                fe_group;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:fe_group_formlabel,
                --linebreak--,editlock
            ',
        ],
    ],
    'columns' => [
        'record_type' => [
            'exclude' => false,
            'label' => 'LLL:EXT:bw_guild/Resources/Private/Language/locallang_tca.xlf:tx_bwguild_domain_model_offer.record_type',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    [
                        'LLL:EXT:bw_guild/Resources/Private/Language/locallang_tca.xlf:tx_bwguild_domain_model_offer.record_type.0',
                        0,
                        'tx_bwguild_domain_model_offer-0',
                    ],
                    [
                        'LLL:EXT:bw_guild/Resources/Private/Language/locallang_tca.xlf:tx_bwguild_domain_model_offer.record_type.1',
                        1,
                        'tx_bwguild_domain_model_offer-1',
                    ],
                    [
                        'LLL:EXT:bw_guild/Resources/Private/Language/locallang_tca.xlf:tx_bwguild_domain_model_offer.record_type.2',
                        2,
                        'tx_bwguild_domain_model_offer-2',
                    ],
                    [
                        'LLL:EXT:bw_guild/Resources/Private/Language/locallang_tca.xlf:tx_bwguild_domain_model_offer.record_type.3',
                        3,
                        'tx_bwguild_domain_model_offer-3',
                    ],
                ],
                'fieldWizard' => [
                    'selectIcons' => [
                        'disabled' => false,
                    ],
                ],
                'size' => 1,
                'maxitems' => 1,
            ],
        ],
        'sys_language_uid' => [
            'exclude' => true,
            'label' => 'LLL:EXT:lang/Resources/Private/Language/locallang_general.xlf:LGL.language',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'special' => 'languages',
                'items' => [
                    [
                        'LLL:EXT:lang/Resources/Private/Language/locallang_general.xlf:LGL.allLanguages',
                        -1,
                        'flags-multiple',
                    ],
                ],
                'default' => 0,
            ],
        ],
        'l10n_parent' => [
            'displayCond' => 'FIELD:sys_language_uid:>:0',
            'exclude' => true,
            'label' => 'LLL:EXT:lang/Resources/Private/Language/locallang_general.xlf:LGL.l18n_parent',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    ['', 0],
                ],
                'foreign_table' => 'tx_bwguild_domain_model_offer',
                'foreign_table_where' => 'AND tx_bwguild_domain_model_offer.pid=###CURRENT_PID### AND tx_bwguild_domain_model_offer.sys_language_uid IN (-1,0)',
                'fieldWizard' => [
                    'selectIcons' => [
                        'disabled' => true,
                    ],
                ],
                'default' => 0,
            ],
        ],
        'l10n_diffsource' => [
            'config' => [
                'type' => 'passthrough',
                'default' => '',
            ],
        ],
        'hidden' => [
            'exclude' => true,
            'label' => 'LLL:EXT:lang/Resources/Private/Language/locallang_general.xlf:LGL.hidden',
            'config' => [
                'type' => 'check',
                'default' => 0,
            ],
        ],
        'cruser_id' => [
            'label' => 'cruser_id',
            'config' => [
                'type' => 'passthrough',
            ],
        ],
        'pid' => [
            'label' => 'pid',
            'config' => [
                'type' => 'passthrough',
            ],
        ],
        'crdate' => [
            'label' => 'crdate',
            'config' => [
                'type' => 'passthrough',
            ],
        ],
        'tstamp' => [
            'label' => 'tstamp',
            'config' => [
                'type' => 'passthrough',
            ],
        ],
        'starttime' => [
            'exclude' => true,
            'label' => 'LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:starttime_formlabel',
            'config' => [
                'type' => 'input',
                'renderType' => 'inputDateTime',
                'size' => 16,
                'eval' => 'datetime,int',
                'default' => 0,
                'behaviour' => [
                    'allowLanguageSynchronization' => true,
                ],
            ],
        ],
        'endtime' => [
            'exclude' => true,
            'label' => 'LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:endtime_formlabel',
            'config' => [
                'type' => 'input',
                'renderType' => 'inputDateTime',
                'size' => 16,
                'eval' => 'datetime,int',
                'default' => 0,
                'behaviour' => [
                    'allowLanguageSynchronization' => true,
                ],
            ],
        ],
        'title' => [
            'exclude' => false,
            'l10n_mode' => 'prefixLangTitle',
            'label' => 'LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:header_formlabel',
            'config' => [
                'type' => 'input',
                'size' => 60,
                'eval' => 'required',
            ],
        ],
        'description' => [
            'exclude' => false,
            'label' => 'LLL:EXT:bw_guild/Resources/Private/Language/locallang_tca.xlf:tx_bwguild_domain_model_offer.description',
            'config' => [
                'type' => 'text',
                'cols' => 30,
                'rows' => 5,
                'softref' => 'rtehtmlarea_images,typolink_tag,images,email[subst],url',
                'enableRichtext' => true,
                'richtextConfiguration' => 'default',
            ],
        ],
        'start_date' => [
            'exclude' => false,
            'label' => 'LLL:EXT:bw_guild/Resources/Private/Language/locallang_tca.xlf:tx_bwguild_domain_model_offer.start_date',
            'config' => [
                'type' => 'input',
                'size' => 30,
            ],
        ],
        'fe_user' => [
            'exclude' => false,
            'label' => 'LLL:EXT:bw_guild/Resources/Private/Language/locallang_tca.xlf:tx_bwguild_domain_model_offer.fe_user',
            'config' => [
                'type' => 'group',
                'internal_type' => 'db',
                'allowed' => 'fe_users',
                'foreign_table' => 'fe_users',
                'foreign_table_field' => 'offers',
                'size' => 1,
                'maxitems' => 1,
                'eval' => 'int',
                'default' => 0,
                'suggestOptions' => [
                    'fe_users' => [
                        'searchWholePhrase' => 1,
                        'additionalSearchFields' => 'company, name, short_name, first_name, last_name',
                    ],
                ],
                'fieldWizard' => [
                    'recordsOverview' => [
                        'disabled' => true,
                    ],
                ],
            ],
        ],
        'fe_users' => [
            'exclude' => false,
            'label' => 'LLL:EXT:bw_guild/Resources/Private/Language/locallang_tca.xlf:tx_bwguild_domain_model_offer.fe_users',
            'config' => [
                'type' => 'group',
                'internal_type' => 'db',
                'allowed' => 'fe_users',
                'foreign_table' => 'fe_users',
                'MM' => 'tx_bwguild_offer_feuser_mm',
                'MM_opposite_field' => 'shared_offers',
                'size' => 10,
                'maxitems' => 9999,
                'eval' => 'int',
                'default' => 0,
                'suggestOptions' => [
                    'fe_users' => [
                        'searchWholePhrase' => 1,
                        'additionalSearchFields' => 'company, name, short_name, first_name, last_name',
                    ],
                ],
                'fieldWizard' => [
                    'recordsOverview' => [
                        'disabled' => true,
                    ],
                ],
            ],
        ],
        'possibilities' => [
            'exclude' => false,
            'label' => 'LLL:EXT:bw_guild/Resources/Private/Language/locallang_tca.xlf:tx_bwguild_domain_model_offer.possibilities',
            'config' => [
                'type' => 'text',
                'cols' => 30,
                'rows' => 5,
                'softref' => 'rtehtmlarea_images,typolink_tag,images,email[subst],url',
                'enableRichtext' => true,
                'richtextConfiguration' => 'default',
            ],
        ],
        'conditions' => [
            'exclude' => false,
            'label' => 'LLL:EXT:bw_guild/Resources/Private/Language/locallang_tca.xlf:tx_bwguild_domain_model_offer.conditions',
            'config' => [
                'type' => 'text',
                'cols' => 30,
                'rows' => 5,
                'softref' => 'rtehtmlarea_images,typolink_tag,images,email[subst],url',
                'enableRichtext' => true,
                'richtextConfiguration' => 'default',
            ],
        ],
        'contact_person' => [
            'exclude' => false,
            'label' => 'LLL:EXT:bw_guild/Resources/Private/Language/locallang_tca.xlf:tx_bwguild_domain_model_offer.contact_person',
            'config' => [
                'type' => 'input',
                'size' => 30,
            ],
        ],
        'contact_mail' => [
            'exclude' => false,
            'label' => 'LLL:EXT:bw_guild/Resources/Private/Language/locallang_tca.xlf:tx_bwguild_domain_model_offer.contact_mail',
            'config' => [
                'type' => 'input',
                'size' => 30,
            ],
        ],
        'contact_phone' => [
            'exclude' => false,
            'label' => 'LLL:EXT:bw_guild/Resources/Private/Language/locallang_tca.xlf:tx_bwguild_domain_model_offer.contact_phone',
            'config' => [
                'type' => 'input',
                'size' => 30,
            ],
        ],
        'address' => [
            'exclude' => true,
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.address',
            'config' => [
                'type' => 'text',
                'cols' => 20,
                'rows' => 3,
            ],
        ],
        'zip' => [
            'exclude' => true,
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.zip',
            'config' => [
                'type' => 'input',
                'eval' => 'trim',
                'size' => 10,
                'max' => 10,
            ],
        ],
        'city' => [
            'exclude' => true,
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.city',
            'config' => [
                'type' => 'input',
                'size' => 20,
                'eval' => 'trim',
                'max' => 50,
            ],
        ],
        'country' => [
            'exclude' => true,
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.country',
            'config' => [
                'type' => 'input',
                'size' => 20,
                'eval' => 'trim',
                'max' => 40,
            ],
        ],
        'slug' => [
            'label' => 'LLL:EXT:bw_guild/Resources/Private/Language/locallang_tca.xlf:tx_bwguild_domain_model_offer.slug',
            'exclude' => 1,
            'config' => [
                'type' => 'slug',
                'generatorOptions' => [
                    'fields' => ['title'],
                    'fieldSeparator' => '/',
                    'prefixParentPageSlug' => true,
                    'replacements' => [
                        '(m/w/d)' => '',
                        '(m/w)' => '',
                        '/-in' => '',
                        '/' => '',
                    ],
                ],
                'fallbackCharacter' => '-',
                'eval' => 'unique',
            ],
        ],
        'fe_group' => [
            'exclude' => true,
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.fe_group',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectMultipleSideBySide',
                'size' => 5,
                'maxitems' => 20,
                'items' => [
                    [
                        'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.hide_at_login',
                        -1,
                    ],
                    [
                        'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.any_login',
                        -2,
                    ],
                    [
                        'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.usergroups',
                        '--div--',
                    ],
                ],
                'exclusiveKeys' => '-1,-2',
                'foreign_table' => 'fe_groups',
                'foreign_table_where' => 'ORDER BY fe_groups.title',
                'enableMultiSelectFilterTextfield' => true,
            ],
        ],

        'latitude' => [
            'exclude' => true,
            'label' => 'LLL:EXT:bw_guild/Resources/Private/Language/locallang_db.xlf:address.latitude',
            'description' => '(To set new coordinates, delete them and save the data set. After that, the new address for the marker will be applied.)',
            'config' => [
                'type' => 'input',
                'eval' => \Blueways\BwGuild\Evaluation\LatitudeEvaluation::class,
                'default' => null,
                'behaviour' => [
                    'allowLanguageSynchronization' => true,
                ],
            ]
        ],
        'longitude' => [
            'exclude' => true,
            'label' => 'LLL:EXT:bw_guild/Resources/Private/Language/locallang_db.xlf:address.longitude',
            'config' => [
                'type' => 'input',
                'eval' => \Blueways\BwGuild\Evaluation\LongitudeEvaluation::class,
                'default' => null,
                'fieldControl' => [
                    'locationMap' => [
                        'renderType' => 'locationMapWizard'
                    ]
                ],
                'behaviour' => [
                    'allowLanguageSynchronization' => true,
                ],
            ]
        ],
    ],
];
