<?php

namespace Blueways\BwGuild\Controller;

use Blueways\BwGuild\Domain\Model\Dto\Userinfo;
use Blueways\BwGuild\Domain\Model\User;
use Blueways\BwGuild\Domain\Repository\UserRepository;
use Blueways\BwGuild\Service\AccessControlService;
use Psr\Http\Message\ResponseInterface;
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
}
