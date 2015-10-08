<?php

namespace SensioLabs\JobBoardBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Eko\FeedBundle\Item\Writer\RoutedItemInterface;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table
 * @ORM\Entity(repositoryClass="SensioLabs\JobBoardBundle\Repository\JobRepository")
 * @Assert\Expression(
 *      "!this.isValidated() or (this.getEndedAt() > this.getPublishedAt())",
 *      message="If the job is validated, the end date must be posterior to the published date."
 * )
 */
class Job implements RoutedItemInterface
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
     * @Gedmo\Slug(fields={"title"})
     * @ORM\Column(length=255, unique=true)
     */
    private $slug;

    /**
     * @ORM\Column(name="viewCountHomepage", type="integer")
     */
    private $viewCountHomepage = 0;

    /**
     * @ORM\Column(name="viewCountDetails", type="integer")
     */
    private $viewCountDetails = 0;

    /**
     * @ORM\Column(name="viewCountAPI", type="integer")
     */
    private $viewCountAPI = 0;

    /**
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="createdAt", type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(name="isValidated", type="boolean")
     */
    private $isValidated = false;

    /**
     * @ORM\Column(name="publishedAt", type="datetime", nullable=true)
     * @Assert\DateTime
     */
    private $publishedAt;

    /**
     * @ORM\Column(name="endedAt", type="datetime", nullable=true)
     * @Assert\DateTime
     */
    private $endedAt;

    /**
     * @ORM\Column(name="status", type="string")
     */
    private $status = JobStatus::NEW_JOB;

    /**
     * @ORM\Column(name="statusUpdatedAt", type="datetime", nullable=true)
     */
    private $statusUpdatedAt;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity="Company")
     * @ORM\JoinColumn(name="company_id", referencedColumnName="id")
     * @Assert\Valid()
     */
    private $company;

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
     * @param Company $company
     *
     * @return Job
     */
    public function setCompany(Company $company)
    {
        $this->company = $company;

        return $this;
    }

    /**
     * Get company.
     *
     * @return Company
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
     * Set user.
     *
     * @param User $user
     *
     * @return Job
     */
    public function setUser(User $user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user.
     *
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @return bool
     */
    public function isValidated()
    {
        return $this->isValidated;
    }

    /**
     * Validates (or invalidates) the job.
     *
     * @param bool $validate
     *
     * @return Job
     */
    public function setIsValidated($validate = true)
    {
        $this->isValidated = $validate;

        return $this;
    }

    /**
     * @return bool
     */
    public function isPublished()
    {
        return $this->status === JobStatus::PUBLISHED;
    }

    /**
     * Set viewCountHomepage.
     *
     * @param int $viewCountHomepage
     *
     * @return Job
     */
    public function setViewCountHomepage($viewCountHomepage)
    {
        $this->viewCountHomepage = $viewCountHomepage;

        return $this;
    }

    /**
     * Get viewCountHomepage.
     *
     * @return int
     */
    public function getViewCountHomepage()
    {
        return $this->viewCountHomepage;
    }

    /**
     * Set viewCountDetails.
     *
     * @param int $viewCountDetails
     *
     * @return Job
     */
    public function setViewCountDetails($viewCountDetails)
    {
        $this->viewCountDetails = $viewCountDetails;

        return $this;
    }

    /**
     * Get viewCountDetails.
     *
     * @return int
     */
    public function getViewCountDetails()
    {
        return $this->viewCountDetails;
    }

    /**
     * Set viewCountAPI.
     *
     * @param int $viewCountAPI
     *
     * @return Job
     */
    public function setViewCountAPI($viewCountAPI)
    {
        $this->viewCountAPI = $viewCountAPI;

        return $this;
    }

    /**
     * Get viewCountAPI.
     *
     * @return int
     */
    public function getViewCountAPI()
    {
        return $this->viewCountAPI;
    }

    /**
     * Get viewCountAPI + viewCountDetails + viewCountHomepage.
     *
     * @return int
     */
    public function getTotalViewCount()
    {
        return $this->getViewCountHomepage() + $this->getViewCountDetails() + $this->getViewCountAPI();
    }

    /**
     * @return \Datetime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param \Datetime $createdAt
     *
     * @return Job
     */
    public function setCreatedAt(\DateTime $createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Set visibleFrom.
     *
     * @param \DateTime $publishedAt
     *
     * @return Job
     */
    public function setPublishedAt($publishedAt)
    {
        $this->publishedAt = $publishedAt;

        return $this;
    }

    /**
     * Get visibleFrom.
     *
     * @return \DateTime
     */
    public function getPublishedAt()
    {
        return $this->publishedAt;
    }

    /**
     * Set visibleTo.
     *
     * @param \DateTime $endedAt
     *
     * @return Job
     */
    public function setEndedAt($endedAt)
    {
        $this->endedAt = $endedAt;

        return $this;
    }

    /**
     * Get visibleTo.
     *
     * @return \DateTime
     */
    public function getEndedAt()
    {
        return $this->endedAt;
    }

    /**
     * @return JobStatus
     */
    public function getStatus()
    {
        return JobStatus::create($this->status);
    }

    /**
     * @param JobStatus $status
     *
     * @return Job
     */
    public function setStatus(JobStatus $status)
    {
        $this->status = $status->getValue();
        $this->statusUpdatedAt = new \DateTime();

        return $this;
    }

    /**
     * @param \Datetime $statusUpdatedAt
     *
     * @return Job
     */
    public function setStatusUpdatedAt(\Datetime $statusUpdatedAt)
    {
        $this->statusUpdatedAt = $statusUpdatedAt;

        return $this;
    }

    /**
     * @return \Datetime
     */
    public function getStatusUpdatedAt()
    {
        return $this->statusUpdatedAt;
    }

    public function getFeedItemTitle()
    {
        return $this->getTitle();
    }

    public function getFeedItemDescription()
    {
        return $this->getDescription();
    }

    public function getFeedItemRouteName()
    {
        return 'job_show';
    }

    public function getFeedItemRouteParameters()
    {
        return [
            'country' => $this->getCompany()->getCountry(),
            'contract' => $this->getContractType(),
            'slug' => $this->getSlug(),
        ];
    }

    public function getFeedItemUrlAnchor()
    {
        return '';
    }

    public function getFeedItemPubDate()
    {
        return $this->publishedAt;
    }
}
