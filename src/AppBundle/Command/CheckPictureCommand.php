<?php

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Class CheckPictureCommand
 */
class CheckPictureCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('app:check:picture')
            ->setDescription('Check picture validity in articles');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<info>Start to check pictures links</info>');
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');

        $articles = $em->getRepository('AppBundle:Article')->findAll();

        $errors = 0;
        foreach ($articles as $article) {
            $output->writeln(sprintf('<info>Check article "%s"</info>', $article->getTitle()));

            $crawler = new Crawler($article->getContent());
            $imgs = $crawler->filter('img')->each(function($node, $i) {
                $src = $node->attr('src');
                if (mb_substr($src, 0, 2) === './') {
                    $src = str_replace('./', 'http://www.modding-area.com/forum/', $src);
                }
                return $src;
            });

            if (!count($imgs)) {
                continue;
            }

            foreach ($imgs as $img) {
                if (false !== mb_strpos($img, 'images/smilies')) {
                    //continue;
                }

                if ($statusCode = $this->hasPictureError($img)) {
                    $output->writeln(sprintf('<error>Error: status code %d for picture "%s"</error>', $statusCode, $img));
                    $article->setPublished(false);
                    $errors++;
                }
            }
        }

        $em->flush();
        $output->writeln(sprintf('<warning>%d articles with img errors</warning>', $errors));
    }

    /**
     * @param $url
     * @return bool|int
     */
    private function hasPictureError($url)
    {
        stream_context_set_default(
            array(
                'http' => array(
                    'method' => 'HEAD'
                )
            )
        );

        $statusCode = (int) substr(@get_headers($url)[0], 9, 3);
        return in_array($statusCode, [
            200,
            301
        ]) ? false : $statusCode;
    }
}
