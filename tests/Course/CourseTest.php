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
use App\Course\Exception\ConstructorException;
use App\Course\Exception\InvalidAccessTimeException;
use App\Course\Exception\EnrollmentException;

/**
 * @coversNothing
 */
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
        $clockDate = FormattedDateTime::getDateFromDateTimeString("01/05/2025 00:00:00.000");
        $this->clock = new FakeClock($clockDate);

        $prepMaterialContent = "The mitochondria is the powerhouse of the cell";
        $this->prepMaterial = new PrepMaterial($this->prepMaterialName, $prepMaterialContent);
        $prepMaterials = array($this->prepMaterial);

        $lessonStartDateTimeString = "15/05/2025 10:00:00.000";
        $lessonStartDate = FormattedDateTime::getDateFromDateTimeString($lessonStartDateTimeString);
        $this->lesson = new Lesson($this->lessonTitle, $this->lessonContent, $lessonStartDate, $this->clock);
        $lessons = array($this->lesson);
        
        $homeworkDueDateString = "17/05/2025";
        $homeworkDueDate = FormattedDateTime::getDayEndTimeFromDayMonthYearString($homeworkDueDateString);
        $this->homework = new Homework($this->homeworkTitle, $homeworkDueDate, $this->clock);
        $homeworks = array($this->homework);

        $courseStartDateString = "13/05/2025";
        $courseEndDateString = "12/06/2025";
        $courseStartDate = FormattedDateTime::getDayStartTimeFromDayMonthYearString($courseStartDateString);
        $courseEndDate = FormattedDateTime::getDayEndTimeFromDayMonthYearString($courseEndDateString);
        $this->course = new Course($this->courseName, $lessons, $homeworks, $prepMaterials, $courseStartDate, $courseEndDate, $this->clock);
        $enrolmentStartDateString = "01/05/2025";
        $enrolmentEndDateString = "30/05/2025";
        $enrolmentStartDate = FormattedDateTime::getDayStartTimeFromDayMonthYearString($enrolmentStartDateString);
        $enrolmentEndDate = FormattedDateTime::getDayEndTimeFromDayMonthYearString($enrolmentEndDateString);
        $studentId = 123;
        $this->enrolment = new Enrolment($studentId, $enrolmentStartDate, $enrolmentEndDate, $this->clock);

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
        $courseStartDateTimeString = "01/05/2025 00:00:00.000";
        $courseStartDate = FormattedDateTime::getDateFromDateTimeString($courseStartDateTimeString);
        $clock = new FakeClock($courseStartDate);
        $courseStartDateString = "13/05/2025";
        $courseStartDate = FormattedDateTime::getDayStartTimeFromDayMonthYearString($courseStartDateString);
        $courseWithoutEndDate = new Course($this->courseName, array($this->lesson), array($this->homework), array(), $courseStartDate, null, $clock);

        $this->assertTrue($courseWithoutEndDate instanceof Course);
    }

    public function testCreatingACourseWithoutLessonsCausesException(): void {
        $courseStartDateTimeString = "01/05/2025 00:00:00.000";
        $courseStartDate = FormattedDateTime::getDateFromDateTimeString($courseStartDateTimeString);
        $clock = new FakeClock($courseStartDate);
        $courseStartDateString = "13/05/2025";
        $courseStartDate = FormattedDateTime::getDayStartTimeFromDayMonthYearString($courseStartDateString);

        $this->expectException(ConstructorException::class);

        $courseWithoutLessons = new Course($this->courseName, array(), array($this->homework), array(), $courseStartDate, null, $clock);
    }

    public function testCreatingACourseWithoutHomeworkssCausesException(): void {
        $courseStartDateTimeString = "01/05/2025 00:00:00.000";
        $courseStartDate = FormattedDateTime::getDateFromDateTimeString($courseStartDateTimeString);
        $clock = new FakeClock($courseStartDate);
        $courseStartDateString = "13/05/2025";
        $courseStartDate = FormattedDateTime::getDayStartTimeFromDayMonthYearString($courseStartDateString);

        $this->expectException(ConstructorException::class);

        $courseWithoutHomeworks = new Course($this->courseName, array($this->lesson), array(), array(), $courseStartDate, null, $clock);
    }

    public function testStudentCannotAccessPrepMaterialBeforeCourseStart(): void {
        $this->expectException(InvalidAccessTimeException::class);

        $this->course->enroll($this->enrolment);
        $prepMaterial = $this->course->getPrepMaterialForStudent($this->student->getId(), $this->prepMaterialName); 

        $this->assertNull($prepMaterial);

    }

    public function testStudentCannotAccessPrepMaterialAfterCourseEnd(): void {
        $this->clock->setTime(FormattedDateTime::getDateFromDateTimeString("13/06/2025 00:00:00.000"));

        $this->expectException(EnrollmentException::class);

        $this->course->enroll($this->enrolment);
        $prepMaterial = $this->course->getPrepMaterialForStudent($this->student->getId(), $this->prepMaterialName); 

        $this->assertNull($prepMaterial);

    }

    public function testStudentCanAccessPrepMaterialAfterCourseStart(): void {
        $enrolmentStartDateString = "01/05/2025";
        $enrolmentEndDateString = "30/05/2025";
        $enrolmentStartDate = FormattedDateTime::getDayStartTimeFromDayMonthYearString($enrolmentStartDateString);
        $enrolmentEndDate = FormattedDateTime::getDayEndTimeFromDayMonthYearString($enrolmentEndDateString);
        $enrolment = new Enrolment($this->student->getId(), $enrolmentStartDate, $enrolmentEndDate, $this->clock);
        $this->clock->setTime(FormattedDateTime::getDateFromDateTimeString("13/05/2025 00:00:00.000"));
        $this->course->enroll($enrolment);
        $prepMaterial = $this->course->getPrepMaterialForStudent($this->student->getId(), $this->prepMaterialName);

        $this->assertNotNull($prepMaterial);
        $this->assertSame($this->prepMaterial->getContent(), $prepMaterial->getContent());
    }

    public function testStudentCannotAccessPrepMaterialWhenNotEnrolled(): void {
        $this->expectException(EnrollmentException::class);

        $prepMaterial = $this->course->getPrepMaterialForStudent($this->student->getId(), $this->prepMaterialName);
    }

    public function testAccessingInexistentPrepMaterialCausesException(): void {
        $this->clock->setTime(FormattedDateTime::getDateFromDateTimeString("15/05/2025 10:01:00.000"));
        $this->course->enroll($this->enrolment);
        $this->expectException(ContentDoesNotExistException::class);
        $prepMaterial = $this->course->getPrepMaterialForStudent($this->student->getId(), "Inexistent prep material title");
    }

    public function testStudentCanAccessLessonAfterLessonStart(): void {
       
        $this->clock->setTime(FormattedDateTime::getDateFromDateTimeString("15/05/2025 10:01:00.000"));
        $courseStartDateString = "13/05/2025";
        $courseEndDateString = "12/06/2025";
        $courseStartDate = FormattedDateTime::getDayStartTimeFromDayMonthYearString($courseStartDateString);
        $courseEndDate = FormattedDateTime::getDayEndTimeFromDayMonthYearString($courseEndDateString);
        $course = new Course($this->courseName, array($this->lesson), array($this->homework), array($this->prepMaterial), $courseStartDate, $courseEndDate, $this->clock);
        $enrolmentStartDateString = "13/05/2025";
        $enrolmentEndDateString = "30/05/2025";
        $enrolmentStartDate = FormattedDateTime::getDayStartTimeFromDayMonthYearString($enrolmentStartDateString);
        $enrolmentEndDate = FormattedDateTime::getDayEndTimeFromDayMonthYearString($enrolmentEndDateString);
        
        $enrolment = new Enrolment($this->student->getId(), $enrolmentStartDate, $enrolmentEndDate, $this->clock);
        $course->enroll($enrolment);
        $lesson = $course->getLessonForStudent($this->student->getId(), $this->lessonTitle);
        
        $this->assertSame($this->lessonContent, $lesson->getContent());
    }

    public function testStudentCannotAccessLessonBeforeLessonStart(): void {
        $this->expectException(InvalidAccessTimeException::class);
        $enrolmentStartDateString = "13/05/2025";
        $enrolmentEndDateString = "30/05/2025";
        $enrolmentStartDate = FormattedDateTime::getDayStartTimeFromDayMonthYearString($enrolmentStartDateString);
        $enrolmentEndDate = FormattedDateTime::getDayEndTimeFromDayMonthYearString($enrolmentEndDateString);
        $enrolment = new Enrolment($this->student->getId(), $enrolmentStartDate, $enrolmentEndDate, $this->clock);
        $this->clock->setTime(FormattedDateTime::getDateFromDateTimeString("13/05/2025 00:00:00.000"));
        $this->course->enroll($enrolment);
        $lesson = $this->course->getLessonForStudent($this->student->getId(), $this->lessonTitle);
    }

    public function testAccessingInexistentLessonCausesException(): void {
        $this->clock->setTime(FormattedDateTime::getDateFromDateTimeString("15/05/2025 10:01:00.000"));
        $this->course->enroll($this->enrolment);
        $this->expectException(ContentDoesNotExistException::class);
        $lesson = $this->course->getLessonForStudent($this->student->getId(), "Inexistent lesson title");
    }

    public function testStudentCannotAccesshomeworkBeforeCourseStart(): void {
        $this->expectException(InvalidAccessTimeException::class);

        $this->course->enroll($this->enrolment);
        $homework = $this->course->getHomeworkForStudent($this->student->getId(), $this->homeworkTitle); 

        $this->assertNull($homework);

    }

    public function testStudentCanAccessHomeworkAfterCourseStart(): void {
        $enrolmentStartDateString = "13/05/2025";
        $enrolmentEndDateString = "30/05/2025";
        $enrolmentStartDate = FormattedDateTime::getDayStartTimeFromDayMonthYearString($enrolmentStartDateString);
        $enrolmentEndDate = FormattedDateTime::getDayEndTimeFromDayMonthYearString($enrolmentEndDateString);
        $enrolment = new Enrolment($this->student->getId(), $enrolmentStartDate, $enrolmentEndDate, $this->clock);
        $this->course->enroll($enrolment);
        $this->clock->setTime(FormattedDateTime::getDateFromDateTimeString("13/05/2025 00:00:00.000"));
        $homework = $this->course->getHomeworkForStudent($this->student->getId(), $this->homeworkTitle);

        $this->assertNotNull($homework);
        $this->assertSame($this->homeworkTitle, $homework->getTitle());
    }

    public function testStudentCannotAccessHomeworkWhenNotEnrolled(): void {
        $this->expectException(EnrollmentException::class);

        $homework = $this->course->getHomeworkForStudent($this->student->getId(), $this->homeworkTitle); 
    }

    public function testAccessingInexistentHomeworkCausesException(): void {
        $this->clock->setTime(FormattedDateTime::getDateFromDateTimeString("15/05/2025 10:01:00.000"));
        $this->course->enroll($this->enrolment);
        $this->expectException(ContentDoesNotExistException::class);
        $homework = $this->course->getHomeworkForStudent($this->student->getId(), "Inexistent homework title");
    }

    public function testStudentCannotAccessHomeworkAfterEnrolmentEnded(): void {
        $this->course->enroll($this->enrolment);
        $this->clock->setTime(FormattedDateTime::getDateFromDateTimeString("13/05/2025 00:00:00.000"));
        $newEnrolmentEndDateString = "20/05/2025";
        $homeworkBeforeEnrolmentChange = $this->course->getHomeworkForStudent($this->student->getId(), $this->homeworkTitle);
        $this->assertNotNull($homeworkBeforeEnrolmentChange);
        $this->enrolment->setEndDate(FormattedDateTime::getDayStartTimeFromDayMonthYearString($newEnrolmentEndDateString));

        $this->clock->setTime(FormattedDateTime::getDateFromDateTimeString("21/05/2025 00:00:00.000"));
        $this->expectException(EnrollmentException::class);
        $homeworkAfterEnrolmentChange = $this->course->getHomeworkForStudent($this->student->getId(), $this->homeworkTitle);
        $this->assertNull($homeworkAfterEnrolmentChange);

        $this->clock->setTime(FormattedDateTime::getDateFromDateTimeString("30/05/2025 00:00:00.000"));
        $this->expectException(EnrollmentException::class);
        $homeworkAfterEnrolmentChange = $this->course->getHomeworkForStudent($this->student->getId(), $this->homeworkTitle);
        $this->assertNull($homeworkAfterEnrolmentChange);

        $this->clock->setTime(FormattedDateTime::getDateFromDateTimeString("10/06/2025 00:00:00.000"));
        $this->expectException(EnrollmentException::class);
        $homeworkAfterEnrolmentChange = $this->course->getHomeworkForStudent($this->student->getId(), $this->homeworkTitle);
        $this->assertNull($homeworkAfterEnrolmentChange);
    }
    
}
