<?php

namespace Blueways\BwGuild\Controller;

use Blueways\BwGuild\Domain\Model\Offer;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
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
     * @inject
     */
    protected $offerRepository;

    /**
     * @var \Blueways\BwGuild\Domain\Repository\UserRepository
     * @inject
     */
    protected $userRepository;

    /**
     * @var \Blueways\BwGuild\Service\AccessControlService
     * @inject
     */
    protected $accessControlService;

    /**
     *
     */
    public function listAction()
    {
        $demand = $this->offerRepository->createDemandObjectFromSettings($this->settings,
            'Blueways\BwGuild\Domain\Model\Dto\OfferDemand');

        $repository = $this->objectManager->get($this->settings['record_type']);

        $offers = $repository->findDemanded($demand);

        $this->view->assign('offers', $offers);
    }

    /**
     * @param \Blueways\BwGuild\Domain\Model\Offer $offer
     */
    public function showAction(Offer $offer)
    {
        $this->view->assign('offer', $offer);
    }

    /**
     * @param \Blueways\BwGuild\Domain\Model\Offer|null $offer
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

        if ($offer->getUid()) {
            $this->offerRepository->update($offer);
        } else {
            $this->offerRepository->add($offer);
        }

        $this->addFlashMessage(
            $this->getLanguageService()->sL('LLL:EXT:bw_guild/Resources/Private/Language/locallang_fe.xlf:user.update.success.message'),
            $this->getLanguageService()->sL('LLL:EXT:bw_guild/Resources/Private/Language/locallang_fe.xlf:user.update.success.title'),
            \TYPO3\CMS\Core\Messaging\AbstractMessage::OK);

        $this->redirect('edit');
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
     * @return \TYPO3\CMS\Lang\LanguageService
     */
    protected function getLanguageService()
    {
        return $this->objectManager->get(\TYPO3\CMS\Lang\LanguageService::class);
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
