<?php declare(strict_types=1);
use PHPUnit\Framework\TestCase;
use App\Access\Enrolment;
use App\Time\FormattedDateTime;

final class EnrolmentTest extends TestCase {


    public function testEnrolmentIsCreatedWithCorrectIdAndDates(): void {
        $studentId = 123;
        $startDate = "01/05/2025";
        $startDateTime = FormattedDateTime::getDayStartTimeFromDayMonthYearString($startDate);
        $endDate = "30/05/2025";
        $endDateTime = FormattedDateTime::getDayEndTimeFromDayMonthYearString($endDate);
        $enrolment = new Enrolment($studentId, $startDate, $endDate);

        $this->assertSame($startDateTime->getTimestamp(), $enrolment->getStartDate()->getTimestamp());
        $this->assertSame($endDateTime->getTimestamp(), $enrolment->getEndDate()->getTimestamp());
        $this->assertSame($studentId, $enrolment->getStudentId());
    }

}