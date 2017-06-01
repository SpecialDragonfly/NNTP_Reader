<?php
namespace Net\Command;

use DOMElement;
use Net\Client;
use Net\Repository\DBInterface;
use Net\Decoder\Yenc;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ExecuteNzbFile extends Command
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
    private $yencDecoder;

    public function __construct(Client $client, DBInterface $db, Yenc $yencDecoder)
    {
        parent::__construct(null);
        $this->client = $client;
        $this->db = $db;
        $this->yencDecoder = $yencDecoder;
    }

    protected function configure()
    {
        $this
            ->setName('newsgroups:execute-nzb-file')
            ->setDescription('Downloads all the files within the nzb file.')
            ->addArgument('nzbfile', InputArgument::REQUIRED, "The NZB file to execute")
            ->addArgument('downloadFolder', InputArgument::REQUIRED, "Where to put the downloaded files");
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $nzbFile = $input->getArgument('nzbfile');
        $downloadLocation = $input->getArgument('downloadFolder');

        $fileContents = file_get_contents($nzbFile);
        $fileContents = substr($fileContents, strpos($fileContents, '<?xml'));
        $fileContents = substr($fileContents, 0, strpos($fileContents, '</nzb>') + 6);

        $xmlDoc = new \DOMDocument();
        $xmlDoc->loadXML($fileContents);

        /** @var \DOMNodeList $domNodeList */
        $fileList = $xmlDoc->getElementsByTagName('file');
        foreach ($fileList as $file) {
            /** @var DOMElement $file */
            $filename = $file->getAttribute('subject');

            $fh = fopen($downloadLocation.'/'.str_replace([" ", "/"], "_", $filename), 'w+b');
            if ($fh === false) {
                continue;
            }

            $segments = $file->getElementsByTagName('segment');
            /** @var DOMElement $segment */
            foreach ($segments as $segment) {
                $messageId = "<".$segment->textContent.">";
                $output->writeln("<info>Looking for message: ".$messageId."</info>");
                $result = $this->db->selectOneFrom('articles', 'message_id', $messageId);
                if (empty($result)) {
                    fwrite($fh, str_repeat("\0", $segment->getAttribute('bytes')));
                    $output->writeln('<error>Missing part: '.$messageId.'</error>');
                } else {
                    $group = $this->db->selectOneFrom('groups', 'id', $result['group_id']);
                    $article = implode("", $this->client->getBodyForArticle($group['group_name'], $result['article_id']));
                    if (strpos($article, '=ybegin') !== false) {
                        $article = $this->yencDecoder->decode($article);
                    }
                    fwrite($fh, $article);
                }
            }
            fclose($fh);
        }
    }
}