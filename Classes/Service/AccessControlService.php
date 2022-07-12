<?php

namespace Blueways\BwGuild\Service;

use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Extbase\Domain\Model\FrontendUser;

class AccessControlService implements SingletonInterface
{
    public function isLoggedIn(?FrontendUser $user = null): bool
    {
        if (is_object($user)) {
            if ($user->getUid() === $this->getFrontendUserUid()) {
                return true;
            }
        }
        return false;
    }

    public function getFrontendUserUid(): ?int
    {
        if ($this->hasLoggedInFrontendUser() && !empty($GLOBALS['TSFE']->fe_user->user['uid'])) {
            return (int)($GLOBALS['TSFE']->fe_user->user['uid']);
        }
        return null;
    }

    public function hasLoggedInFrontendUser(): bool
    {
        return !empty($GLOBALS['TSFE']->fe_user->user);
    }
}
