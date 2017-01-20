<?php

namespace Notimatica\Driver\Apns;

class Streamer
{
    const PAYLOAD_MAX_BYTES = 256;

    /**
     * @var Certificate
     */
    protected $certificate;

    /**
     * @var string
     */
    protected $host;

    /**
     * @var Resource
     */
    protected $apnsResource;

    /**
     * @var int
     */
    protected $error;

    /**
     * @var string
     */
    protected $errorString;

    /**
     * Construct.
     *
     * @param Certificate $certificate
     * @param string $host
     */
    public function __construct(Certificate $certificate, $host)
    {
        $this->certificate = $certificate;
        $this->host = $host;
    }

    /**
     * Writes a binary message to APNS.
     *
     * @param  string $binaryMessage
     * @return int Returns the number of bytes written, or FALSE on error.
     * @throws \InvalidArgumentException
     */
    public function write($binaryMessage)
    {
        $length = strlen($binaryMessage);

        if ($length > self::PAYLOAD_MAX_BYTES) {
            throw new \InvalidArgumentException(
                sprintf('The maximum size allowed for a notification payload is %s bytes; Apple Push Notification Service refuses any notification that exceeds this limit.', self::PAYLOAD_MAX_BYTES)
            );
        }

        return fwrite($this->getApnsResource(), $binaryMessage, $length);
    }

    /**
     * Create stream resource.
     *
     * @return Resource
     */
    protected function getApnsResource()
    {
        if (! is_resource($this->apnsResource)) {
            $this->apnsResource = $this->createStreamClient();
        }

        return $this->apnsResource;
    }

    /**
     * Create stream context.
     *
     * @return Resource
     */
    protected function createStreamContext()
    {
        $streamContext = stream_context_create();
        stream_context_set_option($streamContext, 'ssl', 'local_cert', $this->certificate->getPemCertificatePath());

        return $streamContext;
    }

    /**
     * Create stream client.
     *
     * @return Resource
     */
    protected function createStreamClient()
    {
        $client = stream_socket_client(
            $this->host,
            $this->error,
            $this->errorString,
            2,
            STREAM_CLIENT_CONNECT,
            $this->createStreamContext()
        );

        return $client;
    }

    /**
     * Close connection.
     */
    public function close()
    {
        if (is_resource($this->apnsResource)) {
            fclose($this->apnsResource);
        }
    }

    /**
     * Destruct callback.
     */
    public function __destruct()
    {
        $this->close();
    }

    /**
     * Certificate getter.
     *
     * @return Certificate
     */
    public function getCertificate()
    {
        return $this->certificate;
    }

    /**
     * Certificate setter.
     *
     * @param Certificate $certificate
     */
    public function setCertificate($certificate)
    {
        $this->certificate = $certificate;
    }
}
