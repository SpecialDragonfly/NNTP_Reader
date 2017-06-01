<?php
namespace Net\Command;

use Net\Client;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GetArticleBody extends Command
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
            ->setName('newsgroups:get-article')
            ->setDescription('Gets the body of the specified article')
            ->addArgument('group', InputArgument::REQUIRED, "The group to query.")
            ->addArgument('articleId', InputArgument::REQUIRED, "The article to retrieve the body for.");
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $response = $this->client->getBodyForArticle(
            $input->getArgument('group'),
            $input->getArgument('articleId')
        )->getRaw();
        foreach ($response as $line) {
            $output->writeln($line);
        }
    }
}