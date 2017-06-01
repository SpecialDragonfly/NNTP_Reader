<?php
namespace Net\Encryption;

class TlsEncryption implements EncryptionInterface
{
    /**
     * @var string
     */
    private $transport;

    /**
     * @var int
     */
    private $port;

    public function __construct($port = 563)
    {
        $this->transport = 'tls';
        $this->port = $port;
    }

    public function getTransport(): string
    {
        return $this->transport;
    }

    public function getPort(): string
    {
        return $this->port;
    }
}