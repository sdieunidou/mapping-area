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

            $article->setContent(str_replace('./images/smilies/', 'http://www.modding-area.com/forum/images/smilies/', $article->getContent()));
            $article->setContent(str_replace('http://www.mapping-area.com', 'http://www.modding-area.com', $article->getContent()));
            $article->setContent(str_replace('http://mapping-area.com', 'http://www.modding-area.com', $article->getContent()));
            $article->setContent(str_replace('http://modding-area.com', 'http://www.modding-area.com', $article->getContent()));

            $crawler = new Crawler($article->getContent());
            $imgs = $crawler->filter('img')->each(function($node, $i) {
                $src = $node->attr('src');
                if (mb_substr($src, 0, 2) === './') {
                    $src = str_replace('./', 'http://www.modding-area.com/forum/', $src);
                }
                return $src;
            });

            $hasError = false;
            foreach ($imgs as $img) {
                if (false !== mb_strpos($img, 'images/smilies')) {
                    continue;
                }

                if ($this->hasPictureError($img)) {
                    $hasError = true;
                }
            }

            if ($hasError) {
                $output->writeln(sprintf('<error>Errors detected</error>'));
                $article->setPublished(false);
                $errors++;
            } else {
                $article->setPublished(true);
            }
        }

        $em->flush();
        $output->writeln(sprintf('<info>%d articles with img errors</info>', $errors));
    }

    /**
     * @param $url
     * @return bool|int
     */
    private function hasPictureError($url)
    {
        $url = str_replace('http://mapping-area.com', 'http://www.modding-area.com', $url);
        $url = str_replace('http://www.mapping-area.com', 'http://www.modding-area.com', $url);

        if (false !== mb_strpos($url, 'http://www.siteduzero.com/uploads')) {
            return false;
        }

        if (false !== mb_strpos($url, 'noelshack.com')) {
            return false;
        }

        if (false !== mb_strpos($url, 'developer.valvesoftware.com')) {
            return false;
        }

        if (false !== mb_strpos($url, 'nyko18.free.fr')) {
            return false;
        }

        if (false !== mb_strpos($url, 'perso.wanadoo.fr/remi3d/')) {
            return false;
        }

        if (false !== mb_strpos($url, 'perso.wanadoo.fr/gargamel90/')) {
            return false;
        }

        if (false !== mb_strpos($url, 'perso.wanadoo.fr/tutodmcconfigure1/')) {
            return false;
        }

        if (false !== mb_strpos($url, 'commentcamarche.net')) {
            return false;
        }
        if (false !== mb_strpos($url, 'perso.wanadoo.fr/manu-site/')) {
            return false;
        }
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT , 10);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        curl_exec($ch);
        if(!curl_errno($ch)) {
            $info = curl_getinfo($ch);
            if ($info['http_code'] !== 200 && $info['http_code'] !== 404 && $info['http_code'] !== 502 &&  $info['http_code'] !== 403) {
                //var_dump($url, $info['http_code']);
            }
            return $info['http_code'] !== 200;
        } else {
            return true;
        }

        curl_close( $ch );
        return true;
    }
}
