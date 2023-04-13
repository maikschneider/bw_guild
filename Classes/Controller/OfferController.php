<?php

namespace Blueways\BwGuild\Controller;

use Blueways\BwGuild\Domain\Model\Dto\OfferDemand;
use Blueways\BwGuild\Domain\Model\Offer;
use Blueways\BwGuild\Domain\Model\User;
use Blueways\BwGuild\Domain\Repository\CategoryRepository;
use Blueways\BwGuild\Domain\Repository\OfferRepository;
use Blueways\BwGuild\Domain\Repository\UserRepository;
use Blueways\BwGuild\Service\AccessControlService;
use GeorgRinger\NumberedPagination\NumberedPagination as NumberedPaginationAlias;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Exception\Page\PageNotFoundException;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Messaging\AbstractMessage;
use TYPO3\CMS\Core\MetaTag\MetaTagManagerRegistry;
use TYPO3\CMS\Core\Page\AssetCollector;
use TYPO3\CMS\Core\Pagination\ArrayPaginator;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;

/**
 * Class OfferController
 */
class OfferController extends ActionController
{
    public function __construct(
        protected OfferRepository $offerRepository,
        protected UserRepository $userRepository,
        protected AccessControlService $accessControlService,
        protected CategoryRepository $categoryRepository
    ) {
    }

    public function listAction(?OfferDemand $demand = null): ResponseInterface
    {
        $demand = $demand ?? OfferDemand::createFromSettings($this->settings);

        // override filter from form
        if ($this->request->hasArgument('demand')) {
            $demand->overrideFromRequest($this->request);
        }

        // get categories by category settings in plugin
        $catConjunction = $this->settings['categoryConjunction'];
        if ($catConjunction === 'or' || $catConjunction === 'and') {
            $categories = $this->categoryRepository->findFromUidList($this->settings['categories']);
        } elseif ($catConjunction === 'notor' || $catConjunction === 'notand') {
            $categories = $this->categoryRepository->findFromUidListNot($this->settings['categories']);
        } else {
            $categories = $this->categoryRepository->findAll();
        }
        $offers = $this->offerRepository->findDemanded($demand);

        // create pagination
        $itemsPerPage = (int)$this->settings['maxItems'];
        $maximumLinks = (int)$this->settings['maximumLinks'];
        $currentPage = $this->request->hasArgument('currentPage') ? (int)$this->request->getArgument('currentPage') : 1;
        $paginator = new ArrayPaginator($offers, $currentPage, $itemsPerPage);
        $pagination = new NumberedPaginationAlias($paginator, $maximumLinks);

        $numberOfResults = count($offers);
        $offers = $this->offerRepository->mapResultToObjects($paginator->getPaginatedItems());

        // disbale indexing of list view
        $metaTagManager = GeneralUtility::makeInstance(MetaTagManagerRegistry::class);
        $metaTagManager->getManagerForProperty('robots')->addProperty('robots', 'noindex, follow');

        $this->view->setTemplate($this->settings['template'] ?? 'List');
        $this->view->assign('offers', $offers);
        $this->view->assign('demand', $demand);
        $this->view->assign('categories', $categories);
        $this->view->assign('pagination', [
            'currentPage' => $currentPage,
            'paginator' => $paginator,
            'pagination' => $pagination,
            'numberOfResults' => $numberOfResults,
        ]);

        return $this->htmlResponse($this->view->render());
    }

    public function latestAction(): ResponseInterface
    {
        $demand = $demand ?? OfferDemand::createFromSettings($this->settings);

        $offers = $this->offerRepository->findDemanded($demand);
        $offers = $this->offerRepository->mapResultToObjects($offers);

        $this->view->setTemplate($this->settings['template'] ?? 'Latest');
        $this->view->assign('offers', $offers);
        return $this->htmlResponse();
    }

    public function showAction(?Offer $offer = null): ResponseInterface
    {
        if (!$offer || !$offer->isPublic()) {
            throw new PageNotFoundException('Offer not found', 1678267226);
        }

        $schema = $offer->getJsonSchema($this->settings);

        if (isset($this->settings['schema.']['enable']) && $this->settings['schema.']['enable']) {
            $json = json_encode($schema);
            $assetCollector = GeneralUtility::makeInstance(AssetCollector::class);
            $assetCollector->addInlineJavaScript('bwguild_json', $json, ['type' => 'application/ld+json']);
        }

        $GLOBALS['TSFE']->page['title'] = $schema['title'];
        $GLOBALS['TSFE']->page['description'] = $schema['description'];

        $metaTagManager = GeneralUtility::makeInstance(MetaTagManagerRegistry::class);
        $metaTagManager->getManagerForProperty('og:title')->addProperty('og:title', $schema['title']);
        $metaTagManager->getManagerForProperty('og:description')->addProperty('og:description', $schema['description']);

        $this->view->assign('offer', $offer);
        return $this->htmlResponse();
    }

    public function showPreviewAction(?Offer $offer = null): ResponseInterface
    {
        if (!$offer || !$this->accessControlService->hasLoggedInFrontendUser()) {
            throw new PageNotFoundException('Offer not found', 1679915676);
        }

        $userId = $this->accessControlService->getFrontendUserUid();
        $isAccessible = $offer->getFeUser()?->getUid() === $userId;

        if (!$isAccessible) {
            throw new PageNotFoundException('No access', 1679915674);
        }

        $this->view->assign('offer', $offer);
        return $this->htmlResponse();
    }

    public function editAction(Offer $offer = null): ResponseInterface
    {
        if (!$this->accessControlService->hasLoggedInFrontendUser()) {
            $this->throwStatus(403, 'Not logged in');
        }

        /** @var User $user */
        $user = $this->userRepository->findByUid($this->accessControlService->getFrontendUserUid());

        if ($offer && $offer->getFeUser()->getUid() !== $user->getUid()) {
            $this->throwStatus(403, 'Not allowed to edit this offer');
        }

        if (!$offer) {
            $offers = $user->getOffers();
            $this->view->assign('offers', $offers);
        } else {
            $this->view->assign('offer', $offer);
        }
        return $this->htmlResponse();
    }

    public function updateAction(Offer $offer)
    {
        if (!$this->accessControlService->hasLoggedInFrontendUser()) {
            $this->throwStatus(403, 'Not logged in');
        }

        $userId = $this->accessControlService->getFrontendUserUid();
        /** @var User $user */
        $user = $this->userRepository->findByUid($userId);

        // update: check access
        if ($offer->getUid() && $offer->getFeUser() && $offer->getFeUser()->getUid() !== $userId) {
            $this->throwStatus(403, 'Not allowed to update this offer');
        }

        // new: add current user
        if (!$offer->getUid()) {
            // set current user as owner
            $offer->setFeUser($user);

            $this->offerRepository->add($offer);

            // persist to set pid and generate slug
            $persistenceManager = $this->objectManager->get(PersistenceManager::class);
            $persistenceManager->persistAll();
        }

        // all time: update slug
        $offer->updateSlug();
        $this->offerRepository->update($offer);

        $this->addFlashMessage(
            $this->getLanguageService()->sL('LLL:EXT:bw_guild/Resources/Private/Language/locallang_fe.xlf:user.update.success.message'),
            $this->getLanguageService()->sL('LLL:EXT:bw_guild/Resources/Private/Language/locallang_fe.xlf:user.update.success.title'),
            AbstractMessage::OK
        );

        $this->redirect('edit');
    }

    protected function getLanguageService(): LanguageService
    {
        return $GLOBALS['LANG'] ?? GeneralUtility::makeInstance(LanguageService::class);
    }

    public function deleteAction(Offer $offer)
    {
        if (!$this->accessControlService->hasLoggedInFrontendUser()) {
            $this->throwStatus(403, 'Not logged in');
        }

        /** @var User $user */
        $user = $this->userRepository->findByUid($this->accessControlService->getFrontendUserUid());

        if ($offer && $offer->getFeUser()->getUid() !== $user->getUid()) {
            $this->throwStatus(403, 'Not allowed to delete this offer');
        }

        $this->offerRepository->remove($offer);

        $this->addFlashMessage(
            $this->getLanguageService()->sL('LLL:EXT:bw_guild/Resources/Private/Language/locallang_fe.xlf:offer.delete.success.message'),
            $this->getLanguageService()->sL('LLL:EXT:bw_guild/Resources/Private/Language/locallang_fe.xlf:offer.delete.success.title'),
            AbstractMessage::OK
        );

        $this->redirect('edit');
    }

    public function newAction(): ResponseInterface
    {
        if (!$this->accessControlService->hasLoggedInFrontendUser()) {
            $this->throwStatus(403, 'Not logged in');
        }

        /** @var User $user */
        $user = $this->userRepository->findByUid($this->accessControlService->getFrontendUserUid());

        $offer = new Offer();
        $offer->setFeUser($user);

        $this->view->assign('offer', $offer);
        return $this->htmlResponse();
    }
}
