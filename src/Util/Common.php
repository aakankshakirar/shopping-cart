<?php

namespace App\Util;

class Common
{
    public function callMe(string $value): string
    {
        return ucwords($value);
    }
}