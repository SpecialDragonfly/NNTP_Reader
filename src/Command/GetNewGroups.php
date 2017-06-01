<?php
namespace Net\Command;

use Net\Client;
use Net\Protocol\NewsGroup;
use Net\Repository\DBInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GetNewGroups extends Command
{
    const NEW_GROUP_KEY = 'new_group_last_checked_date';

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
            ->setName('newsgroups:get-new-groups')
            ->setDescription('Gets new newsgroups since last check');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $result = $this->db->selectOneFrom('config', 'key', static::NEW_GROUP_KEY);
        if (count($result) === 0) {
            $date = new \DateTime();
            $this->db->insertInto('config', ['key' => static::NEW_GROUP_KEY, 'value' => $date->format('Ymd 000000')]);
            $result['value'] = $date->format('Ymd 000000');
        }
        list($date, $time) = explode(" ", $result['value']);

        $newGroups = $this->client->getNewNewsgroups($date, $time);
        if (count($newGroups) === 0) {
            $output->writeln('<info>No new groups found</info>');
        }
        /** @var NewsGroup $newGroup */
        foreach ($newGroups as $newGroup) {
            $output->writeln('<info>Found new group: '.$newGroup->getName().'</info>');
            $this->db->insertInto(
                'groups',
                [
                    'group_name' => $newGroup->getName(),
                    'can_post' => $newGroup->isPostingAllowed(),
                    'article_count' => $newGroup->getLast() - $newGroup->getFirst()
                ]
            );
        }
        $date = new \DateTime();
        $this->db->update('config', ['key' => static::NEW_GROUP_KEY], ['value' => $date->format('Ymd 000000')]);
    }

}