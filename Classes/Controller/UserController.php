<?php

namespace Blueways\BwGuild\Controller;

use Blueways\BwGuild\Domain\Model\Dto\UserDemand;
use Blueways\BwGuild\Domain\Model\User;
use Blueways\BwGuild\Domain\Repository\CategoryRepository;
use Blueways\BwGuild\Domain\Repository\UserRepository;
use Blueways\BwGuild\Service\AccessControlService;
use GeorgRinger\NumberedPagination\NumberedPagination as NumberedPaginationAlias;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Crypto\PasswordHashing\PasswordHashFactory;
use TYPO3\CMS\Core\Exception\Page\PageNotFoundException;
use TYPO3\CMS\Core\Http\NullResponse;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\MetaTag\MetaTagManagerRegistry;
use TYPO3\CMS\Core\Page\AssetCollector;
use TYPO3\CMS\Core\Pagination\ArrayPaginator;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;
use TYPO3\CMS\Extbase\Property\TypeConverter\PersistentObjectConverter;
use TYPO3\CMS\Extbase\Validation\Validator\GenericObjectValidator;

/**
 * Class UserController
 */
class UserController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController
{
    protected UserRepository $userRepository;

    protected CategoryRepository $categoryRepository;

    protected AccessControlService $accessControlService;

    protected CacheManager $cacheManager;

    public function __construct(
        UserRepository $userRepository,
        CategoryRepository $categoryRepository,
        AccessControlService $accessControlService,
        CacheManager $cacheManager
    ) {
        $this->userRepository = $userRepository;
        $this->categoryRepository = $categoryRepository;
        $this->accessControlService = $accessControlService;
        $this->cacheManager = $cacheManager;
    }

    public function initializeAction(): void
    {
        parent::initializeAction();

        $this->mergeTyposcriptSettings();

        if ($this->request->hasArgument('demand')) {
            $propertyMappingConfiguration = $this->arguments->getArgument('demand')->getPropertyMappingConfiguration();
            $propertyMappingConfiguration->allowAllProperties();
            $propertyMappingConfiguration->setTypeConverterOption(
                PersistentObjectConverter::class,
                PersistentObjectConverter::CONFIGURATION_CREATION_ALLOWED,
                true
            );
        }
    }

    /**
     * Merges the typoscript settings with the settings from flexform
     */
    private function mergeTyposcriptSettings(): void
    {
        $configurationManager = GeneralUtility::makeInstance(ConfigurationManager::class);
        $typoscript = $configurationManager->getConfiguration(ConfigurationManager::CONFIGURATION_TYPE_FULL_TYPOSCRIPT);
        ArrayUtility::mergeRecursiveWithOverrule(
            $typoscript['plugin.']['tx_bwguild_userlist.']['settings.'],
            $typoscript['plugin.']['tx_bwguild.']['settings.'],
            true,
            false,
            false
        );
        ArrayUtility::mergeRecursiveWithOverrule(
            $typoscript['plugin.']['tx_bwguild_userlist.']['settings.'],
            $this->settings,
            true,
            false,
            false
        );
        $this->settings = $typoscript['plugin.']['tx_bwguild_userlist.']['settings.'];
    }

    /**
     * @param ?UserDemand $demand
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\NoSuchArgumentException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     * @throws \TYPO3\CMS\Extbase\Persistence\Generic\Exception
     */
    public function listAction(UserDemand $demand = null): ResponseInterface
    {
        $demand = $demand ?? UserDemand::createFromSettings($this->settings);

        if ($this->settings['hideFullResultList'] && !($this->request->hasArgument('demand') || $this->request->hasArgument('currentPage'))) {
            return new NullResponse();
        }

        // find user by demand
        $users = $this->userRepository->findDemanded($demand);

        // create pagination
        $itemsPerPage = (int)$this->settings['maxItems'];
        $maximumLinks = (int)$this->settings['maximumLinks'];
        $currentPage = $this->request->hasArgument('currentPage') ? (int)$this->request->getArgument('currentPage') : 1;
        $paginator = new ArrayPaginator($users, $currentPage, $itemsPerPage);
        $pagination = new NumberedPaginationAlias($paginator, $maximumLinks);
        $this->view->assign('pagination', [
            'paginator' => $paginator,
            'pagination' => $pagination,
        ]);

        $numberOfResults = count($users);
        $users = $this->userRepository->mapResultToObjects($paginator->getPaginatedItems());

        // get categories by category settings in plugin
        $catConjunction = $this->settings['categoryConjunction'];
        if ($catConjunction === 'or' || $catConjunction === 'and') {
            $categories = $this->categoryRepository->findFromUidList($this->settings['categories']);
        } elseif ($catConjunction === 'notor' || $catConjunction === 'notand') {
            $categories = $this->categoryRepository->findFromUidListNot($this->settings['categories']);
        } else {
            $categories = $this->categoryRepository->findAll();
        }

        // disable indexing of list view
        $metaTagManager = GeneralUtility::makeInstance(MetaTagManagerRegistry::class);
        $metaTagManager->getManagerForProperty('robots')->addProperty('robots', 'noindex, follow');

        $this->view->assign('users', $users);
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

    /**
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Doctrine\DBAL\Driver\Exception
     */
    public function searchAction(): ResponseInterface
    {
        $demand = UserDemand::createFromSettings($this->settings);

        // override filter from form
        if ($body = $this->request->getParsedBody()) {
            if (isset($body['tx_bwguild_userlist'], $body['tx_bwguild_userlist']['demand'])) {
                $demand = $demand->overrideFromPostBody($body['tx_bwguild_userlist']['demand']);
            }
        }

        // add autocompleter values
        if ($this->settings['autocompleter']) {
            $autocompleteData = $this->userRepository->getAutocompleteData($this->settings['autocompleter']);
            $this->view->assign('autocompleter', $autocompleteData);
        }

        $header = $this->configurationManager->getContentObject()?->data['header'] ?? '';

        $this->view->assign('demand', $demand);
        $this->view->assign('header', $header);

        return $this->htmlResponse($this->view->render());
    }

    /**
     * @throws \JsonException
     */
    public function showAction(?User $user = null): ResponseInterface
    {
        if (!$user || !$user->isPublicProfile()) {
            throw new PageNotFoundException('Profile not found', 1666954785);
        }

        // Add cache tag
        if (!empty($GLOBALS['TSFE']) && is_object($GLOBALS['TSFE'])) {
            static $cacheTagsSet = false;
            $typoScriptFrontendController = $GLOBALS['TSFE'];
            if (!$cacheTagsSet) {
                $typoScriptFrontendController->addCacheTags(['fe_users_' . $user->getUid()]);
                $cacheTagsSet = true;
            }
        }

        $schema = $user->getJsonSchema($this->settings);

        if (isset($schema['logo'])) {
            $schema['logo'] = 'https://' . $_SERVER['SERVER_NAME'] . '/' . $schema['logo'];
            $schema['image'] = $schema['logo'];
        }

        if ((int)$this->settings['schema.']['enable']) {
            $json = json_encode($schema, JSON_THROW_ON_ERROR);
            $assetCollector = GeneralUtility::makeInstance(AssetCollector::class);
            $assetCollector->addInlineJavaScript('bwguild_json', $json, ['type' => 'application/ld+json']);
        }

        $GLOBALS['TSFE']->page['title'] = $schema['name'];
        $GLOBALS['TSFE']->page['description'] = $schema['description'];

        $metaTagManager = GeneralUtility::makeInstance(MetaTagManagerRegistry::class);
        $metaTagManager->getManagerForProperty('og:title')->addProperty('og:title', $schema['name']);
        $metaTagManager->getManagerForProperty('og:description')->addProperty('og:description', $schema['description']);
        $metaTagManager->getManagerForProperty('og:image')->addProperty('og:image', $schema['image']);

        $this->view->assign('user', $user);

        return $this->htmlResponse($this->view->render());
    }

    /**
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     * @throws \TYPO3\CMS\Core\Http\PropagateResponseException
     */
    public function editAction(): ResponseInterface
    {
        if (!$this->accessControlService->hasLoggedInFrontendUser()) {
            $this->throwStatus(403, 'Not logged in');
        }

        $user = $this->userRepository->findByUid($this->accessControlService->getFrontendUserUid());
        $categories = $this->categoryRepository->findFromUidList($this->settings['categories']);

        $this->view->assign('user', $user);
        $this->view->assign('categories', $categories);

        return $this->htmlResponse($this->view->render());
    }

    /**
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\NoSuchArgumentException
     */
    public function initializeUpdateAction(): void
    {
        $isLogoDelete = $this->request->hasArgument('deleteLogo') && $this->request->getArgument('deleteLogo');
        $isEmptyLogoUpdate = $_FILES['tx_bwguild_userlist']['name']['user']['logo'] === '';

        if ($isLogoDelete || $isEmptyLogoUpdate) {
            $this->ignoreLogoArgumentInUpdate();
        }
    }

    protected function ignoreLogoArgumentInUpdate(): void
    {
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

    /**
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Core\Http\PropagateResponseException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\NoSuchArgumentException
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

        // delete existing logo(s) if new one is created
        $userArguments = $this->request->getArgument('user');
        if (isset($userArguments['logo']) && $user->getLogo()) {
            $this->userRepository->deleteAllUserLogos($user->getUid());
        }

        // clear page cache by tag
        $this->cacheManager->flushCachesByTag('fe_users_' . $user->getUid());

        $user->geoCodeAddress();
        $this->userRepository->update($user);

        // clear page cache by tag
        $this->cacheManager->flushCachesByTag('fe_users_' . $user->getUid());

        $this->addFlashMessage(
            $this->getLanguageService()->sL('LLL:EXT:bw_guild/Resources/Private/Language/locallang_fe.xlf:user.update.success.message'),
            $this->getLanguageService()->sL('LLL:EXT:bw_guild/Resources/Private/Language/locallang_fe.xlf:user.update.success.title'),
            \TYPO3\CMS\Core\Messaging\AbstractMessage::OK
        );

        $this->redirect('edit');
    }

    /**
     * @return \TYPO3\CMS\Core\Localization\LanguageService
     */
    protected function getLanguageService(): LanguageService
    {
        return $GLOBALS['LANG'] ?? GeneralUtility::makeInstance(LanguageService::class);
    }

    /**
     * @param \Blueways\BwGuild\Domain\Model\User|null $user
     * @TYPO3\CMS\Extbase\Annotation\IgnoreValidation("user")
     */
    public function newAction(User $user = null): void
    {
        if (!$user) {
            $user = new User();
        }
        $this->view->assign('user', $user);
    }

    /**
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     * @throws \TYPO3\CMS\Core\Crypto\PasswordHashing\InvalidPasswordHashException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     */
    public function createAction(User $user): void
    {
        if ($this->accessControlService->hasLoggedInFrontendUser()) {
            $this->addFlashMessage(
                $this->getLanguageService()->sL('LLL:EXT:bw_guild/Resources/Private/Language/locallang_fe.xlf:user.create.loggedin.message'),
                $this->getLanguageService()->sL('LLL:EXT:bw_guild/Resources/Private/Language/locallang_fe.xlf:user.create.loggedin.title'),
                \TYPO3\CMS\Core\Messaging\AbstractMessage::WARNING
            );
            $this->redirect('new');
        }

        if ($user->getUid()) {
            $this->addFlashMessage(
                $this->getLanguageService()->sL('LLL:EXT:bw_guild/Resources/Private/Language/locallang_fe.xlf:user.create.exists.message'),
                $this->getLanguageService()->sL('LLL:EXT:bw_guild/Resources/Private/Language/locallang_fe.xlf:user.create.exists.title'),
                \TYPO3\CMS\Core\Messaging\AbstractMessage::ERROR
            );
            $this->redirect('new');
        }

        if ($this->settings['useEmailAsUsername'] === '1') {
            $user->setUsername($user->getEmail());
        }

        $user->setPassword($this->encryptPassword($user->getPassword()));
        $user->geoCodeAddress();

        $this->userRepository->add($user);

        $this->addFlashMessage(
            $this->getLanguageService()->sL('LLL:EXT:bw_guild/Resources/Private/Language/locallang_fe.xlf:user.create.success.message'),
            $this->getLanguageService()->sL('LLL:EXT:bw_guild/Resources/Private/Language/locallang_fe.xlf:user.create.success.title'),
            \TYPO3\CMS\Core\Messaging\AbstractMessage::OK
        );

        $this->view->assign('user', $user);
    }

    /**
     * @param string $password
     * @return string
     * @throws \TYPO3\CMS\Core\Crypto\PasswordHashing\InvalidPasswordHashException
     */
    private function encryptPassword(string $password): string
    {
        $passwordHashFactory = GeneralUtility::makeInstance(PasswordHashFactory::class);
        return $passwordHashFactory->getDefaultHashInstance('FE')->getHashedPassword($password);
    }
}
