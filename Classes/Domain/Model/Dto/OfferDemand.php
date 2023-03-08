<?php

namespace Blueways\BwGuild\Domain\Model\Dto;

/**
 * Class OfferDemand
 */
class OfferDemand extends BaseDemand
{
    public const TABLE = 'tx_bwguild_domain_model_offer';

    public function __construct(
        ?array $categories = null,
        ?string $category = null,
        ?string $categoryConjunction = null
    ) {
        $this->categories = $categories ?? [];
        $this->category = $category ?? '';
        $this->categoryConjunction = $categoryConjunction ?? '';
    }
}
