<?php

namespace Blueways\BwGuild\Domain\Model;

use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

class Offer extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity
{

    /**
     * @var string
     * @validate NotEmpty
     */
    protected $title = '';

    /**
     * @var string
     */
    protected $address = '';

    /**
     * @var string
     */
    protected $zip = '';

    /**
     * @var string
     */
    protected $city = '';

    /**
     * @var string
     */
    protected $country = '';

    /**
     * @var string
     */
    protected $description = '';

    /**
     * @var string
     */
    protected $startDate = '';

    /**
     * @var \Blueways\BwGuild\Domain\Model\User
     */
    protected $feUser;

    /**
     * @var string
     */
    protected $contactPerson = '';

    /**
     * @var string
     * @validate EmailAddress
     */
    protected $contactMail = '';

    /**
     * @return string
     */
    public function getContactPhone(): string
    {
        return $this->contactPhone;
    }

    /**
     * @param string $contactPhone
     */
    public function setContactPhone(string $contactPhone): void
    {
        $this->contactPhone = $contactPhone;
    }

    /**
     * @var string 
     */
    protected $contactPhone = '';

    /**
     * @var float
     */
    protected $geo_lat;

    /**
     * @var float
     */
    protected $geo_long;

    /**
     * @var string
     */
    protected $conditions = '';

    /**
     * @var string
     */
    protected $possibilities = '';

    /**
     * @var integer
     */
    protected $recordType = 0;

    /**
     * @var boolean
     */
    protected $hidden;

    /**
     * @var float
     */
    protected $latitude;

    /**
     * @var float
     */
    protected $longitude;

    /**
     * @return float
     */
    public function getLatitude(): float
    {
        return $this->latitude;
    }

    /**
     * @param float $latitude
     */
    public function setLatitude(float $latitude): void
    {
        $this->latitude = $latitude;
    }

    /**
     * @return float
     */
    public function getLongitude(): float
    {
        return $this->longitude;
    }

    /**
     * @param float $longitude
     */
    public function setLongitude(float $longitude): void
    {
        $this->longitude = $longitude;
    }

    /**
     * Offer constructor.
     */
    public function __construct()
    {
        $this->categories = new ObjectStorage();
    }

    /**
     * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage
     */
    public function getCategories(): \TYPO3\CMS\Extbase\Persistence\ObjectStorage
    {
        return $this->categories;
    }

    /**
     * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage $categories
     */
    public function setCategories(\TYPO3\CMS\Extbase\Persistence\ObjectStorage $categories): void
    {
        $this->categories = $categories;
    }

    /**
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\TYPO3\CMS\Extbase\Domain\Model\Category>
     * @lazy
     */
    protected $categories;

    /**
     * @return string
     */
    public function getCity(): string
    {
        return $this->city;
    }

    /**
     * @param string $city
     */
    public function setCity(string $city): void
    {
        $this->city = $city;
    }

    /**
     * @return int
     */
    public function getRecordType(): int
    {
        return $this->recordType;
    }

    /**
     * @param int $recordType
     */
    public function setRecordType(int $recordType): void
    {
        $this->recordType = $recordType;
    }

    /**
     * @return bool
     */
    public function isHidden(): bool
    {
        return $this->hidden;
    }

    /**
     * @param bool $hidden
     */
    public function setHidden(bool $hidden): void
    {
        $this->hidden = $hidden;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    /**
     * @return string
     */
    public function getStartDate(): string
    {
        return $this->startDate;
    }

    /**
     * @param string $startDate
     */
    public function setStartDate(string $startDate): void
    {
        $this->startDate = $startDate;
    }

    /**
     * @return \Blueways\BwGuild\Domain\Model\User
     */
    public function getFeUser(): \Blueways\BwGuild\Domain\Model\User
    {
        return $this->feUser;
    }

    /**
     * @param \Blueways\BwGuild\Domain\Model\User $feUser
     */
    public function setFeUser(\Blueways\BwGuild\Domain\Model\User $feUser): void
    {
        $this->feUser = $feUser;
    }

    /**
     * @return string
     */
    public function getContactPerson(): string
    {
        return $this->contactPerson;
    }

    /**
     * @param string $contactPerson
     */
    public function setContactPerson(string $contactPerson): void
    {
        $this->contactPerson = $contactPerson;
    }

    /**
     * @return string
     */
    public function getContactMail(): string
    {
        return $this->contactMail;
    }

    /**
     * @param string $contactMail
     */
    public function setContactMail(string $contactMail): void
    {
        $this->contactMail = $contactMail;
    }

    /**
     * @return float
     */
    public function getGeoLat(): float
    {
        return $this->geo_lat;
    }

    /**
     * @param float $geo_lat
     */
    public function setGeoLat(float $geo_lat): void
    {
        $this->geo_lat = $geo_lat;
    }

    /**
     * @return float
     */
    public function getGeoLong(): float
    {
        return $this->geo_long;
    }

    /**
     * @param float $geo_long
     */
    public function setGeoLong(float $geo_long): void
    {
        $this->geo_long = $geo_long;
    }

    /**
     * @return string
     */
    public function getConditions(): string
    {
        return $this->conditions;
    }

    /**
     * @param string $conditions
     */
    public function setConditions(string $conditions): void
    {
        $this->conditions = $conditions;
    }

    /**
     * @return string
     */
    public function getPossibilities(): string
    {
        return $this->possibilities;
    }

    /**
     * @param string $possibilities
     */
    public function setPossibilities(string $possibilities): void
    {
        $this->possibilities = $possibilities;
    }

    /**
     * @return string
     */
    public function getAddress(): string
    {
        return $this->address;
    }

    /**
     * @param string $address
     */
    public function setAddress(string $address): void
    {
        $this->address = $address;
    }

    /**
     * @return string
     */
    public function getZip(): string
    {
        return $this->zip;
    }

    /**
     * @param string $zip
     */
    public function setZip(string $zip): void
    {
        $this->zip = $zip;
    }

    /**
     * @return mixed
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * @param mixed $country
     */
    public function setCountry($country): void
    {
        $this->country = $country;
    }
}
