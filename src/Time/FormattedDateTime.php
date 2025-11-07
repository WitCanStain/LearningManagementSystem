<?php declare(strict_types=1);

namespace App\Time;
use DateTime;
use DateTimeZone;

class FormattedDateTime extends DateTime  {
    
    private const DATEFORMAT = "d/m/Y H:i:s.u";
    private const DEFAULT_TIMEZONE = "UTC";

    public static function getDayStartTimeFromDayMonthYearString(string $dayMonthYearString): DateTime {
        $startDate = DateTime::createFromFormat(self::DATEFORMAT, "{$dayMonthYearString} 00:00:00.000", new DateTimeZone(self::DEFAULT_TIMEZONE));
        return $startDate;
    }

    public static function getDayEndTimeFromDayMonthYearString(string $dayMonthYearString): DateTime {
        $endDate = DateTime::createFromFormat(self::DATEFORMAT, "{$dayMonthYearString} 23:59:59.999", new DateTimeZone(self::DEFAULT_TIMEZONE));
        return $endDate;
    }

    public static function getDateFromDateTimeString(string $dateTimeString): DateTime {
        $startDate = DateTime::createFromFormat(self::DATEFORMAT, $dateTimeString, new DateTimeZone(self::DEFAULT_TIMEZONE));
        return $startDate;
    }
    
}
