<?php
namespace Net\Command;

use Net\Client;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Raw extends Command
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
            ->setName('newsgroups:send-raw')
            ->setDescription('Gets all articles in the specified newsgroup')
            ->addArgument('group', InputArgument::REQUIRED, "The group to connect to.")
            ->addArgument('cmd', InputArgument::REQUIRED, "The command to run.");
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $response = $this->client->raw($input->getArgument('group'), $input->getArgument('cmd'));
        foreach ($response as $line) {
            $output->writeln('---');
            $output->writeln($line);
        }
    }
}