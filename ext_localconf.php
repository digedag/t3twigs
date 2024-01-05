<?php

if (!(defined('TYPO3') || defined('TYPO3_MODE'))) {
    exit('Access denied.');
}

if (!\Sys25\RnBase\Utility\TYPO3::isTYPO124OrHigher()) {
    $GLOBALS['TYPO3_CONF_VARS']['FE']['ContentObjects'] = array_merge(
        $GLOBALS['TYPO3_CONF_VARS']['FE']['ContentObjects'] ?? [],
        [
            'TWIGTEMPLATE' => \System25\T3twigs\ContentObject\TwigContentObject::class,
        ]
    );
}

