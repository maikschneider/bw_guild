<?php

use Blueways\BwGuild\Controller\BackendController;

return [
    'bwguild_csv_import' => [
        'path' => '/bwguild/csv/import',
        'target' => BackendController::class . '::csvAction',
    ],
];
