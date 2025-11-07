<?php declare(strict_types=1);

namespace App\Course;
use App\Access\Enrolment;
use App\Time\FormattedDateTime;
use App\Time\Clock;
use App\Course\PrepMaterial;
use App\Course\Lesson;
use App\Course\Homework;
use App\Course\Exception\ContentDoesNotExistException;
use App\Course\Exception\InvalidAccessTimeException;
use App\Course\Exception\EnrollmentException;
use App\Course\Exception\ConstructorException;
use DateTime;

class Course {
    private string $name;
    private DateTime $startDate;
    private DateTime $endDate;
    private Clock $clock;
    private array $enrolments = [];
    private array $prepMaterials = [];
    private array $lessons = [];
    private array $homeworks = [];


    function __construct(string $name, array $lessons, array $homeworks, array $prepMaterials, string $startDateString, ?string $endDateString, Clock $clock) {
        if (!$lessons) {
            throw new ConstructorException("Course requires at least one lesson.");
        }
        if (!$homeworks) {
            throw new ConstructorException("Course requires at least one homework.");
        }
        foreach ($lessons as $lesson) {
            $this->lessons[$lesson->getTitle()] = $lesson;
        }
        foreach ($homeworks as $homework) {
            $this->homeworks[$homework->getTitle()] = $homework;
        }
        foreach ($prepMaterials as $prepMaterial) {
            $this->prepMaterials[$prepMaterial->getTitle()] = $prepMaterial;
        }

        $this->startDate = FormattedDateTime::getDayStartTimeFromDayMonthYearString($startDateString);
        if ($endDateString) $this->endDate = FormattedDateTime::getDayEndTimeFromDayMonthYearString($endDateString);
        $this->name = $name;
        $this->clock = $clock;
    }

    public function enroll(Enrolment $enrolment): void {
        $studentId = $enrolment->getStudentId();
        $studentIsCurrentlyEnrolled = $this->studentIsCurrentlyEnrolled($studentId);
        if ($studentIsCurrentlyEnrolled) {
            throw new EnrollmentException("Student is already enrolled in course.");
        } else {
            $this->enrolments[$studentId] = $enrolment;
        }
    }

    public function getPrepMaterialForStudent(int $studentId, string $prepMaterialTitle): PrepMaterial {
        if (!$this->studentIsCurrentlyEnrolled($studentId)) {
            throw new EnrollmentException("Student is not currently enrolled.");
        }
        $now = $this->clock->now();
        if ($now < $this->startDate) {
            throw new InvalidAccessTimeException("Cannot access prep material before course has started.");
        }
        if (!(array_key_exists($prepMaterialTitle, $this->prepMaterials))) {
            throw new ContentDoesNotExistException("Prep material does not exist in course.");
        }
        return $this->prepMaterials[$prepMaterialTitle];
    }

    public function getLessonForStudent(int $studentId, string $lessonTitle): Lesson {
        if (!$this->studentIsCurrentlyEnrolled($studentId)) {
            throw new EnrollmentException("Student is not currently enrolled.");
        }
        $courseContainsLesson = array_key_exists($lessonTitle, $this->lessons);
        if (!$courseContainsLesson) {
            throw new ContentDoesNotExistException("Lesson does not exist in course.");
        }
        $lesson = $this->lessons[$lessonTitle];
        $now = $this->clock->now();
        $nowIsPastLessonStartTime = $now >= $lesson->getStartDate();
        if (!$nowIsPastLessonStartTime) {
            throw new InvalidAccessTimeException("Cannot access lesson before its due start time.");
        }
        return $lesson;
    }

    public function getHomeworkForStudent(int $studentId, string $homeworkTitle): Homework {
        if (!$this->studentIsCurrentlyEnrolled($studentId)) {
            throw new EnrollmentException("Student is not currently enrolled.");
        }
        $now = $this->clock->now();
        if ($now < $this->startDate) {
            throw new InvalidAccessTimeException("Cannot access homework before course has started.");
        }
        if (!(array_key_exists($homeworkTitle, $this->homeworks))) {
            throw new ContentDoesNotExistException("Homework does not exist in course.");
        }
        return $this->homeworks[$homeworkTitle];
    }

    private function studentIsCurrentlyEnrolled(int $studentId): bool {
        $studentIsCurrentlyEnrolled = array_key_exists($studentId, $this->enrolments);
        if ($studentIsCurrentlyEnrolled) {
            $studentEnrolment = $this->enrolments[$studentId];
            $now = $this->clock->now();
            $enrollmentPeriodIsActive = ($studentEnrolment->getStartDate() <= $now) && ($studentEnrolment->getEndDate() >= $now);
            if ($enrollmentPeriodIsActive) {
                return true;
            }
        }
        return false;
    }

    public function getEnrolments(): array {
        return $this->enrolments;
    }

    public function getStartDate(): DateTime {
        return $this->startDate;
    }

    public function getEndDate(): DateTime {
        return $this->endDate;
    }
}