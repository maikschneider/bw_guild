<?php

namespace Blueways\BwGuild\Hooks;

use TYPO3\CMS\Core\DataHandling\DataHandler;
class TCEmainHook
{
    public function processDatamap_preProcessFieldArray(
        &$fieldArray,
        $table,
        $id,
        DataHandler &$pObj
    ) {
        if ($table === 'fe_users' && isset($fieldArray['sorting_field'])) {
            $fieldArray['sorting_text'] = $fieldArray[$fieldArray['sorting_field']];
        }
    }
}
