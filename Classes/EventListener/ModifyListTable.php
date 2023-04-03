<?php

namespace Blueways\BwGuild\EventListener;

use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Recordlist\Event\ModifyRecordListRecordActionsEvent;

class ModifyListTable
{
    protected array $settings;

    public function __construct(
        protected ConfigurationManager $configurationManager,
        protected PageRenderer $pageRenderer,
        protected IconFactory $iconFactory
    ) {
        $typoScript = $configurationManager->getConfiguration(ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK);
        $this->settings = $typoScript['settings']['tableActions'];
    }

    public function __invoke(ModifyRecordListRecordActionsEvent $event): void
    {
        $table = $event->getTable();
        $row = $event->getRecord();

        foreach ($this->settings as $tableName => $setting) {
            if ($table === $tableName) {
                $event->removeAction('view');

                if ($setting['showNewButton'] === '0') {
                    $event->removeAction('new');
                }

                if ($setting['showDeleteButton'] === '0') {
                    $event->removeAction('delete');
                }

                if ($setting['showHideButton'] === '0') {
                    $event->removeAction('hide');
                }

                if ($setting['showHistoryButton'] === '0') {
                    $event->removeAction('history');
                }

                if ($setting['showViewBigButton'] === '0') {
                    $event->removeAction('viewBig');
                }

                if ($setting['showEditButton'] === '0') {
                    $event->removeAction('edit');
                }

                if ($setting['showPublicizeButton'] === '1') {
                    $this->pageRenderer->loadRequireJsModule(
                        'TYPO3/CMS/BwGuild/BackendModifyListTable',
                        'function(BackendModifyListTable) { BackendModifyListTable.init("' . $tableName . '"); }'
                    );

                    $languageService = $GLOBALS['LANG'];

                    if ($row['public']) {
                        $params = 'data[' . $table . '][' . $row['uid'] . '][public]=0';
                        $btnHtml = '<a class="btn btn-default t3js-record-public" data-public="yes" href="#"'
                            . ' data-params="' . htmlspecialchars($params) . '"'
                            . ' data-toggle="tooltip"'
                            . ' data-toggle-title="' . $languageService->sL('LLL:EXT:bw_guild/Resources/Private/Language/locallang_be.xlf:administration.recordlist.button.public') . '"'
                            . ' title="' . $languageService->sL('LLL:EXT:bw_guild/Resources/Private/Language/locallang_be.xlf:administration.recordlist.button.unpublic') . '">'
                            . $this->iconFactory->getIcon('actions-edit-hide', Icon::SIZE_SMALL)->render() . '</a>';
                    } else {
                        $params = 'data[' . $table . '][' . $row['uid'] . '][public]=1';
                        $btnHtml = '<a class="btn btn-default t3js-record-public" data-public="no" href="#"'
                            . ' data-params="' . htmlspecialchars($params) . '"'
                            . ' data-toggle="tooltip"'
                            . ' data-toggle-title="' . $languageService->sL('LLL:EXT:bw_guild/Resources/Private/Language/locallang_be.xlf:administration.recordlist.button.unpublic') . '"'
                            . ' title="' . $languageService->sL('LLL:EXT:bw_guild/Resources/Private/Language/locallang_be.xlf:administration.recordlist.button.public') . '">'
                            . $this->iconFactory->getIcon('actions-edit-unhide', Icon::SIZE_SMALL)->render() . '</a>';
                    }

                    $event->setAction($btnHtml, 'confirmation', 'primary');
                }
            }

            // remove empty action groups
            $actionGroups = array_filter($event->getActions());
            $event->setActions($actionGroups);
        }
    }
}
