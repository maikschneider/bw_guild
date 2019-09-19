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
    protected $feUser = null;

    /**
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\Blueways\BwGuild\Domain\Model\User>
     * @lazy
     */
    protected $feUsers = null;

    /**
     * @return string
     */
    public function getSlug(): string
    {
        return $this->slug;
    }

    /**
     * @param string $slug
     */
    public function setSlug(string $slug): void
    {
        $this->slug = $slug;
    }

    /**
     * @var string
     */
    protected $slug;

    /**
     * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage|null
     */
    public function getFeUsers()
    {
        return $this->feUsers;
    }

    /**
     * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage $feUsers
     */
    public function setFeUsers(\TYPO3\CMS\Extbase\Persistence\ObjectStorage $feUsers)
    {
        $this->feUsers = $feUsers;
    }

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
    public function getContactPhone()
    {
        return $this->contactPhone;
    }

    /**
     * @param string $contactPhone
     */
    public function setContactPhone(string $contactPhone)
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
    public function getLatitude()
    {
        return $this->latitude;
    }

    /**
     * @param float $latitude
     */
    public function setLatitude(float $latitude)
    {
        $this->latitude = $latitude;
    }

    /**
     * @return float
     */
    public function getLongitude()
    {
        return $this->longitude;
    }

    /**
     * @param float $longitude
     */
    public function setLongitude(float $longitude)
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
    public function getCategories()
    {
        return $this->categories;
    }

    /**
     * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage $categories
     */
    public function setCategories(\TYPO3\CMS\Extbase\Persistence\ObjectStorage $categories)
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
    public function getCity()
    {
        return $this->city;
    }

    /**
     * @param string $city
     */
    public function setCity(string $city)
    {
        $this->city = $city;
    }

    /**
     * @return int
     */
    public function getRecordType()
    {
        return $this->recordType;
    }

    /**
     * @param int $recordType
     */
    public function setRecordType(int $recordType)
    {
        $this->recordType = $recordType;
    }

    /**
     * @return bool
     */
    public function isHidden()
    {
        return $this->hidden;
    }

    /**
     * @param bool $hidden
     */
    public function setHidden(bool $hidden)
    {
        $this->hidden = $hidden;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle(string $title)
    {
        $this->title = $title;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription(string $description)
    {
        $this->description = $description;
    }

    /**
     * @return string
     */
    public function getStartDate()
    {
        return $this->startDate;
    }

    /**
     * @param string $startDate
     */
    public function setStartDate(string $startDate)
    {
        $this->startDate = $startDate;
    }

    /**
     * @return \Blueways\BwGuild\Domain\Model\User|null
     */
    public function getFeUser()
    {
        return $this->feUser;
    }

    /**
     * @param \Blueways\BwGuild\Domain\Model\User $feUser
     */
    public function setFeUser(\Blueways\BwGuild\Domain\Model\User $feUser)
    {
        $this->feUser = $feUser;
    }

    /**
     * @return string
     */
    public function getContactPerson()
    {
        return $this->contactPerson;
    }

    /**
     * @param string $contactPerson
     */
    public function setContactPerson(string $contactPerson)
    {
        $this->contactPerson = $contactPerson;
    }

    /**
     * @return string
     */
    public function getContactMail()
    {
        return $this->contactMail;
    }

    /**
     * @param string $contactMail
     */
    public function setContactMail(string $contactMail)
    {
        $this->contactMail = $contactMail;
    }

    /**
     * @return float
     */
    public function getGeoLat()
    {
        return $this->geo_lat;
    }

    /**
     * @param float $geo_lat
     */
    public function setGeoLat(float $geo_lat)
    {
        $this->geo_lat = $geo_lat;
    }

    /**
     * @return float
     */
    public function getGeoLong()
    {
        return $this->geo_long;
    }

    /**
     * @param float $geo_long
     */
    public function setGeoLong(float $geo_long)
    {
        $this->geo_long = $geo_long;
    }

    /**
     * @return string
     */
    public function getConditions()
    {
        return $this->conditions;
    }

    /**
     * @param string $conditions
     */
    public function setConditions(string $conditions)
    {
        $this->conditions = $conditions;
    }

    /**
     * @return string
     */
    public function getPossibilities()
    {
        return $this->possibilities;
    }

    /**
     * @param string $possibilities
     */
    public function setPossibilities(string $possibilities)
    {
        $this->possibilities = $possibilities;
    }

    /**
     * @return string
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * @param string $address
     */
    public function setAddress(string $address)
    {
        $this->address = $address;
    }

    /**
     * @return string
     */
    public function getZip()
    {
        return $this->zip;
    }

    /**
     * @param string $zip
     */
    public function setZip(string $zip)
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
    public function setCountry($country)
    {
        $this->country = $country;
    }
}
