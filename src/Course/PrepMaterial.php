<?php declare(strict_types=1);
namespace App\Course;

class PrepMaterial {
    private string $title;
    private string $content;

    function __construct(string $title, string $content) {
        $this->title = $title;
        $this->content = $content;
    }

    public function getTitle(): string {
        return $this->title;
    }

    public function getContent(): string {
        return $this->content;
    }
    
}