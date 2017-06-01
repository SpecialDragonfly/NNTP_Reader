<?php
namespace Net\File;

class PlainFile implements FileInterface
{
    /**
     * @var array
     */
    private $lines;

    public function __construct(array $lines)
    {
        $this->lines = $lines;
    }

    public function getRaw() : array
    {
        return $this->lines;
    }
}