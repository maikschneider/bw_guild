<?php

namespace Blueways\BwGuild\Controller;

use Blueways\BwGuild\Domain\Repository\OfferRepository;
use Blueways\BwGuild\Domain\Repository\UserRepository;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Domain\Repository\FrontendUserGroupRepository;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

final class BackendController extends ActionController
{
    public function __construct(
        protected UserRepository $userRepository,
        protected FrontendUserGroupRepository $usergroupRepository,
        protected OfferRepository $offerRepository,
        protected readonly ModuleTemplateFactory $moduleTemplateFactory,
        protected readonly IconFactory $iconFactory,
    ) {
    }

    public function indexAction(): ResponseInterface
    {
        $typoScript = $this->configurationManager->getConfiguration(ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK);
        $startView = $typoScript['settings']['startView'] ?? '';

        $users = $this->userRepository->findAll();
        $this->view->assign('users', $users);

        $moduleTemplate = $this->moduleTemplateFactory->create($this->request);
        $moduleTemplate->setContent($this->view->render());
        return $this->htmlResponse($moduleTemplate->renderContent());
    }

    public function offerAction(): ResponseInterface
    {
        $offers = $this->offerRepository->findAll();
        $offerGroups = $this->offerRepository->getGroupedOffers();

        $pageRenderer = GeneralUtility::makeInstance(PageRenderer::class);
        $pageRenderer->loadRequireJsModule('TYPO3/CMS/BwGuild/BackendOffer');

        $this->view->assign('offers', $offers);
        $this->view->assign('offerGroups', $offerGroups);
        return $this->htmlResponse();
    }
}
