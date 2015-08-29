<?php

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Class ImportCommand.
 */
class ImportCommand extends ContainerAwareCommand
{
    /**
     * @var EntityManager
     */
    private $engineManager;

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('app:import:tuto')
            ->setDescription('Import tutoriels from old website');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<info>Start to import old datas</info>');

        $this->engineManager = $this->getContainer()->get('app.engine_manager');

        $startUrl = 'http://www.modding-area.com/forum/viewforum.php?f=119';
        $crawler = new Crawler(file_get_contents($startUrl), $startUrl);

        // get engines
        $engines = $this->getEngines($crawler);
        foreach ($engines as $engine) {
            $output->writeln(sprintf('<info>Retrieve contents from engine %s</info>', $engine['name']));

            $url = sprintf('http://www.modding-area.com/forum%s', mb_substr($engine['href'], 1));
            $crawler = new Crawler(file_get_contents($url), $url);
        }
    }

    /**
     * Get engines.
     * 
     * @param Crawler $crawler
     *
     * @return array
     *
     * @throws \Exception
     */
    private function getEngines(Crawler $crawler)
    {
        $engines = $crawler->filterXPath('//*[@id="page-body"]/div[2]/div/ul[2]');
        $links = $engines->filter('a.forumtitle')->each(function ($node, $i) {
            return [
                'name' => $node->text(),
                'href' => $node->attr('href'),
            ];
        });

        foreach ($links as &$link) {
            $engineId = $this->getEngineId($link['name']);
            $link['engine'] = $this->engineManager->getOneById($engineId);
        }

        return $links;
    }

    /**
     * Get engine ID.
     *
     * @param $name
     *
     * @return int
     *
     * @throws \Exception
     */
    private function getEngineId($name)
    {
        switch ($name) {
            case 'UDK':
                return 3;
            case 'Source engine':
                return 1;
            case 'GoldSrc':
                return 2;
            case 'Cry Engine 2 et 3':
                return 4;
        }

        throw new \Exception('Cant resolve engineId from engineName');
    }
}
