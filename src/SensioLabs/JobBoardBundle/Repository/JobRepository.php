<?php

namespace SensioLabs\JobBoardBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use SensioLabs\JobBoardBundle\Entity\Job;
use SensioLabs\JobBoardBundle\Entity\JobStatus;
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
        return $this->createQueryBuilder('j')
            ->select('j')
            ->leftJoin('j.company', 'c')
        ;
    }

    public function addValidatedFilter(QueryBuilder $qb, $isValidated = true)
    {
        return $qb->andWhere('j.isValidated = :isValidated')->setParameter('isValidated', $isValidated);
    }

    public function addUserFilter(QueryBuilder $qb, User $user)
    {
        return $qb->andWhere('j.user = :user')->setParameter('user', $user);
    }

    public function addStatusFilter(QueryBuilder $qb, $status)
    {
        return $qb->andWhere('j.status = :status')->setParameter('status', $status);
    }

    public function addDateFilter(QueryBuilder $qb, \DateTime $date)
    {
        return $qb
            ->andWhere('j.publishedAt <= :date')
            ->andWhere('j.endedAt > :date')
            ->setParameter('date', $date)
        ;
    }

    public function addOrderByPubishedDate(QueryBuilder $qb, $order)
    {
        return $qb->addOrderBy('j.publishedAt', $order);
    }

    public function addDynamicFilters(QueryBuilder $builder, array $filters)
    {
        if (isset($filters['country'])) {
            $builder->andWhere('c.country = :country')->setParameter('country', $filters['country']);
        }

        if (isset($filters['contractType'])) {
            $builder->andWhere('j.contractType = :contractType')->setParameter('contractType', $filters['contractType']);
        }

        if (isset($filters['city'])) {
            $builder->andWhere('c.city = :city')->setParameter('city', $filters['city']);
        }

        return $builder;
    }

    public function countFilteredJobsPerCountry(array $filters)
    {
        $builder = $this->createQueryBuilder('j')
            ->leftJoin('j.company', 'c')
            ->select('c.country', 'COUNT(j.id) AS nb_jobs')
            ->groupBy('c.country')
            ->orderBy('c.country')
        ;

        $this->addDynamicFilters($builder, $filters);
        $this->addValidatedFilter($builder);

        return $builder->getQuery()->getArrayResult();
    }

    public function countFilteredJobsPerContractType(array $filters)
    {
        $builder = $this->createQueryBuilder('j')
            ->leftJoin('j.company', 'c')
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

    /**
     * @return Job
     */
    public function findOneRandom()
    {
        $jobsIds = $this->createQueryBuilder('j')
            ->select('j.id')
            ->getQuery()
            ->getScalarResult()
        ;

        $job = $jobsIds[mt_rand(0, count($jobsIds) - 1)];

        return $this->createQueryBuilder('j')
            ->select('j')
            ->where('j.id = :id')->setParameter('id', $job['id'])
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function deleteOldJobs()
    {
        return $this->createQueryBuilder('j')
            ->delete()
            ->where('j.status = :status')->setParameter('status', JobStatus::DELETED)
            ->andWhere('j.statusUpdatedAt <= :date')->setParameter('date', new \DateTime('-20 days'))
            ->getQuery()
            ->execute()
        ;
    }
}
