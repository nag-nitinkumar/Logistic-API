<?php

namespace App\Helpers;

class DistanceHelper
{
    /**
     * @var \App\Library\Distance\GoogleDistanceMatrix
     */
    protected $googleDistanceMatrix;

    /**
     * @param \App\Helpers\GoogleMap $googleMapHelper
     */
    public function __construct(
        \App\Library\Distance\GoogleDistanceMatrix $googleDistanceMatrix
    ) {
        $this->googleDistanceMatrix = $googleDistanceMatrix;
    }

    /**
     * Fetches distance between two pairs of lattitude and longitude
     *
     * @param string $origin
     * @param string destination
     *
     * @return int Distance in meters
     */
    public function getDistance($origin, $destination)
    {
        return $this->googleDistanceMatrix->getDistance($origin, $destination);
    }
}
