<?php

namespace Blueways\BwGuild\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Annotation\Validate;
use TYPO3\CMS\Extbase\Annotation\ORM\Lazy;
use TYPO3\CMS\Extbase\Domain\Model\Category;
use Blueways\BwGuild\Utility\SlugUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

class Offer extends AbstractEntity
{
    /**
     * @Validate("NotEmpty")
     */
    protected string $title = '';

    protected string $address = '';

    protected string $zip = '';

    protected string $city = '';

    protected string $country = '';

    protected string $description = '';

    protected string $startDate = '';

    protected ?User $feUser = null;

    /**
     * @var ObjectStorage<User>|null
     * @Lazy
     */
    protected ?ObjectStorage $feUsers = null;

    protected string $slug = '';

    protected string $contactPerson = '';

    /**
     * @Validate("EmailAddress")
     */
    protected string $contactMail = '';

    protected string $contactPhone = '';

    protected string $conditions = '';

    protected string $possibilities = '';

    protected int $recordType = 0;

    protected bool $hidden = false;

    protected float $latitude = 0.0;

    protected float $longitude = 0.0;

    protected ?\DateTime $crdate = null;

    protected bool $public = true;

    protected float $price = 0.0;

    public function getPrice(): float
    {
        return $this->price;
    }

    public function setPrice(float $price): void
    {
        $this->price = $price;
    }

    public function isPublic(): bool
    {
        return $this->public;
    }

    /**
     * @var ObjectStorage<Category>|null
     * @Lazy
     */
    protected ?ObjectStorage $categories = null;

    /**
     * @var ObjectStorage<FileReference>|null
     */
    protected ?ObjectStorage $images = null;

    public function __construct()
    {
        $this->feUsers = new ObjectStorage();
        $this->categories = new ObjectStorage();
        $this->images = new ObjectStorage();
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): void
    {
        $this->slug = $slug;
    }

    /**
     * @return ObjectStorage<User>|null
     */
    public function getFeUsers(): ?ObjectStorage
    {
        return $this->feUsers;
    }

    /**
     * @param ObjectStorage<User> $feUsers
     */
    public function setFeUsers(ObjectStorage $feUsers)
    {
        $this->feUsers = $feUsers;
    }

    public function getContactPhone(): string
    {
        return $this->contactPhone;
    }

    public function setContactPhone(string $contactPhone)
    {
        $this->contactPhone = $contactPhone;
    }

    public function getLatitude(): float
    {
        return $this->latitude;
    }

    public function setLatitude(float $latitude)
    {
        $this->latitude = $latitude;
    }

    public function getLongitude(): float
    {
        return $this->longitude;
    }

    public function setLongitude(float $longitude)
    {
        $this->longitude = $longitude;
    }

    /**
     * @return ObjectStorage<Category>|null
     */
    public function getCategories(): ?ObjectStorage
    {
        return $this->categories;
    }

    /**
     * @param ObjectStorage<Category> $categories
     */
    public function setCategories(ObjectStorage $categories)
    {
        $this->categories = $categories;
    }

    public function getRecordType(): int
    {
        return $this->recordType;
    }

    public function setRecordType(int $recordType)
    {
        $this->recordType = $recordType;
    }

    public function isHidden(): bool
    {
        return $this->hidden;
    }

    public function setHidden(bool $hidden)
    {
        $this->hidden = $hidden;
    }

    public function getStartDate(): string
    {
        return $this->startDate;
    }

    public function setStartDate(string $startDate)
    {
        $this->startDate = $startDate;
    }

    public function getContactPerson(): string
    {
        return $this->contactPerson;
    }

    public function setContactPerson(string $contactPerson)
    {
        $this->contactPerson = $contactPerson;
    }

    public function getContactMail(): string
    {
        return $this->contactMail;
    }

    public function setContactMail(string $contactMail)
    {
        $this->contactMail = $contactMail;
    }

    public function getConditions(): string
    {
        return $this->conditions;
    }

    public function setConditions(string $conditions)
    {
        $this->conditions = $conditions;
    }

    public function getPossibilities(): string
    {
        return $this->possibilities;
    }

    public function setPossibilities(string $possibilities)
    {
        $this->possibilities = $possibilities;
    }

    public function getJsonSchema($settings): array
    {
        $name = '';
        $url = '';
        $logo = '';

        // defaults from typoscript
        if ($settings['schema']['hiringOrganization']['name']) {
            $name = $settings['schema']['hiringOrganization']['name'];
        }
        if ($settings['schema']['hiringOrganization']['url']) {
            $url = $settings['schema']['hiringOrganization']['url'];
        }
        if ($settings['schema']['hiringOrganization']['logo']) {
            $logo = $settings['schema']['hiringOrganization']['logo'];
        }

        // override from feUser
        if ($this->getFeUser()) {
            $name = $this->getFeUser()->getCompany();
            $url = $this->getFeUser()->getWww();
            $logo = $this->getFeUser()->getLogo() ? '/' . $this->getFeUser()->getLogo()->getOriginalResource()->getPublicUrl() : $logo;
        }

        $schema = [
            '@context' => 'http://schema.org/',
            '@type' => 'JobPosting',
            'title' => $this->getTitle(),
            'description' => strip_tags($this->getDescription()),
            'hiringOrganization' => [
                'name' => $name,
                '@type' => 'Organization',
                'sameAs' => $url,
                'logo' => $logo,
            ],
            'employmentType' => 'FULL_TIME',
            'datePosted' => $this->getCrdate()->format('Y-m-d'),
            'jobLocation' => [
                '@type' => 'Place',
                'address' => [
                    'streetAddress' => $this->getAddress(),
                    'addressLocality' => $this->getCity(),
                    'postalCode' => $this->getZip(),
                    'addressCountry' => $this->getCountry(),
                ],
            ],
        ];

        // overrides from fe_user
        if ($this->feUser) {
            if (!$this->getAddress() && $this->feUser->getAddress()) {
                $schema['jobLocation']['address']['streetAddress'] = $this->feUser->getAddress();
            }
            if (!$this->getCity() && $this->feUser->getCity()) {
                $schema['jobLocation']['address']['addressLocality'] = $this->feUser->getCity();
            }
            if (!$this->getZip() && $this->feUser->getZip()) {
                $schema['jobLocation']['address']['postalCode'] = $this->feUser->getZip();
            }
            if (!$this->getCountry() && $this->feUser->getCountry()) {
                $schema['jobLocation']['address']['addressCountry'] = $this->feUser->getCountry();
            }
        }

        return $schema;
    }

    public function getImages(): ?ObjectStorage
    {
        return $this->images;
    }

    public function setImages(?ObjectStorage $images): void
    {
        $this->images = $images;
    }

    public function getFeUser(): ?User
    {
        return $this->feUser;
    }

    /**
     * @param User $feUser
     */
    public function setFeUser(User $feUser)
    {
        $this->feUser = $feUser;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title)
    {
        $this->title = $title;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description)
    {
        $this->description = $description;
    }

    public function getCrdate(): \DateTime
    {
        return $this->crdate;
    }

    /**
     * @param \DateTime $crdate
     */
    public function setCrdate(\DateTime $crdate)
    {
        $this->crdate = $crdate;
    }

    public function getAddress(): string
    {
        return $this->address;
    }

    public function setAddress(string $address)
    {
        $this->address = $address;
    }

    public function getCity(): string
    {
        return $this->city;
    }

    public function setCity(string $city)
    {
        $this->city = $city;
    }

    public function getZip(): string
    {
        return $this->zip;
    }

    public function setZip(string $zip)
    {
        $this->zip = $zip;
    }

    public function getCountry(): string
    {
        return $this->country;
    }

    public function setCountry(string $country)
    {
        $this->country = $country;
    }

    public function updateSlug(): void
    {
        $slugUtility = GeneralUtility::makeInstance(SlugUtility::class);
        $slug = $slugUtility->getSlug($this);
        $this->setSlug($slug);
    }
}
