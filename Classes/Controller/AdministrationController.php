<?php

namespace Blueways\BwGuild\Controller;

use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

class AdministrationController extends ActionController
{

    /**
     * @var \Blueways\BwGuild\Domain\Repository\UserRepository
     * @inject
     */
    protected $userRepository;

    public function indexAction()
    {
        $users = $this->userRepository->findAll();

        $this->view->assign('users', $users);
    }
}
