<?php

namespace Blueways\BwGuild\Domain\Model;

/**
 * Class Education
 *
 * @package Blueways\Domain\Model
 */
class Education extends Offer
{

    /**
     * @return int
     */
    public function getRecordType()
    {
        return 1;
    }
}
