<?php
namespace Net\Protocol;

use Generator;
use Net\Encryption\EncryptionInterface;
use Net\Exception\FailedToReadFromSocket;
use Net\NextArticleResponse;
use Psr\Log\LoggerInterface;

class NntpClient
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var resource
     */
    private $socket;

    /**
     * Client constructor.
     *
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Connect to an NNTP server
     *
     * @param EncryptionInterface $encryption
     * @param string              $host
     * @param int                 $timeout
     *
     * @return bool
     * @throws \Exception
     */
    public function connect(EncryptionInterface $encryption, $host = 'localhost', $timeout = 15): bool
    {
        if ($this->isConnected()) {
            throw new \Exception("Already connected, disconnect first!");
        }

        // Open Connection
        $transport = $encryption->getTransport();
        $port = $encryption->getPort();
        $R = stream_socket_client($transport.'://'.$host.':'.$port, $errno, $errstr, $timeout);
        if ($R === false) {
            $this->logger->error("Connection to ".$transport."://$host:".$port." failed.");

            return $R;
        }

        $this->socket = $R;

        $this->logger->info("Connection to ".$transport."://$host:".$port." has been established.");

        // Retrieve the server's initial response.
        $response = $this->getStatusResponse();

        switch ($response->getCode()) {
            case ResponseCode::READY_POSTING_ALLOWED:
                return true;
                break;
            case ResponseCode::READY_POSTING_PROHIBITED:
                $this->logger->info('Posting not allowed!');

                return false;
                break;
            case ResponseCode::DISCONNECTING_FORCED:
                throw new \Exception("Server refused connection");
            case ResponseCode::NOT_PERMITTED:
                throw new \Exception("Server refused connection");
            default:
                $this->handleUnexpectedResponse($response);
        }
    }

    /**
     * Disconnect from the NNTP server
     *
     * @return bool
     */
    public function disconnect()
    {
        // Tell the server to close the connection
        $response = $this->sendCommand('QUIT');

        switch ($response->getCode()) {
            case ResponseCode::DISCONNECTING_REQUESTED:
                // If socket is still open, close it.
                if ($this->isConnected()) {
                    fclose($this->socket);
                }

                $this->logger->info('Connection closed.');

                return true;
                break;
            default:
                $this->handleUnexpectedResponse($response);
        }
    }

    /**
     * Authenticate using 'original' method
     *
     * @param string $user The username to authenticate as.
     * @param string $pass The password to authenticate with.
     *
     * @return bool
     * @throws \Exception
     */
    public function authenticate($user, $pass): bool
    {
        // Send the username
        $response = $this->sendCommand('AUTHINFO user '.$user);

        // Send the password, if the server asks
        if (($response->getCode() == ResponseCode::AUTHENTICATION_CONTINUE) && ($pass !== null)) {
            // Send the password
            $response = $this->sendCommand('AUTHINFO pass '.$pass);
        }

        switch ($response->getCode()) {
            case ResponseCode::AUTHENTICATION_ACCEPTED:
                $this->logger->info("Authenticated (as user '$user')");
                break;
            case ResponseCode::AUTHENTICATION_CONTINUE:
                throw new \Exception("Authentication uncompleted", 381);
            case ResponseCode::AUTHENTICATION_REJECTED:
                throw new \Exception("Authentication rejected", 482);
            case ResponseCode::NOT_PERMITTED:
                throw new \Exception("Authentication rejected", 502);
            default:
                $this->handleUnexpectedResponse($response);
        }

        return true;
    }

    /**
     * Fetches a list of all available newsgroups
     *
     * @param string $wildMatch Wildcard matching.
     *
     * @return Generator
     */
    public function listActive($wildMatch = null) : Generator
    {
        $command = 'LIST ACTIVE';
        if (!is_null($wildMatch)) {
            $command .= ' '.$wildMatch;
        }

        $response = $this->sendCommand($command);

        switch ($response->getCode()) {
            case ResponseCode::GROUPS_FOLLOW:
                $data = $this->getTextResponse();

                foreach ($data as $line) {
                    list($group, $last, $first, $posting) = explode(' ', trim($line));

                    yield new NewsGroup($group, $last, $first, $posting);
                }

                $this->logger->info('Fetched list of available groups');

                break;
            default:
                $this->handleUnexpectedResponse($response);
        }
    }

    /**
     * Returns servers capabilities
     *
     * @return array
     */
    public function getCapabilities(): array
    {
        $response = $this->sendCommand('CAPABILITIES');

        switch ($response->getCode()) {
            case ResponseCode::CAPABILITIES_FOLLOW:
                return $this->getTextResponse();
                break;
            default:
                $this->handleUnexpectedResponse($response);
        }
    }

    /**
     * Fetches a list of all available newsgroups
     *
     * @return Generator
     */
    public function list() : Generator
    {
        $response = $this->sendCommand('LIST');

        switch ($response->getCode()) {
            case ResponseCode::GROUPS_FOLLOW:
                $data = $this->getTextResponse();

                foreach ($data as $line) {
                    list($name, $last, $first, $posting) = explode(' ', trim($line));
                    yield new NewsGroup($name, $last, $first, $posting);
                }

                break;
            default:
                $this->handleUnexpectedResponse($response);
        }
    }

    /**
     * @param string $wildMatch
     *
     * @return Generator
     * @throws \Exception
     */
    public function getAllNewsgroups(string $wildMatch = '') : Generator
    {
        $command = 'LIST NEWSGROUPS';
        if (!empty($wildMatch)) {
            $command .= ' '.$wildMatch;
        }

        $response = $this->sendCommand($command);

        switch ($response->getCode()) {
            case ResponseCode::GROUPS_FOLLOW:
                $data = $this->getTextResponse();

                foreach ($data as $line) {
                    if (preg_match('/^(\S+)\s+(.*)$/', ltrim($line), $matches)) {
                        yield $matches[1] => (string)$matches[2];
                    } else {
                        $this->logger->warning("Received non-standard line: '$line'");
                    }
                }

                $this->logger->info('Fetched group descriptions');

                break;
            case ResponseCode::NOT_SUPPORTED: // RFC2980: 'program error, function not performed'
                throw new \Exception("Internal server error. Function not performed.");
            default:
                $this->handleUnexpectedResponse($response);
        }
    }

    /**
     * Selects a news group (issue a GROUP command to the server)
     *
     * @param string $group The newsgroup name
     *
     * @return array
     * @throws \Exception
     */
    public function accessGroup($group) : array
    {
        $response = $this->sendCommand('GROUP '.$group);

        switch ($response->getCode()) {
            case ResponseCode::GROUP_SELECTED:
                $responseArray = explode(' ', trim($response->getText()));

                $this->logger->info('Group selected: '.$responseArray[3]);

                return array(
                    'group' => $responseArray[3],
                    'first' => $responseArray[1],
                    'last' => $responseArray[2],
                    'count' => $responseArray[0]
                );
                break;
            case ResponseCode::NO_SUCH_GROUP:
                throw new \Exception("No such news group: ".$group);
                break;
            default:
                $this->handleUnexpectedResponse($response);
        }
    }

    /**
     * @param string   $group
     * @param int|null $start
     *
     * @return Generator
     */
    public function getHeadersForGroup(string $group, int $start = null) : Generator
    {

        $groupResponse = $this->accessGroup($group);
        $firstArticleId = (($start === null) ? $groupResponse['first'] : $start);
        $lastArticleId = $groupResponse['last'];

        $this->logger->info('Fetching headers '.$firstArticleId." to end (".$lastArticleId.")");
        $response = $this->sendCommand('OVER '.$firstArticleId."-");
        switch ($response->getCode()) {
            case ResponseCode::OVERVIEW_INFORMATION_FOLLOWS:
                $response = $this->getGeneratorTextResponse();
                foreach ($response as $line) {
                    $parts = explode("\t", str_replace("\t\t", "\t", $line));
                    $articleId = $parts[0];
                    $subject = $parts[1];
                    $from = $parts[2];
                    $messageId = $parts[4];
                    yield new Header($articleId, $subject, $messageId, $line, $from);
                }
                break;
            case ResponseCode::NO_GROUP_SELECTED:
                $this->logger->error("No group selected");
                break;
            case ResponseCode::NO_ARTICLE_SELECTED:
                $this->logger->error("No article selected");
                break;
            case ResponseCode::NO_SUCH_ARTICLE_NUMBER:
                $this->logger->error("No such article number");
                break;
            case ResponseCode::NOT_PERMITTED:
                $this->logger->error("Not permitted");
                break;
        }
    }

    public function getBodyForArticle($group, $articleId) : array
    {
        $this->accessGroup($group);
        $this->sendCommand('ARTICLE '.$articleId);
        $this->getTextResponse();
        $this->sendCommand('BODY '.$articleId);
        return $this->getTextResponse();
    }

    public function canRetrieveBody($messageId) : bool
    {
        $response = $this->sendCommand('STAT '.$messageId);
        return $response->getCode() === ResponseCode::ARTICLE_SELECTED;
    }

    public function isArticleExpired($messageId) : bool
    {
        $response = $this->sendCommand('STAT '.$messageId);
        $this->logger->debug('isArticleExpired: '.$response->getCode());
        return $response->getCode() === ResponseCode::NO_SUCH_ARTICLE_ID;
    }

    public function getNewGroups($date, $time)
    {
        $response = $this->sendCommand('NEWGROUPS '.$date.' '.$time.' GMT');
        switch ($response->getCode()) {
            case ResponseCode::NEW_GROUPS_FOLLOW:
                $lines = $this->getTextResponse();
                foreach ($lines as $line) {
                    list($name, $last, $first, $posting) = explode(" ", $line);
                    yield new NewsGroup($name, $last, $first, $posting);
                }
                break;
            default:
                $this->handleUnexpectedResponse($response);
        }
    }

    /**
     * Test whether we are connected or not.
     *
     * @return bool true or false
     */
    protected function isConnected()
    {
        return (is_resource($this->socket) && (!feof($this->socket)));
    }

    private function getGeneratorTextResponse() : Generator
    {
        $line = '';

        // Continue until connection is lost
        while (!feof($this->socket)) {

            // Retrieve and append up to 1024 characters from the server.
            $received = @fgets($this->socket, 1024);

            if ($received === false) {
                throw new \Exception("Failed to read line from the socket");
            }

            $line .= $received;
            $this->logger->debug('T Before: '.$line);

            // Continue if the line is not terminated by CRLF
            if (substr($line, -2) != "\r\n" || strlen($line) < 2) {
                $this->logger->debug("T: Line not terminated correctly.");
                continue;
            }

            // Remove CRLF from the end of the line
            $line = substr($line, 0, -2);

            // Check if the line terminates the textresponse
            if ($line == '.') {
                $this->logger->debug("T: Line started with a '.', returning data.");
                // return all previous lines
                return;
            }

            // If 1st char is '.' it's doubled (NNTP/RFC977 2.4.1)
            if (substr($line, 0, 2) == '..') {
                $line = substr($line, 1);
            }
            $this->logger->debug('T After: '.$line);

            // Add the line to the array of lines
            yield $line;

            // Reset/empty $line
            $line = '';
        }

        $this->logger->error('Broke out of reception loop! This shouldn\'t happen unless connection has been lost?');

        throw new \Exception("End of stream! Connection lost?");
    }

    /**
     * Retrieve textural data
     *
     * Get data until a line with only a '.' in it is read and return data.
     * @return array
     * @throws \Exception
     */
    private function getTextResponse()
    {
        $data = array();
        $line = '';

        // Continue until connection is lost
        while (!feof($this->socket)) {

            // Retrieve and append up to 1024 characters from the server.
            $received = @fgets($this->socket, 1024);

            if ($received === false) {
                throw new \Exception("Failed to read line from the socket");
            }

            $line .= $received;
            $this->logger->debug('T Before: '.$line);

            // Continue if the line is not terminated by CRLF
            if (substr($line, -2) != "\r\n" || strlen($line) < 2) {
                $this->logger->debug("T: Line not terminated correctly.");
                continue;
            }

            // Remove CRLF from the end of the line
            $line = substr($line, 0, -2);

            // Check if the line terminates the textresponse
            if ($line == '.') {
                $this->logger->debug("T: Line started with a '.', returning data.");
                // return all previous lines
                return $data;
            }

            // If 1st char is '.' it's doubled (NNTP/RFC977 2.4.1)
            if (substr($line, 0, 2) == '..') {
                $line = substr($line, 1);
            }
            $this->logger->debug('T After: '.$line);

            // Add the line to the array of lines
            $data[] = $line;

            // Reset/empty $line
            $line = '';
        }

        $this->logger->error('Broke out of reception loop! This shouldn\'t happen unless connection has been lost?');

        throw new \Exception("End of stream! Connection lost?");
    }

    /**
     * Send command
     *
     * Send a command to the server. A carriage return / linefeed (CRLF) sequence
     * will be appended to each command string before it is sent to the IMAP server.
     *
     * @param string $cmd The command to launch, ie: "ARTICLE 1004853"
     *
     * @return CommandResponse
     * @throws \Exception
     */
    private function sendCommand($cmd): CommandResponse
    {
        // NNTP/RFC977 only allows command up to 512 (-2) chars.
        if (!strlen($cmd) > 510) {
            throw new \Exception("Failed writing to socket! Command to long - max 510 characters");
        }

        // Check if connected
        if (!$this->isConnected()) {
            throw new \Exception("Failed to write to socket! (connection lost!)");
        }

        // Send the command
        $R = @fwrite($this->socket, $cmd."\r\n");
        if ($R === false) {
            throw new \Exception("Failed to write to socket!");
        }

        $this->logger->debug('C: '.$cmd);

        return $this->getStatusResponse();
    }

    /**
     * Get servers status response after a command.
     *
     * @return CommandResponse
     *
     * @throws \Exception
     */
    private function getStatusResponse(): CommandResponse
    {
        // Retrieve a line (terminated by "\r\n") from the server.
        $response = fgets($this->socket, 256);
        if ($response === false) {
            throw new FailedToReadFromSocket("Failed to read from socket...!");
        }

        $this->logger->debug('S: '.rtrim($response, "\r\n"));

        // Trim the start of the response in case of misplaced whitespace (should not be needed!!!)
        $response = ltrim($response);

        return new CommandResponse((int)substr($response, 0, 3), rtrim(substr($response, 4)));
    }

    /**
     * @param CommandResponse $response
     *
     * @throws \Exception
     */
    private function handleUnexpectedResponse(CommandResponse $response)
    {
        $code = $response->getCode();
        $message = $response->getText();

        switch ($code) {
            case ResponseCode::NOT_PERMITTED:
                throw new \Exception(
                    "Command not permitted / Access restriction / Permission denied: ".$message, $code
                );
            default:
                throw new \Exception("Unexpected response: (".$code.")".$message, $code);
        }
    }

    public function raw($group, $commands) : array
    {
        $response = $this->accessGroup($group);
        $this->logger->error(print_r($response, true));
        $commandsList = explode(",", $commands);
        foreach ($commandsList as $command) {
            $this->sendCommand($command);
        }

        return $this->getTextResponse();
    }
}