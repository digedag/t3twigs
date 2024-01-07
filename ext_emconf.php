<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'T3twigs',
    'description' => 'TYPO3 extension to render page templates with Twig. Usable in composer mode only.',
    'shy' => 0,
    'version' => '1.0.0',
    'dependencies' => 'cms',
    'conflicts' => '',
    'priority' => '',
    'loadOrder' => '',
    'module' => '',
    'state' => 'stable',
    'uploadfolder' => 0,
    'modify_tables' => '',
    'clearcacheonload' => 0,
    'lockType' => '',
    'category' => 'misc',
    'author' => 'René Nitzsche',
    'author_email' => 'rene@system25.de',
    'author_company' => 'System 25',
    'CGLcompliance' => '',
    'CGLcompliance_note' => '',
    'constraints' => [
        'depends' => [
            'rn_base' => '1.18.0-',
            'typo3' => '8.7.30-12.4.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
    '_md5_values_when_last_written' => '',
    'suggests' => [],
];
