<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Bw Guild',
    'description' => 'Guild management based on fe_users',
    'category' => 'plugin',
    'constraints' => [
        'depends' => [
            'typo3' => '9.5.0-11.99.99',
        ],
        'conflicts' => [
        ],
    ],
    'autoload' => [
        'psr-4' => [
            'Blueways\\BwGuild\\' => 'Classes',
        ],
    ],
    'state' => 'stable',
    'clearCacheOnLoad' => true,
    'author' => 'Maik Schneider',
    'author_email' => 'maik.schneider@xima.de',
    'author_company' => 'XIMA MEDIA GmbH',
    'version' => '2.4.15',
];
