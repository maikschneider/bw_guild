<?php

namespace Blueways\BwGuild\Controller;

use Blueways\BwGuild\Domain\Model\Dto\Userinfo;
use Blueways\BwGuild\Domain\Model\User;
use Blueways\BwGuild\Domain\Repository\UserRepository;
use Blueways\BwGuild\Service\AccessControlService;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Database\RelationHandler;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

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

    //public function createBookmark(string $tableName, int $uid): ResponseInterface
    //{
    //
    //}
}
