<?php

namespace Blueways\BwGuild\Utility;

use TYPO3\CMS\Backend\Utility\BackendUtility;

class LabelUtility
{
    public function feUserLabel(&$parameters, $parentObject)
    {
        $record = BackendUtility::getRecord($parameters['table'], $parameters['row']['uid']);
        $newTitle = $record['company'];
        $newTitle .= ' (' . $record['username'] . ')';
        $parameters['title'] = $newTitle;
    }
}
