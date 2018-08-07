<?php namespace Pckg\Manager;

use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Cache\ChainCache;
use Doctrine\Common\Cache\RedisCache;

class Cache
{

    protected $handlers = [];

    public function __construct()
    {
        foreach (config('pckg.cache.handler') as $type => $config) {
            $this->registerHandler($type, $config);
        }
    }

    protected function getNamespaceByType($type)
    {
        $namespace = $type . '*';

        if ($type == 'app') {
            /**
             * Cached value per application.
             */
            return $namespace . config('app') . '*';
        }

        if ($type == 'request') {
            /**
             * Cached value for current request.
             */
            return $namespace . microtime() . '*';
        }

        if ($type == 'session') {
            /**
             * Cached value for current session.
             */
            return $namespace . session_id() . '*';
        }

        if ($type == 'user') {
            /**
             * Cached value for current user.
             */
            return $namespace . auth()->user('id') . '*';
        }

        if ($type == 'userGroup') {
            /**
             * Cached value for current user group.
             */
            return $namespace . auth()->user('user_group_id') . '*';
        }

        return $namespace;
    }

    protected function registerHandler($type, $config)
    {
        $handler = (new $config['handler']);
        if ($handler instanceof RedisCache) {
            $redisConfig = $config['redis'] ?? ['host' => '127.0.0.1'];
            $redis = new \Redis();
            try {
                $redis->connect($redisConfig['host']);
                $handler->setRedis($redis);
            } catch (\Throwable $e) {
                // cache is not available
                $handler = null;
            }
        }

        if ($handler) {
            $namespace = $this->getNamespaceByType($type);
            $handler->setNamespace($namespace);
        }

        /**
         * Default, in-memory cache.
         */
        $arrayCache = new ArrayCache();
        $cacheArray = [$arrayCache];

        /**
         * Put actual handler after in-memory cache.
         */
        if ($handler && get_class($handler) != get_class($arrayCache)) {
            $cacheArray[] = $handler;
        }

        /**
         * Create cache chain.
         */
        $chainCache = new ChainCache($cacheArray);

        return $this->handlers[$type] = $chainCache;
    }

    /**
     * @return \Doctrine\Common\Cache\Cache
     */
    public function getAppCache()
    {
        return $this->handlers['app'];
    }

    /**
     * @return \Doctrine\Common\Cache\Cache
     */
    public function getRequestCache()
    {
        return $this->handlers['request'];
    }

    /**
     * @return \Doctrine\Common\Cache\Cache
     */
    public function getSessionCache()
    {
        return $this->handlers['session'];
    }

    public function cache($key, callable $val, $type = 'request', $time = 0)
    {
        $cache = $this->getHandler($type);

        if (is_object($key)) {
            $key = get_class($key) . '.' . $key->id . '.';
        }

        /**
         * We need to cache things per identifier.
         */
        $key = config('identifier', null) . ':' . $key;

        /**
         * Return directly whenc cached.
         */
        if ($cache->contains($key)) {
            return $cache->fetch($key);
        }

        /**
         * Transform stringed time to numeric.
         */
        if (!is_numeric($time)) {
            $time = strtotime('+' . $time) - time();
        }

        $value = $val();
        $cache->save($key, $value, $time);

        return $value;
    }

    /**
     * @param $type
     *
     * @return mixed|null|RedisCache
     */
    public function getHandler($type)
    {
        return $this->handlers[$type] ?? null;
    }

}