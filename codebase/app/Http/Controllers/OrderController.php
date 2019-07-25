<?php

namespace App\Http\Controllers;

use App\Http\Models\Order as OrderModel;

use Illuminate\Http\Request;
use App\Http\Requests\NewOrderRequest;
use App\Http\Requests\AcceptOrderRequest;
use App\Http\Requests\ListOrdersRequest;
use App\Helpers\ResponseHelper;
use App\Http\Services\OrderService;
use Illuminate\Http\JsonResponse;

class OrderController extends Controller
{
    /**
     * @var ResponseHelper
     */
    protected $responseHelper;

    /**
     * @var OrderService $orderService
     */
    protected $orderService;

    public function __construct(
        OrderService $orderService,
        ResponseHelper $responseHelper
    ) {
        $this->orderService = $orderService;
        $this->responseHelper = $responseHelper;
    }

    /**
     * function to create new order for provided source and destination
     * validation for proper format for lat long of source and destination
     * if validation failed then raise error
     *
     * @param  $newOrderRequest
     * @return string JSON
     */
    public function newOrder(NewOrderRequest $newOrderRequest)
    {
        try {
            $origin = $newOrderRequest->get('origin');
            $destination = $newOrderRequest->get('destination');
            $order = $this->orderService->createNewOrder($origin, $destination);

            if ($order) {
                $orderResponseData = ['id' => $order->id, 'distance' =>$order->getDistance(), 'status' => $order->status];

                return $this->responseHelper->sendSuccess('Success', JsonResponse::HTTP_OK, $orderResponseData);
            } else {
                return $this->responseHelper->sendError($this->orderService->error, $this->orderService->errorCode);
            }
        } catch (\Exception $e) {
            return $this->responseHelper->sendError($e->getMessage(), JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * This method is used to accept order and it will change the order status to taken if successfully accepted.
     * @param  AcceptOrderRequest $acceptOrderRequest
     * @param  int $id
     * @return string JSON
     */
    public function acceptOrder(AcceptOrderRequest $acceptOrderRequest, $id)
    {
        try {
            // Make sure $id should be a valid integer
            if (!preg_match('/^\d+$/', $id)) {
                return $this->responseHelper->sendError('INVALID_ORDER', JsonResponse::HTTP_NOT_FOUND);
            }

            $order = $this->orderService->getOrderById($id);

            // if order status is already taken then raise error
            if ($order && $order->status === OrderModel::ASSIGNED_ORDER_STATUS) {
                return $this->responseHelper->sendError('ALREADY_TAKEN', JsonResponse::HTTP_CONFLICT);
            }

            // Accept order
            if (false === $this->orderService->acceptOrder($id)) {
                return $this->responseHelper->sendError('ALREADY_TAKEN', JsonResponse::HTTP_CONFLICT);
            }

            //if successfully accepted then send success message
            return $this->responseHelper->sendSuccess('Success', JsonResponse::HTTP_OK, ["status" => "SUCCESS"]);
        } catch (\Exception $e) {
            return $this->responseHelper->sendError('INVALID_ORDER', JsonResponse::HTTP_NOT_FOUND);
        }
    }

    /**
     * listOrders to return all orders as per pagination and limit
     * @param  ListOrdersRequest $listOrderRequest
     * @return string JSON
     */
    public function listOrders(ListOrdersRequest $listOrderRequest)
    {
        try {
            $page = (int) $listOrderRequest->get('page', 1);
            $limit = (int) $listOrderRequest->get('limit', 1);

            $result = $this->orderService->getAll($page, $limit);

            $orders = [];
            if ($result && $result->count() > 0) {
                foreach ($result as $order) {
                    $orders[] = ['id' => $order->id, 'distance' =>$order->getDistance(), 'status' => $order->status];
                }
            }
            //return orders
            return $this->responseHelper->sendSuccess('Success', JsonResponse::HTTP_OK, $orders);
        } catch (\Exception $e) {
            return $this->responseHelper->sendError($e->getMessage(), JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
