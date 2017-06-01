<?php
namespace Net\Command;

use Net\Client;
use Net\Repository\DBInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GetNzbFiles extends Command
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
            ->setName('newsgroups:get-nzb-files')
            ->setDescription('Lists all the nzb files available within a given group.')
            ->addArgument('group', InputArgument::REQUIRED, "The group to retrieve headers for");
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $result = $this->db->selectOneFrom('groups', 'group_name', $input->getArgument('group'));
        if (empty($result)) {
            $output->writeln('<error>No group found for: '.$input->getArgument('group').'</error>');
            die();
        }
        $results = $this->db->selectAllFrom('articles', ['group_id' => $result['id']], 0, 0);
        foreach ($results as $result) {
            if (strpos($result['subject'], '.nzb') !== false) {
                $output->writeln(
                    implode(
                        " ",
                        [
                            $result['article_id'],
                            $input->getArgument('group'),
                            $result['subject'],
                            $result['message_id']
                        ]
                    )
                );
            }
        }
    }
}