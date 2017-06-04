<?php
namespace Net\Command;

use Net\Client;
use Net\Repository\DBInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CheckArticles extends Command
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
            ->setName('newsgroups:check-articles')
            ->setDescription('Checks the articles in the DB to see whether the body is readable')
            ->addArgument('group', InputArgument::OPTIONAL, "The group to validate db entries of.");
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $parameters = [];
        $sql = <<<SQL
SELECT message_id FROM articles LEFT JOIN groups ON articles.group_id = groups.id
SQL;
        if ($input->hasArgument('group') && $input->getArgument('group') !== '') {
            $sql .= ' WHERE groups.group_name = :group_name';
            $parameters['group_name'] = $input->getArgument('group');
        }
        $results = $this->db->raw($sql, $parameters);

        foreach ($results as $result) {
            $expired = $this->client->isArticleExpired($result['message_id']);
            if ($expired === true) {
                $output->writeln('<info>Deleting '.$result['message_id'].' from table');
                $this->db->deleteFromTable('articles', 'message_id', $result['message_id']);
            }
        }
    }
}