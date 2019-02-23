<?php

namespace Blueways\BwGuild\Controller;

use Blueways\BwGuild\Domain\Model\Offer;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

/**
 * Class OfferController
 *
 * @package Blueways\BwGuild\Controller
 */
class OfferController extends ActionController
{

    /**
     * @var \Blueways\BwGuild\Domain\Repository\OfferRepository
     * @inject
     */
    protected $offerRepository;

    /**
     *
     */
    protected function initializeAction()
    {
        parent::initializeAction();

        $this->mergeTyposcriptSettings();
    }

    /**
     *
     */
    public function listAction()
    {
        $demand = $this->offerRepository->createDemandObjectFromSettings($this->settings, 'Blueways\BwGuild\Domain\Model\Dto\OfferDemand');

        $repository =  $this->objectManager->get($this->settings['record_type']);

        $offers = $repository->findDemanded($demand);

        $this->view->assign('offers', $offers);
    }

    /**
     * @param \Blueways\BwGuild\Domain\Model\Offer $offer
     */
    public function showAction(Offer $offer)
    {
        $this->view->assign('offer', $offer);
    }

    /**
     * Merges the typoscript settings with the settings from flexform
     */
    private function mergeTyposcriptSettings()
    {
        $configurationManager = $this->objectManager->get(ConfigurationManager::class);
        try {
            $typoscript = $configurationManager->getConfiguration(ConfigurationManager::CONFIGURATION_TYPE_FULL_TYPOSCRIPT);
            ArrayUtility::mergeRecursiveWithOverrule($typoscript['plugin.']['tx_bwguild_offerlist.']['settings.'],
                $this->settings, true, false, false);
            $this->settings = $typoscript['plugin.']['tx_bwguild_offerlist.']['settings.'];
        } catch (\TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException $exception) {
        }
    }
}
