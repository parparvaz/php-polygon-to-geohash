<?php

namespace PolygonToGeoHash;

use Generator;

class GeoHashGenerator
{
    private const GEO_HASH_CHARS = '0123456789bcdefghjkmnpqrstuvwxyz';
    private const LATITUDE_STEP = 10e-5;
    private const LONGITUDE_STEP = 10e-5;
    private const GEO_HASH_PRECISION = 8;

    /** @var float[][] $polygon Array of polygon vertices where each vertex is [latitude, longitude] */
    private array $polygon;

    /** @var array $geoHashes Array to store unique geoHashes */
    private array $geoHashes;

    /**
     * GeoHashGenerator constructor.
     *
     * @param float[][] $polygon An array of coordinates representing the polygon
     */
    public function __construct(array $polygon)
    {
        $this->polygon = $polygon;
        $this->geoHashes = [];
    }

    /**
     * Generates geoHashes for the polygon in a memory-efficient manner using a generator.
     *
     * @return Generator<string> GeoHashes of length 8
     */
    public function generateGeoHashes(): Generator
    {
        // For each point in the grid, yield the geoHash if it's inside the polygon
        foreach ($this->getGridPoints() as $point) {
            $geoHash = $this->encodeGeoHash($point[0], $point[1], 8);
            if (!isset($this->geoHashes[$geoHash])) {
                $this->geoHashes[$geoHash] = true;
                yield $geoHash;
            }
        }
    }

    /**
     * Generates a grid of points that covers the polygon.
     * This version returns points one by one to avoid loading all into memory.
     *
     * @return Generator<float[]> Each item is a [latitude, longitude] pair
     */
    private function getGridPoints(): Generator
    {
        // Get bounding box for the polygon (min/max lat/lng)
        $minLat = min(array_column($this->polygon, 0));
        $maxLat = max(array_column($this->polygon, 0));
        $minLng = min(array_column($this->polygon, 1));
        $maxLng = max(array_column($this->polygon, 1));

        // Loop over each point in the bounding box
        for ($lat = $minLat; $lat <= $maxLat; $lat += self::LATITUDE_STEP) {
            for ($lng = $minLng; $lng <= $maxLng; $lng += self::LONGITUDE_STEP) {
                if ($this->isPointInPolygon($lat, $lng)) {
                    yield [$lat, $lng];
                }
            }
        }
    }

    /**
     * Checks if a point is inside the polygon using the ray-casting algorithm.
     *
     * @param float $lat Latitude of the point
     * @param float $lng Longitude of the point
     * @return bool True if the point is inside the polygon, otherwise false
     */
    private function isPointInPolygon(float $lat, float $lng): bool
    {
        $inside = false;
        $numPoints = count($this->polygon);
        for ($i = 0, $j = $numPoints - 1; $i < $numPoints; $j = $i++) {
            [$xi, $yi, $xj, $yj] = [
                $this->polygon[$i][0],
                $this->polygon[$i][1],
                $this->polygon[$j][0],
                $this->polygon[$j][1],
            ];

            $intersect = (($yi > $lng) !== ($yj > $lng)) &&
                ($lat < ($xj - $xi) * ($lng - $yi) / ($yj - $yi) + $xi);
            if ($intersect) {
                $inside = !$inside;
            }
        }
        return $inside;
    }

    /**
     * Encodes a latitude and longitude into a geoHash.
     *
     * @param float $lat Latitude
     * @param float $lng Longitude
     * @param int $precision The desired length of the geoHash
     * @return string GeoHash string of the specified length
     */
    private function encodeGeoHash(float $lat, float $lng, int $precision = self::GEO_HASH_PRECISION): string
    {
        $minLat = -90.0;
        $maxLat = 90.0;
        $minLng = -180.0;
        $maxLng = 180.0;
        $geoHash = '';
        $even = true;
        $bit = 0;
        $ch = 0;

        while (strlen($geoHash) < $precision) {
            if ($even) {
                $mid = ($minLng + $maxLng) / 2;
                if ($lng > $mid) {
                    $ch |= 1 << (4 - $bit);
                    $minLng = $mid;
                } else {
                    $maxLng = $mid;
                }
            } else {
                $mid = ($minLat + $maxLat) / 2;
                if ($lat > $mid) {
                    $ch |= 1 << (4 - $bit);
                    $minLat = $mid;
                } else {
                    $maxLat = $mid;
                }
            }
            $even = !$even;
            if ($bit < 4) {
                $bit++;
            } else {
                $geoHash .= self::GEO_HASH_CHARS[$ch];
                $bit = 0;
                $ch = 0;
            }
        }

        return $geoHash;
    }
}