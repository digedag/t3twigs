<?php

if (!(defined('TYPO3') || defined('TYPO3_MODE'))) {
    exit('Access denied.');
}

if (!\Sys25\RnBase\Utility\TYPO3::isTYPO121OrHigher()) {
    $GLOBALS['TYPO3_CONF_VARS']['FE']['ContentObjects'] = array_merge(
        $GLOBALS['TYPO3_CONF_VARS']['FE']['ContentObjects'] ?? [],
        [
            'TWIGTEMPLATE' => \System25\T3twigs\Typo3\TwigContentObject::class,
        ]
    );
}

if (!isset($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['t3twigs'])) {
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['t3twigs'] = [
        'frontend' => \TYPO3\CMS\Core\Cache\Frontend\PhpFrontend::class,
        'backend' => \TYPO3\CMS\Core\Cache\Backend\FileBackend::class,
        'groups' => ['system'],
    ];
}
