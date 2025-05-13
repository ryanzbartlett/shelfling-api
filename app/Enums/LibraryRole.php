<?php

namespace App\Enums;

enum LibraryRole: int
{
    case OWNER = 1;
    case EDITOR = 2;
    case VIEWER = 3;

    public function level(): int
    {
        return match($this) {
            self::VIEWER => 1,
            self::EDITOR => 2,
            self::OWNER  => 3,
        };
    }
}
