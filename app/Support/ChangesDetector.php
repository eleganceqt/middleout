<?php

namespace App\Support;

use Illuminate\Support\Collection;
use App\Contracts\ChangesDetector as ChangesDetectorContract;

class ChangesDetector implements ChangesDetectorContract
{
    /**
     * Naive implementation of the changes detection mechanism. (Basically strict equality comparison)
     *
     * @param array $original
     * @param array $actual
     *
     * @return array
     */
    public function diffs(array $original, array $actual): array
    {
        $intersect = array_intersect(array_keys($original), array_keys($actual));

        return (new Collection($intersect))
            ->reject(fn(string $key) => $original[$key] === $actual[$key])
            ->mapWithKeys(fn(string $key) => [$key => $actual[$key]])
            ->all();
    }
}
