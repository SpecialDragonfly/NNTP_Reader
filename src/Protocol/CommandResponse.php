<?php
namespace Net\Protocol;

class CommandResponse
{
    /**
     * @var int
     */
    private $code;
    /**
     * @var string
     */
    private $text;

    /**
     * CommandResponse constructor.
     *
     * @param int    $code
     * @param string $text
     */
    public function __construct(int $code, string $text)
    {
        $this->code = $code;
        $this->text = $text;
    }

    /**
     * @return int
     */
    public function getCode(): int
    {
        return $this->code;
    }

    /**
     * @return string
     */
    public function getText(): string
    {
        return $this->text;
    }
}