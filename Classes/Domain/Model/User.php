<?php

namespace Blueways\BwGuild\Domain\Model;

use TYPO3\CMS\Extbase\Annotation\ORM\Lazy;
use TYPO3\CMS\Extbase\Domain\Model\Category;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use Blueways\BwGuild\Service\GeoService;
use TYPO3\CMS\Extbase\Domain\Model\FrontendUser;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

/**
 * Class User
 */
class User extends FrontendUser
{
    /**
     * @var string
     */
    protected $shortName = '';

    /**
     * @var string
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
     * @var ObjectStorage<Offer>
     * @Lazy
     */
    protected $offers;

    /**
     * @var ObjectStorage<AbstractUserFeature>
     * @Lazy
     */
    protected $features;

    /**
     * @var ObjectStorage<Category>
     * @Lazy
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
     * @var ObjectStorage<Offer>
     * @Lazy
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
     * @var bool
     */
    protected $publicProfile;

    protected string $bookmarks = '';

    public function getBookmarks(): string
    {
        return $this->bookmarks;
    }

    protected string $slug = '';

    public function getSlug(): string
    {
        return $this->slug;
    }

    protected ?FileReference $logo = null;

    public function __construct(string $username = '', string $password = '')
    {
        parent::__construct($username, $password);

        $this->categories = new ObjectStorage();
        $this->offers = new ObjectStorage();
        $this->sharedOffers = new ObjectStorage();
        $this->sortingField = 'company';
        $this->features = new ObjectStorage();
    }

    public function getLogo(): ?FileReference
    {
        return $this->logo;
    }

    public function setLogo(?FileReference $logo): void
    {
        $this->logo = $logo;
    }

    /**
     * @return bool
     */
    public function isPublicProfile(): bool
    {
        return (bool)$this->publicProfile;
    }

    /**
     * @param bool $publicProfile
     */
    public function setPublicProfile(bool $publicProfile): void
    {
        $this->publicProfile = $publicProfile;
    }

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
     * @return ObjectStorage|null
     */
    public function getSharedOffers()
    {
        return $this->sharedOffers;
    }

    /**
     * @param ObjectStorage $sharedOffers
     */
    public function setSharedOffers(ObjectStorage $sharedOffers): void
    {
        $this->sharedOffers = $sharedOffers;
    }

    /**
     * @return ObjectStorage<Offer>
     */
    public function getAllOffers()
    {
        $offers = $this->offers;
        if ($this->sharedOffers) {
            $offers->addAll($this->sharedOffers);
        }

        return $offers;
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
     * @return ObjectStorage
     */
    public function getCategories(): ObjectStorage
    {
        return $this->categories;
    }

    /**
     * @param ObjectStorage $categories
     */
    public function setCategories(ObjectStorage $categories): void
    {
        $this->categories = $categories;
    }

    /**
     * @return ObjectStorage
     */
    public function getOffers(): ObjectStorage
    {
        return $this->offers;
    }

    /**
     * @param ObjectStorage $offers
     */
    public function setOffers(ObjectStorage $offers): void
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

    public function geoCodeAddress()
    {
        $geocodingService = GeneralUtility::makeInstance(GeoService::class);
        $coords = $geocodingService->getCoordinatesForAddress(
            $this->getAddress(),
            $this->getZip(),
            $this->getCity(),
            $this->getCountry()
        );

        if (count($coords)) {
            $this->latitude = $coords['latitude'];
            $this->longitude = $coords['longitude'];
        }
    }

    public function getJsonSchema($settings)
    {
        $image = $settings['schema.']['defaultImage'] ?: '';

        $schema = [
            '@context' => 'http://schema.org/',
            '@type' => 'LocalBusiness',
            'name' => $this->getCompany(),
            'description' => $this->getName(),
            'image' => $image,

            'address' => [
                '@type' => 'PostalAddress',
                'addressLocality' => $this->getCity(),
                'postalCode' => $this->getZip(),
                'streetAddress' => $this->getAddress(),
                'addressCountry' => $this->getCountry() === 'Deutschland' ? 'Germany' : '',
            ],
            'member' => [
                '@type' => 'Person',
                'familyName' => $this->getLastName(),
                'givenName' => $this->getFirstName(),
            ],
            'telephone' => $this->getTelephone(),
            'faxNumber' => $this->getFax(),
            'email' => $this->getEmail(),
            'url' => $this->getWww(),
        ];

        if ($this->getLogo()) {
            $schema['logo'] = $this->getLogo()->getOriginalResource()->getPublicUrl();
        }

        return $schema;
    }

    public function getFeatures(): ObjectStorage
    {
        return $this->features;
    }

    public function getFeaturesGroupedByRecordType(): array
    {
        $groupedFeatures = [];
        /** @var AbstractUserFeature $feature */
        foreach ($this->features as $feature) {
            $groupedFeatures[(int)$feature->getRecordType()] ??= new ObjectStorage();
            $groupedFeatures[(int)$feature->getRecordType()]->attach($feature);
        }
        return $groupedFeatures;
    }

    public function getFeaturesAsJsonGroupedByRecordType(): array
    {
        $groupedFeatures = $this->getFeaturesGroupedByRecordType();

        return array_map(function ($featureGroup) {
            $featureGroup = array_map(function ($feature) {
                return $feature->getApiOutputArray();
            }, [...$featureGroup]);

            return json_encode(array_values($featureGroup));
        }, $groupedFeatures);
    }

    public function setFeatures(ObjectStorage $features): void
    {
        $this->features = $features;
    }
}
