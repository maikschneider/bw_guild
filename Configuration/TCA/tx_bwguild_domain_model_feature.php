<?php

return [
    'ctrl' => [
        'title' => 'LLL:EXT:bw_guild/Resources/Private/Language/locallang_tca.xlf:tx_bwguild_domain_model_feature',
        'label' => 'title',
        'crdate' => 'crdate',
        'hideAtCopy' => true,
        'type' => 'record_type',
        'searchFields' => 'title',
        'iconfile' => 'EXT:bw_guild/Resources/Public/Images/tx_bwguild_domain_model_feature.svg',
    ],
    'interface' => [
        'showRecordFieldList' => 'cruser_id,pid,sys_language_uid,l10n_parent,l10n_diffsource,hidden,starttime,endtime, record_type, title, address, zip, country, description, start_date, geo_lat, geo_long, fe_user, fe_users, conditions, possibilities, contact_person, contact_mail, contact_phone, fe_group',
    ],
    'types' => [
        // Skill
        '0' => [
            'showitem' => 'record_type, title',
        ],
        // Interest
        '1' => [
            'showitem' => 'record_type, title',
        ],
        // Hobby
        '2' => [
            'showitem' => 'record_type, title',
        ],
    ],
    'columns' => [
        'record_type' => [
            'exclude' => false,
            'label' => 'LLL:EXT:bw_guild/Resources/Private/Language/locallang_tca.xlf:tx_bwguild_domain_model_feature.record_type',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    [
                        'LLL:EXT:bw_guild/Resources/Private/Language/locallang_tca.xlf:tx_bwguild_domain_model_feature.record_type.0',
                        0,
                    ],
                    [
                        'LLL:EXT:bw_guild/Resources/Private/Language/locallang_tca.xlf:tx_bwguild_domain_model_feature.record_type.1',
                        1,
                    ],
                    [
                        'LLL:EXT:bw_guild/Resources/Private/Language/locallang_tca.xlf:tx_bwguild_domain_model_feature.record_type.2',
                        2,
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
        'fe_users' => [
            'exclude' => false,
            'label' => 'LLL:EXT:bw_guild/Resources/Private/Language/locallang_tca.xlf:tx_bwguild_domain_model_offer.fe_users',
            'config' => [
                'type' => 'group',
                'internal_type' => 'db',
                'allowed' => 'fe_users',
                'foreign_table' => 'fe_users',
                'MM' => 'tx_bwguild_feature_feuser_mm',
                'MM_opposite_field' => 'features',
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
    ],
];
