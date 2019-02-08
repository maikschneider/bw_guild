<?php

namespace Blueways\BwGuild\Controller;

use TYPO3\CMS\Backend\View\BackendTemplateView;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder;
use TYPO3Fluid\Fluid\View\ViewInterface;

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

    public function importerAction()
    {

    }
    
}
