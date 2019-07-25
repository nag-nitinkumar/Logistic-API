<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;

class GoogleDistanceMatrixTest extends Tests\TestCase
{
    use WithoutMiddleware;

    public function testGetDistanceWithInValidData()
    {
        echo "\n *** Unit Test - Library::GoogleDistanceMatrix - Method:getDistance with --- INVALID LAT LONG (Out of range) --- *** \n";

        $origin = ['90.123457', '77.102493'];
        $destination = ['28.535517', '77.391029'];

        $googleApi = new App\Library\Distance\GoogleDistanceMatrix;
        $distance = $googleApi->getDistance($origin, $destination);

        /** if invalid lat long then it will return some error code */
        $this->assertRegExp("/^GOOGLE_API(.*)/", $distance);
    }


    public function testGetDistanceWithInValidData_NoLatLong()
    {
        echo "\n *** Unit Test - Library::GoogleDistanceMatrix - Method:getDistance with --- NO LAT LONG --- *** \n";

        $origin = [];
        $destination = [];

        $googleApi = new App\Library\Distance\GoogleDistanceMatrix;
        $distance = $googleApi->getDistance($origin, $destination);

        /** if invalid lat long then it will return some error code */
        $this->assertRegExp("/^GOOGLE_API(.*)/", $distance);
    }
}
