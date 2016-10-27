<?php namespace Notimatica\Driver\Apns;

use App\Project;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Http\UploadedFile;

class Certificate
{
    /**
     * @var Project
     */
    protected $project;

    /**
     * @var \Illuminate\Contracts\Filesystem\Filesystem
     */
    protected $storage;

    /**
     * @var array
     */
    protected $paths = [
        'p12' => '%s/certificate.p12',
        'pem' => '%s/certificate.pem',
        'password' => '%s/certificate.password',
    ];

    /**
     * Create a new Certificate.
     *
     * @param Project $project
     * @param Filesystem $storage
     */
    public function __construct(Project $project, Filesystem $storage)
    {
        $this->project = $project;
        $this->storage = $storage;
    }

    /**
     * Save p12 certificate.
     *
     * @param  string $certificate
     * @return bool
     */
    public function saveP12Certificate($certificate)
    {
        if ($certificate instanceof UploadedFile) {
            return move_uploaded_file_to_storage($certificate, $this->storage, $this->filePath('p12'));
        }

        return $this->storage->put($this->filePath('p12'), $certificate);
    }

    /**
     * Return certificate.
     *
     * @return string
     */
    public function getP12Certificate()
    {
        try {
            return $this->storage->get($this->filePath('p12'));
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Save password to certificate.
     *
     * @param  string $password
     * @return bool
     */
    public function savePassword($password)
    {
        return $this->storage->put($this->filePath('password'), $password);
    }

    /**
     * Return password.
     *
     * @return string
     */
    public function getPassword()
    {
        try {
            return $this->storage->get($this->filePath('password'));
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Convert p12 to pem.
     *
     * @return string
     */
    public function savePemCertificate()
    {
        $pem = '';

        $password = $this->getPassword();

        if (!openssl_pkcs12_read($this->getP12Certificate(), $certificate, $password)) {
            dd(openssl_error_string());
        }

        if (isset($certificate['cert'])) {
            openssl_x509_export($certificate['cert'], $cert);
            $pem .= $cert;
        }

        if (isset($certificate['pkey'])) {
            openssl_pkey_export($certificate['pkey'], $pkey, null);
            $pem .= $pkey;
        }

        file_put_contents($this->storageFilePath('pem'), $pem);

        return true;
    }

    /**
     * Get pem certificate.
     *
     * @return string
     */
    public function getPemCertificate()
    {
        try {
            return $this->storage->get($this->filePath('pem'));
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Make path to a file.
     *
     * @param  string $file
     * @return string
     */
    public function storageFilePath($file)
    {
        return $this->storage->getAdapter()->applyPathPrefix($this->filePath($file));
    }

    /**
     * @param  string $file
     * @return string
     */
    protected function filePath($file)
    {
        return sprintf($this->paths[$file], $this->project->uuid);
    }
}