<?php

namespace Blueways\BwGuild\Domain\Model;

use JetBrains\PhpStorm\ArrayShape;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

class AbstractUserFeature extends AbstractEntity
{
    protected string $name = '';

    protected string $recordType = '';

    public function __construct()
    {
        $this->feUsers = new ObjectStorage();
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @param string $recordType
     */
    public function setRecordType(string $recordType): void
    {
        $this->recordType = $recordType;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getRecordType(): string
    {
        return $this->recordType;
    }

    #[ArrayShape(['label' => 'string', 'value' => 'int|null'])] public function getApiOutputArray(): array
    {
        return [
            'label' => $this->name,
            'value' => $this->getUid(),
        ];
    }
}
