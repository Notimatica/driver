<?php

namespace Notimatica\Driver\Apns;

use League\Flysystem\Filesystem;
use Notimatica\Driver\Project;

class Certificate
{
    /**
     * @var Filesystem
     */
    protected $storage;
    /**
     * @var array
     */
    protected $paths = [
        'p12'       => 'certificate.p12',
        'pem'       => 'certificate.pem',
        'password'  => 'certificate.password',
    ];

    /**
     * Create a new Certificate.
     *
     * @param Filesystem $storage
     */
    public function __construct(Filesystem $storage)
    {
        $this->storage = $storage;
    }

    /**
     * Return certificate.
     *
     * @return string
     */
    public function getP12Certificate()
    {
        try {
            return $this->storage->get($this->paths['p12']);
        } catch (\Exception $e) {
            return '';
        }
    }

    /**
     * Return password.
     *
     * @return string
     */
    public function getPassword()
    {
        try {
            return $this->storage->get($this->paths['password']);
        } catch (\Exception $e) {
            return '';
        }
    }

    /**
     * Get pem certificate.
     *
     * @return string
     */
    public function getPemCertificate()
    {
        try {
            return $this->storage->get($this->paths['pem']);
        } catch (\Exception $e) {
            return '';
        }
    }

    /**
     * Return pem certificate path.
     *
     * @return string
     */
    public function getPemCertificatePath()
    {
        return $this->storage->getAdapter()->applyPathPrefix($this->paths['pem']);
    }

    /**
     * Set certificates filenames.
     *
     * @param  array $paths
     * @return $this
     */
    public function setPaths(array $paths)
    {
        $this->paths = $paths;

        return $this;
    }
}
