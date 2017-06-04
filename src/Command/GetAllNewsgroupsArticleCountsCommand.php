<?php
namespace Net\Command;

use Net\Client;
use Net\Exception\FailedToReadFromSocket;
use Net\Protocol\NewsGroup;
use Net\Repository\DBInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GetAllNewsgroupsArticleCountsCommand extends Command
{
    /**
     * @var Client
     */
    private $client;
    /**
     * @var DBInterface
     */
    private $db;

    public function __construct(Client $client, DBInterface $db)
    {
        parent::__construct(null);
        $this->client = $client;
        $this->db = $db;
    }

    protected function configure()
    {
        $this
            ->setName('newsgroups:get-article-counts')
            ->setDescription('Gets all article counts for all newsgroups');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $newsgroups = $this->client->getAllNewsgroups();
        $count = 0;
        $start = microtime(true);
        /** @var NewsGroup $newsgroup */
        foreach ($newsgroups as $groupName => $groupDescription) {
            try {
                $result = $this->client->accessGroup($groupName);
                $record = $this->db->selectOneFrom('groups', 'group_name', $groupName);
                if (empty($record)) {
                    $this->db->insertInto('groups', ['group_name' => $groupName, 'article_count' => $result['last'] - $result['first']]);
                } else {
                    $this->db->update('groups', ['group_name' => $groupName], ['article_count' =>  $result['last'] - $result['first']]);
                }
            } catch (FailedToReadFromSocket $ex) {
                $output->writeln('<error>'.$ex->getMessage().'</error>');
                $this->client->reconnect();
            } catch (\Exception $ex) {
                $output->writeln('<error>'.$ex->getMessage().'</error>');
            }
            $count++;
            if ($count === 1000) {
                $count = 0;
                $end = microtime(true);
                $output->writeln('<info>1000 groups took: '.($end - $start).' seconds</info>');
                $start = microtime(true);
            }
        }
        $this->client->disconnect();
    }
}