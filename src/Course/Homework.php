<?php declare(strict_types=1);
namespace App\Course;
use App\TimedContent;
use App\Time\Clock;
use DateTimeImmutable;

class Homework extends TimedContent{
    private string $title;

    function __construct(string $title, DateTimeImmutable $endDate, Clock $clock) {
        parent::__construct(null, $endDate, $clock);
        $this->title = $title;
        $this->endDate = $endDate;
    }

    public function getTitle(): string {
        return $this->title;
    }
    
}