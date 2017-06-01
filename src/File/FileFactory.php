<?php
namespace Net\File;

class FileFactory
{
    public static function createFromResponse(array $response) : FileInterface
    {
        if (strpos($response[0], "=y") === 0 &&
            strpos($response[count($response) - 1], "=y") === 0
        ) {
            return new YencFile($response);
        }

        return new PlainFile($response);
    }
}