<?php

namespace SensioLabs\JobBoardBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;

class JobRepository extends EntityRepository
{
    public function getAllFilteredQueryBuilder($filters)
    {
        $query = $this->createQueryBuilder('j')
            ->select('j')
            ->orderBy('j.id', 'ASC')
        ;
        $query->where($this->getFiltersExpression($query, $filters));

        return $query;
    }

    public function getAllForUserQueryBuilder($userName)
    {
        return $this->createQueryBuilder('j')
            ->select('j')
            ->where('j.userName = :user_name')->setParameter('user_name', $userName)
        ;
    }

    public function countFilteredJobsPerCountry(array $filters)
    {
        $builder = $this->createQueryBuilder('j')
            ->select('j.country', 'COUNT(j.id) AS nb_jobs')
            ->groupBy('j.country')
            ->orderBy('j.country')
        ;

        $builder->where($this->getFiltersExpression($builder, $filters));

        return $builder->getQuery()->getArrayResult();
    }

    public function countFilteredJobsPerContractType(array $filters)
    {
        $builder = $this->createQueryBuilder('j')
            ->select('j.contractType', 'COUNT(j.id) AS nb_jobs')
            ->groupBy('j.contractType')
            ->orderBy('j.contractType')
        ;

        $builder->where($this->getFiltersExpression($builder, $filters));

        return $builder->getQuery()->getArrayResult();
    }

    /**
     * Returns the expression corresponding to the filters and add set the corresponding parameters.
     *
     * @param QueryBuilder $builder
     * @param array        $filters
     *
     * @return Expr
     */
    public function getFiltersExpression(QueryBuilder $builder, array $filters)
    {
        $expr = $builder->expr();
        $conditions = [$expr->eq(true, true)];

        if (isset($filters['country'])) {
            $conditions[] = $expr->eq('j.country', ':country');
            $builder->setParameter('country', $filters['country']);
        }

        if (isset($filters['contractType'])) {
            $conditions[] = $expr->eq('j.contractType', ':contractType');
            $builder->setParameter('contractType', $filters['contractType']);
        }

        if (isset($filters['city'])) {
            $conditions[] = $expr->eq('j.city', ':city');
            $builder->setParameter('city', $filters['city']);
        }

        return new Expr\Andx($conditions);
    }
}
