<?php declare(strict_types=1);

namespace App\Access;


class Student {
    private int $id;
    private string $name;
    
    function __construct(int $id, string $name) {
        $this->id = $id;
        $this->name = $name;
    }

    function getId(): int {
        return $this->id;
    }
}