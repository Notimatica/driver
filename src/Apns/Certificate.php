<?php

namespace Notimatica\Driver\Apns;

use League\Flysystem\FilesystemInterface;

class Certificate
{
    /**
     * @var FilesystemInterface
     */
    protected $storage;
    /**
     * @var array
     */
    protected $files;

    /**
     * Create a new Certificate.
     *
     * @param array $files
     * @param FilesystemInterface $storage
     */
    public function __construct(array $files, FilesystemInterface $storage)
    {
        $this->files = $files;
        $this->storage = $storage;
    }

    /**
     * Return certificate.
     *
     * @return string|null
     */
    public function getP12Certificate()
    {
        try {
            return $this->storage->get($this->files['p12']);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Return password.
     *
     * @return string|null
     */
    public function getPassword()
    {
        try {
            return $this->storage->get($this->files['password']);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Get pem certificate.
     *
     * @return string|null
     */
    public function getPemCertificate()
    {
        try {
            return $this->storage->get($this->files['pem']);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Return pem certificate path.
     *
     * @return string
     */
    public function getPemCertificatePath()
    {
        return $this->storage->getAdapter()->applyPathPrefix($this->files['pem']);
    }


    /**
     * Convert p12 to pem.
     *
     * @return string|null
     * @throws \RuntimeException
     */
    public function convertP12toPem()
    {
        $pem = '';
        $p12 = $this->getP12Certificate();

        if (empty($p12)) {
            return null;
        }

        if (! openssl_pkcs12_read($p12, $certificate, $this->getPassword())) {
            throw new \RuntimeException("Certificate or password is invalid.");
        }

        if (isset($certificate['cert'])) {
            openssl_x509_export($certificate['cert'], $cert);
            $pem .= $cert;
        }

        if (isset($certificate['pkey'])) {
            openssl_pkey_export($certificate['pkey'], $pkey, null);
            $pem .= $pkey;
        }

        return $pem;
    }

    /**
     * Set certificates filenames.
     *
     * @param  array $files
     * @return $this
     */
    public function setFiles(array $files)
    {
        $this->files = $files;

        return $this;
    }
}
