<?php

namespace Blueways\BwGuild\Domain\Model\Dto;

use Blueways\BwGuild\Service\GeoService;
use ReflectionClass;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Mvc\Request;

class BaseDemand extends AbstractEntity
{
    public const TABLE = 'tx_bwguild_domain_model_offer';

    public const EXCLUDE_FIELDS = 'pid,lockToDomain,image,lastlogin,uid,_localizedUid,_languageUid,_versionedUid';

    public const SEARCH_FIELDS = '';

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

    protected int $limit = -1;

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

    public function overrideFromRequest(Request $request): void
    {
        // abort if no valid demand array
        if (!$request->hasArgument('demand') || !$demand = $request->getArgument('demand')) {
            return;
        }

        $reflectionClass = new ReflectionClass($this);

        // override properties
        foreach ($demand as $key => $value) {
            if (!$reflectionClass->hasProperty($key)) {
                continue;
            }

            settype($value, gettype($this->$key));
            $this->$key = $value;
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
     * @param $settings
     * @return static
     */
    public static function createFromSettings($settings): static
    {
        $demand = new static();

        $demand->setCategories(GeneralUtility::trimExplode(',', $settings['categories'], true));
        $demand->setCategoryConjunction($settings['categoryConjunction'] ?? '');
        $demand->setIncludeSubCategories($settings['includeSubCategories'] ?? false);
        $demand->setOrder($settings['order'] ?? '');
        $demand->setOrderDirection($settings['orderDirection'] ?? '');
        $demand->setItemsPerPage((int)$settings['itemsPerPage']);

        if ($settings['limit']) {
            $demand->setLimit((int)$settings['limit']);
        }

        if ($settings['maxItems']) {
            $demand->setLimit((int)$settings['maxItems']);
        }

        return $demand;
    }

    /**
     * @return string[]
     */
    public function getSearchFields(): array
    {
        $fields = static::SEARCH_FIELDS ?: $GLOBALS['TCA'][static::TABLE]['ctrl']['searchFields'];
        return GeneralUtility::trimExplode(',', $fields, true);
    }
}
