<?php

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    'BwGuild',
    'Userlist',
    'LLL:EXT:bw_guild/Resources/Private/Language/locallang_be.xlf:userlist.wizard.title',
    'tx_bwguild_userlist'
);
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist']['bwguild_userlist'] = 'pi_flexform';
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue(
    'bwguild_userlist',
    'FILE:EXT:bw_guild/Configuration/FlexForms/flexform_userlist.xml'
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    'BwGuild',
    'Offerlist',
    'LLL:EXT:bw_guild/Resources/Private/Language/locallang_be.xlf:offerlist.wizard.title',
    'tx_bwguild_offerlist'
);
\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    'BwGuild',
    'Offerlatest',
    'LLL:EXT:bw_guild/Resources/Private/Language/locallang_be.xlf:offerlatest.wizard.title',
    'tx_bwguild_offerlatest'
);
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist']['bwguild_offerlist'] = 'pi_flexform';
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue(
    'bwguild_offerlist',
    'FILE:EXT:bw_guild/Configuration/FlexForms/flexform_offerlist.xml'
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    'BwGuild',
    'Usershow',
    'LLL:EXT:bw_guild/Resources/Private/Language/locallang_be.xlf:usershow.wizard.title',
    'tx_bwguild_usershow'
);
