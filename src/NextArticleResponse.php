<?php
namespace Net;

class NextArticleResponse
{
    /**
     * @var int
     */
    private $articleId;
    /**
     * @var string
     */
    private $email;

    public function __construct(int $articleId, string $email)
    {
        $this->articleId = $articleId;
        $this->email = $email;
    }

    /**
     * @return int
     */
    public function getArticleId(): int
    {
        return $this->articleId;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }
}