<?php

namespace Blueways\BwGuild\Validation\Validator;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Domain\Model\FileReference;
use TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator;

class UserLogoValidator extends AbstractValidator
{
    /**
     * @param mixed $value
     */
    protected function isValid($value)
    {
        /** @var FileReference $file */
        $file = $value;
        $original = $file->getOriginalResource();

        $this->validateFileExtension($original);
        $this->validateFileSize($original);
    }

    /**
     * @param \TYPO3\CMS\Core\Resource\FileReference $reference
     */
    private function validateFileExtension(\TYPO3\CMS\Core\Resource\FileReference $reference): void
    {
        $allowedExtensions = GeneralUtility::trimExplode(
            ',',
            $GLOBALS['TYPO3_CONF_VARS']['GFX']['imagefile_ext'],
            true
        );

        if (in_array($reference->getExtension(), $allowedExtensions, true)) {
            return;
        }

        $this->addError('File type not allowed', 1579082088);
    }

    private function validateFileSize(\TYPO3\CMS\Core\Resource\FileReference $reference): void
    {
        if ($reference->getSize() <= 5000000) {
            return;
        }

        $this->addError('File size is more than 5MB', 1579082542);
    }
}
