<?php

namespace RinProject\FastCrudBundle\Utils;

class DateTime
{
    public function newInstance($time = 'now', ?\DateTimeZone $timezone = null)
    {
        return new \DateTime($time, $timezone);
    }
}
