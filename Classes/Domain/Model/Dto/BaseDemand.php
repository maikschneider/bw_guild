<?php

namespace Blueways\BwGuild\Domain\Model\Dto;

class BaseDemand
{
    /**
     * @var array
     */
    protected $categories;

    /**
     * @var string
     */
    protected $categoryConjunction;

    /**
     * @var \Blueways\BwGuild\Domain\Model\Dto\Search
     */
    protected $search;

    /**
     * @var bool
     */
    protected $includeSubCategories = false;

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
        return $this->categories;
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
     * @return \Blueways\BwGuild\Domain\Model\Dto\Search
     */
    public function getSearch(): \Blueways\BwGuild\Domain\Model\Dto\Search
    {
        return $this->search;
    }

    /**
     * @param \Blueways\BwGuild\Domain\Model\Dto\Search $search
     */
    public function setSearch(\Blueways\BwGuild\Domain\Model\Dto\Search $search): void
    {
        $this->search = $search;
    }

}
