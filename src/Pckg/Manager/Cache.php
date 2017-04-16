<?php namespace Pckg\Manager;

class Cache
{

    protected $handlers = [];

    public function __construct()
    {
        foreach (config('pckg.cache.handler') as $type => $config) {
            $this->registerHandler($type, $config);
        }
    }

    protected function registerHandler($type, $config)
    {
        $handler = (new $config['handler']);
        $namespace = $type . '*';
        if ($type == 'app') {
            $namespace .= config('app') . '*';
        } else if ($type == 'request') {
            $namespace .= microtime() . '*';
        } else if ($type == 'session') {
            $namespace .= session_id() . '*';
        }
        $handler->setNamespace($namespace);

        return $this->handlers[$type] = $handler;
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

    public function cache($key, $val, $type = 'request', $time = 0)
    {
        $cache = $this->handlers[$type];

        if (is_object($key)) {
            $key = get_class($key) . '.' . $key->id . '.';
        }

        if (!$cache->contains($key)) {
            $value = $val();
            $cache->save($key, $value);

            return $value;
        }

        return $cache->fetch($key);
    }

}