<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;

class DistanceHelperTest extends Tests\TestCase
{
    use WithoutMiddleware;

    public function testGetValidDistanceHelper()
    {
        echo "\n *** Unit Test - Get Distance Helper Test --- Success --- *** \n";

        $origin = ['90.123457', '77.102493'];
        $destination = ['28.535517', '77.391029'];

        $distanceHelperMock = $this->getMockBuilder(\App\Helpers\DistanceHelper::class)
            ->setMethods(['getDistance'])
            ->disableOriginalConstructor()
            ->getMock();

        $googleApi = new App\Library\Distance\GoogleDistanceMatrix;

        $distanceHelper = new \App\Helpers\DistanceHelper($googleApi);

        $this->assertInstanceOf('App\Helpers\DistanceHelper', $distanceHelper);
    }
}
