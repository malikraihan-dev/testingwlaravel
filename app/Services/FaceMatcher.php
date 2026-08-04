<?php

namespace App\Services;

class FaceMatcher
{
    /**
     * Jarak Euclidean antara dua face descriptor (masing-masing 128 angka
     * float dari face-api.js). Semakin kecil nilainya, semakin mirip wajahnya.
     */
    public static function distance(array $a, array $b): float
    {
        if (count($a) !== 128 || count($b) !== 128) {
            return INF;
        }

        $sum = 0.0;
        foreach ($a as $i => $value) {
            $sum += ($value - $b[$i]) ** 2;
        }

        return sqrt($sum);
    }

    /**
     * Threshold umum untuk face-api.js: 0.6 = longgar, 0.5 = ketat.
     * Mulai dari 0.5, naikkan sedikit kalau terlalu sering gagal match
     * padahal orangnya sama (misal karena pencahayaan berbeda).
     */
    public static function isMatch(array $a, array $b, float $threshold = 0.55): bool
    {
        return self::distance($a, $b) <= $threshold;
    }
}
