<?php

namespace Blueways\BwGuild\Controller;

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
        return $this->jsonResponse('hello');
    }
}
