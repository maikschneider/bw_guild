<?php

namespace Blueways\BwGuild\Controller;

use Blueways\BwGuild\Domain\Model\Offer;
use TYPO3\CMS\Core\DataHandling\Model\RecordStateFactory;
use TYPO3\CMS\Core\MetaTag\MetaTagManagerRegistry;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use Blueways\BwGuild\Domain\Model\Dto\OfferDemand;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;

/**
 * Class OfferController
 *
 * @package Blueways\BwGuild\Controller
 */
class OfferController extends ActionController
{

    /**
     * @var \Blueways\BwGuild\Domain\Repository\OfferRepository
     *
     */
    protected $offerRepository;

    /**
     * @var \Blueways\BwGuild\Domain\Repository\UserRepository
     *
     */
    protected $userRepository;

    /**
     * @var \Blueways\BwGuild\Service\AccessControlService
     *
     */
    protected $accessControlService;

    /**
     *
     */
    public function listAction()
    {
        $demand = $this->offerRepository->createDemandObjectFromSettings($this->settings, OfferDemand::class);

        // override filter from form
        if ($this->request->hasArgument('demand')) {
            $demand->overrideDemand($this->request->getArgument('demand'));
        }

        /** @var \Blueways\BwGuild\Domain\Repository\OfferRepository $repository */
        $repository = $this->objectManager->get($this->settings['record_type']);

        $offers = $repository->findDemanded($demand);

        // disbale indexing of list view
        $metaTagManager = GeneralUtility::makeInstance(MetaTagManagerRegistry::class);
        $metaTagManager->getManagerForProperty('robots')->addProperty('robots', 'noindex, follow');

        $this->view->setTemplate($this->settings['template'] ?? 'List');
        $this->view->assign('offers', $offers);
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

    /**
     * @param \Blueways\BwGuild\Domain\Model\Offer $offer
     * @TYPO3\CMS\Extbase\Annotation\IgnoreValidation("offer")
     */
    public function showAction(Offer $offer)
    {
        $configurationManager = $this->objectManager->get(ConfigurationManager::class);
        $typoscript = $configurationManager->getConfiguration(ConfigurationManager::CONFIGURATION_TYPE_FULL_TYPOSCRIPT);

        $schema = $offer->getJsonSchema($typoscript);

        if ((int)$typoscript['plugin.']['tx_bwguild_offerlist.']['settings.']['schema.']['enable']) {
            $json = json_encode($schema);
            $jsCode = '<script type="application/ld+json">' . $json . '</script>';
            $this->response->addAdditionalHeaderData($jsCode);
        }

        $GLOBALS['TSFE']->page['title'] = $schema['title'];
        $GLOBALS['TSFE']->page['description'] = $schema['description'];

        $metaTagManager = GeneralUtility::makeInstance(MetaTagManagerRegistry::class);
        $metaTagManager->getManagerForProperty('og:title')->addProperty('og:title', $schema['title']);
        $metaTagManager->getManagerForProperty('og:description')->addProperty('og:description', $schema['description']);

        $this->view->assign('offer', $offer);
    }

    /**
     * @param \Blueways\BwGuild\Domain\Model\Offer|null $offer
     * @TYPO3\CMS\Extbase\Annotation\IgnoreValidation("offer")
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException
     */
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

    /**
     * @param \Blueways\BwGuild\Domain\Model\Offer $offer
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     */
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
            \TYPO3\CMS\Core\Messaging\AbstractMessage::OK);

        $this->redirect('edit');
    }

    /**
     * @return \TYPO3\CMS\Lang\LanguageService
     */
    protected function getLanguageService()
    {
        return $this->objectManager->get(\TYPO3\CMS\Lang\LanguageService::class);
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
            \TYPO3\CMS\Core\Messaging\AbstractMessage::OK);

        $this->redirect('edit');
    }

    /**
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException
     */
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

    /**
     *
     */
    protected function initializeAction()
    {
        parent::initializeAction();

        $this->mergeTyposcriptSettings();
    }

    public function injectAccessControlService(\Blueways\BwGuild\Service\AccessControlService $accessControlService)
    {
        $this->accessControlService = $accessControlService;
    }

    public function injectOfferRepository(\Blueways\BwGuild\Domain\Repository\OfferRepository $offerRepository)
    {
        $this->offerRepository = $offerRepository;
    }

    public function injectUserRepository(\Blueways\BwGuild\Domain\Repository\UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * Merges the typoscript settings with the settings from flexform
     */
    private function mergeTyposcriptSettings()
    {
        $configurationManager = $this->objectManager->get(ConfigurationManager::class);
        try {
            $typoscript = $configurationManager->getConfiguration(ConfigurationManager::CONFIGURATION_TYPE_FULL_TYPOSCRIPT);
            ArrayUtility::mergeRecursiveWithOverrule($typoscript['plugin.']['tx_bwguild_offerlist.']['settings.'],
                $this->settings, true, false, false);
            $this->settings = $typoscript['plugin.']['tx_bwguild_offerlist.']['settings.'];
        } catch (\TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException $exception) {
        }
    }

}
