<?php

namespace Blueways\BwGuild\Event;

use Blueways\BwGuild\Domain\Model\Dto\BaseDemand;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;

final class ModifyQueryBuilderEvent
{
    private QueryBuilder $queryBuilder;

    private BaseDemand $demand;

    public function __construct(QueryBuilder $queryBuilder, BaseDemand $demand)
    {
        $this->queryBuilder = $queryBuilder;
        $this->demand = $demand;
    }

    public function getQueryBuilder(): QueryBuilder
    {
        return $this->queryBuilder;
    }

    public function getDemand(): BaseDemand
    {
        return $this->demand;
    }
}
