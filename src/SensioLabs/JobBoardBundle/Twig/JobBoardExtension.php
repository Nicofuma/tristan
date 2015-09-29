<?php

namespace SensioLabs\JobBoardBundle\Twig;

use SensioLabs\JobBoardBundle\Entity\Job;
use Symfony\Component\Intl\Intl;

class JobBoardExtension extends \Twig_Extension
{
    /**
     * @return array
     */
    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('country', [$this, 'filterCountry']),
            new \Twig_SimpleFilter('contractType', [$this, 'filterContractType']),
        ];
    }

    /**
     * Replaces a country code by the country name.
     *
     * @param string $code
     *
     * @return null|string
     */
    public function filterCountry($code)
    {
        return Intl::getRegionBundle()->getCountryName($code);
    }

    /**
     * Replace a contract type code by it's pretty name.
     *
     * @param string $contractType
     *
     * @return null|string
     */
    public function filterContractType($contractType)
    {
        $contractTypes = Job::getReadableContractTypes();

        return isset($contractTypes[$contractType]) ? $contractTypes[$contractType] : null;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'jobboard_extension';
    }
}
