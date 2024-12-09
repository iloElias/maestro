<?php

namespace Ilias\Maestro\Types;

class Time extends \DateTime
{
    public function __construct(string $Timestamp = 'now', \DateTimeZone|null $timezone = null)
    {
        parent::__construct($Timestamp, $timezone);
    }

    public function __toString()
    {
        return $this->format('Y-m-d');
    }
}
