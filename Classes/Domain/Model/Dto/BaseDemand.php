<?php

namespace Blueways\BwGuild\Domain\Model\Dto;

use Blueways\BwGuild\Service\GeoService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

class BaseDemand extends AbstractEntity
{

    public const TABLE = 'tx_bwguild_domain_model_offer';

    public const EXCLUDE_FIELDS = 'pid,lockToDomain,image,lastlogin,uid,_localizedUid,_languageUid,_versionedUid';

    protected array $categories = [];

    protected string $categoryConjunction = '';

    protected string $search = '';

    protected string $excludeSearchFields = '';

    protected bool $includeSubCategories = false;

    protected string $order = '';

    protected string $orderDirection = '';

    protected int $itemsPerPage = 0;

    protected int $maxDistance = 10;

    protected string $searchDistanceAddress = '';

    protected float $latitude = 0.0;

    protected float $longitude = 0.0;

    /**
     * @return int
     */
    public function getItemsPerPage(): int
    {
        return $this->itemsPerPage;
    }

    /**
     * @param int $itemsPerPage
     */
    public function setItemsPerPage(int $itemsPerPage): void
    {
        $this->itemsPerPage = $itemsPerPage;
    }

    /**
     * @return string
     */
    public function getOrderDirection(): string
    {
        return $this->orderDirection;
    }

    /**
     * @param string $orderDirection
     */
    public function setOrderDirection(string $orderDirection): void
    {
        $this->orderDirection = $orderDirection;
    }

    /**
     * @return int
     */
    public function getMaxDistance(): int
    {
        return $this->maxDistance;
    }

    /**
     * @param int $maxDistance
     */
    public function setMaxDistance(int $maxDistance): void
    {
        $this->maxDistance = $maxDistance;
    }

    /**
     * @return string
     */
    public function getSearchDistanceAddress(): string
    {
        return $this->searchDistanceAddress;
    }

    /**
     * @param string $searchDistanceAddress
     */
    public function setSearchDistanceAddress(string $searchDistanceAddress): void
    {
        $this->searchDistanceAddress = $searchDistanceAddress;
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
     * @return string
     */
    public function getOrder(): string
    {
        return $this->order;
    }

    /**
     * @param string $order
     */
    public function setOrder(string $order): void
    {
        $this->order = $order;
    }

    /**
     * @return int
     */
    public function getLimit(): int
    {
        return $this->limit;
    }

    /**
     * @param int $limit
     */
    public function setLimit(int $limit): void
    {
        $this->limit = $limit;
    }

    /**
     * @var int
     */
    protected $limit = -1;

    /**
     * @return string
     */
    public function getExcludeSearchFields(): string
    {
        return $this->excludeSearchFields;
    }

    /**
     * @param string $excludeSearchFields
     */
    public function setExcludeSearchFields(string $excludeSearchFields): void
    {
        $this->excludeSearchFields = $excludeSearchFields;
    }

    /**
     * @return bool
     */
    public function isIncludeSubCategories(): bool
    {
        return $this->includeSubCategories;
    }

    /**
     * @param bool $includeSubCategories
     */
    public function setIncludeSubCategories(bool $includeSubCategories): void
    {
        $this->includeSubCategories = $includeSubCategories;
    }

    /**
     * @return array
     */
    public function getCategories(): array
    {
        return array_filter($this->categories);
    }

    /**
     * @param array $categories
     */
    public function setCategories(array $categories): void
    {
        $this->categories = $categories;
    }

    /**
     * @return string
     */
    public function getCategoryConjunction(): string
    {
        return $this->categoryConjunction;
    }

    /**
     * @param string $categoryConjunction
     */
    public function setCategoryConjunction(string $categoryConjunction): void
    {
        $this->categoryConjunction = $categoryConjunction;
    }

    /**
     * @return string
     */
    public function getSearch(): string
    {
        return $this->search;
    }

    /**
     * @param string $search
     */
    public function setSearch(string $search): void
    {
        $this->search = $search;
    }

    /**
     * @param \Blueways\BwGuild\Domain\Model\Dto\BaseDemand
     */
    public function overrideDemand($demand): void
    {
        // abort if no valid demand
        if (!$demand || !is_array($demand)) {
            return;
        }

        // override properties
        foreach ($demand as $key => $value) {
            $this->_setProperty($key, $value);
        }
    }

    /**
     * @return bool
     */
    public function geoCodeSearchString(): bool
    {
        $geocodingService = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(GeoService::class);
        $coords = $geocodingService->getCoordinatesForAddress($this->searchDistanceAddress);

        if (!count($coords)) {
            return false;
        }

        $this->latitude = $coords['latitude'];
        $this->longitude = $coords['longitude'];

        return true;
    }

    /**
     * @return array
     */
    public function getSearchParts(): array
    {
        return GeneralUtility::trimExplode(' ', $this->search, true);
    }

}
