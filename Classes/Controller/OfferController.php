<?php

namespace Blueways\BwGuild\Controller;

use Blueways\BwGuild\Domain\Model\Dto\OfferDemand;
use Blueways\BwGuild\Domain\Model\Offer;
use Blueways\BwGuild\Domain\Repository\OfferRepository;
use Blueways\BwGuild\Domain\Repository\UserRepository;
use Blueways\BwGuild\Service\AccessControlService;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\MetaTag\MetaTagManagerRegistry;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;
use TYPO3\CMS\Core\Pagination\ArrayPaginator;
use TYPO3\CMS\Core\Pagination\SimplePagination;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Core\Page\AssetCollector;

/**
 * Class OfferController
 */
class OfferController extends ActionController
{
    protected OfferRepository $offerRepository;

    protected UserRepository $userRepository;

    protected AccessControlService $accessControlService;

    public function __construct() {
        $offerRepository = GeneralUtility::makeInstance('Blueways\BwGuild\Domain\Repository\OfferRepository');
        $this->offerRepository = $offerRepository;
        $userRepository = GeneralUtility::makeInstance('Blueways\BwGuild\Domain\Repository\UserRepository');
        $this->userRepository = $userRepository;
        $accessControlService = GeneralUtility::makeInstance('Blueways\BwGuild\Service\AccessControlService');
        $this->accessControlService = $accessControlService;
    }

    public function initializeAction(): void
    {
        parent::initializeAction();

        $this->mergeTyposcriptSettings();
    }

    /**
     * Merges the typoscript settings with the settings from flexform
     */
    private function mergeTyposcriptSettings(): void
    {
        $configurationManager = GeneralUtility::makeInstance(ConfigurationManager::class);
        try {
            $typoscript = $configurationManager->getConfiguration(ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT);
            ArrayUtility::mergeRecursiveWithOverrule(
                $typoscript['plugin.']['tx_bwguild_offerlist.']['settings.'],
                $typoscript['plugin.']['tx_bwguild.']['settings.'],
                true,
                false,
                false
            );
            ArrayUtility::mergeRecursiveWithOverrule(
                $typoscript['plugin.']['tx_bwguild_offerlist.']['settings.'],
                $this->settings,
                true,
                false,
                false
            );
            $this->settings = $typoscript['plugin.']['tx_bwguild_offerlist.']['settings.'];
        } catch (\TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException $exception) {
        }
    }

    public function listAction(): ResponseInterface
    {
        $demand = $this->offerRepository->createDemandObjectFromSettings($this->settings, OfferDemand::class);

        // override filter from form
        if ($this->request->hasArgument('demand')) {
            $demand->overrideFromRequest($this->request);
        }

        /** @var \Blueways\BwGuild\Domain\Repository\OfferRepository $repository */
        $repository = GeneralUtility::makeInstance($this->settings['record_type']);

        $offers = $repository->findDemanded($demand);

        // create pagination
        $currentPage = $this->request->hasArgument('currentPage') ? (int)$this->request->getArgument('currentPage') : 1;
        $currentPage = $currentPage > 0 ? $currentPage : 1;
        $itemsPerPage = (int)$this->settings['itemsPerPage'];
        $paginator = new ArrayPaginator($offers, $currentPage, $itemsPerPage);
        $pagination = new SimplePagination($paginator);

        // disbale indexing of list view
        $metaTagManager = GeneralUtility::makeInstance(MetaTagManagerRegistry::class);
        $metaTagManager->getManagerForProperty('robots')->addProperty('robots', 'noindex, follow');

        $this->view->setTemplate($this->settings['template'] ?? 'List');
        $this->view->assign('offers', $offers);
        $this->view->assign('pagination', [
            'currentPage' => $currentPage,
            'paginator' => $paginator,
            'pagination' => $pagination,
        ]);
        return $this->htmlResponse($this->view->render());
    }

    public function latestAction(): void
    {
        $demand = $this->offerRepository->createDemandObjectFromSettings($this->settings, OfferDemand::class);

        /** @var \Blueways\BwGuild\Domain\Repository\OfferRepository $repository */
        $repository = $this->objectManager->get($this->settings['record_type']);

        $offers = $repository->findDemanded($demand);
        $this->view->setTemplate($this->settings['template'] ?? 'Latest');
        $this->view->assign('offers', $offers);
    }

    public function showAction(Offer $offer)
    {
        $configurationManager = GeneralUtility::makeInstance(ConfigurationManager::class);
        $typoscript = $configurationManager->getConfiguration(ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT);

        $schema = $offer->getJsonSchema($typoscript);

        if ((int)$typoscript['plugin.']['tx_bwguild_offerlist.']['settings.']['schema.']['enable']) {
            $json = json_encode($schema, JSON_THROW_ON_ERROR);
            $assetCollector = GeneralUtility::makeInstance(AssetCollector::class);
            $assetCollector->addInlineJavaScript('bwguild_json', $json, ['type' => 'application/ld+json']);
        }

        $GLOBALS['TSFE']->page['title'] = $schema['title'];
        $GLOBALS['TSFE']->page['description'] = $schema['description'];

        $metaTagManager = GeneralUtility::makeInstance(MetaTagManagerRegistry::class);
        $metaTagManager->getManagerForProperty('og:title')->addProperty('og:title', $schema['title']);
        $metaTagManager->getManagerForProperty('og:description')->addProperty('og:description', $schema['description']);

        $this->view->assign('offer', $offer);
    }

    public function editAction(Offer $offer = null)
    {
        if (!$this->accessControlService->hasLoggedInFrontendUser()) {
            $this->throwStatus(403, 'Not logged in');
        }

        /** @var \Blueways\BwGuild\Domain\Model\User $user */
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
    }

    public function updateAction(Offer $offer)
    {
        if (!$this->accessControlService->hasLoggedInFrontendUser()) {
            $this->throwStatus(403, 'Not logged in');
        }

        $userId = $this->accessControlService->getFrontendUserUid();
        /** @var \Blueways\BwGuild\Domain\Model\User $user */
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
            \TYPO3\CMS\Core\Messaging\AbstractMessage::OK
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

        /** @var \Blueways\BwGuild\Domain\Model\User $user */
        $user = $this->userRepository->findByUid($this->accessControlService->getFrontendUserUid());

        if ($offer && $offer->getFeUser()->getUid() !== $user->getUid()) {
            $this->throwStatus(403, 'Not allowed to delete this offer');
        }

        $this->offerRepository->remove($offer);

        $this->addFlashMessage(
            $this->getLanguageService()->sL('LLL:EXT:bw_guild/Resources/Private/Language/locallang_fe.xlf:offer.delete.success.message'),
            $this->getLanguageService()->sL('LLL:EXT:bw_guild/Resources/Private/Language/locallang_fe.xlf:offer.delete.success.title'),
            \TYPO3\CMS\Core\Messaging\AbstractMessage::OK
        );

        $this->redirect('edit');
    }

    public function newAction()
    {
        if (!$this->accessControlService->hasLoggedInFrontendUser()) {
            $this->throwStatus(403, 'Not logged in');
        }

        /** @var \Blueways\BwGuild\Domain\Model\User $user */
        $user = $this->userRepository->findByUid($this->accessControlService->getFrontendUserUid());

        $offer = new Offer();
        $offer->setFeUser($user);

        $this->view->assign('offer', $offer);
    }
}
