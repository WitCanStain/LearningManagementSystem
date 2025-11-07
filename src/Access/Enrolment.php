<?php declare(strict_types=1);

namespace App\Access;

use App\TimedContent;
use App\Time\Clock;
use DateTimeImmutable;

class Enrolment extends TimedContent {
    public int $studentId;
    function __construct(int $studentId, DateTimeImmutable $startDate, DateTimeImmutable $endDate, Clock $clock) {
        parent::__construct($startDate, $endDate, $clock);
        $this->studentId = $studentId;
    }

    public function getStudentId(): int {
        return $this->studentId;
    }
}
