<?php declare(strict_types=1);

use App\Time\FormattedDateTime;
use PHPUnit\Framework\TestCase;

final class FormattedDateTimeTest extends TestCase {
    private string $startDate = "13/05/2025";
    private string $endDate = "12/06/2025";

    public function testgetDayStartTimeFromDayMonthYearStringReturnsDateTimeObjectWithCorrectDateAndTime(): void {
        $date = FormattedDateTime::getDayStartTimeFromDayMonthYearString($this->startDate);
        $this->assertSame("13/05/2025 00:00:00.000000", $date->format("d/m/Y H:i:s.u"));
    }

    public function testgetDayEndTimeFromDayMonthYearStringReturnsDateTimeObjectWithCorrectDateAndTime(): void {
        $date = FormattedDateTime::getDayEndTimeFromDayMonthYearString($this->endDate);
        $this->assertSame("12/06/2025 23:59:59.999000", $date->format("d/m/Y H:i:s.u"));
    }
}