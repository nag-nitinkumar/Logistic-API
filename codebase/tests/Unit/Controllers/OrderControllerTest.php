<?php

use App\Http\Controllers\OrderController;
use App\Http\Models\Order;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Http\JsonResponse;

// use Mockery;

class OrderControllerTest extends Tests\TestCase
{
    use WithoutMiddleware;

    protected static $allowedOrderStatus = [
        Order::UNASSIGNED_ORDER_STATUS,
        Order::ASSIGNED_ORDER_STATUS,
    ];

    public function setUp()
    {
        parent::setUp();

        $this->faker = Faker\Factory::create();
        $this->orderServiceMock = \Mockery::mock(\App\Http\Services\OrderService::class);

        $this->responseHelper = \App::make(\App\Helpers\ResponseHelper::class);

        $this->app->instance(
             OrderController::class,
             new OrderController(
                $this->orderServiceMock,
                $this->responseHelper
            )
        );
    }

    public function tearDown()
    {
        \Mockery::close();
    }

    /**
     * @test
     */
    public function testNewOrder_Success()
    {
        echo "\n *** Unit Test - Create New Order  ---  Success --- *** \n";

        $order = $this->generateRandomOrder();

        $params = [
            'origin' => [strval($this->faker->latitude()), strval($this->faker->longitude())],
            'destination' => [strval($this->faker->latitude()), strval($this->faker->longitude())]
        ];

        //Order Service will return success
        $this->orderServiceMock
            ->shouldReceive('createNewOrder')
            ->with($params['origin'], $params['destination'])
            ->once()
            ->andReturn($order);

        $response = $this->call('POST', '/orders', $params);
        $data = $response->decodeResponseJson();

        $response->assertStatus(200);
        $this->assertInternalType('array', $data);
        $this->assertArrayHasKey('id', $data);
        $this->assertArrayHasKey('distance', $data);
    }

    /**
     * @test
     */
    public function testNewOrder_Success_NoMessage()
    {
        echo "\n *** Unit Test - Create New Order ---  Success No Message Given --- *** \n";

        $responseHelperMock = $this->createMock(\App\Helpers\ResponseHelper::class);

        $responseHelperMock->method('sendSuccess')->with('success', '200')->willReturn(true);

        $responseHelper = new \App\Helpers\ResponseHelper();

        $data = $responseHelper->sendSuccess('success', '200');

        $this->assertInternalType('object', $data);
    }

    /**
     * @test
     */
    public function testNewOrder_Failed_NoOrigin()
    {
        echo "\n *** Unit Test - Create New Order --- Failed Test Case (No input origin param) --- *** \n";

        $order = $this->generateRandomOrder();

        $params = [
            'origin' => [],
            'destination' => [strval($this->faker->latitude()), strval($this->faker->longitude())]
        ];

        //Order Service will return failure
        $this->orderServiceMock
            ->shouldReceive('createOrder')
            ->with($params['origin'], $params['destination'])
            ->andReturn(false);

        $this->orderServiceMock->error = 'INVALID_PARAMETERS';
        $this->orderServiceMock->errorCode = JsonResponse::HTTP_UNPROCESSABLE_ENTITY;

        $response = $this->call('POST', '/orders', $params);
        $data = (array) $response->getData();
        $response->assertStatus(JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        $this->assertInternalType('array', $data);
        $this->assertArrayHasKey('error', $data);
    }

    /**
     * @test
     */
    public function testNewOrder_Failed_InvalidLatLong()
    {
        echo "\n *** Unit Test - Create New Order --- Failed (Invalid lat long)--- *** \n";

        $order = $this->generateRandomOrder();

        $params = [
            'origin' => [strval($this->faker->latitude(100)), strval($this->faker->longitude())],
            'destination' => [strval($this->faker->latitude()), strval($this->faker->longitude())]
        ];

        //Order Service will return failure
        $this->orderServiceMock
            ->shouldReceive('createOrder')
            ->with($params['origin'], $params['destination'])
            ->andReturn(false);

        $this->orderServiceMock->error = 'INVALID_PARAMETERS';
        $this->orderServiceMock->errorCode = JsonResponse::HTTP_UNPROCESSABLE_ENTITY;

        $response = $this->call('POST', '/orders', $params);
        $data = (array) $response->getData();
        $response->assertStatus(JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        $this->assertInternalType('array', $data);
        $this->assertArrayHasKey('error', $data);
    }

    /**
     * @test
     */
    public function testNewOrder_Failed_Exception()
    {
        echo "\n *** Unit Test - Create New Order ---  Failed (Exception Handling) - *** \n";

        $params = [
            'origin'      => [strval($this->faker->latitude()), strval($this->faker->longitude())],
            'destination' => [strval($this->faker->latitude()), strval($this->faker->longitude())],
        ];

        //Order Service will return failure
        $this->orderServiceMock
            ->shouldReceive('createNewOrder')
            ->andThrow(
                new \InvalidArgumentException('Invalid Argument Exception')
            );

        $this->orderServiceMock->error     = 'Invalid_Argument_Exception';
        $this->orderServiceMock->errorCode = JsonResponse::HTTP_INTERNAL_SERVER_ERROR;

        $response = $this->call('POST', '/orders', $params);
        $data     = (array) $response->getData();
        $response->assertStatus(JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        $this->assertInternalType('array', $data);
        $this->assertArrayHasKey('error', $data);
    }

    /**
     * @test
     */
    public function testNewOrder_Failed_NoOrder()
    {
        echo "\n *** Unit Test - Create New Order ---  Failed(No Order Found) - *** \n";

        $params = [
            'origin'      => [strval($this->faker->latitude()), strval($this->faker->longitude())],
            'destination' => [strval($this->faker->latitude()), strval($this->faker->longitude())],
        ];

        //Order Service will return failure
        $this->orderServiceMock
            ->shouldReceive('createNewOrder')
            ->with($params['origin'], $params['destination'])
            ->andReturn(false);

        $this->orderServiceMock->error = 'INVALID_PARAMETERS';
        $this->orderServiceMock->errorCode = JsonResponse::HTTP_UNPROCESSABLE_ENTITY;

        $response = $this->call('POST', '/orders', $params);
        $data     = (array) $response->getData();

        $this->assertInternalType('array', $data);
        $this->assertArrayHasKey('error', $data);
    }

    /**
     * @test
     */
    public function testAcceptOrder_Success()
    {
        echo "\n *** Unit Test - Accept Order --- Success --- *** \n";

        $id = $this->faker->numberBetween(1, 9999);

        $order = $this->generateRandomOrder($id);

        //update order status as "UNASSIGNED"
        $order->status = Order::UNASSIGNED_ORDER_STATUS;

        $this->orderServiceMock
            ->shouldReceive('getOrderById')
            ->once()
            ->with($id)
            ->andReturn($order);

        $this->orderServiceMock
            ->shouldReceive('acceptOrder')
            ->once()
            ->with($id)
            ->andReturn(true);

        $params = ['status' => 'TAKEN'];

        $response = $this->call('PATCH', "/orders/{$id}", $params);
        $data = (array) $response->decodeResponseJson();

        $response->assertStatus(JsonResponse::HTTP_OK);

        $this->assertInternalType('array', $data);
        $this->assertArrayHasKey('status', $data);
        $this->assertEquals('SUCCESS', $data['status']);
    }

    /**
     * @test
     */
    public function testAcceptOrder_Failed_invalidParamater()
    {
        echo "\n *** Unit Test - Accept Order --- Failed (Invalid Input Parameter) --- *** \n";

        $id = $this->faker->numberBetween(1, 9999);

        $order = $this->generateRandomOrder($id);

        //In Valid order id provided
        $this->orderServiceMock
            ->shouldReceive('getOrderById')
            ->with($id)
            ->andReturn(true);

        $params = ['status' => 'ASSIGNED'];

        $response = $this->call('PATCH', "/orders/{$id}", $params);
        $data = (array) $response->decodeResponseJson();

        $response->assertStatus(JsonResponse::HTTP_UNPROCESSABLE_ENTITY);

        $this->assertInternalType('array', $data);
        $this->assertArrayHasKey('error', $data);
        $this->assertEquals('STATUS_IS_INVALID', $data['error']);
    }

    /**
     * @test
     */
    public function testAcceptOrder_Failed_invalidId()
    {
        echo "\n *** Unit Test - Accept Order --- Failed (Invalid id) --- *** \n";

        $id = $this->faker->numberBetween(499999, 999999);

        $order = $this->generateRandomOrder($id);

        //In Valid order id provided
        $this->orderServiceMock
            ->shouldReceive('getOrderById')
            ->once()
            ->with($id)
            ->andThrow(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        $params = ['status' => 'TAKEN'];

        $response = $this->call('PATCH', "/orders/{$id}", $params);
        $data = (array) $response->getData();
        $response->assertStatus(JsonResponse::HTTP_NOT_FOUND);

        $this->assertInternalType('array', $data);
        $this->assertArrayHasKey('error', $data);
        $this->assertEquals('INVALID_ID', $data['error']);
    }

    /**
     * @test
     */
    public function testAcceptOrder_Failed_AlreadyTaken()
    {
        echo "\n *** Unit Test - Accept Order --- Failed (Already Taken) --- *** \n";

        $id = $this->faker->numberBetween(1, 9999);

        $order = $this->generateRandomOrder($id);

        //status should already taken
        $order->status = Order::ASSIGNED_ORDER_STATUS;

        //In Valid order id provided
        $this->orderServiceMock
            ->shouldReceive('getOrderById')
            ->once()
            ->with($id)
            ->andReturn($order);

        $params = ['status' => 'TAKEN'];

        $response = $this->call('PATCH', "/orders/{$id}", $params);
        $data = (array) $response->decodeResponseJson();

        $response->assertStatus(JsonResponse::HTTP_CONFLICT);
        $this->assertInternalType('array', $data);
        $this->assertArrayHasKey('error', $data);
        $this->assertEquals('ORDER_ALREADY_BEEN_TAKEN', $data['error']);
    }

    /**
     * @test
     */
    public function testAcceptOrder_Failed_AlreadyTaken_Conflict()
    {
        echo "\n *** Unit Test - Accept Order --- Failed (To Accept Order) --- *** \n";

        $id = $this->faker->numberBetween(1, 9999);

        $order = $this->generateRandomOrder($id);

        $order->status = Order::UNASSIGNED_ORDER_STATUS;

        $this->orderServiceMock
            ->shouldReceive('getOrderById')
            ->once()
            ->with($id)
            ->andReturn($order);

        $this->orderServiceMock
            ->shouldReceive('acceptOrder')
            ->once()
            ->with($id)
            ->andReturn(false);

        $params = ['status' => 'TAKEN'];

        $response = $this->call('PATCH', "/orders/{$id}", $params);
        $data = (array) $response->decodeResponseJson();

        $response->assertStatus(JsonResponse::HTTP_CONFLICT);
        $this->assertInternalType('array', $data);
        $this->assertArrayHasKey('error', $data);
    }

    /**
     * @test
     */
    public function testListOrders_PositiveTestCase()
    {
        echo "\n *** Unit Test - List Orders --- Success --- *** \n";

        $page = 1;
        $limit = 5;

        $orderList = [];

        for ($i=0; $i < 5; $i++) {
            $orderList[] = $this->generateRandomOrder();
        }

        $orderRecordCollection = new \Illuminate\Database\Eloquent\Collection($orderList);

        //In Valid order id provided
        $this->orderServiceMock
            ->shouldReceive('getAll')
            ->once()
            ->andReturn($orderRecordCollection);

        $params = ['page' => $page, 'limit' => $limit];

        $response = $this->call('GET', "/orders", $params);
        $data = $response->getData();

        $response->assertStatus(JsonResponse::HTTP_OK);

        $this->assertInternalType('array', $data);

        $this->assertArrayHasKey('id', (array) $data[0]);
        $this->assertArrayHasKey('distance', (array) $data[0]);
        $this->assertArrayHasKey('status', (array) $data[0]);
    }

    /**
     * @test
     */
    public function testListOrders_PositiveTestCase_Nodata()
    {
        echo "\n *** Unit Test - List Orders - Success (No Param) *** \n";

        $page = 599999;
        $limit = 5;

        $orderRecordCollection = new \Illuminate\Database\Eloquent\Collection([]);

        //In Valid order id provided
        $this->orderServiceMock
            ->shouldReceive('getAll')
            ->once()
            ->andReturn($orderRecordCollection);

        $params = ['page' => $page, 'limit' => $limit];

        $response = $this->call('GET', "/orders", $params);

        $response->assertStatus(JsonResponse::HTTP_OK);
    }

    /**
     * @test
     */
    public function testListOrders_NegativeTestCase_inValidPage()
    {
        echo "\n *** Unit Test - List Orders --- Failed (Invalid Page Param) *** \n";

        $page = 'A';
        $limit = 5;

        $orderRecordCollection = new \Illuminate\Database\Eloquent\Collection([]);

        //In Valid order id provided
        $this->orderServiceMock
            ->shouldReceive('getAll')
            ->andReturn($orderRecordCollection);

        $params = ['page' => $page, 'limit' => $limit];

        $response = $this->call('GET', "/orders", $params);

        $response->assertStatus(JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
    }

    /**
     * @test
     */
    public function testListOrders_Failed_Exception()
    {
        echo "\n *** Unit Test - List Orders --- Failed (Exception Handling) *** \n";

        $page = 1;
        $limit = 5;

        //In Valid order id provided
        $this->orderServiceMock
            ->shouldReceive('getAll')
            ->andThrow(
                new \InvalidArgumentException()
            );

        $this->orderServiceMock->error     = 'Invalid_Argument_Exception';
        $this->orderServiceMock->errorCode = JsonResponse::HTTP_INTERNAL_SERVER_ERROR;

        $params = ['page' => $page, 'limit' => $limit];

        $response = $this->call('GET', "/orders", $params);

        $data     = (array) $response->getData();
        $response->assertStatus(JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        $this->assertInternalType('array', $data);
        $this->assertArrayHasKey('error', $data);
    }

    /**
     * @param int|null $id
     *
     * @return Order
     */
    private function generateRandomOrder($id = null)
    {
        $id = $id?:$this->faker->randomDigit();

        $order = new Order();
        $order->id = $id;
        $order->status = $this->faker->randomElement(self::$allowedOrderStatus);
        $order->distance_id = $this->faker->randomDigit();
        $order->total_distance = $this->faker->numberBetween(1000, 9999);
        $order->created_at = $this->faker->dateTimeBetween();
        $order->updated_at = $this->faker->dateTimeBetween();

        return $order;
    }
}
