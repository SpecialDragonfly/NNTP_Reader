<?php
namespace Net\Decoder;

class YencEnd
{
    /**
     * @var int
     */
    private $size;
    /**
     * @var int
     */
    private $part;
    /**
     * @var string
     */
    private $crc;
    /**
     * @var string
     */
    private $previousCrc;

    public function __construct(int $size, int $part, string $crc, string $previousCrc)
    {
        $this->size = $size;
        $this->part = $part;
        $this->crc = $crc;
        $this->previousCrc = $previousCrc;
    }

    public static function fromString(string $data) : YencEnd
    {
        // =yend size=584 crc32=ded29f4f
        // =yend size=11250 part=1 pcrc32=bfae5c0b
        // =yend size=8088 part=2 pcrc32=aca76043
        $size = 0;
        $part = 0;
        $crc = '';
        $pcrc = '';

        $sections = explode(" ", $data);
        foreach ($sections as $section) {
            if (strpos($section, "size=") === 0) {
                $size = str_replace('size=', '', $section);
            }
            if (strpos($section, "part=") === 0) {
                $part = str_replace('part=', '', $section);
            }
            if (strpos($section, "crc32=") === 0) {
                $crc = str_replace('crc32=', '', $section);
            }
            if (strpos($section, "pcrc32=") === 0) {
                $pcrc = str_replace('pcrc32=', '', $section);
            }
        }

        return new self($size, $part, $crc, $pcrc);
    }
}