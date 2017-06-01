<?php
namespace Net\Command;

use Net\Client;
use Net\Protocol\NewsGroup;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GetGroups extends Command
{
    /**
     * @var Client
     */
    private $client;

    public function __construct(Client $client)
    {
        parent::__construct(null);
        $this->client = $client;
    }

    protected function configure()
    {
        $this
            ->setName('newsgroups:get-groups')
            ->setDescription('Gets the available groups')
            ->addArgument('wild-match', InputArgument::OPTIONAL, "An optional argument to filter groups by.");
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($input->hasArgument('wild-match')) {
            $groups = $this->client->getActiveGroups($input->getArgument('wild-match'));
        } else {
            $groups = $this->client->getActiveGroups();
        }

        /** @var NewsGroup $group */
        foreach ($groups as $group) {
            $output->writeln($group->getName());
        }
    }
}