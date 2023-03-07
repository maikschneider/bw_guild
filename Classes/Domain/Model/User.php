<?php

namespace Blueways\BwGuild\Domain\Model;

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
    protected string $shortName = '';

    protected string $passwordRepeat = '';

    protected string $mobile = '';

    protected string $memberNr = '';

    /**
     * @var ObjectStorage<Offer>|null
     */
    protected ?ObjectStorage $offers = null;

    /**
     * @var ObjectStorage<AbstractUserFeature>|null
     */
    protected ?ObjectStorage $features = null;

    /**
     * @var ObjectStorage<Category>|null
     */
    protected ?ObjectStorage $categories = null;

    protected float $latitude = 0.0;

    protected float $longitude = 0.0;

    /**
     * @var ObjectStorage<Offer>|null
     */
    protected ?ObjectStorage $sharedOffers = null;

    protected string $sortingText = '';

    protected string $sortingField = '';

    protected bool $publicProfile = true;

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

    public function isPublicProfile(): bool
    {
        return $this->publicProfile;
    }

    public function setPublicProfile(bool $publicProfile): void
    {
        $this->publicProfile = $publicProfile;
    }

    public function getSortingText(): string
    {
        return $this->sortingText;
    }

    public function setSortingText(string $sortingText)
    {
        $this->sortingText = $sortingText;
    }

    public function getSortingField(): string
    {
        return $this->sortingField;
    }

    public function setSortingField(string $sortingField): void
    {
        $this->sortingField = $sortingField;
    }

    public function getPasswordRepeat(): string
    {
        return $this->passwordRepeat;
    }

    public function setPasswordRepeat(string $passwordRepeat): void
    {
        $this->passwordRepeat = $passwordRepeat;
    }

    public function getLatitude(): float
    {
        return $this->latitude;
    }

    public function setLatitude(float $latitude): void
    {
        $this->latitude = $latitude;
    }

    public function getSharedOffers(): ?ObjectStorage
    {
        return $this->sharedOffers;
    }

    public function setSharedOffers(ObjectStorage $sharedOffers): void
    {
        $this->sharedOffers = $sharedOffers;
    }

    public function getAllOffers(): ?ObjectStorage
    {
        $offers = $this->offers;
        if ($this->sharedOffers) {
            $offers->addAll($this->sharedOffers);
        }

        return $offers;
    }

    public function getLongitude(): float
    {
        return $this->longitude;
    }

    public function setLongitude(float $longitude): void
    {
        $this->longitude = $longitude;
    }

    public function getCategories(): ObjectStorage
    {
        return $this->categories;
    }

    public function setCategories(ObjectStorage $categories): void
    {
        $this->categories = $categories;
    }

    public function getOffers(): ObjectStorage
    {
        return $this->offers;
    }

    public function setOffers(ObjectStorage $offers): void
    {
        $this->offers = $offers;
    }

    public function getShortName(): string
    {
        return $this->shortName;
    }

    public function setShortName(string $shortName): void
    {
        $this->shortName = $shortName;
    }

    public function getMobile(): string
    {
        return $this->mobile;
    }

    public function setMobile(string $mobile): void
    {
        $this->mobile = $mobile;
    }

    public function getMemberNr(): string
    {
        return $this->memberNr;
    }

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
