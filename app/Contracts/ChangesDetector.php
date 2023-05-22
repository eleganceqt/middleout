<?php

namespace App\Contracts;

interface ChangesDetector
{
    public function diffs(array $original, array $actual): array;
}
