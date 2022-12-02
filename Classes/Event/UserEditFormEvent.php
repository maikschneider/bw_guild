<?php

namespace Blueways\BwGuild\Event;

use TYPO3\CMS\Extbase\Domain\Model\FrontendUser;

final class UserEditFormEvent
{
    private FrontendUser $user;

    /**
     * @var mixed[]
     */
    private array $additionalViewData = [];

    public function __construct(FrontendUser $user)
    {
        $this->user = $user;
    }

    public function getAdditionalViewData(): array
    {
        return $this->additionalViewData;
    }

    /**
     * @return FrontendUser
     */
    public function getUser(): FrontendUser
    {
        return $this->user;
    }

    /**
     * @param mixed[] $additionalViewData
     */
    public function setAdditionalViewData(array $additionalViewData): void
    {
        $this->additionalViewData = $additionalViewData;
    }

}
