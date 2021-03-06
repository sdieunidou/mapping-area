<?php

namespace AppBundle\Manager;

use Doctrine\ORM\EntityManagerInterface;

/**
 * Class EngineManager.
 */
class EngineManager
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
     * find all.
     *
     * @return array
     */
    public function findAll()
    {
        return $this->getRepository()->findAll();
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
        return $this->em->getRepository('AppBundle:Engine');
    }
}
