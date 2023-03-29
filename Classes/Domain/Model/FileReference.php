<?php

namespace Blueways\BwGuild\Domain\Model;

class FileReference extends \TYPO3\CMS\Extbase\Domain\Model\FileReference
{
    protected int $originalFileIdentifier = 0;

    protected string $crop = '';

    public function setOriginalResource(\TYPO3\CMS\Core\Resource\ResourceInterface $originalResource): void
    {
        $this->originalResource = $originalResource;
        $this->originalFileIdentifier = (int)$originalResource->getOriginalFile()->getUid();
        $this->uidLocal = (int)$originalResource->getOriginalFile()->getUid();
    }

    public function setFile(\TYPO3\CMS\Core\Resource\File $falFile): void
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
