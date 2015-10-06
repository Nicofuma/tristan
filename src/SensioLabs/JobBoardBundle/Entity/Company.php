<?php

namespace SensioLabs\JobBoardBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="SensioLabs\JobBoardBundle\Repository\CompanyRepository")
 * @ORM\Table(
 *      indexes={
 *          @ORM\Index(name="company_idx", columns={"name", "country", "city"})
 *      },
 *      uniqueConstraints={
 *          @ORM\UniqueConstraint(name="company_cstr", columns={"name", "country", "city"})
 *      }
 * )
 */
class Company
{
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(name="name", type="string", length=255)
     *
     * @Assert\Length(max=255)
     * @Assert\NotBlank(message="Company should not be empty")
     */
    private $name;

    /**
     * @ORM\Column(name="country", type="string", length=2)
     *
     * @Assert\Country()
     * @Assert\NotBlank(message="Country should not be empty")
     */
    private $country;

    /**
     * @ORM\Column(name="city", type="string", length=80)
     *
     * @Assert\Length(max=80)
     * @Assert\NotBlank(message="City should not be empty")
     */
    private $city;

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return Company
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * @param string $country
     *
     * @return Company
     */
    public function setCountry($country)
    {
        $this->country = $country;

        return $this;
    }

    /**
     * @return string
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * @param string $city
     *
     * @return Company
     */
    public function setCity($city)
    {
        $this->city = $city;

        return $this;
    }
}
