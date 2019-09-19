<?php

namespace Blueways\BwGuild\Domain\Model;

use TYPO3\CMS\Extbase\Domain\Model\FrontendUser;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

/**
 * Class User
 *
 * @package Blueways\BwGuild\Domain\Model
 */
class User extends FrontendUser
{

    /**
     * @var string
     */
    protected $shortName = '';

    /**
     * @var string
     * @TYPO3\CMS\Extbase\Annotation\Validate("StringLength", options={"minimum": 3, "maximum": 50})
     */
    protected $password = '';

    /**
     * @var string
     * @validate
     */
    protected $passwordRepeat = '';

    /**
     * @var string
     */
    protected $mobile = '';

    /**
     * @var string
     */
    protected $memberNr = '';

    /**
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\Blueways\BwGuild\Domain\Model\Offer>
     * @lazy
     */
    protected $offers;

    /**
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\TYPO3\CMS\Extbase\Domain\Model\Category>
     * @lazy
     */
    protected $categories;

    /**
     * @var float
     */
    protected $latitude;

    /**
     * @var float
     */
    protected $longitude;

    /**
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\Blueways\BwGuild\Domain\Model\Offer>
     * @lazy
     */
    protected $sharedOffers;

    /**
     * @var string
     */
    protected $sortingText;

    /**
     * @var string
     */
    protected $sortingField;

    /**
     * @return string
     */
    public function getSortingText()
    {
        return $this->sortingText;
    }

    /**
     * @param string $sortingText
     */
    public function setSortingText(string $sortingText)
    {
        $this->sortingText = $sortingText;
    }

    /**
     * @return string
     */
    public function getSortingField()
    {
        return $this->sortingField;
    }

    /**
     * @param string $sortingField
     */
    public function setSortingField(string $sortingField)
    {
        $this->sortingField = $sortingField;
    }

    public function __construct(string $username = '', string $password = '')
    {
        parent::__construct($username, $password);

        $this->categories = new ObjectStorage();
        $this->offers = new ObjectStorage();
        $this->sharedOffers = new ObjectStorage();
        $this->sortingField = 'company';
    }

    /**
     * @return string
     */
    public function getPasswordRepeat(): string
    {
        return $this->passwordRepeat;
    }

    /**
     * @param string $passwordRepeat
     */
    public function setPasswordRepeat(string $passwordRepeat): void
    {
        $this->passwordRepeat = $passwordRepeat;
    }

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
     * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage|null
     */
    public function getSharedOffers()
    {
        return $this->sharedOffers;
    }

    /**
     * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage $sharedOffers
     */
    public function setSharedOffers(\TYPO3\CMS\Extbase\Persistence\ObjectStorage $sharedOffers): void
    {
        $this->sharedOffers = $sharedOffers;
    }

    /**
     * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage|null
     */
    public function getAllOffers()
    {
        if ($this->offers && $this->sharedOffers) {
            return $this->offers->addAll($this->sharedOffers);
        }
        if ($this->offers) {
            return $this->offers;
        }
        if ($this->sharedOffers) {
            return $this->sharedOffers;
        }
        return null;
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
     * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage
     */
    public function getOffers(): \TYPO3\CMS\Extbase\Persistence\ObjectStorage
    {
        return $this->offers;
    }

    /**
     * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage $offers
     */
    public function setOffers(\TYPO3\CMS\Extbase\Persistence\ObjectStorage $offers): void
    {
        $this->offers = $offers;
    }

    /**
     * @return string
     */
    public function getShortName(): string
    {
        return $this->shortName;
    }

    /**
     * @param string $shortName
     */
    public function setShortName(string $shortName): void
    {
        $this->shortName = $shortName;
    }

    /**
     * @return string
     */
    public function getMobile(): string
    {
        return $this->mobile;
    }

    /**
     * @param string $mobile
     */
    public function setMobile(string $mobile): void
    {
        $this->mobile = $mobile;
    }

    /**
     * @return string
     */
    public function getMemberNr(): string
    {
        return $this->memberNr;
    }

    /**
     * @param string $memberNr
     */
    public function setMemberNr(string $memberNr): void
    {
        $this->memberNr = $memberNr;
    }
}
