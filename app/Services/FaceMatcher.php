<?php

namespace App\Services;

class FaceMatcher
{
    /**
     * Default matching threshold used by face-api.js recommendations.
     * Lower = stricter. Typical safe range is 0.4 - 0.6.
     */
    public float $threshold = 0.5;

    public function euclideanDistance(array $a, array $b): float
    {
        $sum = 0.0;
        $count = min(count($a), count($b));

        for ($i = 0; $i < $count; $i++) {
            $sum += ($a[$i] - $b[$i]) ** 2;
        }

        return sqrt($sum);
    }

    public function isMatch(array $storedDescriptor, array $submittedDescriptor): bool
    {
        if (count($storedDescriptor) !== 128 || count($submittedDescriptor) !== 128) {
            return false;
        }

        return $this->euclideanDistance($storedDescriptor, $submittedDescriptor) < $this->threshold;
    }
}
