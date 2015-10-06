<?php

namespace SensioLabs\JobBoardBundle\TestsFunctional\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use SensioLabs\JobBoardBundle\Entity\Job;
use SensioLabs\JobBoardBundle\Entity\JobStatus;
use Symfony\Component\Intl\Intl;

class FilterJobsData extends AbstractFixture
{
    const NB_COUNTRIES = 10;
    const JOBS_PER_COUNTRY_FACTOR = 10;
    const CONTRACT_TYPES_PER_COUNTRY_DISTRIBUTION = [
        Job::CONTRACT_FULL_TIME => 0.4,
        Job::CONTRACT_PART_TIME => 0.2,
        Job::CONTRACT_INTERNSHIP_TIME => 0.2,
        Job::CONTRACT_ALTERNANCE_TIME => 0.1,
        Job::CONTRACT_FREELANCE_TIME => 0.1,
    ];
    const CITIES = ['Paris', 'Toulouse'];

    public static function getNbJobsForCountry($country)
    {
        $countryReverseMap = array_flip(array_keys(Intl::getRegionBundle()->getCountryNames()));

        return (1 + $countryReverseMap[$country]) * self::JOBS_PER_COUNTRY_FACTOR;
    }

    public static function getNbJobsForCountryAndContractType($country, $contractType)
    {
        $distribution = self::CONTRACT_TYPES_PER_COUNTRY_DISTRIBUTION;

        return $distribution[$contractType] * self::getNbJobsForCountry($country);
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $contractTypes = Job::getContractTypes();
        $countries = array_keys(Intl::getRegionBundle()->getCountryNames());
        $cities = self::CITIES;

        for ($i = 0; $i < self::NB_COUNTRIES; ++$i) {
            $country = $countries[$i];
            foreach ($contractTypes as $contractType) {
                for ($j = self::getNbJobsForCountryAndContractType($country, $contractType); $j > 0; --$j) {
                    $job = new Job();
                    $job
                        ->setTitle("#$i-$j - $country - $contractType - FooBar Job")
                        ->setDescription('This is the description of an amazing job!')
                        ->setCompany($this->getCompany($manager, 'SensioLabs', $country, $cities[$j % 2]))
                        ->setContractType($contractType)
                        ->setHowToApply('Send an email to jobs@foobar.com')
                        ->setIsValidated()
                        ->setPublishedAt(new \DateTime())
                        ->setEndedAt(new \DateTime('+1 year'))
                        ->setStatus(JobStatus::create(JobStatus::PUBLISHED))
                    ;

                    $manager->persist($job);
                }
            }
        }

        $manager->flush();
    }
}
