<?php
namespace Net\Protocol;

class NewsGroup
{
    /**
     * @var string
     */
    private $name;
    /**
     * @var int
     */
    private $last;
    /**
     * @var int
     */
    private $first;
    /**
     * @var string
     */
    private $posting;

    /**
     * NewsGroup constructor.
     *
     * @param string $name
     * @param int    $last
     * @param int    $first
     * @param string $posting
     */
    public function __construct(string $name, int $last, int $first, string $posting)
    {

        $this->name = $name;
        $this->last = $last;
        $this->first = $first;
        $this->posting = $posting;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return int
     */
    public function getLast(): int
    {
        return $this->last;
    }

    /**
     * @return int
     */
    public function getFirst(): int
    {
        return $this->first;
    }

    /**
     * @return bool
     */
    public function isPostingAllowed(): bool
    {
        return $this->posting === 'y';
    }
}