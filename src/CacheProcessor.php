<?php
declare(strict_types = 1);

namespace DynDNSKit\Processor;

use DynDNSKit\Query;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Cache\InvalidArgumentException;

class CacheProcessor implements ProcessorInterface
{
    /**
     * @var ProcessorInterface
     */
    private $processor;

    /**
     * @var CacheItemPoolInterface
     */
    private $cache;

    /**
     * @var int
     */
    private $ttl;

    /**
     * @param ProcessorInterface $processor
     * @param CacheItemPoolInterface $cache
     * @param int $ttl
     */
    public function __construct(ProcessorInterface $processor, CacheItemPoolInterface $cache, int $ttl = 86400)
    {
        $this->processor = $processor;
        $this->cache = $cache;
        $this->ttl = $ttl;
    }

    /**
     * @param Query $query
     * @return bool
     * @throws InvalidArgumentException
     */
    public function process(Query $query): bool
    {
        foreach ($query->getHostnames() as $hostname) {
            $key = str_replace([':', '.', '-'], '_', $hostname);
            $ip = $query->getIp();
            $item = $this->cache->getItem($key);
            if (!($item->isHit() && $item->get() === $ip)) {
                $this->processor->process(new Query($ip, [$hostname]));
                $item->set($ip);
                $item->expiresAfter($this->ttl);
                $this->cache->save($item);
            }
        }

        return true;
    }
}
