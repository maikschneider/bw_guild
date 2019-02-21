<?php

namespace Blueways\BwGuild\Controller;

use Blueways\BwGuild\Service\AccessControlService;
use Blueways\BwGuild\Utility\DemandUtility;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;
use TYPO3\CMS\Extbase\Domain\Model\FrontendUser;

/**
 * Class UserController
 *
 * @package Blueways\BwGuild\Controller
 */
class UserController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController
{

    /**
     * @var \Blueways\BwGuild\Domain\Repository\UserRepository
     * @inject
     */
    protected $userRepository;

    /**
     * @var \Blueways\BwGuild\Service\AccessControlService
     */
    protected $accessControlService;

    public function initializeAction()
    {
        parent::initializeAction();

        $this->accessControlService = GeneralUtility::makeInstance(AccessControlService::class);
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
            ArrayUtility::mergeRecursiveWithOverrule($typoscript['plugin.']['tx_bwguild_userlist.']['settings.'],
                $typoscript['plugin.']['tx_bwguild.']['settings.'], true, false, false);
            ArrayUtility::mergeRecursiveWithOverrule($typoscript['plugin.']['tx_bwguild_userlist.']['settings.'],
                $this->settings, true, false, false);
            $this->settings = $typoscript['plugin.']['tx_bwguild_userlist.']['settings.'];
        } catch (\TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException $exception) {
        }
    }

    /**
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     */
    public function listAction()
    {
        /** @var \Blueways\BwGuild\Domain\Model\Dto\UserDemand $demand */
        $demandUtility = $this->objectManager->get(DemandUtility::class);
        $demand = $demandUtility::createDemandObjectFromSettings($this->settings);

        $users = $this->userRepository->findDemanded($demand);

        $this->view->assign('users', $users);
    }

    /**
     * @param \TYPO3\CMS\Extbase\Domain\Model\FrontendUser $user
     */
    public function showAction(FrontendUser $user)
    {
        $this->view->assign('user', $user);
    }

    /**
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException
     */
    public function editAction()
    {
        if (!$this->accessControlService->hasLoggedInFrontendUser()) {
            $this->throwStatus(403, 'Not logged in');
        }

        $user = $this->userRepository->findByUid($this->accessControlService->getFrontendUserUid());

        $this->view->assign('user', $user);
    }

    /**
     * @param \Blueways\BwGuild\Domain\Model\User $user
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @TYPO3\CMS\Extbase\Annotation\IgnoreValidation("user")
     */
    public function updateAction($user)
    {
        if (!$this->accessControlService->isLoggedIn($user)) {
            $this->throwStatus(403, 'No access to edit this user');
        }

        $this->userRepository->update($user);

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
        return $GLOBALS['LANG'];
    }

}
