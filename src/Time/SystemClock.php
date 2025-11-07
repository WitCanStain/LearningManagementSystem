<?php declare(strict_types=1);

namespace App\Time;

use DateTimeImmutable;
use DateTimeZone;
use App\Time\TimeConfig;

class SystemClock implements Clock {
    public function now(): DateTimeImmutable {
        return new DateTimeImmutable("now", new DateTimeZone(TimeConfig::DEFAULT_TIMEZONE));
    }
}
