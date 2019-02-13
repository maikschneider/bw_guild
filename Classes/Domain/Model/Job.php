<?php

namespace Blueways\BwGuild\Domain\Model;

class Job
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
     * @validate NotEmpty, DateTime
     */
    protected $start_date;

    /**
     * @var \Blueways\BwGuild\Domain\Model\User
     */
    protected $feUser;

    /**
     * @var float
     */
    protected $geo_lat;

    /**
     * @var float
     */
    protected $geo_long;

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
