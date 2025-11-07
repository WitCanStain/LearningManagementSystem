<?php declare(strict_types=1);

namespace App\Time;
use DateTimeImmutable;
use DateTimeZone;
use App\Time\TimeConfig;

class FormattedDateTime extends DateTimeImmutable  {
    
    public static function getDayStartTimeFromDayMonthYearString(string $dayMonthYearString): DateTimeImmutable {
        $startDate = DateTimeImmutable::createFromFormat(TimeConfig::DATEFORMAT, "{$dayMonthYearString} 00:00:00.000", new DateTimeZone(TimeConfig::DEFAULT_TIMEZONE));
        return $startDate;
    }

    public static function getDayEndTimeFromDayMonthYearString(string $dayMonthYearString): DateTimeImmutable {
        $endDate = DateTimeImmutable::createFromFormat(TimeConfig::DATEFORMAT, "{$dayMonthYearString} 23:59:59.999", new DateTimeZone(TimeConfig::DEFAULT_TIMEZONE));
        return $endDate;
    }

    public static function getDateFromDateTimeString(string $dateTimeString): DateTimeImmutable {
        $startDate = DateTimeImmutable::createFromFormat(TimeConfig::DATEFORMAT, $dateTimeString, new DateTimeZone(TimeConfig::DEFAULT_TIMEZONE));
        return $startDate;
    }
    
}
