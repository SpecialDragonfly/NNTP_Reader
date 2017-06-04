<?php

use Net\Client;
use Net\Command\GetAllNewsgroupsArticleCountsCommand;
use Net\Protocol\NntpClient;
use Net\Decoder\Yenc;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Output\ConsoleOutput;

require_once '../vendor/autoload.php';

include_once 'credentials.php';

$encryption = new \Net\Encryption\NoEncryption(119);
//$db = new \Net\Repository\SQLiteDB(dirname(dirname(__FILE__)).'/data/sqlite.db');
$db = new \Net\Repository\MysqlDB($dsn, $mysqlUsername, $mysqlPassword);
$logger = new \Net\Logger\InfoLogger(new ConsoleOutput());
$fileFactory = new \Net\File\FileFactory();
$client = new Client(new NntpClient($logger), $encryption, $host, $username, $password, $fileFactory);
$success = $client->connect();
if ($success === false) {
    echo "Failed to connect.\n";
    die();
}
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
$application->add(new \Net\Command\Raw($client));
$application->add(new \Net\Command\CheckArticles($client, $db));
$application->run();

//$groups = $client->getActiveGroups();
//file_put_contents('test.json', json_encode($result));