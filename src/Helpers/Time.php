<?php

namespace Progressus\Zoovu\Helpers;

class Time
{
    private static function convertDateFromTimezone($date,$timezone,$timezone_to,$format) {

        $date = new \DateTime($date,new \DateTimeZone($timezone));
        $date->setTimezone( new \DateTimeZone($timezone_to) );
        return $date->format($format);
    }

    public static function convertUTCLocal( $utcTime ) {

        return self::convertDateFromTimezone($utcTime, 'UTC', wp_timezone_string(), 'Y-m-d H:i:s');
    }
}
