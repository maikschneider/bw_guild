<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Bw Guild',
    'description' => 'Guild management based on fe_users',
    'category' => 'templates',
    'constraints' => [
        'depends' => [
            'typo3' => '9.5.0-10.9.*',
            'fluid_styled_content' => '9.5.0-10.9.*',
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
    'uploadfolder' => 0,
    'createDirs' => '',
    'clearCacheOnLoad' => 1,
    'author' => 'Maik Schneider',
    'author_email' => 'm.schneider@blueways.de',
    'author_company' => 'blueways',
    'version' => '2.4.15',
];
