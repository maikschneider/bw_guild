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

        /**
         * Register BE Module
         */
        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
            'Blueways.BwGuild',
            'web',
            'tx_bwguild_admin',
            '',
            array(
                'Administration' => 'index, importer',
            ),
            array(
                'access' => 'user,group',
                'icon' => 'EXT:bw_guild/Resources/Public/Images/module_administration.svg',
                'labels' => 'LLL:EXT:bw_guild/Resources/Private/Language/locallang_be.xlf:tx_bwguild_admin',
            )
        );
    }
);


