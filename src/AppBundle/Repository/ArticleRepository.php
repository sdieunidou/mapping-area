<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Engine;
use Doctrine\ORM\EntityRepository;

/**
 * ArticleRepository.
 */
class ArticleRepository extends EntityRepository
{
    /**
     * @param Engine $engine
     * @return array
     */
    public function getByEngine(Engine $engine)
    {
        return $this->createQueryBuilder('a')
            ->select('a, au')
            ->leftJoin('a.author', 'au')
            ->leftJoin('a.category', 'c')
            ->leftJoin('c.engine', 'e')
            ->andWhere('e.id = :engineId')
            ->setParameter('engineId', $engine->getId())
            ->getQuery()
            ->getResult();
    }
}
