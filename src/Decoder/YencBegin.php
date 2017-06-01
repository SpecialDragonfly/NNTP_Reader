<?php
namespace Net\Decoder;

class YencBegin
{
    /**
     * @var int
     */
    private $part;
    /**
     * @var int
     */
    private $line;
    /**
     * @var int
     */
    private $size;
    /**
     * @var string
     */
    private $name;

    public function __construct(int $part, int $line, int $size, string $name)
    {
        $this->part = $part;
        $this->line = $line;
        $this->size = $size;
        $this->name = $name;
    }

    public static function fromString(string $data)
    {
        $part = 0;
        $line = 0;
        $size = 0;
        $name = '';
        $sections = explode(" ", $data);
        foreach ($sections as $section) {
            if (strpos($section, "part=") === 0) {
                $part = (int) str_replace('part=', '', $section);
            }
            if (strpos($section, "line=") === 0) {
                $line = (int) str_replace('line=', '', $section);
            }
            if (strpos($section, "size=") === 0) {
                $size = (int) str_replace('section=', '', $section);
            }
            if (strpos($section, "name=") === 0) {
                $name = str_replace('name=', '', $section);
            }
        }

        return new self($part, $line, $size, $name);
    }
}