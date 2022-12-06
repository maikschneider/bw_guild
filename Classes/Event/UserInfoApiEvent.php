<?php

namespace Blueways\BwGuild\Event;

use Blueways\BwGuild\Domain\Model\Dto\Userinfo;

final class UserInfoApiEvent
{
    private Userinfo $userinfo;

    public function __construct(Userinfo $userinfo)
    {
        $this->userinfo = $userinfo;
    }

    public function getUserinfo(): Userinfo
    {
        return $this->userinfo;
    }

    public function setUserinfo(Userinfo $userinfo): void
    {
        $this->userinfo = $userinfo;
    }
}
