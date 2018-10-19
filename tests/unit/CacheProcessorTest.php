<?php

namespace DynDNSKit\Tests\Unit;

use DynDNSKit\Processor\CacheProcessor;
use DynDNSKit\Processor\ProcessorInterface;
use DynDNSKit\Query;
use DynDNSKit\Tests\Common\TestCase;
use Hamcrest\Matchers;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;

class CacheProcessorTest extends TestCase
{
    public function dpTestProcess()
    {
        return [
            // single hostname with a cold cache
            0 => [
                new Query($ip = '127.0.0.1', [$hostname = 'myhost_com']), // $query
                function () use ($ip, $hostname) { // closure needed to force mock creation at test time not data provider time
                    $processor = \Mockery::mock(ProcessorInterface::class);
                    $processor->shouldReceive('process')->once()->with(Matchers::equalTo(new Query($ip, [$hostname])))->andReturn(true);

                    return $processor;
                }, // $processor
                function () use ($ip, $hostname) { // closure needed to force mock creation at test time not data provider time
                    $item = \Mockery::mock(CacheItemInterface::class);
                    $item->shouldReceive('isHit')->once()->andReturn(false);
                    $item->shouldReceive('get')->never();
                    $item->shouldReceive('set')->once()->with($ip);
                    $item->shouldReceive('expiresAfter')->once();

                    $cache = \Mockery::mock(CacheItemPoolInterface::class);
                    $cache->shouldReceive('getItem')->once()->with($hostname)->andReturn($item);
                    $cache->shouldReceive('save')->once()->with($item);

                    return $cache;
                }, // $cache
            ],
            // single hostname with a cache hit
            1 => [
                new Query($ip = '127.0.0.1', [$hostname = 'myhost_com']), // $query
                function () { // closure needed to force mock creation at test time not data provider time
                    $processor = \Mockery::mock(ProcessorInterface::class);
                    $processor->shouldReceive('process')->never();

                    return $processor;
                }, // $processor
                function () use ($ip, $hostname) { // closure needed to force mock creation at test time not data provider time
                    $item = \Mockery::mock(CacheItemInterface::class);
                    $item->shouldReceive('isHit')->once()->andReturn(true);
                    $item->shouldReceive('get')->once()->andReturn($ip);
                    $item->shouldReceive('set')->never();
                    $item->shouldReceive('expiresAfter')->never();

                    $cache = \Mockery::mock(CacheItemPoolInterface::class);
                    $cache->shouldReceive('getItem')->once()->with($hostname)->andReturn($item);
                    $cache->shouldReceive('save')->never();

                    return $cache;
                }, // $cache
            ],
            // single hostname with a cache hit and ip change
            2 => [
                new Query($ip = '127.0.0.1', [$hostname = 'myhost_com']), // $query
                function () use ($ip, $hostname) { // closure needed to force mock creation at test time not data provider time
                    $processor = \Mockery::mock(ProcessorInterface::class);
                    $processor->shouldReceive('process')->once()->with(Matchers::equalTo(new Query($ip, [$hostname])))->andReturn(true);

                    return $processor;
                }, // $processor
                function () use ($ip, $hostname) { // closure needed to force mock creation at test time not data provider time
                    $item = \Mockery::mock(CacheItemInterface::class);
                    $item->shouldReceive('isHit')->once()->andReturn(true);
                    $item->shouldReceive('get')->once()->andReturn('192.168.1.1');
                    $item->shouldReceive('set')->once()->with($ip);
                    $item->shouldReceive('expiresAfter')->once();

                    $cache = \Mockery::mock(CacheItemPoolInterface::class);
                    $cache->shouldReceive('getItem')->once()->with($hostname)->andReturn($item);
                    $cache->shouldReceive('save')->once()->with($item);

                    return $cache;
                }, // $cache
            ],
            // multiple hostnames with a cold cache
            3 => [
                new Query($ip = '127.0.0.1', [$hostname1 = 'myhost_com', $hostname2 = 'myotherhost_com']), // $query
                function () use ($ip, $hostname1, $hostname2) { // closure needed to force mock creation at test time not data provider time
                    $processor = \Mockery::mock(ProcessorInterface::class);
                    $processor->shouldReceive('process')->once()->with(Matchers::equalTo(new Query($ip, [$hostname1])))->andReturn(true);
                    $processor->shouldReceive('process')->once()->with(Matchers::equalTo(new Query($ip, [$hostname2])))->andReturn(true);

                    return $processor;
                }, // $processor
                function () use ($ip, $hostname1, $hostname2) { // closure needed to force mock creation at test time not data provider time
                    $item1 = \Mockery::mock(CacheItemInterface::class);
                    $item1->shouldReceive('isHit')->once()->andReturn(false);
                    $item1->shouldReceive('get')->never();
                    $item1->shouldReceive('set')->once()->with($ip);
                    $item1->shouldReceive('expiresAfter')->once();

                    $cache = \Mockery::mock(CacheItemPoolInterface::class);
                    $cache->shouldReceive('getItem')->once()->with($hostname1)->andReturn($item1);
                    $cache->shouldReceive('save')->once()->with($item1);

                    $item2 = \Mockery::mock(CacheItemInterface::class);
                    $item2->shouldReceive('isHit')->once()->andReturn(false);
                    $item2->shouldReceive('get')->never();
                    $item2->shouldReceive('set')->once()->with($ip);
                    $item2->shouldReceive('expiresAfter')->once();

                    $cache->shouldReceive('getItem')->once()->with($hostname2)->andReturn($item2);
                    $cache->shouldReceive('save')->once()->with($item2);

                    return $cache;
                }, // $cache
            ],
            // test ttl pass through
            4 => [
                new Query($ip = '127.0.0.1', [$hostname = 'myhost_com']), // $query
                function () use ($ip, $hostname) { // closure needed to force mock creation at test time not data provider time
                    $processor = \Mockery::mock(ProcessorInterface::class);
                    $processor->shouldReceive('process')->once()->with(Matchers::equalTo(new Query($ip, [$hostname])))->andReturn(true);

                    return $processor;
                }, // $processor
                function () use ($ip, $hostname) { // closure needed to force mock creation at test time not data provider time
                    $item = \Mockery::mock(CacheItemInterface::class);
                    $item->shouldReceive('isHit')->once()->andReturn(false);
                    $item->shouldReceive('get')->never();
                    $item->shouldReceive('set')->once()->with($ip);
                    $item->shouldReceive('expiresAfter')->once()->with(300);

                    $cache = \Mockery::mock(CacheItemPoolInterface::class);
                    $cache->shouldReceive('getItem')->once()->with($hostname)->andReturn($item);
                    $cache->shouldReceive('save')->once()->with($item);

                    return $cache;
                }, // $cache
                300, // $ttl
            ],
        ];
    }

    /**
     * @dataProvider dpTestProcess
     */
    public function testProcess($query, $processor, $cache, $ttl = 86400)
    {
        $sut = new CacheProcessor($processor(), $cache(), $ttl);

        $this->assertTrue($sut->process($query));
    }
}
