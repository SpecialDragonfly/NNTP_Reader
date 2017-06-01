<?php
namespace Net\Decoder;

class YencPart
{
    /**
     * @var int
     */
    private $begin;
    /**
     * @var int
     */
    private $end;

    public function __construct(int $begin, int $end)
    {
        $this->begin = $begin;
        $this->end = $end;
    }

    public static function fromString(string $data) : YencPart
    {
        // =ypart begin=11251 end=19338
        $sections = explode(" ", $data);
        foreach ($sections as $section) {
            if (strpos($section, 'begin=') === 0) {
                $begin = str_replace('begin=', '', $section);
            }
            if (strpos($section, 'end=') === 0) {
                $end = str_replace('end=', '', $section);
            }
        }

        return new self($begin, $end);
    }
}