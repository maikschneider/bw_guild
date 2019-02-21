<?php

namespace Blueways\BwGuild\Domain\Model\Dto;

class Search
{

    /**
     * @return string
     */
    public function getSubject(): string
    {
        return $this->subject;
    }

    /**
     * @param string $subject
     */
    public function setSubject(string $subject): void
    {
        $this->subject = $subject;
    }
    /**
     * Basic search word
     *
     * @var string
     */
    protected $subject;
}
