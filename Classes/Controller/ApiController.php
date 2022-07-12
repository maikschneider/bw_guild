<?php

namespace Blueways\BwGuild\Controller;

use Blueways\BwGuild\Domain\Model\Dto\Userinfo;
use Blueways\BwGuild\Domain\Model\User;
use Blueways\BwGuild\Domain\Repository\UserRepository;
use Blueways\BwGuild\Service\AccessControlService;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Database\RelationHandler;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Http\ForwardResponse;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Validation\Validator\GenericObjectValidator;

class ApiController extends ActionController
{
    protected AccessControlService $accessControlService;

    protected UserRepository $userRepository;

    public function __construct(AccessControlService $accessControlService, UserRepository $userRepository)
    {
        $this->accessControlService = $accessControlService;
        $this->userRepository = $userRepository;
    }

    public function userinfoAction(): ResponseInterface
    {
        if (!($userId = $this->accessControlService->getFrontendUserUid())) {
            return $this->responseFactory->createResponse('403', '');
        }

        /** @var User $user */
        $user = $this->userRepository->findByIdentifier($userId);
        if (!$user) {
            return $this->responseFactory->createResponse('404', '');
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

            $userinfo->setBookmarkOutput($relationHandler->results);
        }

        if ($this->settings['showPid']) {
            $url = $this->uriBuilder
                ->reset()
                ->setTargetPageUid((int)$this->settings['showPid'])
                ->uriFor(
                    'show',
                    ['user' => $userinfo->user['uid']],
                    'User',
                    'BwGuild',
                    'Usershow'
                );
            $userinfo->user['url'] = $url;
        }

        return $this->jsonResponse(json_encode($userinfo));
    }

    public function bookmarkAction(string $tableName, int $recordUid): ResponseInterface
    {
        if (!($userId = $this->accessControlService->getFrontendUserUid())) {
            return $this->responseFactory->createResponse('403', '');
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
            return $this->responseFactory->createResponse('403', '');
        }

        $user = $this->userRepository->findByUid($userId);

        $this->view->assign('user', $user);
        $html = $this->view->render();
        $response = ['html' => $html];

        return $this->jsonResponse(json_encode($response));
    }

    public function initializeUserEditUpdateAction()
    {
        $isLogoDelete = $this->request->hasArgument('deleteLogo') && $this->request->getArgument('deleteLogo');
        $isEmptyLogoUpdate = $_FILES['tx_bwguild_api']['name']['user']['logo'] === '';

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

    public function userEditUpdateAction(User $user): ResponseInterface
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

        $user->geoCodeAddress();
        $this->userRepository->update($user);

        return new ForwardResponse('userEditForm');
    }
}
