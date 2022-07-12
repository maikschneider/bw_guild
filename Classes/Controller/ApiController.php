<?php

namespace Blueways\BwGuild\Controller;

use Blueways\BwGuild\Domain\Model\User;
use Blueways\BwGuild\Domain\Repository\UserRepository;
use Blueways\BwGuild\Service\AccessControlService;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;

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
        return $this->jsonResponse('{"success": true}');
    }
}
