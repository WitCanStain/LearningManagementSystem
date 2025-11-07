<?php declare(strict_types=1);

namespace App\Access;
use DateTime;
use App\Time\FormattedDateTime;

class Enrolment {
    public int $studentId;
    public DateTime $startDate;
    public DateTime $endDate;

    function __construct(int $studentId, string $startDateString, string $endDateString) {
        $this->studentId = $studentId;
        $this->startDate = FormattedDateTime::getDayStartTimeFromDayMonthYearString($startDateString);
        $this->endDate = FormattedDateTime::getDayEndTimeFromDayMonthYearString($endDateString);
    }

    public function getStartDate(): DateTime {
        return $this->startDate;
    }

    public function getEndDate(): DateTime {
        return $this->endDate;
    }

    public function setEndDate(string $endDateString): void {
        $this->endDate = FormattedDateTime::getDayEndTimeFromDayMonthYearString($endDateString);
    }

    public function getStudentId(): int {
        return $this->studentId;
    }
}
