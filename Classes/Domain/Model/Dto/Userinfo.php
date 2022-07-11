<?php

namespace Blueways\BwGuild\Domain\Model\Dto;

use Blueways\BwGuild\Domain\Model\User;

class Userinfo
{
    public array $user = [
        'url' => '',
        'username' => '',
        'uid' => '',
    ];

    public array $offers = [];

    public array $bookmarks = [];

    public function __construct(User $feUser)
    {
        $this->setUserdata($feUser);
    }

    protected function setUserdata(User $feUser): void
    {
        $this->user['username'] = $feUser->getUsername();
        $this->user['uid'] = $feUser->getUid();
    }

    public function setBookmarkOutput(array $relationHandlerResult): void
    {
        // @TODO: nice and compact output for all tables
        $this->bookmarks = $relationHandlerResult;
    }

}
