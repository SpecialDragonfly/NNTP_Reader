<?php
namespace Net\Protocol;

class Header
{
    /**
     * @var string
     */
    private $messageId;
    /**
     * @var string
     */
    private $subject;
    /**
     * @var string
     */
    private $email;
    /**
     * @var string
     */
    private $head;
    /**
     * @var int
     */
    private $articleId;

    public function __construct(int $articleId, string $subject, string $messageId, string $head, string $email)
    {
        $this->messageId = $messageId;
        $this->subject = $subject;
        $this->email = $email;
        $this->head = $head;
        $this->articleId = $articleId;
    }

    /**
     * @return string
     */
    public function getHead(): string
    {
        return $this->head;
    }

    /**
     * @return string
     */
    public function getMessageId(): string
    {
        return $this->messageId;
    }

    /**
     * @return string
     */
    public function getSubject(): string
    {
        return $this->subject;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
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
    public function toString() : string
    {
        return $this->articleId." ".$this->subject." ".$this->messageId;
    }
}