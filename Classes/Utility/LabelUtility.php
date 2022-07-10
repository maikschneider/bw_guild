<?php

namespace Blueways\BwGuild\Utility;

class LabelUtility
{
    public function feUserLabel(&$parameters, $parentObject)
    {
        $record = \TYPO3\CMS\Backend\Utility\BackendUtility::getRecord($parameters['table'], $parameters['row']['uid']);
        $newTitle = $record['company'];
        $newTitle .= ' (' . $record['username'] . ')';
        $parameters['title'] = $newTitle;
    }
}
