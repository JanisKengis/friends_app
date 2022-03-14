<?php

namespace App\Validation;
use App\Exceptions;

class Errors
{
    public static function getAll(): array
    {
        return $_SESSION['errors'] ?? [];
    }

    public static function get(string $key): ?string
    {
        return $_SESSION['errors'][$key] ?? null;
    }
}