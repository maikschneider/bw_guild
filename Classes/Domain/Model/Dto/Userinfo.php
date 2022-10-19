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

    public string $html = '';

    public function __construct(User $feUser)
    {
        $this->setUserdata($feUser);
    }

    protected function setUserdata(User $feUser): void
    {
        $this->user['username'] = $feUser->getUsername();
        $this->user['uid'] = $feUser->getUid();
        $this->user['first_name'] = $feUser->getFirstName();
        $this->user['last_name'] = $feUser->getLastName();
        $this->user['email'] = $feUser->getEmail();
    }

    public function setBookmarkOutput(array $relationHandlerResult): void
    {
        $tableFieldsToKeep = [
            'pages' => ['uid', 'title'],
            'fe_users' => ['uid', 'username', 'first_name', 'last'],
            'tx_news_domain_model_news' => ['uid', 'title'],
        ];

        foreach ($relationHandlerResult as $tableName => &$records) {
            if (!isset($tableFieldsToKeep[$tableName])) {
                continue;
            }

            $fieldConfig = $tableFieldsToKeep[$tableName];
            $records = array_map(function ($record) use ($fieldConfig) {
                return array_filter($record, function ($key) use ($fieldConfig) {
                    return in_array($key, $fieldConfig);
                }, ARRAY_FILTER_USE_KEY);
            }, $records);
        }

        $this->bookmarks = $relationHandlerResult;
    }
}
