<?php

namespace Blueways\BwGuild\Controller;

use Blueways\BwGuild\Domain\Model\User;
use TYPO3\CMS\Core\Messaging\AbstractMessage;
use TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException;
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

/**
 * Class OfferController
 */
class OfferController extends ActionController
{
    public function __construct(
        protected OfferRepository $offerRepository,
        protected UserRepository $userRepository,
        protected AccessControlService $accessControlService
    ) {
    }

    public function listAction(?OfferDemand $demand = null): ResponseInterface
    {
        $demand = $demand ?? OfferDemand::createFromSettings($this->settings);

        // override filter from form
        if ($this->request->hasArgument('demand')) {
            $demand->overrideFromRequest($this->request);
        }

        $offers = $this->offerRepository->findDemanded($demand);

        // disbale indexing of list view
        $metaTagManager = GeneralUtility::makeInstance(MetaTagManagerRegistry::class);
        $metaTagManager->getManagerForProperty('robots')->addProperty('robots', 'noindex, follow');

        $this->view->setTemplate($this->settings['template'] ?? 'List');
        $this->view->assign('offers', $offers);

        return $this->htmlResponse($this->view->render());
    }

    public function latestAction(): ResponseInterface
    {
        $demand = $demand ?? OfferDemand::createFromSettings($this->settings);

        /** @var OfferRepository $repository */
        $repository = $this->objectManager->get($this->settings['record_type']);

        $offers = $repository->findDemanded($demand);
        $this->view->setTemplate($this->settings['template'] ?? 'Latest');
        $this->view->assign('offers', $offers);
        return $this->htmlResponse();
    }

    public function showAction(Offer $offer): ResponseInterface
    {
        $configurationManager = $this->objectManager->get(ConfigurationManager::class);
        $typoscript = $configurationManager->getConfiguration(ConfigurationManager::CONFIGURATION_TYPE_FULL_TYPOSCRIPT);

        $schema = $offer->getJsonSchema($typoscript);

        if ((int)$typoscript['plugin.']['tx_bwguild_offerlist.']['settings.']['schema.']['enable']) {
            $json = json_encode($schema);
            $jsCode = '<script type="application/ld+json">' . $json . '</script>';
            //$this->response->addAdditionalHeaderData($jsCode);
        }

        $GLOBALS['TSFE']->page['title'] = $schema['title'];
        $GLOBALS['TSFE']->page['description'] = $schema['description'];

        $metaTagManager = GeneralUtility::makeInstance(MetaTagManagerRegistry::class);
        $metaTagManager->getManagerForProperty('og:title')->addProperty('og:title', $schema['title']);
        $metaTagManager->getManagerForProperty('og:description')->addProperty('og:description', $schema['description']);

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

    protected function initializeAction()
    {
        parent::initializeAction();

        $this->mergeTyposcriptSettings();
    }

    private function mergeTyposcriptSettings()
    {
        $configurationManager = $this->objectManager->get(ConfigurationManager::class);
        try {
            $typoscript = $configurationManager->getConfiguration(ConfigurationManager::CONFIGURATION_TYPE_FULL_TYPOSCRIPT);
            ArrayUtility::mergeRecursiveWithOverrule(
                $typoscript['plugin.']['tx_bwguild_offerlist.']['settings.'],
                $this->settings,
                true,
                false,
                false
            );
            $this->settings = $typoscript['plugin.']['tx_bwguild_offerlist.']['settings.'];
        } catch (InvalidConfigurationTypeException $exception) {
        }
    }
}
