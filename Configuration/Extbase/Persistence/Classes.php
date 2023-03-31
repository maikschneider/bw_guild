<?php

declare(strict_types=1);

return [
    Blueways\BwGuild\Domain\Model\Category::class => [
        'tableName' => 'sys_category',
    ],
    Blueways\BwGuild\Domain\Model\User::class => [
        'tableName' => 'fe_users',
    ],
    Blueways\BwGuild\Domain\Model\Offer::class => [
        'columns' => [
            'crdate' => [
                'mapOnProperty' => 'crdate',
            ],
        ],
        'subclasses' => [
            Blueways\BwGuild\Domain\Model\Job::class,
            Blueways\BwGuild\Domain\Model\Education::class,
            Blueways\BwGuild\Domain\Model\Internship::class,
            Blueways\BwGuild\Domain\Model\Help::class,
            Blueways\BwGuild\Domain\Model\ProductOffer::class,
            Blueways\BwGuild\Domain\Model\ProductSearch::class,
            Blueways\BwGuild\Domain\Model\ProductGiveAway::class,
        ],
    ],
    Blueways\BwGuild\Domain\Model\Job::class => [
        'recordType' => 0,
        'tableName' => 'tx_bwguild_domain_model_offer',
    ],
    Blueways\BwGuild\Domain\Model\Education::class => [
        'recordType' => 1,
        'tableName' => 'tx_bwguild_domain_model_offer',
    ],
    Blueways\BwGuild\Domain\Model\Internship::class => [
        'recordType' => 2,
        'tableName' => 'tx_bwguild_domain_model_offer',
    ],
    Blueways\BwGuild\Domain\Model\Help::class => [
        'recordType' => 3,
        'tableName' => 'tx_bwguild_domain_model_offer',
    ],
    Blueways\BwGuild\Domain\Model\ProductOffer::class => [
        'recordType' => 4,
        'tableName' => 'tx_bwguild_domain_model_offer',
    ],
    Blueways\BwGuild\Domain\Model\ProductSearch::class => [
        'recordType' => 5,
        'tableName' => 'tx_bwguild_domain_model_offer',
    ],
    Blueways\BwGuild\Domain\Model\ProductGiveAway::class => [
        'recordType' => 6,
        'tableName' => 'tx_bwguild_domain_model_offer',
    ],
    Blueways\BwGuild\Domain\Model\FileReference::class => [
        'tableName' => 'sys_file_reference',
        'columns' => [
            'crop' => [
                'mapOnProperty' => 'crop',
            ],
        ],
    ],
    Blueways\BwGuild\Domain\Model\AbstractUserFeature::class => [
        'tableName' => 'tx_bwguild_domain_model_feature',
        'columns' => [
            'crdate' => [
                'mapOnProperty' => 'crdate',
            ],
        ],
        'subclasses' => [
            Blueways\BwGuild\Domain\Model\UserFeatureInterest::class,
            Blueways\BwGuild\Domain\Model\UserFeatureHobby::class,
            Blueways\BwGuild\Domain\Model\UserFeatureSkill::class,
        ],
    ],
    Blueways\BwGuild\Domain\Model\UserFeatureInterest::class => [
        'recordType' => 1,
        'tableName' => 'tx_bwguild_domain_model_feature',
    ],
    Blueways\BwGuild\Domain\Model\UserFeatureSkill::class => [
        'recordType' => 0,
        'tableName' => 'tx_bwguild_domain_model_feature',
    ],
    Blueways\BwGuild\Domain\Model\UserFeatureHobby::class => [
        'recordType' => 2,
        'tableName' => 'tx_bwguild_domain_model_feature',
    ],
];
