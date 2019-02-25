<?php
namespace Vesica\Waf\Cacher;

use Cache\Adapter\Memcached\MemcachedCachePool;
use Cache\Namespaced\NamespacedCachePool;

/**
 * Class Memcached
 * @package Vesica\Waf\Cacher
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
     * @param string $nameSpaceExtension
     */
    public function __construct($host, $port, $nameSpaceExtension = '')
    {
        $memCached = new \Memcached();
        try {
            $memCached->addServer($host, $port);
            $this->cache = new NamespacedCachePool(new MemcachedCachePool($memCached), self::NAMESPACE . $nameSpaceExtension);
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
    public function set($k, $v, $ttl = 86400): bool
    {
        $item = $this->cache->getItem($k);
        $item->set($v);
        $item->expiresAfter((int) $ttl); //1 day

        return $this->cache->save($item);
    }

    /**
     * @param $k
     * @return mixed
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function get($k)
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
