<?php declare(strict_types=1);

use App\Time\Clock;
use DateTime;

class TimedContent {
    protected ?DateTime $startDate;
    protected ?DateTime $endDate;
    protected Clock $clock;

    function __construct(DateTime $startDate, DateTime $endDate, Clock $clock) {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->clock = $clock;
    }

    protected function isAccessible(): bool {
        $now = $this->clock->now();
        if (($this->startDate && $now < $this->startDate) || ($this->endDate && $now > $this->endDate)) {
            return false;
        }
        return true;
    }
}