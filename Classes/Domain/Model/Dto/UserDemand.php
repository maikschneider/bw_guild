<?php

namespace Blueways\BwGuild\Domain\Model\Dto;

class UserDemand extends BaseDemand
{

    public CONST EXCLUDE_FIELDS = 'pid,lockToDomain,image,lastlogin,uid,_localizedUid,_languageUid,_versionedUid,passwordRepeat';

    public CONST TABLE = 'fe_users';
}
