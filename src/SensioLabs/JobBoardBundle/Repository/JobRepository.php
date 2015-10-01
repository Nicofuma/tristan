<?php

namespace SensioLabs\JobBoardBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use SensioLabs\JobBoardBundle\Entity\Job;

class JobRepository extends EntityRepository
{
    const VIEW_LOCATION_HOMEPAGE = 'Homepage';
    const VIEW_LOCATION_DETAILS = 'Details';
    const VIEW_LOCATION_API = 'API';

    const VIEW_LOCATIONS = [
        self::VIEW_LOCATION_HOMEPAGE,
        self::VIEW_LOCATION_DETAILS,
        self::VIEW_LOCATION_API,
    ];

    public function getAllFilteredQueryBuilder($filters)
    {
        $query = $this->createQueryBuilder('j')
            ->select('j')
            ->orderBy('j.id', 'ASC')
        ;
        $query->where($this->getFiltersExpression($query, $filters));

        return $query;
    }

    public function getAllPublishedQueryBuilder()
    {
        return $this->createQueryBuilder('j')
            ->select('j')
            ;
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

    /**
     * @param Job[]  $jobs
     * @param string $type type of view, must be in self::VIEW_TYPES
     */
    public function view(array $jobs, $type)
    {
        if (!in_array($type, self::VIEW_LOCATIONS, true)) {
            throw new \InvalidArgumentException();
        }

        $builder = $this->createQueryBuilder('j');
        $builder
            ->update()
            ->set('j.viewCount'.$type, 'j.viewCount'.$type.' + 1')
            ->where('j IN (:jobs)')->setParameter('jobs', $jobs)
            ->getQuery()
            ->execute()
        ;

        // Note: the entities are not refreshed because we don't use the counters values in the same request
    }
}
