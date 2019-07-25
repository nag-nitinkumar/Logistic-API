<?php

namespace App\Repositories;

use App\Http\Models\Distance;
use App\Helpers\DistanceHelper;
use Illuminate\Http\Request;

class DistanceRepository implements RepositoryInterface
{
    /**
     * @var DistanceHelper
     */
    protected $distanceHelper;

    /**
     * @var DistanceModel
     */
    protected $distanceModel;

    public function __construct(
        Distance $distanceModel,
        DistanceHelper $distanceHelper
    ) {
        $this->distanceModel = $distanceModel;
        $this->distanceHelper = $distanceHelper;
    }

    /**
     * Create new Distance.
     *
     * @param  array $postData
     * @return App\Http\Models\Distance
     */
    public function create(array $postData)
    {
        $distance = new Distance;
        $distance->start_lat = $postData['start_lat'];
        $distance->start_long = $postData['start_long'];
        $distance->end_lat = $postData['end_lat'];
        $distance->end_long = $postData['end_long'];
        $distance->total_distance = $postData['total_distance'];
        $distance->save();

        return $distance;
    }

    /**
     * Get all instances of model.
     *
     * @param  int $page
     * @param  int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function all($page, $limit)
    {
        $skip = ($page -1) * $limit;

        return $this->distanceModel
                ->skip($skip)
                ->take($limit)
                ->orderBy('id', 'asc')
                ->get();
    }

    /**
     * Find the record with the given id.
     *
     * @param int $id
     * @return App\Http\Models\Distance|null
     */
    public function find(int $id)
    {
        return $this->distanceModel->findOrFail($id);
    }

    /**
     * Find the distance based on origin and destination.
     *
     * @param  array $origin      [array of 2 string lat and long]
     * @param  array $destination [array of 2 string lat and long]
     * @return App\Http\Models\Distance
     */
    public function getDistance($origin, $destination)
    {
        return $this->distanceModel->getDistance($origin, $destination);
    }
}
