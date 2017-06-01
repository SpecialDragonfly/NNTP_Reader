<?php
namespace Net\Command;

use Net\Client;
use Net\Protocol\Header;
use Net\Repository\DBInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GetArticles extends Command
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
            ->setName('newsgroups:get-articles')
            ->setDescription('Gets all articles in the specified newsgroup')
            ->addArgument('group', InputArgument::REQUIRED, "The group to retrieve headers for");
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $result = $this->db->selectOneFrom('groups', 'group_name', $input->getArgument('group'));
        if (empty($result)) {
            $output->writeln('<error>Unable to find newsgroup: '.$input->getArgument('group').'</error>');
            die();
        }
        $groupId = $result['id'];

        $headers = $this->client->getHeadersForGroup($input->getArgument('group'));
        /** @var Header $header */
        foreach($headers as $header) {
            $output->writeln("Writing to db: ".$header->toString());
            $this->db->insertInto(
                'articles',
                [
                    'article_id' => $header->getArticleId(),
                    'group_id' => $groupId,
                    'head' => $header->getHead(),
                    'body' => '',
                    'subject' => $header->getSubject(),
                    'message_id' => $header->getMessageId(),
                    'has_nzb_file' => 0
                ]
            );
            $output->writeln("written");
        }
    }
}