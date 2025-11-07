<?php declare(strict_types=1);

namespace App\Time;

use DateTimeImmutable;
use DateInterval;
use DateTimeZone;

class FakeClock implements Clock {
    private DateTimeImmutable $currentTime;

    public function __construct(string $timeString) {
        $this->currentTime = new DateTimeImmutable($timeString, new DateTimeZone("UTC"));
    }

    public function now(): DateTimeImmutable {
        return $this->currentTime;
    }

    public function setTime(DateTimeImmutable $newTime): void {
        $this->currentTime = $newTime;
    }
}
