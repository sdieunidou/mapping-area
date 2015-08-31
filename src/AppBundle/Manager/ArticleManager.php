<?php

namespace AppBundle\Manager;

use AppBundle\Entity\Engine;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Class ArticleManager.
 */
class ArticleManager
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * Constructor.
     *
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @param Engine $engine
     * @return array
     */
    public function getByEngine(Engine $engine)
    {
        return $this->getRepository()->getByEngine($engine);
    }

    /**
     * @param int $id
     *
     * @return Engine
     */
    public function getOneById($id)
    {
        return $this->getRepository()->findOneById($id);
    }

    /**
     * @param  string $slug
     *
     * @return Engine
     */
    public function getOneBySlug($slug)
    {
        return $this->getRepository()->findOneBySlug($slug);
    }

    /**
     * Get repository.
     *
     * @return \Doctrine\Common\Persistence\ObjectRepository
     */
    public function getRepository()
    {
        return $this->em->getRepository('AppBundle:Article');
    }
}
