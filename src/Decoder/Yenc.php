<?php
namespace Net\Decoder;

use Net\File\YencFile;

class Yenc
{
    /**
     * yDecodes an encoded string and either writes the result to a file
     * or returns it as a string.
     *
     * @param YencFile $file yEncoded file to decode.
     *
     * @return string The decoded string
     */
    public function decode(YencFile $file)
    {
        $encodedString = implode("", $file->getBody());

        // Remove linebreaks from the string.
        $encoded = trim(str_replace("\r\n", "", $encodedString));

        // Decode
        $decoded = '';
        for( $i = 0; $i < strlen($encoded); $i++)
        {
            if ($encoded[$i] == "=") {
                $i++;
                $decoded .= chr((ord($encoded[$i]) - 64) - 42);
            } else {
                $decoded .= chr(ord($encoded[$i]) - 42);
            }
        }

        return $decoded;
    }
}