<?php
namespace Net\Encryption;

class NoEncryption implements EncryptionInterface
{
    /**
     * @var string
     */
    private $transport;

    /**
     * @var int
     */
    private $port;

    public function __construct($port = '119')
    {
        $this->transport = 'tcp';
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