<?php

defined('TYPO3_MODE') || die();

call_user_func(
    function () {

        /**
         * Register icon
         */
        $iconRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Imaging\IconRegistry::class);
        $iconRegistry->registerIcon(
            'tx_bwguild_userlist',
            \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
            ['source' => 'EXT:bw_guild/Resources/Public/Images/tt_content_userlist.svg']
        );
        $iconRegistry->registerIcon(
            'tx_bwguild_usershow',
            \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
            ['source' => 'EXT:bw_guild/Resources/Public/Images/tt_content_usershow.svg']
        );
        $iconRegistry->registerIcon(
            'tx_bwguild_offerlatest',
            \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
            ['source' => 'EXT:bw_guild/Resources/Public/Images/tt_content_userlist.svg']
        );
        $iconRegistry->registerIcon(
            'tx_bwguild_offerlist',
            \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
            ['source' => 'EXT:bw_guild/Resources/Public/Images/tt_content_offerlist.svg']
        );
        $iconRegistry->registerIcon(
            'tx_bwguild_domain_model_offer',
            \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
            ['source' => 'EXT:bw_guild/Resources/Public/Images/tx_bwguild_domain_model_offer.svg']
        );
        $iconRegistry->registerIcon(
            'tx_bwguild_domain_model_offer-0',
            \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
            ['source' => 'EXT:bw_guild/Resources/Public/Images/tx_bwguild_domain_model_offer.0.svg']
        );
        $iconRegistry->registerIcon(
            'tx_bwguild_domain_model_offer-1',
            \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
            ['source' => 'EXT:bw_guild/Resources/Public/Images/tx_bwguild_domain_model_offer.1.svg']
        );
        $iconRegistry->registerIcon(
            'tx_bwguild_domain_model_offer-2',
            \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
            ['source' => 'EXT:bw_guild/Resources/Public/Images/tx_bwguild_domain_model_offer.2.svg']
        );
        $iconRegistry->registerIcon(
            'tx_bwguild_domain_model_offer-3',
            \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
            ['source' => 'EXT:bw_guild/Resources/Public/Images/tx_bwguild_domain_model_offer.3.svg']
        );

        /**
         * Register BE Module
         */
        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
            'Blueways.BwGuild',
            'web',
            'tx_bwguild_admin',
            '',
            [
                'Administration' => 'index, importer, csv, csvImport, offer, passwordRefresh',
            ],
            [
                'access' => 'user,group',
                'icon' => 'EXT:bw_guild/Resources/Public/Images/module_administration.svg',
                'labels' => 'LLL:EXT:bw_guild/Resources/Private/Language/locallang_be.xlf:tx_bwguild_admin',
            ]
        );
    }
);
