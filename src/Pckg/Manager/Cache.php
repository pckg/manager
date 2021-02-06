<?php

namespace Pckg\Manager;

use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Cache\ChainCache;
use Doctrine\Common\Cache\RedisCache;

class Cache
{

    protected $handlers = [];

    public function __construct()
    {
        foreach (config('pckg.cache.handler', []) as $type => $config) {
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
        $handler = is_only_callable($config['handler']) ? $config['handler']() : new $config['handler']();
        if ($handler instanceof RedisCache) {
            $redisConfig = $config['redis'] ?? [];
            try {
                $redis = new \Redis();
                $pass = $redisConfig['pass'] ?? null;
                $host = $redisConfig['host'] ?? '127.0.0.1';
                /**
                 * First connect, then auth.
                 */
                $redis->connect($host, 6379, 5);
                $redis->auth($pass);
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

    public function delete($key, $type = 'app')
    {
        /**
         * Get proper handler.
         */
        $cache = $this->getHandler($type);

        /**
         * Prepend platform key.
         */
        $key = config('identifier', 'identifier') . ':' . config(
            'database.default.db',
            'database.default.db'
        ) . ':' . $key;

        /**
         * Delete key.
         */
        $cache->delete($key);

        return $this;
    }

    /**
     * @param          $key
     * @param callable $val
     * @param string   $type
     * @param int|string      $time
     *
     * @return mixed
     */
    public function cache($key, $val, $type = 'request', $time = 0)
    {
        $cache = $this->getHandler($type);

        if (!$cache) {
            message('No cache defined');
            return $val();
        }

        if (is_object($key)) {
            $key = get_class($key) . '.' . $key->id . '.';
        }

        /**
         * We need to cache things per identifier ... and db connection?
         */
        $key = config('identifier', 'identifier') . ':' . config(
            'database.default.db',
            'database.default.db'
        ) . ':' . $key;

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

        if (is_only_callable($val)) {
            $val = $val();
        }
        $cache->save($key, $val, $time);

        return $val;
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
