<?php

namespace Notimatica\Driver;

use Notimatica\Driver\StatisticsStorages\AbstractStorage;
use Notimatica\Driver\StatisticsStorages\Model;

class StatisticsStoragesFactory
{
    /**
     * @var \Closure[]
     */
    protected static $resolvers;

    /**
     * Extend resolver to support customers storage.
     *
     * @param string $name
     * @param \Closure $resolver
     */
    public static function extend($name, $resolver)
    {
        static::$resolvers[$name] = $resolver;
    }

    /**
     * Resolve provider from it's name.
     *
     * @param  string $name
     * @return AbstractStorage
     */
    public function make($name)
    {
        switch ($name) {
            case Model::NAME:
                $return = new Model();
                break;
            default:
                $return = $this->resolveExtends($name);

                if (is_null($return)) {
                    throw new \RuntimeException("Unsupported statistics storage '$name'");
                }
        }

        return $return;
    }

    /**
     * Try to resolve extra storage.
     *
     * @param  string $name
     * @return AbstractStorage|null
     */
    protected function resolveExtends($name)
    {
        if (empty(static::$resolvers[$name])) {
            return;
        }

        return call_user_func(static::$resolvers[$name]);
    }
}
