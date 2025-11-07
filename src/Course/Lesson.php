<?php declare(strict_types=1);
namespace App\Course;
use App\TimedContent;
use App\Time\Clock;
use DateTimeImmutable;

class Lesson extends TimedContent {
    private string $title;
    private string $content;

    function __construct(string $title, string $content, DateTimeImmutable $startDate, Clock $clock) {
        parent::__construct($startDate, null, $clock);
        $this->title = $title;
        $this->content = $content;
        $this->startDate = $startDate;
    }

    public function getTitle(): string {
        return $this->title;
    }

    public function getContent(): string {
        return $this->content;
    }
    
}