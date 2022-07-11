<?php

use Blueways\BwGuild\Controller\AdministrationController;

return [
    'bwguild_csv_import' => [
        'path' => '/bwguild/csv/import',
        'target' => AdministrationController::class . '::csvAction',
    ],
];
