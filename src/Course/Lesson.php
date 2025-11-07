<?php declare(strict_types=1);
namespace App\Course;
use DateTime;

class Lesson {
    private string $title;
    private string $content;
    private DateTime $startDateTime;

    function __construct(string $title, string $content, DateTime $startDateTime) {
        $this->title = $title;
        $this->content = $content;
        $this->startDateTime = $startDateTime;
    }

    public function getTitle(): string {
        return $this->title;
    }

    public function getContent(): string {
        return $this->content;
    }

    public function getStartDate(): DateTime {
        return $this->startDateTime;
    }
    
}