<?php

namespace AppBundle\Command;

use AppBundle\Entity\Article;
use AppBundle\Entity\Author;
use AppBundle\Entity\Category;
use AppBundle\Entity\Engine;
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

    private $users = [];

    private $em;

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
        $this->em = $this->getContainer()->get('doctrine.orm.entity_manager');

        $startUrl = 'http://www.modding-area.com/forum/viewforum.php?f=119';
        $crawler = new Crawler(file_get_contents($startUrl), $startUrl);

        // get engines
        $engines = $this->getEngines($crawler);
        foreach ($engines as $key => $engine) {
            $output->writeln(sprintf('<info>Retrieve contents from engine %s</info>', $engine['name']));

            $url = sprintf('http://www.modding-area.com/forum%s', mb_substr($engine['href'], 1));
            $crawler = new Crawler(file_get_contents($url), $url);

            $pagination = $crawler->filter('.topic-actions .pagination > span');
            $pages = $pagination->filter('a')->each(function ($node, $i) {
                return $node->attr('href');
            });

            $pages[] = $engine['href'];
            $pages = array_unique($pages);

            $topics = [];
            foreach ($pages as $page) {
                $pageUrl = sprintf('http://www.modding-area.com/forum%s', mb_substr($page, 1));
                $topics = array_merge($topics, $this->getTopics($pageUrl));
            }

            $engines[$key]['topics'] = $topics;
        }

        foreach ($engines as $key => $engine) {
            foreach ($engine['topics'] as $key2 => $topic) {
                if (empty($topic)) {
                    continue;
                }
                $topicUrl = sprintf('http://www.modding-area.com/forum%s', mb_substr($topic['topicUrl'], 1));
                $engines[$key]['topics'][$key2] = array_merge($this->getTopicContent($topicUrl), $engines[$key]['topics'][$key2]);
            }
        }

        foreach ($engines as $key => $engine) {
            foreach ($engine['topics'] as $key2 => $topic) {
                if (empty($topic) || (int) $topic['topicId'] === 24314) {
                    continue;
                }

                $article = new Article();
                $article->setContent($topic['content']);
                //$article->setCreatedAt(new \DateTime($topic['date']));
                $article->setTitle(trim($topic['title']));
                $article->setTopicId($topic['topicId']);
                $article->setAuthor($this->resolveAuthor($topic));
                $article->setCategory($this->resolveCategory($engine['engine']));

                $this->em->persist($article);
            }
        }

        $this->em->flush();
    }

    private function resolveCategory(Engine $engine)
    {
        if ($engine->getCategories()->first()) {
            return $engine->getCategories()->first();
        }

        $category = new Category();
        $category->setName('unclassed');
        $category->setEngine($engine);
        $engine->addCategory($category);

        $this->em->persist($category);

        return $category;
    }

    private function resolveAuthor(array $topic)
    {
        if (isset($this->users[$topic['userId']])) {
            return $this->users[$topic['userId']];
        }

        $author = new Author();
        $author->setName($topic['user']);
        $author->setUserId($topic['userId']);

        if (!empty($topic['avatar'])) {
            $author->setAvatar($topic['avatar']);
        }

        $this->em->persist($author);
        $this->users[$topic['userId']] = $author;

        return $author;
    }

    /**
     * Get topic content.
     *
     * @param $topicUrl
     *
     * @return array
     */
    private function getTopicContent($topicUrl)
    {
        $crawler = new Crawler(file_get_contents($topicUrl), $topicUrl);

        // get content
        $content = $crawler->filter('.post .postbody .content')->each(function ($node, $i) {
            return $node->html();
        });
        $content = $content[0];

        // get author
        $author = $crawler->filter('.post .postprofile > dt > a:nth-child(3)')->each(function ($node, $i) {
            preg_match('/u=([0-9]+)/', $node->attr('href'), $matches);
            if (empty($matches[1])) {
                throw new \Exception('userId is missing');
            }

            return [
                'user' => $node->text(),
                'userId' => $matches[1]
            ];
        });

        if (empty($author)) {
            $author = $crawler->filter('.post .postprofile > dt > a')->each(function ($node, $i) {
                preg_match('/u=([0-9]+)/', $node->attr('href'), $matches);
                if (empty($matches[1])) {
                    throw new \Exception('userId is missing');
                }

                return [
                    'user' => $node->text(),
                    'userId' => $matches[1]
                ];
            });
        }
        $author = $author[0];

        $data = [
            'content' => $content,
            'user'    => $author['user'],
            'userId'  => $author['userId'],
        ];

        // get avatar
        $avatar = $crawler->filter('.post .postprofile > dt > a > img')->each(function ($node, $i) {
            return $node->attr('src');
        });
        if (count($avatar)) {
            $avatar = $avatar[0];
            $author['avatar'] = $avatar;
            $author['avatar'] = str_replace('./download', 'http://www.modding-area.com/forum/download', $author['avatar']);
            if (!$this->hasPictureError($author['avatar'])) {
                $data['avatar'] = $author['avatar'];
            } else {
                unset($author['avatar']);
            }
        }

        return $data;
    }

    /**
     * @param $url
     * @return bool|int
     */
    private function hasPictureError($url)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT , 10);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        curl_exec($ch);
        if(!curl_errno($ch)) {
            $info = curl_getinfo($ch);
            return $info['http_code'] !== 200;
        } else {
            return true;
        }

        curl_close( $ch );
        return true;
    }

    /**
     * Get topics.
     * 
     * @param $pageUrl
     *
     * @return array
     */
    private function getTopics($pageUrl)
    {
        $crawler = new Crawler(file_get_contents($pageUrl), $pageUrl);
        $topics = $crawler->filter('ul.topiclist');
        if (!$topics->count()) {
            return [];
        }
        $item = $topics->filter('li')->each(function ($node, $i) {
            if (!$node->filter('.topictitle')->count()) {
                return;
            }

            $dateXpath = $node->filterXPath('//dl/dt/text()[3]');
            $date = null;
            if ($dateXpath->count()) {
                $date = str_replace(',', '', mb_substr(trim($dateXpath->text()), 3));
            }

            preg_match('/t=([0-9]+)/', $node->filter('.topictitle')->attr('href'), $matches);
            if (empty($matches[1])) {
                throw new \Exception('topicId is missing');
            }

            $title = $node->filter('.topictitle')->text();
            $title = str_replace([
                '[ TUTO ]',
                '[TUTO] ',
                '[TUTOS] ',
                '[tuto]',
                '[Tuto]',
                '[TuTo]',
                'TUTO : ',
                '[CE] ',
            ], '', $title);

            return [
                'title' => $title,
                'topicUrl' => $node->filter('.topictitle')->attr('href'),
                'topicId' => $matches[1],
                'date' => $date,
            ];
        });

        return $item;
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

        unset($links[3]);

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
        }

        throw new \Exception('Cant resolve engineId from engineName');
    }
}
