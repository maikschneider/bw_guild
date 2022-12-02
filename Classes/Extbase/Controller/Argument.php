<?php

namespace Blueways\BwGuild\Extbase\Controller;

class Argument extends \TYPO3\CMS\Extbase\Mvc\Controller\Argument
{
    public function setDataType(string $dataType): void
    {
        $this->dataType = $dataType;
    }
}
