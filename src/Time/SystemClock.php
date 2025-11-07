<?php declare(strict_types=1);

namespace App\Time;

use DateTimeImmutable;
use DateTimeZone;

class SystemClock implements Clock {
    public function now(): DateTimeImmutable {
        return new DateTimeImmutable("now", new DateTimeZone("UTC"));
    }
}
