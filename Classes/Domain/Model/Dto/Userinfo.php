<?php

namespace Blueways\BwGuild\Domain\Model\Dto;

use Blueways\BwGuild\Domain\Model\Offer;
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
        'logo' => '',
    ];

    public array $offers = [];

    public array $bookmarks = [];

    public string $html = '';

    public int $validUntil = 0;

    public array $bookmarkFieldsToKeep = [
        'pages' => ['uid', 'title'],
        'fe_users' => ['uid', 'username', 'first_name', 'last_name'],
        'tx_news_domain_model_news' => ['uid', 'title'],
        'sys_file' => ['uid', 'extension', 'name'],
    ];

    public function __construct(User $feUser)
    {
        $this->user['username'] = $feUser->getUsername();
        $this->user['uid'] = (string)$feUser->getUid();
        $this->user['first_name'] = $feUser->getFirstName();
        $this->user['last_name'] = $feUser->getLastName();
        $this->user['email'] = $feUser->getEmail();
        $expireDate = (new \DateTime('now'))->modify('+2 minutes');
        $this->validUntil = $expireDate->getTimestamp();
        $this->offers = $this->getUserOffers($feUser);
    }

    protected function getUserOffers(User $feUser): array
    {
        $offers = [];
        /** @var Offer $userOffer */
        foreach ($feUser->getOffers() ?? [] as $userOffer) {
            $offerArray = [];
            $offerArray['url'] = '#';
            $offerArray['uid'] = $userOffer->getUid();
            $offerArray['title'] = $userOffer->getTitle();
            $offerArray['public'] = $userOffer->isPublic();
            $offerArray['crdate'] = $userOffer->getCrdate()->getTimestamp();
            $offerArray['categories'] = [];
            foreach ($userOffer->getCategories() as $category) {
                $offerArray['categories'][] = [
                    'uid' => $category->getUid(),
                    'title' => $category->getTitle(),
                ];
            }

            $offers[] = $offerArray;
        }
        return $offers;
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
