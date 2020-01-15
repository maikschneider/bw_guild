<?php

namespace Blueways\BwGuild\Controller;

use Blueways\BwGuild\Domain\Model\User;
use Blueways\BwGuild\Service\AccessControlService;
use Blueways\BwGuild\Utility\DemandUtility;
use TYPO3\CMS\Core\MetaTag\MetaTagManagerRegistry;
use TYPO3\CMS\Core\Resource\DuplicationBehavior;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;
use TYPO3\CMS\Extbase\Validation\Validator\GenericObjectValidator;
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

        // disbale indexing of list view
        $metaTagManager = GeneralUtility::makeInstance(MetaTagManagerRegistry::class);
        $metaTagManager->getManagerForProperty('robots')->addProperty('robots', 'noindex, follow');

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
     * @param \Blueways\BwGuild\Domain\Model\User $user
     */
    public function showAction(User $user)
    {
        $schema = $user->getJsonSchema($this->settings);

        if (isset($schema['logo'])) {
            $schema['logo'] = 'https://' . $_SERVER['SERVER_NAME'] . '/' . $schema['logo'];
            $schema['image'] = 'https://' . $_SERVER['SERVER_NAME'] . '/' . $schema['logo'];
        }

        if ((int)$this->settings['schema.']['enable']) {
            $json = json_encode($schema);
            $jsCode = '<script type="application/ld+json">' . $json . '</script>';
            $this->response->addAdditionalHeaderData($jsCode);
        }

        $GLOBALS['TSFE']->page['title'] = $schema['name'];
        $GLOBALS['TSFE']->page['description'] = $schema['description'];

        $metaTagManager = GeneralUtility::makeInstance(MetaTagManagerRegistry::class);
        $metaTagManager->getManagerForProperty('og:title')->addProperty('og:title', $schema['name']);
        $metaTagManager->getManagerForProperty('og:description')->addProperty('og:description', $schema['description']);
        $metaTagManager->getManagerForProperty('og:image')->addProperty('og:image', $schema['image']);

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

    public function initializeUpdateAction()
    {
        if ($this->arguments->hasArgument('user')) {

            $deleteLog = $this->request->hasArgument('deleteLogo') && $this->request->getArgument('deleteLogo') ? true : false;

            // ignore logo parameter if empty
            if ($deleteLog || $_FILES['tx_bwguild_userlist']['name']['user']['logo'] === '') {
                // unset logo argument
                $userArgument = $this->request->getArgument('user');
                unset($userArgument['logo']);
                $this->request->setArgument('user', $userArgument);

                // unset logo validator
                $validator = $this->arguments->getArgument('user')->getValidator();
                foreach ($validator->getValidators() as $subValidator) {
                    /** @var GenericObjectValidator $subValidatorSub */
                    foreach ($subValidator->getValidators() as $subValidatorSub) {
                        $subValidatorSub->getPropertyValidators('logo')->removeAll(
                            $subValidatorSub->getPropertyValidators('logo')
                        );
                    }
                }
            }
        }
    }

    /**
     * @param \Blueways\BwGuild\Domain\Model\User $user
     * @throws \TYPO3\CMS\Core\Resource\Exception\ExistingTargetFolderException
     * @throws \TYPO3\CMS\Core\Resource\Exception\InsufficientFolderAccessPermissionsException
     * @throws \TYPO3\CMS\Core\Resource\Exception\InsufficientFolderWritePermissionsException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\NoSuchArgumentException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     */
    public function updateAction(User $user): void
    {
        if (!$this->accessControlService->isLoggedIn($user)) {
            $this->throwStatus(403, 'No access to edit this user');
        }

        // delete all logos
        if ($this->request->hasArgument('deleteLogo') && $this->request->getArgument('deleteLogo') === '1') {
            $this->userRepository->deleteAllUserLogos($user->getUid());
        }

        // move logo if newly created
        $userArguments = $this->request->getArgument('user');
        if (isset($userArguments['logo']) && $logo = $user->getLogo()) {
            $this->userRepository->deleteAllUserLogos($user->getUid());
            $this->moveLogo($logo);
        }

        // delete old file references -> backend would show up these

        $user->geoCodeAddress();
        $this->userRepository->update($user);

        $this->addFlashMessage(
            $this->getLanguageService()->sL('LLL:EXT:bw_guild/Resources/Private/Language/locallang_fe.xlf:user.update.success.message'),
            $this->getLanguageService()->sL('LLL:EXT:bw_guild/Resources/Private/Language/locallang_fe.xlf:user.update.success.title'),
            \TYPO3\CMS\Core\Messaging\AbstractMessage::OK);

        $this->redirect('edit');
    }

    /**
     * @param \TYPO3\CMS\Extbase\Domain\Model\FileReference $logo
     * @throws \TYPO3\CMS\Core\Resource\Exception\ExistingTargetFolderException
     * @throws \TYPO3\CMS\Core\Resource\Exception\InsufficientFolderAccessPermissionsException
     * @throws \TYPO3\CMS\Core\Resource\Exception\InsufficientFolderWritePermissionsException
     */
    private function moveLogo(\TYPO3\CMS\Extbase\Domain\Model\FileReference $logo): void
    {
        $resourceFactory = $this->objectManager->get(ResourceFactory::class);

        // determine target storage and directory
        $targetParts = GeneralUtility::trimExplode(':', $this->settings['userLogoFolder']);
        $targetFolder = count($targetParts) === 2 ? $targetParts[1] : $targetParts[0];
        $storageUid = count($targetParts) === 2 ? $targetParts[0] : null;
        $storage = $storageUid ? $resourceFactory->getStorageObject($storageUid) : $resourceFactory->getDefaultStorage();

        // abort if no valid storage
        if (!$storage) {
            return;
        }

        // create logo folder
        if (!$storage->hasFolder($targetFolder)) {
            $storage->createFolder($targetFolder);
        }

        // move file
        $file = $resourceFactory->getFileObjectByStorageAndIdentifier(
            $logo->getOriginalResource()->getStorage()->getUid(),
            $logo->getOriginalResource()->getIdentifier());

        if (!$file) {
            return;
        }

        $file->moveTo($storage->getFolder($targetFolder), $logo->getOriginalResource()->getName(),
            DuplicationBehavior::RENAME);
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
