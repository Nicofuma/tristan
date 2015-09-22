<?php

namespace SensioLabs\JobBoardBundle\Repository;

use Doctrine\ORM\EntityRepository;

class JobRepository extends EntityRepository
{
    public function getAllWithBounds($offset, $limit)
    {
        return $this->createQueryBuilder('j')
            ->select('j')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->execute()
        ;
    }
}
