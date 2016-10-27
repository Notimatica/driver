<?php namespace Notimatica\Driver;

use Notimatica\Driver\Providers\AbstractProvider;
use Notimatica\Driver\Providers\Chrome;
use Notimatica\Driver\Providers\Firefox;
use Notimatica\Driver\Providers\Safari;

class ProvidersFactory
{
    /**
     * @var \Closure[]
     */
    protected static $resolvers;

    /**
     * @var Project
     */
    protected $project;

    /**
     * Create a new ProvidersFactory.
     *
     * @param Project $project
     */
    public function __construct(Project $project)
    {
        $this->project = $project;
    }

    /**
     * Extend resolver to support customers providers.
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
     * @param  array $options
     * @return AbstractProvider
     */
    public function make($name, array $options = [])
    {
        switch ($name)
        {
            case Chrome::NAME:
                $return = new Chrome($options);
                break;
            case Firefox::NAME:
                $return = new Firefox($options);
                break;
            case Safari::NAME:
                $return = new Safari($options);
                break;
            default:
                $return = $this->resolveExtends($name, $options);

                if (is_null($return)) {
                    throw new \RuntimeException("Unsupported provider '$name'");
                }
        }

        return $return->setProject($this->project);
    }

    /**
     * Try to resolve extra providers.
     *
     * @param  string $name
     * @param  array $options
     * @return AbstractProvider|null
     */
    protected function resolveExtends($name, array $options)
    {
        if (empty(static::$resolvers[$name])) return null;

        return call_user_func(static::$resolvers[$name], $options);
    }
}