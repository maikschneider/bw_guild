<?php

namespace Blueways\BwGuild\Validation\Validator;

use Blueways\BwGuild\Domain\Model\User;
use TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator;

class PasswordRepeatValidator extends AbstractValidator
{
    protected function isValid($value)
    {
        /** @var User $user */
        $user = $value;
        if (strlen($user->getPassword()) < 4) {
            $this->addError('Password is too short', 1559596331);
        }
        if ($user->getPassword() !== $user->getPasswordRepeat()) {
            $this->addError('Passwords do not match', 1559596203);
        }
    }
}
