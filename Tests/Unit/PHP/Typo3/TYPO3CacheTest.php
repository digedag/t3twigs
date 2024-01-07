<?php

namespace System25\T3twigs\Tests\Unit\Typo3;

use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use System25\T3twigs\Typo3\TYPO3Cache;
use TYPO3\CMS\Core\Cache\Backend\SimpleFileBackend;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Cache\Frontend\PhpFrontend;

class TYPO3CacheTest extends TestCase
{
    use ProphecyTrait;

    private $twigCache;
    private $cache;
    private $root;

    protected function setUp(): void
    {
        $this->cache = $this->prophesize(PhpFrontend::class);

        $cacheManager = $this->prophesize(CacheManager::class);
        $cacheManager->getCache(TYPO3Cache::CACHE_NAME)->willReturn($this->cache->reveal());

        $this->twigCache = new TYPO3Cache($cacheManager->reveal());

        $this->root = vfsStream::setup();
    }

    public function testWrite()
    {
        $this->cache->set('foo', '#bar')
            ->shouldBeCalled();

        $this->twigCache->write('foo', 'bar');
    }

    public function testGetTimeStamp()
    {
        $this->cache->has('foo')->willReturn(true);

        $backend = $this->prophesize(SimpleFileBackend::class);
        $backend->getCacheDirectory()->willReturn($this->root->url());

        $this->cache->getBackend()->willReturn($backend);

        self::assertSame(0, $this->twigCache->getTimestamp('foo'));
    }

    public function testGetTimestampWhichDoesNotExists()
    {
        $this->cache->has('foo')->willReturn(false);

        self::assertSame(0, $this->twigCache->getTimestamp('foo'));
    }

    public function testLoad()
    {
        $this->cache->requireOnce('foo')->shouldBeCalled();

        $this->twigCache->load('foo');
    }

    /**
     * @dataProvider getKeyData
     */
    public function testGenerateKey($name, $class, $expected)
    {
        self::assertMatchesRegularExpression($expected, $this->twigCache->generateKey($name, $class));
    }

    public static function getKeyData()
    {
        return [
            [
                'name' => 'foo',
                'class' => '\Twig_Template',
                'expected' => '/foo__Twig_Template/',
            ],
            [
                'name' => '@foo.html.twig',
                'class' => '\Twig\Template',
                'expected' => '/(.*)_foo_html_twig__Twig_Template/',
            ],
        ];
    }
}
