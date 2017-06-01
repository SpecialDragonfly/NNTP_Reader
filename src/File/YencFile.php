<?php
namespace Net\File;

use Net\Decoder\YencBegin;
use Net\Decoder\YencEnd;
use Net\Decoder\YencPart;

class YencFile implements FileInterface
{
    /**
     * @var array
     */
    private $lines;

    /**
     * @var YencBegin
     */
    private $yBegin;

    /**
     * @var YencEnd
     */
    private $yEnd;

    /**
     * @var YencPart
     */
    private $yPart;

    /**
     * @var array
     */
    private $body;

    public function __construct(array $lines)
    {
        $this->lines = $lines;
        $this->yBegin = YencBegin::fromString($lines[0]);
        $this->yEnd = YencEnd::fromString($lines[count($lines) - 1]);

        // Multipart binary
        if (strpos($lines[1], "=y") === 0) {
            $this->yPart = YencPart::fromString($lines[1]);
        }
        $this->body = array_slice($this->lines, 1, count($this->lines) - 2);
    }

    public function getRaw() : array
    {
        return $this->lines;
    }

    public function getBody() : array
    {
        return $this->body;
    }
}