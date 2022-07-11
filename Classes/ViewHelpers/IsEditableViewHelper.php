<?php

namespace Blueways\BwGuild\ViewHelpers;

use Blueways\BwGuild\Domain\Model\Offer;
use Blueways\BwGuild\Service\AccessControlService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Domain\Model\FrontendUser;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithRenderStatic;

class IsEditableViewHelper extends AbstractViewHelper
{
    use CompileWithRenderStatic;

    public function initializeArguments()
    {
        $this->registerArgument('offer', 'mixed', 'Offer object to edit', false);
        $this->registerArgument('user', 'mixed', 'Frontend user object to edit', false);
    }

    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ) {
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $accessControlService = $objectManager->get(AccessControlService::class);

        if (!$accessControlService->hasLoggedInFrontendUser()) {
            return false;
        }

        if ($arguments['offer'] instanceof Offer && $arguments['offer']->getFeUser()) {
            return $arguments['offer']->getFeUser()->getUid() === $accessControlService->getFrontendUserUid();
        }

        if ($arguments['user'] instanceof FrontendUser) {
            return $arguments['user']->getUid() === $accessControlService->getFrontendUserUid();
        }

        return false;
    }
}
