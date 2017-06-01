<?php
namespace Net\Command;

use Net\Client;
use Net\Repository\DBInterface;
use Net\Decoder\Yenc;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class YencDecode extends Command
{
    /**
     * @var Client
     */
    private $client;
    /**
     * @var DBInterface
     */
    private $db;
    /**
     * @var Yenc
     */
    private $decoder;

    /**
     * YencDecode constructor.
     *
     * @param Client      $client
     * @param DBInterface $db
     * @param Yenc        $decoder
     */
    public function __construct(Client $client, DBInterface $db, Yenc $decoder)
    {
        parent::__construct(null);
        $this->client = $client;
        $this->db = $db;
        $this->decoder = $decoder;
    }

    protected function configure()
    {
        $this
            ->setName('newsgroups:decode-yenc-file')
            ->setDescription('Lists all the nzb files available within a given group.')
            ->addArgument('group', InputArgument::REQUIRED, "The group to retrieve headers for")
            ->addArgument('messageId', InputArgument::REQUIRED, "The article id relating to the yenc file")
            ->addArgument('destination', InputArgument::REQUIRED, "Where the decoded file should be placed");
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $response = $this->client->getBodyForArticle($input->getArgument('group'), $input->getArgument('messageId'));
        $article = implode("", $response);

        $result = file_put_contents($input->getArgument('destination'), $this->decoder->decode($article));
        if ($result === false) {
            $output->writeln('<error>File unable to be written.</error>');
        }
        $output->writeln('<info>File decoded to: '.$input->getArgument('destination').'</info>');
    }
}