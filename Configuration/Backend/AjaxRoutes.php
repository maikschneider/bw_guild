<?php

return [
    'tx_bwguild_preview' => [
        'path' => '/tx_bwguild_admin/preview',
        'target' => \Blueways\BwGuild\Controller\BackendAjaxController::class . '::previewAction',
    ],
];
