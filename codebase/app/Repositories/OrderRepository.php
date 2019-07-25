<?php

namespace App\Repositories;

use App\Http\Models\Order as OrderModel;
use App\Http\Requests\ListOrdersRequest;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

class OrderRepository implements RepositoryInterface
{
    /**
     * @var OrderModel
     */
    protected $orderModel;

    public function __construct(OrderModel $orderModel)
    {
        $this->orderModel = $orderModel;
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

        return $this->orderModel
                ->skip($skip)
                ->take($limit)
                ->orderBy('id', 'asc')
                ->get();
    }

    /**
     * Find the record with the given id.
     *
     * @param int $id
     * @return App\Http\Models\Order|null
     */
    public function find(int $id)
    {
        return $this->orderModel->findOrFail($id);
    }

    /**
     * Create a new record in the database.
     *
     * @param  array $postData
     * @return App\Http\Models\Order
     */
    public function create(array $postData)
    {
        $order = new OrderModel;
        $order->status = $postData['status'];
        $order->distance_id = $postData['distance_id'];
        $order->total_distance = $postData['total_distance'];
        $order->save();

        return $order;
    }

    /**
     * Update the Order Status to TAKEN if UNASSIGNED.
     *
     * @param int $id
     * @return App\Http\Models\Order
     */
    public function acceptOrder($id)
    {
        return $this->orderModel->acceptOrder($id);
    }
}
