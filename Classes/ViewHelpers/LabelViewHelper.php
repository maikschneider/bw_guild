<?php

namespace Blueways\BwGuild\ViewHelpers;

use Blueways\BwGuild\Domain\Model\User;
use TYPO3\CMS\Extbase\Domain\Model\FrontendUser;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithRenderStatic;

class LabelViewHelper extends AbstractViewHelper
{

    use CompileWithRenderStatic;

    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ) {
        $request = $renderingContext->getControllerContext()->getRequest();
        $extensionName = $extensionName ?? $request->getControllerExtensionName();

        $propertySnake = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $arguments['property']));
        $label = $propertySnake;

        // fix telephone label, different translation than field name in core
        $propertySnake = $propertySnake === 'telephone' ? 'phone' : $propertySnake;

        if ($arguments['class'] === FrontendUser::class) {

            $id = 'LLL:EXT:frontend/Resources/Private/Language/locallang_tca.xlf:fe_users.' . $propertySnake;
            $label = LocalizationUtility::translate($id, $extensionName, null, null);

            if (!$label) {
                $id = 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.' . $propertySnake;
                $label = LocalizationUtility::translate($id, $extensionName, null, null);
            }
        }

        if ($arguments['class'] === User::class) {
            $id = 'LLL:EXT:bw_guild/Resources/Private/Language/locallang_tca.xlf:user.' . $propertySnake;
            $label = LocalizationUtility::translate($id, $extensionName, null, null);
        }

        return $label;
    }

    public function initializeArguments()
    {
        $this->registerArgument('class', 'string', 'Class name of the object the label should be guessed from', true);
        $this->registerArgument('property', 'string', 'Property name the label shall be guessed for', true);
    }
}
