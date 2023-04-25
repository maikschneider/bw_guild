<?php

namespace Blueways\BwGuild\Controller;

use Blueways\BwGuild\Domain\Repository\OfferRepository;
use Blueways\BwGuild\Domain\Repository\UserRepository;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

final class BackendController extends ActionController
{
    public function __construct(
        protected UserRepository $userRepository,
        protected OfferRepository $offerRepository,
        protected PageRenderer $pageRenderer
    ) {
    }

    public function indexAction(): ResponseInterface
    {
        $this->pageRenderer->addInlineLanguageLabelFile('EXT:core/Resources/Private/Language/locallang_mod_web_list.xlf');

        $users = $this->userRepository->findAll();
        $this->view->assign('users', $users);

        return $this->htmlResponse();
    }

    public function offerAction(): ResponseInterface
    {
        $this->pageRenderer->loadRequireJsModule('TYPO3/CMS/BwGuild/BackendOffer');
        $this->pageRenderer->addInlineLanguageLabelFile('EXT:core/Resources/Private/Language/locallang_mod_web_list.xlf');

        $offers = $this->offerRepository->findAll();
        $this->view->assign('offers', $offers);

        return $this->htmlResponse();
    }
}
