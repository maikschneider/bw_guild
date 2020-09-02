<?php

namespace Blueways\BwGuild\Domain\Model\Dto;

use Blueways\BwGuild\Service\GeoService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

class BaseDemand extends AbstractEntity
{

    public CONST TABLE = 'tx_bwguild_domain_model_offer';

    public CONST EXCLUDE_FIELDS = 'pid,lockToDomain,image,lastlogin,uid,_localizedUid,_languageUid,_versionedUid';

    /**
     * @var array
     */
    protected $categories;

    /**
     * @var string
     */
    protected $categoryConjunction = '';

    /**
     * @var string
     */
    protected $search = '';

    /**
     * @var string
     */
    protected $excludeSearchFields = '';

    /**
     * @var bool
     */
    protected $includeSubCategories = false;

    /**
     * @var string
     */
    protected $order;

    /**
     * @var string
     */
    protected $orderDirection = '';

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
     * @var int
     */
    protected $maxDistance = 10;

    /**
     * @var string
     */
    protected $searchDistanceAddress = '';

    /**
     * @var float
     */
    protected $latitude;

    /**
     * @var float
     */
    protected $longitude;

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
    public function getOrder()
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
    protected $limit;

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
    public function overrideDemand($demand)
    {
        // abort if no valid demand
        if(!$demand || !is_array($demand)) {
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
    public function geoCodeSearchString()
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
    public function getSearchParts()
    {
        return GeneralUtility::trimExplode(' ', $this->search, true);
    }

}
