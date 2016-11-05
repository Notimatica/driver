<?php

namespace Notimatica\Driver\Apns;

use League\Flysystem\FilesystemInterface;

class Package
{
    /**
     * @var string
     */
    protected $name;
    /**
     * @var array
     */
    protected $website;

    /**
     * @var array
     */
    protected $icons;

    /**
     * @var Certificate
     */
    protected $certificate;

    /**
     * @var array
     */
    protected $manifest;

    /**
     * @var \ZipArchive
     */
    protected $zip;

    /**
     * @var FilesystemInterface
     */
    private $storage;

    /**
     * Create a new Package.
     *
     * @param string $name
     * @param array $icons
     * @param array $website
     * @param Certificate $certificate
     * @param FilesystemInterface $storage
     */
    public function __construct($name, $icons, $website, Certificate $certificate, FilesystemInterface $storage)
    {
        $this->name = $name;
        $this->icons = $icons;
        $this->website = $website;
        $this->certificate = $certificate;
        $this->storage = $storage;
    }

    /**
     * Generate zip package.
     *
     * @return string|null
     */
    public function generate()
    {
        $packagePath = $this->getPackagePath();

        $this->zip = new \ZipArchive();
        if ($this->zip->open($packagePath, \ZipArchive::CREATE) !== true) {
            return false;
        }

        $this->addWebsiteJson();
        $this->addIcons();
        $this->addManifest();
        $this->addSignature();

        return $this->zip->close() ? $packagePath : false;
    }

    /**
     * Generates path to package file.
     *
     * @return string
     */
    public function getPackagePath()
    {
        return $this->storage->getAdapter()->applyPathPrefix($this->name);
    }

    /**
     * Add website.json.
     */
    protected function addWebsiteJson()
    {
        $this->addString('website.json', json_encode($this->website));
    }

    /**
     * Add icons.
     */
    protected function addIcons()
    {
        foreach ($this->icons as $file) {
            $this->addFile('icon.iconset/' . $file, $file);
        }
    }

    /**
     * Add string to package.
     *
     * @param string $name
     * @param string $string
     */
    protected function addString($name, $string)
    {
        $this->manifest[$name] = sha1($string);
        $this->zip->addFromString($name, $string);
    }

    /**
     * Add file to package.
     *
     * @param string $name
     * @param string $path
     */
    protected function addFile($name, $path)
    {
        $path = $this->storage->getAdapter()->applyPathPrefix($path);
        $this->manifest[$name] = sha1_file($path);
        $this->zip->addFile($path, $name);
    }

    /**
     * Add manifest.json.
     */
    protected function addManifest()
    {
        $this->zip->addFromString(
            'manifest.json',
            json_encode($this->manifest)
        );
    }

    /**
     * Add signature.
     */
    protected function addSignature()
    {
        // Load the push notification certificate
        $pkcs12 = $this->certificate->getP12Certificate();
        $password = $this->certificate->getPassword();

        $certs = [];
        if (! openssl_pkcs12_read($pkcs12, $certs, $password)) {
            throw new \RuntimeException(openssl_error_string());
        }

        $signaturePath = tempnam(sys_get_temp_dir(), '_sign');
        $manifestPath = tempnam(sys_get_temp_dir(), '_manifest');
        file_put_contents($manifestPath, json_encode($this->manifest));

        // Sign the manifest.json file with the private key from the certificate
        $certificateData = openssl_x509_read($certs['cert']);
        $privateKey = openssl_pkey_get_private($certs['pkey'], $password);
        openssl_pkcs7_sign($manifestPath, $signaturePath, $certificateData, $privateKey, [], PKCS7_BINARY | PKCS7_DETACHED);

        // Convert the signature from PEM to DER
        $signature_pem = file_get_contents($signaturePath);
        $matches = [];
        if (! preg_match('~Content-Disposition:[^\n]+\s*?([A-Za-z0-9+=/\r\n]+)\s*?-----~', $signature_pem, $matches)) {
            throw new \RuntimeException(openssl_error_string());
        }

        $this->zip->addFromString('signature', base64_decode($matches[1]));
    }
}
