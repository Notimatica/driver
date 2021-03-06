<?php

namespace Notimatica\Driver\Support;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Pool;
use Notimatica\Driver\Contracts\Project;
use Notimatica\Driver\Providers\AbstractProvider;

abstract class ProviderWithHttpClient extends AbstractProvider
{
    const DEFAULT_TTL = 2419200;
    const DEFAULT_TIMEOUT = 30;

    /**
     * @var ClientInterface
     */
    protected $client;
    /**
     * @var array
     */
    protected $headers = [
        'Content-Type' => 'application/json',
    ];

    /**
     * ProviderWithHttpClient constructor.
     *
     * @param Project $project
     * @param ClientInterface $client
     */
    public function __construct(Project $project, ClientInterface $client)
    {
        parent::__construct($project);

        $this->client = $client;
        $this->headers['TTL'] = isset($this->config['ttl']) ? $this->config['ttl'] : static::DEFAULT_TTL;
    }

    /**
     * Send data to provider.
     *
     * @param  array $subscribers
     * @param  \Closure $success
     * @param  \Closure $fail
     */
    protected function flush(array $subscribers, \Closure $success = null, \Closure $fail = null)
    {
        if (is_null($success)) {
            $success = function ($response, $index) {};
        }

        if (is_null($fail)) {
            $fail = function ($reason, $index) {};
        }

        $pool = new Pool($this->getClient(), $this->prepareRequests($subscribers), [
            'concurrency' => $this->config['concurrent_requests'],
            'fulfilled'   => $success,
            'rejected'    => $fail,
        ]);

        // Initiate the transfers and create a promise
        $promise = $pool->promise();

        // Force the pool of requests to complete.
        $promise->wait();
    }

    /**
     * Client getter.
     *
     * @return ClientInterface
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * Client setter.
     *
     * @param ClientInterface $client
     */
    public function setClient($client)
    {
        $this->client = $client;
    }
}
