<?php

namespace Blueways\BwGuild\Domain\Model;

/**
 * Class Job
 *
 * @package Blueways\BwGuild\Domain\Model
 */
class Job extends Offer
{

    /**
     * @return int
     */
    public function getRecordType()
    {
        return 0;
    }
}