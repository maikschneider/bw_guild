<?php

namespace Blueways\BwGuild\Domain\Model;

use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\ResourceInterface;

class FileReference extends \TYPO3\CMS\Extbase\Domain\Model\FileReference
{
    protected int $originalFileIdentifier = 0;

    protected string $crop = '';

    /**
     * setOriginalResource
     *
     * @param ResourceInterface $originalResource
     */
    public function setOriginalResource(ResourceInterface $originalResource): void
    {
        $this->originalResource = $originalResource;
        $this->originalFileIdentifier = (int)$originalResource->getOriginalFile()->getUid();
        $this->uidLocal = (int)$originalResource->getOriginalFile()->getUid();
    }

    /**
     * setFile
     *
     * @param File $falFile
     */
    public function setFile(File $falFile): void
    {
        $this->originalFileIdentifier = (int)$falFile->getUid();
    }

    public function getCrop(): string
    {
        return $this->crop;
    }

    public function setCrop(string $crop): void
    {
        $this->crop = $crop;
    }
}
