<?php declare(strict_types=1);

namespace App\Course;
use App\Access\Enrolment;
use App\Time\Clock;
use App\Course\PrepMaterial;
use App\Course\Lesson;
use App\Course\Homework;
use App\Course\Exception\ContentDoesNotExistException;
use App\Course\Exception\InvalidAccessTimeException;
use App\Course\Exception\EnrollmentException;
use App\Course\Exception\ConstructorException;
use DateTimeImmutable;
use App\TimedContent;

class Course extends TimedContent{
    private string $name;
    private array $enrolments = [];
    private array $prepMaterials = [];
    private array $lessons = [];
    private array $homeworks = [];


    function __construct(string $name, array $lessons, array $homeworks, ?array $prepMaterials, DateTimeImmutable $startDate, ?DateTimeImmutable $endDate, Clock $clock) {
        parent::__construct($startDate, $endDate, $clock);
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
        if ($prepMaterials) {
            foreach ($prepMaterials as $prepMaterial) {
                $this->prepMaterials[$prepMaterial->getTitle()] = $prepMaterial;
            }
        }
            
        $this->name = $name;
    }

    public function enroll(Enrolment $enrolment): void {
        $studentId = $enrolment->getStudentId();
        $studentIsCurrentlyEnrolled = $this->studentIsCurrentlyEnrolled($studentId);
        if ($studentIsCurrentlyEnrolled) {
            throw new EnrollmentException("Student is already enrolled in course.");
        }
        $this->enrolments[$studentId] = $enrolment;
    }

    public function getPrepMaterialForStudent(int $studentId, string $prepMaterialTitle): PrepMaterial {
        $this->validateStudentEnrolment($studentId);
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
        $this->validateStudentEnrolment($studentId);
        $courseContainsLesson = array_key_exists($lessonTitle, $this->lessons);
        if (!$courseContainsLesson) {
            throw new ContentDoesNotExistException("Lesson does not exist in course.");
        }
        $lesson = $this->lessons[$lessonTitle];
        if (!$lesson->isActive()) {
            throw new InvalidAccessTimeException("Cannot access lesson before its due start time.");
        }
        return $lesson;
    }

    public function getHomeworkForStudent(int $studentId, string $homeworkTitle): Homework {
        $this->validateStudentEnrolment($studentId);
        if (!$this->isActive()) {
            throw new InvalidAccessTimeException("Cannot access homework before course has started.");
        }
        if (!(array_key_exists($homeworkTitle, $this->homeworks))) {
            throw new ContentDoesNotExistException("Homework does not exist in course.");
        }
        return $this->homeworks[$homeworkTitle];
    }

    private function validateStudentEnrolment(int $studentId): void {
        if (!$this->studentIsCurrentlyEnrolled($studentId)) {
            throw new EnrollmentException("Student is not currently enrolled.");
        }
    }

    private function studentIsCurrentlyEnrolled(int $studentId): bool {
        $studentIsEnrolled = array_key_exists($studentId, $this->enrolments);
        if ($studentIsEnrolled) {
            $studentEnrolment = $this->enrolments[$studentId];
            $enrollmentPeriodIsActive = $studentEnrolment->isActive();
            if ($enrollmentPeriodIsActive) {
                return true;
            }
        }
        return false;
    }

    public function getEnrolments(): array {
        return $this->enrolments;
    }
    
}