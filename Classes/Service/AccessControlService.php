<?php

namespace Blueways\BwGuild\Service;

use TYPO3\CMS\Core\SingletonInterface;

class AccessControlService implements SingletonInterface
{

    /**
     * @param \TYPO3\CMS\Extbase\Domain\Model\FrontendUser $user
     * @return bool
     */
    public function isLoggedIn($user = null)
    {
        if (is_object($user)) {
            if ($user->getUid() === $this->getFrontendUserUid()) {
                return true;
            }
        }
        return false;
    }

    /**
     * @return int|null
     */
    public function getFrontendUserUid()
    {
        if ($this->hasLoggedInFrontendUser() && !empty($GLOBALS['TSFE']->fe_user->user['uid'])) {
            return (int)($GLOBALS['TSFE']->fe_user->user['uid']);
        }
        return null;
    }

    /**
     * @return bool
     */
    public function hasLoggedInFrontendUser()
    {
        return !empty($GLOBALS['TSFE']->fe_user->user);
    }
}
