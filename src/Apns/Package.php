<?php namespace Notimatica\Driver\Apns;

use App\Project;
use Illuminate\Contracts\Filesystem\Filesystem;

class Package
{
    const PACKAGE_FILENAME = 'safari-package.zip';

    /**
     * @var array
     */
    protected $website;

    /**
     * @var array
     */
    protected $icons = [
        'icon_16x16.png',
        'icon_16x16@2x.png',
        'icon_32x32.png',
        'icon_32x32@2x.png',
        'icon_128x128.png',
        'icon_128x128@2x.png',
    ];

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
     * @var Project
     */
    private $project;

    /**
     * @var Filesystem
     */
    private $storage;

    /**
     * Create a new Package.
     *
     * @param array $website
     * @param Project $project
     * @param Certificate $certificate
     * @param Filesystem $storage
     */
    public function __construct($website, Project $project, Certificate $certificate, Filesystem $storage)
    {
        $this->website = $website;
        $this->project = $project;
        $this->certificate = $certificate;
        $this->storage = $storage;
    }

    /**
     * Generate zip package
     */
    public function generate()
    {
        $packagePath = $this->storage->getAdapter()->applyPathPrefix($this->project->uuid . '/' . static::PACKAGE_FILENAME);

        if ($this->storage->exists($this->project->uuid . '/' . static::PACKAGE_FILENAME)) {
            return $packagePath;
        }

        $this->zip = new \ZipArchive();
        if ($this->zip->open($packagePath, \ZipArchive::CREATE) !== true) {
            return false;
        }

        $this->addWebsite();
        $this->addIcons();
        $this->addManifest();
        $this->addSignature();

        return $this->zip->close() ? $packagePath : false;
    }

    /**
     * Add website.json.
     */
    protected function addWebsite()
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
        $path = $this->storage->getAdapter()->applyPathPrefix($this->project->uuid . '/' . $path);
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
        if (!openssl_pkcs12_read($pkcs12, $certs, $password)) {
            throw new \RuntimeException(openssl_error_string());
        }

        $signaturePath = tempnam(sys_get_temp_dir(), '_sign');
        $manifestPath = tempnam(sys_get_temp_dir(), '_manifest');
        file_put_contents($manifestPath, json_encode($this->manifest));

        // Sign the manifest.json file with the private key from the certificate
        $certificateData = openssl_x509_read($certs['cert']);
        $privateKey = openssl_pkey_get_private($certs['pkey'], $password);
        openssl_pkcs7_sign($manifestPath, $signaturePath, $certificateData, $privateKey, array(), PKCS7_BINARY | PKCS7_DETACHED);

        // Convert the signature from PEM to DER
        $signature_pem = file_get_contents($signaturePath);
        $matches = [];
        if (!preg_match('~Content-Disposition:[^\n]+\s*?([A-Za-z0-9+=/\r\n]+)\s*?-----~', $signature_pem, $matches)) {
            throw new \RuntimeException(openssl_error_string());
        }

        $this->zip->addFromString('signature', base64_decode($matches[1]));
    }
}