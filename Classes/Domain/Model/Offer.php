<?php

namespace Blueways\BwGuild\Domain\Model;

abstract class Offer extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity
{

    /**
     * @var string
     * @validate NotEmpty
     */
    protected $title;

    /**
     * @var string
     */
    protected $address;

    /**
     * @var string
     */
    protected $zip;

    /**
     * @var
     */
    protected $country;

    /**
     * @var string
     */
    protected $description;

    /**
     * @var \DateTime
     * @validate DateTime
     */
    protected $start_date;

    /**
     * @var \Blueways\BwGuild\Domain\Model\User
     */
    protected $feUser;

    /**
     * @var string
     */
    protected $contactPerson;

    /**
     * @var string
     * @validate EmailAddress
     */
    protected $contactMail;

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
    protected $conditions;

    /**
     * @var string
     */
    protected $possibilities;

    /**
     * @return int
     */
    abstract public function getRecordType();

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
     * @return \DateTime
     */
    public function getStartDate(): \DateTime
    {
        return $this->start_date;
    }

    /**
     * @param \DateTime $start_date
     */
    public function setStartDate(\DateTime $start_date): void
    {
        $this->start_date = $start_date;
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
