<?php

namespace Blueways\BwGuild\Domain\Model\Dto;

use ReflectionClass;

class UserDemand extends BaseDemand
{
    public const EXCLUDE_FIELDS = 'pid,lockToDomain,image,lastlogin,uid,_localizedUid,_languageUid,_versionedUid,passwordRepeat';

    public const SEARCH_FIELDS = 'first_name,last_name,username,email';

    public const TABLE = 'fe_users';

    public string $feature = '';

    public string $feGroup = '';

    /**
     * @param ?string $feature
     */
    public function __construct(?string $feature = null, ?string $feGroup = null)
    {
        $this->feature = $feature ?? '';
        $this->feGroup = $feGroup ?? '';
    }

    /**
     * @param array<string, mixed> $body
     * @return $this
     */
    public function overrideFromPostBody(array $body): self
    {
        $reflectionClass = new ReflectionClass($this);

        foreach ($body as $key => $value) {
            if (!$reflectionClass->hasProperty($key)) {
                continue;
            }

            settype($value, gettype($this->$key));
            $this->$key = $value;
        }

        return $this;
    }
}
