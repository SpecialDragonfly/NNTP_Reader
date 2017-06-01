<?php
namespace Net\Encryption;

class TcpEncryption implements EncryptionInterface
{
    /**
     * @var string
     */
    private $transport;

    /**
     * @var int
     */
    private $port;

    public function __construct(int $port = 119)
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