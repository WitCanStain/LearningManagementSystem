<?php declare(strict_types=1);

namespace App;

use App\Time\Clock;
use DateTimeImmutable;

abstract class TimedContent {
    protected ?DateTimeImmutable $startDate;
    protected ?DateTimeImmutable $endDate;
    protected Clock $clock;

    function __construct(?DateTimeImmutable $startDate, ?DateTimeImmutable $endDate, Clock $clock) {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->clock = $clock;
    }

    protected function isActive(): bool {
        $now = $this->clock->now();
        if (($this->startDate && $now < $this->startDate) || ($this->endDate && $now > $this->endDate)) {
            return false;
        }
        return true;
    }

    public function setEndDate(DateTimeImmutable $endDate): void {
        $this->endDate = $endDate;
    }
}