<?php

namespace App\Services;

class GeofencingService
{
    /**
     * Earth radius in meters.
     */
    protected const EARTH_RADIUS = 6371000;

    /**
     * Calculate distance between two coordinates in meters using Haversine formula.
     */
    public function calculateDistance(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $lat1Rad = deg2rad($lat1);
        $lng1Rad = deg2rad($lng1);
        $lat2Rad = deg2rad($lat2);
        $lng2Rad = deg2rad($lng2);

        $deltaLat = $lat2Rad - $lat1Rad;
        $deltaLng = $lng2Rad - $lng1Rad;

        $a = sin($deltaLat / 2) * sin($deltaLat / 2) +
             cos($lat1Rad) * cos($lat2Rad) *
             sin($deltaLng / 2) * sin($deltaLng / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return self::EARTH_RADIUS * $c;
    }

    /**
     * Check if a coordinate lies within a specific radius from school coordinates.
     */
    public function isWithinRange(float $teacherLat, float $teacherLng, float $schoolLat, float $schoolLng, float $radiusMeters): bool
    {
        $distance = $this->calculateDistance($teacherLat, $teacherLng, $schoolLat, $schoolLng);
        return $distance <= $radiusMeters;
    }
}
