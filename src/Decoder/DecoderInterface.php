<?php
namespace Net\Decoder;

interface DecoderInterface
{
    public function decode(string $encodedString);
}