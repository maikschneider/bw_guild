<?php

namespace Blueways\BwGuild\Domain\Model\Dto;

class UserDemand extends BaseDemand
{

    protected $searchFields = 'member_nr, title, short_name, mobile, name, first_name, last_name, address, telephone, fax, email, zip, city, www, company';
}
