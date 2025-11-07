<?php declare(strict_types=1);
namespace App\Course;
use DateTime;

class Homework {
    private string $title;
    private DateTime $dueDate;

    function __construct(string $title, DateTime $dueDate) {
        $this->title = $title;
        $this->dueDate = $dueDate;
    }

    public function getTitle(): string {
        return $this->title;
    }

    public function getDueDate(): DateTime {
        return $this->dueDate;
    }
    
}