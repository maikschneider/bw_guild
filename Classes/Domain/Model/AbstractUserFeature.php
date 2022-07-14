<?php

namespace Blueways\BwGuild\Domain\Model;

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

    public function getJson(): string
    {
        return json_encode([
            'name' => $this->name,
        ]);
    }
}
