<?php

namespace Ilias\Maestro\Types;

class Timestamp extends \DateTime
{
    public function __construct(string $Timestamp = 'now', ?\DateTimeZone $timezone = null)
    {
        parent::__construct($Timestamp, $timezone);
    }

    public function __toString()
    {
        return $this->format('Y-m-d H:i:s');
    }
}
