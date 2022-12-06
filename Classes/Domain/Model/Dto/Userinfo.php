<?php

namespace Blueways\BwGuild\Domain\Model\Dto;

use Blueways\BwGuild\Domain\Model\User;

class Userinfo
{
    /**
     * @var string[]
     */
    public array $user = [
        'url' => '',
        'username' => '',
        'uid' => '',
    ];

    public array $offers = [];

    public array $bookmarks = [];

    public string $html = '';

    public int $validUntil = 0;

    public array $bookmarkFieldsToKeep = [
        'pages' => ['uid', 'title'],
        'fe_users' => ['uid', 'username', 'first_name', 'last_name'],
        'tx_news_domain_model_news' => ['uid', 'title'],
    ];

    public function __construct(User $feUser)
    {
        $this->setUserdata($feUser);
        $expireDate = (new \DateTime('now'))->modify('+2 minutes');
        $this->validUntil = $expireDate->getTimestamp();
    }

    protected function setUserdata(User $feUser): void
    {
        $this->user['username'] = $feUser->getUsername();
        $this->user['uid'] = (string)$feUser->getUid();
        $this->user['first_name'] = $feUser->getFirstName();
        $this->user['last_name'] = $feUser->getLastName();
        $this->user['email'] = $feUser->getEmail();
    }

    public function cleanBookmarkFields(): void
    {
        foreach ($this->bookmarks as $tableName => &$records) {
            if (!isset($this->bookmarkFieldsToKeep[$tableName])) {
                continue;
            }

            $fieldConfig = $this->bookmarkFieldsToKeep[$tableName];
            $records = array_map(function ($record) use ($fieldConfig) {
                return array_filter($record, function ($key) use ($fieldConfig) {
                    return in_array($key, $fieldConfig);
                }, ARRAY_FILTER_USE_KEY);
            }, $records);
        }

        unset($this->bookmarkFieldsToKeep);
    }
}
