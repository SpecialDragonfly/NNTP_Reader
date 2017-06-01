<?php
namespace Net\Encryption;

interface EncryptionInterface
{
    public function getTransport(): string;
    public function getPort(): string;
}