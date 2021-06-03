<?php

namespace Blueways\BwGuild\Domain\Model;

class FileReference extends \TYPO3\CMS\Extbase\Domain\Model\FileReference
{

    /**
     * uid of a sys_file
     *
     * @var integer
     */
    protected $originalFileIdentifier;

    /**
     * setOriginalResource
     *
     * @param \TYPO3\CMS\Core\Resource\ResourceInterface $originalResource
     * @return void
     */
    public function setOriginalResource(\TYPO3\CMS\Core\Resource\ResourceInterface $originalResource): void
    {
        $this->originalResource = $originalResource;
        $this->originalFileIdentifier = (int)$originalResource->getOriginalFile()->getUid();
        $this->uidLocal = (int)$originalResource->getOriginalFile()->getUid();
    }

    /**
     * setFile
     *
     * @param \TYPO3\CMS\Core\Resource\File $falFile
     * @return void
     */
    public function setFile(\TYPO3\CMS\Core\Resource\File $falFile): void
    {
        $this->originalFileIdentifier = (int)$falFile->getUid();
    }
}
