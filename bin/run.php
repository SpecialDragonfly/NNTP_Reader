<?php

use Net\Client;
use Net\Command\GetAllNewsgroupsArticleCountsCommand;
use Net\Logger\WarningLogger;
use Net\Protocol\NntpClient;
use Net\Decoder\Yenc;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Output\ConsoleOutput;

require_once '../vendor/autoload.php';

include_once 'credentials.php';

$encryption = new \Net\Encryption\NoEncryption(80);
$db = new \Net\Repository\SQLiteDB(dirname(dirname(__FILE__)).'/data/sqlite.db');
$logger = new WarningLogger(new ConsoleOutput());
$fileFactory = new \Net\File\FileFactory();
$client = new Client(new NntpClient($logger), $encryption, $host, $username, $password, $fileFactory);
$client->connect();
$client->authenticate($username, $password);

$application = new Application();
$application->add(new GetAllNewsgroupsArticleCountsCommand($client, $db));
$application->add(new \Net\Command\GetArticles($client, $db));
$application->add(new \Net\Command\GetArticleBody($client, $db));
$application->add(new \Net\Command\GetGroups($client, $db));
$application->add(new \Net\Command\GetNewGroups($client, $db));
$application->add(new \Net\Command\GetNzbFiles($client, $db));
$application->add(new \Net\Command\ExecuteNzbFile($client, $db, new Yenc()));
$application->add(new \Net\Command\YencDecode($client, $db, new Yenc()));
$application->run();

//$groups = $client->getActiveGroups();
//file_put_contents('test.json', json_encode($result));