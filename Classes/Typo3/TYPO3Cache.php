<?php

namespace System25\T3twigs\Typo3;

use Twig\Cache\CacheInterface;
use tx_rnbase;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Cache\Frontend\PhpFrontend;

class TYPO3Cache implements CacheInterface
{
    public const CACHE_NAME = 't3twigs';

    /**
     * @var PhpFrontend
     */
    private $delegate;

    public function __construct(?CacheManager $cacheManager = null)
    {
        if (!$cacheManager) {
            $cacheManager = tx_rnbase::makeInstance(CacheManager::class);
        }

        $this->delegate = $cacheManager->getCache(self::CACHE_NAME);
    }

    public function generateKey(string $name, string $className): string
    {
        $cacheKey = implode('_', [$name, $className]);
        // strip all unallowed characters
        $cacheKey = preg_replace('/[^A-Za-z0-9-_]/', '_', $cacheKey);

        return $cacheKey;
    }

    public function write($key, $content): void
    {
        $this->delegate->set($key, '#'.$content);
    }

    public function load($key): void
    {
        $this->delegate->requireOnce($key);
    }

    public function getTimestamp($key): int
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
