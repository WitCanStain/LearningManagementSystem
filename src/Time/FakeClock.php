<?php declare(strict_types=1);

namespace App\Time;

use DateTimeImmutable;

class FakeClock implements Clock {
    private DateTimeImmutable $currentTime;

    public function __construct(DateTimeImmutable $currentTime) {
        $this->currentTime = $currentTime;
    }

    public function now(): DateTimeImmutable {
        return $this->currentTime;
    }

    public function setTime(DateTimeImmutable $newTime): void {
        $this->currentTime = $newTime;
    }
}
