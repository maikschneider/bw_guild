<?php

namespace Blueways\BwGuild\Domain\Model;

use TYPO3\CMS\Core\Resource\ResourceInterface;
use TYPO3\CMS\Core\Resource\File;
class FileReference extends \TYPO3\CMS\Extbase\Domain\Model\FileReference
{
    /**
     * uid of a sys_file
     *
     * @var int
     */
    protected $originalFileIdentifier;

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
}
