<?php

namespace SensioLabs\JobBoardBundle\Form\DataTransformer;

use Doctrine\Common\Persistence\ObjectManager;
use SensioLabs\JobBoardBundle\Entity\Company;
use Symfony\Component\Form\DataTransformerInterface;

class CompanyTransformer implements DataTransformerInterface
{
    private $manager;

    public function __construct(ObjectManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * Do nothing.
     *
     * @param Company $company
     *
     * @return Company
     */
    public function transform($company)
    {
        return $company;
    }

    /**
     * Create the company if necessary and fetch a managed entity.
     *
     * @param Company $company
     *
     * @return Company
     */
    public function reverseTransform($company)
    {
        if (!$company->getName() || !$company->getCountry() || !$company->getCity()) {
            return $company;
        }

        $managedCompany = $this->manager->getRepository(Company::class)->findManagedOne($company);
        if (!$managedCompany) {
            $this->manager->persist($company);
            $this->manager->flush();
            $managedCompany = $company;
        }

        return $managedCompany;
    }
}
