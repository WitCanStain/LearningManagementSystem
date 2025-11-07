<?php declare(strict_types=1);
use PHPUnit\Framework\TestCase;
use App\Access\Enrolment;
use App\Time\FormattedDateTime;
use App\Time\FakeClock;
final class EnrolmentTest extends TestCase {


    public function testEnrolmentIsCreatedWithCorrectIdAndDates(): void {
        $studentId = 123;
        $clock = new FakeClock(FormattedDateTime::getDateFromDateTimeString("01/05/2025 00:00:00.000"));
        $startDateString = "01/05/2025";
        $endDateString = "30/05/2025";
        $startDate = FormattedDateTime::getDayStartTimeFromDayMonthYearString($startDateString);
        $endDate = FormattedDateTime::getDayEndTimeFromDayMonthYearString($endDateString);
        $enrolment = new Enrolment($studentId, $startDate, $endDate, $clock);

        $this->assertSame($startDate->getTimestamp(), $enrolment->getStartDate()->getTimestamp());
        $this->assertSame($endDate->getTimestamp(), $enrolment->getEndDate()->getTimestamp());
        $this->assertSame($studentId, $enrolment->getStudentId());
    }

}