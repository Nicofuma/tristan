<?php

namespace SensioLabs\JobBoardBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(
 *     indexes={@ORM\Index(columns={"user_name"})}
 * )
 * @ORM\Entity(repositoryClass="SensioLabs\JobBoardBundle\Repository\JobRepository")
 */
class Job
{
    const CONTRACT_FULL_TIME = 'full-time';
    const CONTRACT_PART_TIME = 'part-time';
    const CONTRACT_INTERNSHIP_TIME = 'internship';
    const CONTRACT_FREELANCE_TIME = 'freelance';
    const CONTRACT_ALTERNANCE_TIME = 'alternance';

    const CONTRACTS_TYPES = [
        self::CONTRACT_FULL_TIME => 'Full Time',
        self::CONTRACT_PART_TIME => 'Part Time',
        self::CONTRACT_INTERNSHIP_TIME => 'Internship',
        self::CONTRACT_FREELANCE_TIME => 'Freelance',
        self::CONTRACT_ALTERNANCE_TIME => 'Alternance',
    ];

    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(name="title", type="string", length=255)
     *
     * @Assert\Length(max=255)
     * @Assert\NotBlank(message="Job title should not be empty")
     */
    private $title;

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
     * @ORM\Column(name="contractType", type="string", length=15)
     *
     * @Assert\NotBlank(message="Contract must be selected")
     * @Assert\Length(max=15)
     * @Assert\Choice(callback="getContractTypes")
     */
    private $contractType;

    /**
     * @ORM\Column(name="description", type="text")
     *
     * @Assert\NotBlank(message="Your job offer must be longer.")
     */
    private $description;

    /**
     * @ORM\Column(name="howToApply", type="text", nullable=true)
     */
    private $howToApply;

    /**
     * @ORM\Column(name="name", type="string", length=255)
     *
     * @Assert\Length(max=255)
     * @Assert\NotBlank(message="Company should not be empty")
     */
    private $company;

    /**
     * @Gedmo\Slug(fields={"title"})
     * @ORM\Column(length=255, unique=true)
     */
    private $slug;

    /**
     * @ORM\Column(name="user_name", type="string", length=255, nullable=true)
     */
    private $userName;

    public static function getContractTypes()
    {
        return array_keys(self::CONTRACTS_TYPES);
    }

    public static function getReadableContractTypes()
    {
        return self::CONTRACTS_TYPES;
    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set title.
     *
     * @param string $title
     *
     * @return Job
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title.
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set country.
     *
     * @param string $country
     *
     * @return Job
     */
    public function setCountry($country)
    {
        $this->country = $country;

        return $this;
    }

    /**
     * Get country.
     *
     * @return string
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * Set city.
     *
     * @param string $city
     *
     * @return Job
     */
    public function setCity($city)
    {
        $this->city = $city;

        return $this;
    }

    /**
     * Get city.
     *
     * @return string
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * Set contractType.
     *
     * @param string $contractType
     *
     * @return Job
     */
    public function setContractType($contractType)
    {
        $this->contractType = $contractType;

        return $this;
    }

    /**
     * Get contractType.
     *
     * @return string
     */
    public function getContractType()
    {
        return $this->contractType;
    }

    /**
     * Set description.
     *
     * @param string $description
     *
     * @return Job
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description.
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set howToApply.
     *
     * @param string $howToApply
     *
     * @return Job
     */
    public function setHowToApply($howToApply)
    {
        $this->howToApply = $howToApply;

        return $this;
    }

    /**
     * Get howToApply.
     *
     * @return string
     */
    public function getHowToApply()
    {
        return $this->howToApply;
    }

    /**
     * Set company.
     *
     * @param string $company
     *
     * @return Job
     */
    public function setCompany($company)
    {
        $this->company = $company;

        return $this;
    }

    /**
     * Get company.
     *
     * @return string
     */
    public function getCompany()
    {
        return $this->company;
    }

    /**
     * Set slug.
     *
     * @param string $slug
     *
     * @return Job
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * Get slug.
     *
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * Set username.
     *
     * @param string $userName
     *
     * @return Job
     */
    public function setUserName($userName)
    {
        $this->userName = $userName;

        return $this;
    }

    /**
     * Get username.
     *
     * @return string
     */
    public function getUserName()
    {
        return $this->userName;
    }

    /**
     * @return bool
     */
    public function isValidated()
    {
        return true;
    }
}
