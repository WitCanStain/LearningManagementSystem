<?php declare(strict_types=1);
use PHPUnit\Framework\TestCase;
use App\Course\Course;
use App\Access\Enrolment;
use App\Access\Student;
use App\Course\PrepMaterial;
use App\Course\Lesson;
use App\Course\Homework;
use App\Time\FakeClock;
use App\Time\FormattedDateTime;
use App\Course\Exception\ContentDoesNotExistException;
use App\Course\Exception\ContentAlreadyExistsException;
use App\Course\Exception\InvalidAccessTimeException;
use App\Course\Exception\EnrollmentException;

final class CourseTest extends TestCase {

    private Course $course;
    private Enrolment $enrolment;
    private Student $student;
    private FakeClock $clock;
    private Lesson $lesson;
    private Homework $homework;
    private PrepMaterial $prepMaterial;
    private string $courseName = "A-Level Biology";
    private string $studentName = "Emma";
    private string $prepMaterialName = "Biology Reading Guide";
    private string $lessonTitle = "Cell Biology";
    private string $lessonContent = "The Krebbs Cycle powers the body";
    private string $homeworkTitle = "Label a Plant Cell";


    protected function setUp(): void {
        
        $courseStartDate = "13/05/2025";
        $courseEndDate = "12/06/2025";
        $this->clock = new FakeClock("2025-05-01 00:00:00");

        $prepMaterialContent = "The mitochondria is the powerhouse of the cell";
        $this->prepMaterial = new PrepMaterial($this->prepMaterialName, $prepMaterialContent);
        $prepMaterials = array($this->prepMaterial);

        $lessonStartDateTimeString = "15/05/2025 10:00:00.000";
        $lessonStartDate = FormattedDateTime::getDateFromDateTimeString($lessonStartDateTimeString);
        $this->lesson = new Lesson($this->lessonTitle, $this->lessonContent, $lessonStartDate);
        $lessons = array($this->lesson);
        
        $homeworkDueDateString = "17/05/2025";
        $homeworkDueDate = FormattedDateTime::getDayEndTimeFromDayMonthYearString($homeworkDueDateString);
        $this->homework = new Homework($this->homeworkTitle, $homeworkDueDate);
        $homeworks = array($this->homework);

        $this->course = new Course($this->courseName, $lessons, $homeworks, $prepMaterials, $courseStartDate, $courseEndDate, $this->clock);
        $enrolmentStartDate = "01/05/2025";
        $enrolmentEndDate = "30/05/2025";

        $studentId = 123;
        $this->enrolment = new Enrolment($studentId, $enrolmentStartDate, $enrolmentEndDate);

        $this->student = new Student($studentId, $this->studentName);

        

    }
    
    public function testEnrollingStudentAddsEnrolmentToCourseEnrolments(): void {
        $numberOfCourseEnrolmentsBeforeAddingANewStudent = count($this->course->getEnrolments());
        
        $this->assertSame(0, $numberOfCourseEnrolmentsBeforeAddingANewStudent);
        
        $this->course->enroll($this->enrolment);
        $courseEnrolmentsAfterAddingANewStudent = $this->course->getEnrolments();
        $numberOfCourseEnrolmentsAfterAddingANewStudent = count($courseEnrolmentsAfterAddingANewStudent);

        $this->assertSame(1, $numberOfCourseEnrolmentsAfterAddingANewStudent);
        $this->assertSame($this->student->getId(), $courseEnrolmentsAfterAddingANewStudent[$this->student->getId()]->getStudentId());
    }

    public function testEnrollingStudentTwiceCausesException(): void {
        $this->expectException(EnrollmentException::class);

        $this->course->enroll($this->enrolment);
        $this->course->enroll($this->enrolment);
    }

    public function testCreatingACourseWithoutEndDateCreatesInstance(): void {
        $startDate = "13/05/2025";
        $clock = new FakeClock("2025-05-01 00:00:00");
        $courseWithoutEndDate = new Course($this->courseName, array($this->lesson), array($this->homework), array(), $startDate, null, $clock);

        $this->assertTrue($courseWithoutEndDate instanceof Course);
    }

    public function testStudentCannotAccessPrepMaterialBeforeCourseStart(): void {
        $this->expectException(InvalidAccessTimeException::class);

        $this->course->enroll($this->enrolment);
        $prepMaterial = $this->course->getPrepMaterialForStudent($this->student->getId(), $this->prepMaterialName); 

        $this->assertNull($prepMaterial);

    }

    public function testStudentCannotAccessPrepMaterialWhenNotEnrolled(): void {
        $this->expectException(EnrollmentException::class);

        $prepMaterial = $this->course->getPrepMaterialForStudent($this->student->getId(), $this->prepMaterialName); 
    }

    public function testStudentCanAccessPrepMaterialAfterCourseStart(): void {
        
        $enrolmentStartDate = "01/05/2025";
        $enrolmentEndDate = "30/05/2025";
        $enrolment = new Enrolment($this->student->getId(), $enrolmentStartDate, $enrolmentEndDate);
        $this->clock->setTime(new DateTimeImmutable('2025-05-13 00:00:00 UTC'));
        $this->course->enroll($enrolment);
        $prepMaterial = $this->course->getPrepMaterialForStudent($this->student->getId(), $this->prepMaterialName);

        $this->assertNotNull($prepMaterial);
        $this->assertSame($this->prepMaterial->getContent(), $prepMaterial->getContent());
    }

    public function testStudentCanAccessLessonAfterLessonStart(): void {
       
        $afterLessonStartclock = new FakeClock("2025-05-15 10:01:00");
        $courseStartDate = "13/05/2025";
        $courseEndDate = "12/06/2025";
        $course = new Course($this->courseName, array($this->lesson), array($this->homework), array($this->prepMaterial), $courseStartDate, $courseEndDate, $afterLessonStartclock);
        $enrolmentStartDate = "13/05/2025";
        $enrolmentEndDate = "30/05/2025";
        $enrolment = new Enrolment($this->student->getId(), $enrolmentStartDate, $enrolmentEndDate);
        $course->enroll($enrolment);
        $lesson = $course->getLessonForStudent($this->student->getId(), $this->lessonTitle);
        
        $this->assertSame($this->lessonContent, $lesson->getContent());
    }

    public function testStudentCannotAccessLessonBeforeLessonStart(): void {
        $this->expectException(InvalidAccessTimeException::class);
        $enrolmentStartDate = "13/05/2025";
        $enrolmentEndDate = "30/05/2025";
        $enrolment = new Enrolment($this->student->getId(), $enrolmentStartDate, $enrolmentEndDate);
        $this->clock->setTime(new DateTimeImmutable('2025-05-13 00:00:00 UTC'));
        $this->course->enroll($enrolment);
        $lesson = $this->course->getLessonForStudent($this->student->getId(), $this->lessonTitle);
    }

    public function testStudentCannotAccesshomeworkBeforeCourseStart(): void {
        $this->expectException(InvalidAccessTimeException::class);

        $this->course->enroll($this->enrolment);
        $homework = $this->course->getHomeworkForStudent($this->student->getId(), $this->homeworkTitle); 

        $this->assertNull($homework);

    }

    public function testStudentCannotAccessHomeworkWhenNotEnrolled(): void {
        $this->expectException(EnrollmentException::class);

        $homework = $this->course->getHomeworkForStudent($this->student->getId(), $this->homeworkTitle); 
    }

    public function testStudentCanAccessHomeworkAfterCourseStart(): void {
        
        $enrolmentStartDate = "13/05/2025";
        $enrolmentEndDate = "30/05/2025";
        $enrolment = new Enrolment($this->student->getId(), $enrolmentStartDate, $enrolmentEndDate);
        $this->course->enroll($enrolment);
        $this->clock->setTime(new DateTimeImmutable('2025-05-13 00:00:00 UTC'));
        $homework = $this->course->getHomeworkForStudent($this->student->getId(), $this->homeworkTitle);

        $this->assertNotNull($homework);
        $this->assertSame($this->homeworkTitle, $homework->getTitle());
    }

    public function testStudentCannotAccessHomeworkAfterEnrolmentEnded(): void {
        $this->course->enroll($this->enrolment);
        $this->clock->setTime(new DateTimeImmutable('2025-05-13 00:00:00 UTC'));
        $newEnrolmentEndDate = "20/05/2025";
        $homeworkBeforeEnrolmentChange = $this->course->getHomeworkForStudent($this->student->getId(), $this->homeworkTitle);
        $this->assertNotNull($homeworkBeforeEnrolmentChange);
        $this->enrolment->setEndDate($newEnrolmentEndDate);

        $this->clock->setTime(new DateTimeImmutable('2025-05-21 00:00:00 UTC'));
        $this->expectException(EnrollmentException::class);
        $homeworkAfterEnrolmentChange = $this->course->getHomeworkForStudent($this->student->getId(), $this->homeworkTitle);
        $this->assertNull($homeworkAfterEnrolmentChange);

        $this->clock->setTime(new DateTimeImmutable('2025-05-30 00:00:00 UTC'));
        $this->expectException(EnrollmentException::class);
        $homeworkAfterEnrolmentChange = $this->course->getHomeworkForStudent($this->student->getId(), $this->homeworkTitle);
        $this->assertNull($homeworkAfterEnrolmentChange);

        $this->clock->setTime(new DateTimeImmutable('2025-06-10 00:00:00 UTC'));
        $this->expectException(EnrollmentException::class);
        $homeworkAfterEnrolmentChange = $this->course->getHomeworkForStudent($this->student->getId(), $this->homeworkTitle);
        $this->assertNull($homeworkAfterEnrolmentChange);
    }
    
}
