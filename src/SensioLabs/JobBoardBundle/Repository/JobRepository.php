<?php

namespace SensioLabs\JobBoardBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use SensioLabs\JobBoardBundle\Entity\Job;
use SensioLabs\JobBoardBundle\Entity\User;

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

    public function findAllQb()
    {
        return $this->createQueryBuilder('j')->select('j');
    }

    public function addValidatedFilter(QueryBuilder $qb, $isValidated = true)
    {
        return $qb->andWhere('j.isValidated = :isValidated')->setParameter('isValidated', $isValidated);
    }

    public function addUserFilter(QueryBuilder $qb, User $user)
    {
        return $qb->andWhere('j.user = :user')->setParameter('user', $user);
    }

    public function addDynamicFilters(QueryBuilder $builder, array $filters)
    {
        if (isset($filters['country'])) {
            $builder->andWhere('j.country = :country')->setParameter('country', $filters['country']);
        }

        if (isset($filters['contractType'])) {
            $builder->andWhere('j.contractType = :contractType')->setParameter('contractType', $filters['contractType']);
        }

        if (isset($filters['city'])) {
            $builder->andWhere('j.country = :city')->setParameter('city', $filters['city']);
        }

        return $builder;
    }

    public function countFilteredJobsPerCountry(array $filters)
    {
        $builder = $this->createQueryBuilder('j')
            ->select('j.country', 'COUNT(j.id) AS nb_jobs')
            ->groupBy('j.country')
            ->orderBy('j.country')
        ;

        $this->addDynamicFilters($builder, $filters);
        $this->addValidatedFilter($builder);

        return $builder->getQuery()->getArrayResult();
    }

    public function countFilteredJobsPerContractType(array $filters)
    {
        $builder = $this->createQueryBuilder('j')
            ->select('j.contractType', 'COUNT(j.id) AS nb_jobs')
            ->groupBy('j.contractType')
            ->orderBy('j.contractType')
        ;

        $this->addDynamicFilters($builder, $filters);
        $this->addValidatedFilter($builder);

        return $builder->getQuery()->getArrayResult();
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
