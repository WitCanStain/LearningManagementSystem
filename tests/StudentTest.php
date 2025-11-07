<?php declare(strict_types=1);
use PHPUnit\Framework\TestCase;
use App\Access\Student;


final class StudentTest extends TestCase {


    public function testStudentIsCreatedWithCorrectIdAndName(): void {
        $studentName = "Emma";
        $studentId = 123;
        $student = new Student($studentId, $studentName);

        $this->assertSame($studentName, $student->getName());
        $this->assertSame($studentId, $student->getId());
    }

}