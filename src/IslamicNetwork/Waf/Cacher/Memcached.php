<?php
namespace IslamicNetwork\Waf\Cacher;

use Cache\Adapter\Common\CacheItem;
use Cache\Adapter\Memcached\MemcachedCachePool;
use Cache\Namespaced\NamespacedCachePool;
use IslamicNetwork\Waf\Cacher\Cacher;

/**
 * Class Memcached
 * @package Helper\Cacher
 */
class Memcached implements Cacher
{
    /**
     * Namespaced cached pool object
     * @var Object
     */
    private $cache;

    /**
     * Memcached constructor.
     * @param $host
     * @param $port
     */
    public function __construct($host, $port)
    {
        $memCached = new \Memcached();
        try {
            $memCached->addServer($host, $port);
            $this->cache = new NamespacedCachePool(new MemcachedCachePool($memCached), self::NAMESPACE);
        } catch (Exception $e) {
            throw new Exception('Unable to Connect to Memcached', $e->getMessage());
        }
    }

    /**
     * @param string $k Key
     * @param string $v Value
     * @return bool
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function set($k, $v): bool
    {
        $item = $this->cache->getItem($k);
        $item->set($v);

        return $this->cache->save($item);
    }

    /**
     * Gets the value of a key
     * @param string $k Key
     * @return \Psr\Cache\CacheItemInterface
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function get($k): ?\Psr\Cache\CacheItemInterface
    {
        $item = $this->cache->getItem($k);

        return $item->get();
    }

    /**
     * Checks if a key exists
     * @param string $k Key
     * @return bool
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function exists($k): bool
    {
        return $this->cache->hasItem($k);
    }
}