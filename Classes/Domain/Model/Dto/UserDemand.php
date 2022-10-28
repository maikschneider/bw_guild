<?php

namespace Blueways\BwGuild\Domain\Model\Dto;

class UserDemand extends BaseDemand
{
    public const EXCLUDE_FIELDS = 'pid,lockToDomain,image,lastlogin,uid,_localizedUid,_languageUid,_versionedUid,passwordRepeat';

    public const TABLE = 'fe_users';

    public string $feature = '';
}
