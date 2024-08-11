<?php

if (!(defined('TYPO3') || defined('TYPO3_MODE'))) {
    exit('Access denied.');
}

$GLOBALS['TYPO3_CONF_VARS']['FE']['ContentObjects'] = array_merge(
    $GLOBALS['TYPO3_CONF_VARS']['FE']['ContentObjects'] ?? [],
    [
        'TWIGTEMPLATE' => \System25\T3twigs\ContentObject\TwigContentObject::class,
    ]
);

