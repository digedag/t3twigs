<?php

if (!(defined('TYPO3') || defined('TYPO3_MODE'))) {
    exit('Access denied.');
}

call_user_func(function () {
    $extKey = 't3twigs';

    \Sys25\RnBase\Utility\Extensions::addStaticFile($extKey, 'Configuration/TypoScript/', 'T3twigs');
});
