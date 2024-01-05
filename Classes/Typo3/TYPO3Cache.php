<?php

namespace System25\T3twigs\Typo3;

use Sys25\RnBase\Cache\CacheManager as CacheCacheManager;
use Twig\Cache\CacheInterface;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Cache\Frontend\PhpFrontend;

class TYPO3Cache implements CacheInterface
{
    private const CACHE_NAME = 't3twigs';

    /**
     * @var PhpFrontend
     */
    private $delegate;

    public function __construct(?CacheManager $cacheManager = null)
    {
        $this->delegate = $cacheManager ? $cacheManager->getCache(self::CACHE_NAME) : CacheCacheManager::getCache(self::CACHE_NAME);
    }


    public function generateKey($name, $className)
    {
        $cacheKey = implode('_', [$name, $className]);
        // strip all unallowed characters
        $cacheKey = preg_replace('/[^A-Za-z0-9-_]/', '_', $cacheKey);

        return $cacheKey;
    }

    public function write($key, $content)
    {
        $this->delegate->set($key, '#'.$content);
    }

    public function load($key)
    {
        $this->delegate->requireOnce($key);
    }

    public function getTimestamp($key)
    {
        if (!$this->delegate->has($key)) {
            return 0;
        }

        $path = sprintf(
            '%s.%s.php',
            $this->delegate->getBackend()->getCacheDirectory(),
            $key
        );

        // Ignore errors, because they may not be relevant at this point.
        set_error_handler(function () {});
        $time = filemtime($path);
        restore_error_handler();

        return (int) $time;
    }
}
