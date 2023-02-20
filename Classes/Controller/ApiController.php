<?php

namespace Blueways\BwGuild\Controller;

use Blueways\BwGuild\Domain\Model\Dto\Userinfo;
use Blueways\BwGuild\Domain\Model\User;
use Blueways\BwGuild\Domain\Repository\AbstractUserFeatureRepository;
use Blueways\BwGuild\Domain\Repository\UserRepository;
use Blueways\BwGuild\Event\InitializeUserUpdateEvent;
use Blueways\BwGuild\Event\UserEditFormEvent;
use Blueways\BwGuild\Event\UserInfoApiEvent;
use Blueways\BwGuild\Service\AccessControlService;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Database\RelationHandler;
use TYPO3\CMS\Core\Utility\ClassNamingUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Domain\Model\FrontendUser;
use TYPO3\CMS\Extbase\Http\ForwardResponse;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Property\TypeConverter\PersistentObjectConverter;
use TYPO3\CMS\Extbase\Service\ImageService;

class ApiController extends ActionController
{
    protected AccessControlService $accessControlService;

    protected UserRepository $userRepository;

    protected AbstractUserFeatureRepository $featureRepository;

    protected CacheManager $cacheManager;

    public function __construct(
        AccessControlService $accessControlService,
        UserRepository $userRepository,
        AbstractUserFeatureRepository $featureRepository,
        CacheManager $cacheManager
    ) {
        $this->accessControlService = $accessControlService;
        $this->userRepository = $userRepository;
        $this->featureRepository = $featureRepository;
        $this->cacheManager = $cacheManager;
    }

    public function userinfoAction(): ResponseInterface
    {
        if (!($userId = $this->accessControlService->getFrontendUserUid())) {
            return $this->responseFactory->createResponse(403, '');
        }

        /** @var User|null $user */
        $user = $this->userRepository->findByIdentifier($userId);
        if (!$user) {
            return $this->responseFactory->createResponse(404, '');
        }

        $userinfo = new Userinfo($user);

        if ($bookmarks = $user->getBookmarks()) {
            $relationHandler = GeneralUtility::makeInstance(RelationHandler::class);
            $relationHandler->start(
                $bookmarks,
                $GLOBALS['TCA']['fe_users']['columns']['bookmarks']['config']['allowed'],
                '',
                '',
                'fe_users',
                $GLOBALS['TCA']['fe_users']['columns']['bookmarks']
            );
            $relationHandler->getFromDB();
            $userinfo->bookmarks = $relationHandler->results;
        }

        if ($this->settings['showPid'] && $user->getSlug()) {
            $url = $this->uriBuilder
                ->reset()
                ->setTargetPageUid((int)$this->settings['showPid'])
                ->uriFor(
                    'show',
                    ['user' => $user->getSlug()],
                    'User',
                    'BwGuild',
                    'Usershow'
                );
            $userinfo->user['url'] = $url;
        }

        if ($user->getLogo()) {
            try {
                $imageService = GeneralUtility::makeInstance(ImageService::class);
                $image = $imageService->getImage('', $user->getLogo(), true);
                $processedImage = $imageService->applyProcessingInstructions($image, ['width' => '75c', 'height' => '75c']);
                $userinfo->user['logo'] = $processedImage->getPublicUrl();
            } catch (\Exception) {
            }
        }

        $this->eventDispatcher->dispatch(new UserInfoApiEvent($userinfo));

        $this->view->assign('userinfo', $userinfo);
        $html = $this->view->render();
        $userinfo->html = $html;

        $userinfo->cleanBookmarkFields();

        return $this->jsonResponse((string)json_encode($userinfo));
    }

    public function bookmarkAction(string $tableName, int $recordUid): ResponseInterface
    {
        if (!($userId = $this->accessControlService->getFrontendUserUid())) {
            return $this->responseFactory->createResponse(403, '');
        }

        if ($this->request->getMethod() === 'POST') {
            $this->userRepository->addBookmarkForUser($userId, $tableName, $recordUid);
            return new ForwardResponse('userinfo');
        }

        if ($this->request->getMethod() === 'DELETE') {
            $this->userRepository->removeBookmarkForUser($userId, $tableName, $recordUid);
            return new ForwardResponse('userinfo');
        }

        return $this->responseFactory->createResponse(405);
    }

    public function userEditFormAction(): ResponseInterface
    {
        if (!($userId = $this->accessControlService->getFrontendUserUid())) {
            return $this->responseFactory->createResponse(403, '');
        }

        /** @var FrontendUser $user */
        $user = $this->userRepository->findByUid($userId);
        $features = $this->featureRepository->findAll();
        $groupedJsonFeatures = $this->featureRepository->getFeaturesAsJsonGroupedByRecordType();

        $this->view->assign('user', $user);
        $this->view->assign('features', $features);
        $this->view->assign('groupedJsonFeatures', $groupedJsonFeatures);

        /** @var UserEditFormEvent $event */
        $event = $this->eventDispatcher->dispatch(
            new UserEditFormEvent($user)
        );
        $this->view->assignMultiple($event->getAdditionalViewData());

        $html = $this->view->render();
        $response = ['html' => $html];

        return $this->jsonResponse((string)json_encode($response));
    }

    public function initializeUserEditUpdateAction(): void
    {
        $isLogoDelete = $this->request->hasArgument('deleteLogo') && $this->request->getArgument('deleteLogo');
        $isEmptyLogoUpdate = $_FILES['tx_bwguild_api']['name']['user']['logo'] === '';

        if ($isLogoDelete || $isEmptyLogoUpdate) {
            $this->ignoreLogoArgumentInUpdate();
        }

        $userModelName = ClassNamingUtility::translateRepositoryNameToModelName($this->userRepository::class);
        $this->arguments->getArgument('user')->setDataType($userModelName);

        $propertyMappingConfiguration = $this->arguments->getArgument('user')->getPropertyMappingConfiguration();
        $propertyMappingConfiguration->allowAllProperties();
        $propertyMappingConfiguration->forProperty('features.*')->allowCreationForSubProperty('*');
        $propertyMappingConfiguration->forProperty('features.*')->allowProperties('name', 'record_type');
        $propertyMappingConfiguration->forProperty('features.*')->setTypeConverter(
            GeneralUtility::makeInstance(PersistentObjectConverter::class),
        );
        $propertyMappingConfiguration->forProperty('features.*')->setTypeConverterOption(
            'TYPO3\CMS\Extbase\Property\TypeConverter\PersistentObjectConverter',
            PersistentObjectConverter::CONFIGURATION_CREATION_ALLOWED,
            true
        );

        $this->eventDispatcher->dispatch(new InitializeUserUpdateEvent($propertyMappingConfiguration));
    }

    protected function ignoreLogoArgumentInUpdate(): void
    {
        // unset logo argument
        $userArgument = $this->request->getArgument('user');
        unset($userArgument['logo']);
        $this->request->setArgument('user', $userArgument);
    }

    public function userEditUpdateAction(User $user): ResponseInterface
    {
        if (!$this->accessControlService->isLoggedIn($user)) {
            $this->throwStatus(403, 'No access to edit this user');
        }

        // delete all logos
        if ($this->request->hasArgument('deleteLogo') && $this->request->getArgument('deleteLogo') === '1') {
            $this->userRepository->deleteAllUserLogos((int)$user->getUid());
            $user->setLogo(null);
        }

        // delete existing logo(s) if new one is created
        $userArguments = $this->request->getArgument('user');
        if (isset($userArguments['logo']) && $user->getLogo()) {
            $this->userRepository->deleteAllUserLogos((int)$user->getUid());
        }

        $user->geoCodeAddress();
        $this->userRepository->update($user);

        // clear page cache by tag
        $this->cacheManager->flushCachesByTag('fe_users_' . $user->getUid());

        return new ForwardResponse('userEditForm');
    }
}
