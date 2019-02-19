<?php

namespace Blueways\BwGuild\Domain\Model;

/**
 * Class Internship
 *
 * @package Blueways\Domain\Mdodel
 */
class Internship extends Offer
{

    /**
     * @return int
     */
    public function getRecordType()
    {
        return 2;
    }
}
