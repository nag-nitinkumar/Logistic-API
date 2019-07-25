<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;

class Distance extends Model
{
    /**
    * @var \App\Helpers\DistanceHelper
    */
    protected $distanceHelper;

    protected $table = 'distances';

    /**
     * getDistance from DB based based on origin and destination
     * @param  [array] $origin      [array of 2 string lat and long]
     * @param  [array] $destination [array of 2 string lat and long]
     * @return [self|null] [if distance exists then return self else null]
     */
    public function getDistance($origin, $destination)
    {
        return self::where([
            ['start_lat', '=', $origin[0]],
            ['start_long', '=', $origin[1]],
            ['end_lat', '=', $destination[0]],
            ['end_long', '=', $destination[1]],
        ])->first();
    }
}
