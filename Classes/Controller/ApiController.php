<?php

namespace Blueways\BwGuild\Controller;

use Blueways\BwGuild\Domain\Model\Dto\Userinfo;
use Blueways\BwGuild\Domain\Model\Offer;
use Blueways\BwGuild\Domain\Model\User;
use Blueways\BwGuild\Domain\Repository\AbstractUserFeatureRepository;
use Blueways\BwGuild\Domain\Repository\CategoryRepository;
use Blueways\BwGuild\Domain\Repository\OfferRepository;
use Blueways\BwGuild\Domain\Repository\UserRepository;
use Blueways\BwGuild\Event\InitializeUserUpdateEvent;
use Blueways\BwGuild\Event\UserEditFormEvent;
use Blueways\BwGuild\Event\UserInfoApiEvent;
use Blueways\BwGuild\Property\TypeConverter\PriceConverter;
use Blueways\BwGuild\Service\AccessControlService;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Database\RelationHandler;
use TYPO3\CMS\Core\Http\JsonResponse;
use TYPO3\CMS\Core\Utility\ClassNamingUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Domain\Model\FrontendUser;
use TYPO3\CMS\Extbase\Http\ForwardResponse;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Property\TypeConverter\PersistentObjectConverter;
use TYPO3\CMS\Extbase\Service\ImageService;

class ApiController extends ActionController
{

    public function __construct(
        protected AccessControlService $accessControlService,
        protected UserRepository $userRepository,
        protected AbstractUserFeatureRepository $featureRepository,
        protected CacheManager $cacheManager,
        protected OfferRepository $offerRepository,
        protected CategoryRepository $categoryRepository
    ) {
    }

    protected function getUserinfoResponse(User $user): Userinfo
    {
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
                $processedImage = $imageService->applyProcessingInstructions($image,
                    ['width' => '75c', 'height' => '75c']);
                $userinfo->user['logo'] = $processedImage->getPublicUrl();
            } catch (\Exception) {
            }
        }

        if ($this->settings['showOfferPid']) {
            foreach ($userinfo->offers as &$offer) {
                $url = $this->uriBuilder
                    ->reset()
                    ->setTargetPageUid((int)$this->settings['showOfferPid'])
                    ->uriFor(
                        'show',
                        ['offer' => $offer['uid']],
                        'Offer',
                        'BwGuild',
                        'Offershow'
                    );
                $offer['url'] = $url;
            }
        }

        $this->eventDispatcher->dispatch(new UserInfoApiEvent($userinfo));

        $this->view->assign('userinfo', $userinfo);
        $html = $this->view->render();
        $userinfo->html = $html;

        $userinfo->cleanBookmarkFields();

        return $userinfo;
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

        $userinfo = $this->getUserinfoResponse($user);

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
        $isEmptyLogoUpdate = !$_FILES || $_FILES['tx_bwguild_api']['name']['user']['logo'] === '';

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

    public function offerEditFormAction(?Offer $offer = null): ResponseInterface
    {
        if (!$this->accessControlService->hasLoggedInFrontendUser()) {
            $this->throwStatus(403, 'No access');
        }

        $userId = $this->accessControlService->getFrontendUserUid();
        /** @var User $user */
        $user = $this->userRepository->findByUid($userId);

        if ($offer && $offer->getFeUser()->getUid() !== $userId) {
            $this->throwStatus(403, 'Permission denied');
        }

        if (!$offer) {
            $offer = new Offer();
            $offer->setFeUser($user);
        }

        // get categories by category settings in plugin
        $catConjunction = $this->settings['categoryConjunction'] ?? '';
        if ($catConjunction === 'or' || $catConjunction === 'and') {
            $categories = $this->categoryRepository->findFromUidList($this->settings['categories'] ?? '');
        } elseif ($catConjunction === 'notor' || $catConjunction === 'notand') {
            $categories = $this->categoryRepository->findFromUidListNot($this->settings['categories'] ?? '');
        } else {
            $categories = $this->categoryRepository->findAll();
        }

        $this->view->assign('offer', $offer);
        $this->view->assign('categories', $categories);

        ///** @var UserEditFormEvent $event */
        //$event = $this->eventDispatcher->dispatch(
        //    new UserEditFormEvent($user)
        //);
        //$this->view->assignMultiple($event->getAdditionalViewData());

        $userinfo = $this->getUserinfoResponse($user);
        $html = $this->view->render();
        $response = ['html' => $html, 'userinfo' => $userinfo];

        return $this->jsonResponse((string)json_encode($response));
    }

    public function initializeOfferEditUpdateAction(): void
    {
        $propertyMappingConfiguration = $this->arguments->getArgument('offer')->getPropertyMappingConfiguration();
        $propertyMappingConfiguration->forProperty('price')->setTypeConverter(
            GeneralUtility::makeInstance(PriceConverter::class),
        );
    }

    public function offerEditUpdateAction(Offer $offer): ResponseInterface
    {
        if (!$this->accessControlService->hasLoggedInFrontendUser()) {
            $this->throwStatus(403, 'No access');
        }

        $userId = $this->accessControlService->getFrontendUserUid();

        if ($offer->getFeUser()->getUid() !== $userId) {
            $this->throwStatus(403, 'Permission denied');
        }

        $offer->updateSlug();

        if ($offer->getUid()) {
            $this->offerRepository->update($offer);
        } else {
            $this->offerRepository->add($offer);
        }

        // clear page cache by tag
        $this->cacheManager->flushCachesByTag('tx_bwguild_domain_model_offer_' . $offer->getUid());

        return new ForwardResponse('offerEditForm');
    }

    public function offerDeleteAction(Offer $offer): ResponseInterface
    {
        if (!$this->accessControlService->hasLoggedInFrontendUser()) {
            $this->throwStatus(403, 'No access');
        }

        $userId = $this->accessControlService->getFrontendUserUid();

        if ($offer->getFeUser()->getUid() !== $userId) {
            $this->throwStatus(403, 'Permission denied');
        }

        $this->offerRepository->remove($offer);
        $this->cacheManager->flushCachesByTag('tx_bwguild_domain_model_offer_' . $offer->getUid());

        return new JsonResponse(['success' => true]);
    }
}
