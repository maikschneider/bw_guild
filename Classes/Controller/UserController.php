<?php

namespace Blueways\BwGuild\Controller;

use Blueways\BwGuild\Domain\Model\User;
use Blueways\BwGuild\Service\AccessControlService;
use Blueways\BwGuild\Utility\DemandUtility;
use TYPO3\CMS\Core\MetaTag\MetaTagManagerRegistry;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;
use TYPO3\CMS\Extbase\Domain\Model\FrontendUser;
use TYPO3\CMS\Lang\LanguageService;

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
     * @var \Blueways\BwGuild\Domain\Repository\CategoryRepository
     * @inject
     */
    protected $categoryRepository;

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
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\NoSuchArgumentException
     */
    public function listAction()
    {
        $demand = $this->userRepository->createDemandObjectFromSettings($this->settings);

        // override filter from form
        if ($this->request->hasArgument('demand')) {
            $demand->overrideDemand($this->request->getArgument('demand'));
        }

        // redirect to search action to display another view
        if ($this->settings['mode'] === 'search') {
            $this->forward('search');
        }

        // find user by demand
        $users = $this->userRepository->findDemanded($demand);

        // get categories by category settings in plugin
        $catConjunction = $this->settings['categoryConjunction'];
        if ($catConjunction === 'or' || $catConjunction === 'and') {
            $categories = $this->categoryRepository->findFromUidList($this->settings['categories']);
        } elseif ($catConjunction === 'notor' || $catConjunction === 'notand') {
            $categories = $this->categoryRepository->findFromUidListNot($this->settings['categories']);
        } else {
            $categories = $this->categoryRepository->findAll();
        }

        $this->view->assign('users', $users);
        $this->view->assign('demand', $demand);
        $this->view->assign('categories', $categories);
    }

    public function searchAction()
    {
        $demand = $this->userRepository->createDemandObjectFromSettings($this->settings);

        // override filter from form
        if ($this->request->hasArgument('demand')) {
            $demand->overrideDemand($this->request->getArgument('demand'));
        }

        $this->view->assign('demand', $demand);
    }

    /**
     * @param \TYPO3\CMS\Extbase\Domain\Model\FrontendUser $user
     */
    public function showAction(FrontendUser $user)
    {
        $schema = $user->getJsonSchema();

        if ((int)$this->settings['schema.']['enable']) {
            $json = json_encode($schema);
            $jsCode = '<script type="application/ld+json">' . $json . '</script>';
            $this->response->addAdditionalHeaderData($jsCode);
        }

        $GLOBALS['TSFE']->page['title'] = $schema['title'];
        $GLOBALS['TSFE']->page['description'] = $schema['description'];

        $metaTagManager = GeneralUtility::makeInstance(MetaTagManagerRegistry::class);
        $metaTagManager->getManagerForProperty('og:title')->addProperty('og:title', $schema['title']);
        $metaTagManager->getManagerForProperty('og:description')->addProperty('og:description', $schema['description']);

        $this->view->assign('user', $user);
    }

    /**
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     */
    public function editAction()
    {
        if (!$this->accessControlService->hasLoggedInFrontendUser()) {
            $this->throwStatus(403, 'Not logged in');
        }

        $user = $this->userRepository->findByUid($this->accessControlService->getFrontendUserUid());
        $categories = $this->categoryRepository->findFromUidList($this->settings['categories']);

        $this->view->assign('user', $user);
        $this->view->assign('categories', $categories);
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

        $user->geoCodeAddress();
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
        return $GLOBALS['LANG'] ?? $this->objectManager->get(LanguageService::class);
    }

    /**
     * @param User $user
     * @TYPO3\CMS\Extbase\Annotation\IgnoreValidation("user")
     */
    public function newAction(User $user = null)
    {
        if (!$user) {
            $user = new User();
        }
        $this->view->assign('user', $user);
    }

    /**
     * @param \Blueways\BwGuild\Domain\Model\User $user
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @TYPO3\CMS\Extbase\Annotation\Validate("Blueways\BwGuild\Validation\Validator\PasswordRepeatValidator", param="user")
     * @TYPO3\CMS\Extbase\Annotation\Validate("Blueways\BwGuild\Validation\Validator\CustomUsernameValidator", param="user")
     */
    public function createAction($user)
    {
        if ($this->accessControlService->hasLoggedInFrontendUser()) {
            $this->addFlashMessage(
                $this->getLanguageService()->sL('LLL:EXT:bw_guild/Resources/Private/Language/locallang_fe.xlf:user.create.loggedin.message'),
                $this->getLanguageService()->sL('LLL:EXT:bw_guild/Resources/Private/Language/locallang_fe.xlf:user.create.loggedin.title'),
                \TYPO3\CMS\Core\Messaging\AbstractMessage::WARNING);
            $this->redirect('new');
        }

        if ($user->getUid()) {
            $this->addFlashMessage(
                $this->getLanguageService()->sL('LLL:EXT:bw_guild/Resources/Private/Language/locallang_fe.xlf:user.create.exists.message'),
                $this->getLanguageService()->sL('LLL:EXT:bw_guild/Resources/Private/Language/locallang_fe.xlf:user.create.exists.title'),
                \TYPO3\CMS\Core\Messaging\AbstractMessage::ERROR);
            $this->redirect('new');
        }

        if ($this->settings['useEmailAsUsername'] === "1") {
            $user->setUsername($user->getEmail());
        }

        $user->setPassword($this->encryptPassword($user->getPassword()));
        $user->geoCodeAddress();

        $this->userRepository->add($user);

        $this->addFlashMessage(
            $this->getLanguageService()->sL('LLL:EXT:bw_guild/Resources/Private/Language/locallang_fe.xlf:user.create.success.message'),
            $this->getLanguageService()->sL('LLL:EXT:bw_guild/Resources/Private/Language/locallang_fe.xlf:user.create.success.title'),
            \TYPO3\CMS\Core\Messaging\AbstractMessage::OK);

        $this->view->assign('user', $user);
    }

    /**
     * @param string $password
     * @return string
     * @throws \TYPO3\CMS\Core\Crypto\PasswordHashing\InvalidPasswordHashException
     */
    private function encryptPassword(string $password): string
    {
        /** @var \TYPO3\CMS\Core\Crypto\PasswordHashing\PasswordHashFactory $passwordHashFactory */
        $passwordHashFactory = $this->objectManager->get(
            \TYPO3\CMS\Core\Crypto\PasswordHashing\PasswordHashFactory::class
        );
        $passwordHash = $passwordHashFactory->getDefaultHashInstance('FE');
        return $passwordHash->getHashedPassword($password);
    }

}
