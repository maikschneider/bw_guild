<?php
$EM_CONF[$_EXTKEY] = [
    'title' => 'Bw Guild',
    'description' => 'Guild management based on fe_users',
    'category' => 'templates',
    'constraints' => [
        'depends' => [
            'typo3' => '11.5.*',
            'fluid_styled_content' => '11.5.*',
        ],
        'conflicts' => [
        ],
    ],
    'autoload' => [
        'psr-4' => [
            'Blueways\\BwGuild\\' => 'Classes'
        ],
    ],
    'state' => 'stable',
    'uploadfolder' => 0,
    'createDirs' => '',
    'clearCacheOnLoad' => 1,
    'author' => 'Maik Schneider',
    'author_email' => 'm.schneider@blueways.de',
    'author_company' => 'blueways',
    'version' => '3.0.0',
];
