<?php

namespace App\Library\Distance;

interface DistanceMatrixInterface
{
    /**
     * Returns distance between Origin and Destination in meters
     * In case of any error send error code in string format
     *
     * @param array $origin
     * @param array $destination
     *
     * @return int|string
     */
    public function getDistance($origin, $destination);
}
