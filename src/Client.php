<?php
namespace Net;

use Generator;
use Net\Encryption\EncryptionInterface;
use Net\File\FileFactory;
use Net\File\FileInterface;
use Net\Protocol\NntpClient;

class Client
{
    /**
     * @var NntpClient
     */
    private $nntpClient;
    /**
     * @var string
     */
    private $host;
    /**
     * @var string
     */
    private $username;
    /**
     * @var string
     */
    private $password;
    /**
     * @var EncryptionInterface
     */
    private $encryption;
    /**
     * @var FileFactory
     */
    private $fileFactory;

    public function __construct(
        NntpClient $nntpClient,
        EncryptionInterface $encryption,
        string $host,
        string $username,
        string $password,
        FileFactory $fileFactory
    ) {
        $this->nntpClient = $nntpClient;
        $this->host = $host;
        $this->username = $username;
        $this->password = $password;
        $this->encryption = $encryption;
        $this->fileFactory = $fileFactory;
    }

	public function connect(int $timeout = 15)
    {
        return $this->nntpClient->connect($this->encryption, $this->host, $timeout);
	}

    /**
     * Disconnect from server.
     *
     * @return mixed <br>
     *  - (bool)
     *  - (object)	Pear_Error on failure
     */
    public function disconnect()
    {
        return $this->nntpClient->disconnect();
    }

    /**
     * @return bool
     */
    public function reconnect() : bool
    {
        $this->connect();
        return $this->authenticate($this->username, $this->password);
    }

    /**
     * @param string      $user
     * @param string|null $pass
     *
     * @return bool
     */
    public function authenticate(string $user, string $pass = null) : bool
    {
        return $this->nntpClient->authenticate($user, $pass);
    }

    /**
     * Returns a list of valid groups (that the client is permitted to select) and associated information.
     *
     * @param string|null $wildMatch
     *
     * @return Generator
     */
    public function getActiveGroups(string $wildMatch = null) : Generator
    {
        return $this->nntpClient->listActive($wildMatch);
    }

    /**
     * @return Generator
     */
    public function getAllGroups() : Generator
    {
        return $this->nntpClient->list();
    }

    public function accessGroup($group) : array
    {
        return $this->nntpClient->accessGroup($group);
    }

    /**
     * @param string $group
     *
     * @return Generator
     */
    public function getHeadersForGroup(string $group) : Generator
    {
        return $this->nntpClient->getHeadersForGroup($group);
    }

    /**
     * @return array
     */
    public function getCapabilities() : array
    {
        return $this->nntpClient->getCapabilities();
    }

    /**
     * @return Generator
     */
    public function getAllNewsgroups() : Generator
    {
        return $this->nntpClient->getAllNewsgroups();
    }

    public function getBodyForArticle($group, $articleId) : FileInterface
    {
        return $this->fileFactory::createFromResponse($this->nntpClient->getBodyForArticle($group, $articleId));
    }

    /**
     * @param string $date
     * @param string $time
     *
     * @return Generator
     */
    public function getNewNewsgroups(string $date, string $time) : Generator
    {
        return $this->nntpClient->getNewGroups($date, $time);
    }
}