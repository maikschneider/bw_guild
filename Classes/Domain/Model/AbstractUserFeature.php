<?php

namespace Blueways\BwGuild\Domain\Model;

use JetBrains\PhpStorm\ArrayShape;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

class AbstractUserFeature extends AbstractEntity
{
    protected string $name = '';

    protected string $recordType = '';

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
