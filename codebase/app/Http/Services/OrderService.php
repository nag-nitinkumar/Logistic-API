<?php

namespace App\Http\Services;

use App\Http\Models\Order as OrderModel;
use App\Repositories\OrderRepository;
use App\Repositories\DistanceRepository;
use App\Http\Requests\NewOrderRequest;
use App\Http\Requests\ListOrdersRequest;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use App\Helpers\DistanceHelper;

class OrderService
{
    /**
     * @var OrderRepository $orderRepository
     */
    protected $orderRepository;

    /**
     * @var DistanceRepository $distanceRepository
     */
    protected $distanceRepository;

    /**
     * @var null|string
     */
    public $error = null;

    /**
     * @var int
     */
    public $errorCode;

    /**
     * @var DistanceHelper
     */
    protected $distanceHelper;

    /**
     * @param OrderRepository $orderRepository
     * @param DistanceRepository $distanceRepository
     * @param DistanceHelper $distanceHelper
     */
    public function __construct(
        OrderRepository $orderRepository,
        DistanceRepository $distanceRepository,
        DistanceHelper $distanceHelper
    ) {
        $this->orderRepository = $orderRepository;
        $this->distanceRepository = $distanceRepository;
        $this->distanceHelper = $distanceHelper;
    }

    /**
     * It create new order based on origin & desgination locations]
     * @param  NewOrderRequest $newOrderRequest
     * @return \App\Http\Models\Order
     */
    public function createNewOrder($origin, $destination)
    {
        $distanceObject = $this->distanceRepository->getDistance($origin, $destination);

        //if distance object is null then
        if (null === $distanceObject) {
            $totalDistance = $this->distanceHelper->getDistance($origin, $destination);

            //if get any error in api then return
            if (!is_int($totalDistance)) {
                $this->error = $totalDistance;
                $this->errorCode = JsonResponse::HTTP_SERVICE_UNAVAILABLE;
                return false;
            }

            $distanceObject = $this->distanceRepository->create([
                'start_lat' => $origin[0],
                'start_long' => $origin[1],
                'end_lat' => $destination[0],
                'end_long' => $destination[1],
                'total_distance' => $totalDistance,
            ]);

            //if any error
            if (!$distanceObject instanceof \App\Http\Models\Distance) {
                $this->error = $distanceObject;
                $this->errorCode = JsonResponse::HTTP_INTERNAL_SERVER_ERROR;
                return false;
            }
        }

        $postData = [
            'status' => OrderModel::UNASSIGNED_ORDER_STATUS,
            'distance_id' => $distanceObject->id,
            'total_distance' => $distanceObject->total_distance
        ];

        return $this->orderRepository->create($postData);
    }

    /**
     * Get the list of all filter ordered as per given criteria
     *
     * @param ListOrdersRequest $listOrderRequest
     *
     * @return array
     */
    public function getAll($page, $limit)
    {
        return $this->orderRepository->all($page, $limit);
    }

    /**
     * Fetches Order model based on primary key provided
     *
     * @param int $id
     *
     * @return OrderModel
     */
    public function getOrderById($id)
    {
        return $this->orderRepository->find($id);
    }

    /**
     * Mark an order as TAKEN, if not already
     *
     * @param int $id
     *
     * @return bool
     */
    public function acceptOrder($id)
    {
        return $this->orderRepository->acceptOrder($id);
    }
}
