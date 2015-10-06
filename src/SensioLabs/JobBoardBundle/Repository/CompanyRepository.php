<?php

namespace SensioLabs\JobBoardBundle\Repository;

use Doctrine\ORM\EntityRepository;
use SensioLabs\JobBoardBundle\Entity\Company;

class CompanyRepository extends EntityRepository
{
    public function findManagedOne(Company $company)
    {
        if (!$company->getName() || !$company->getCountry() || !$company->getCity()) {
            return;
        }

        return $this->findOneByFields($company->getName(), $company->getCountry(), $company->getCity());
    }

    public function findOneByFields($name, $country, $city)
    {
        return $this->createQueryBuilder('c')
            ->select('c')
            ->where('c.city = :city')->setParameter('city', $city)
            ->andWhere('c.country = :country')->setParameter('country', $country)
            ->andWhere('c.name = :name')->setParameter('name', $name)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
