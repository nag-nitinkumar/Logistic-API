<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use App\Http\Models\Order;
use App\Http\Models\Distance;
use Illuminate\Http\JsonResponse;

class OrderServiceTest extends Tests\TestCase
{
    use WithoutMiddleware;

    protected $distanceHelper;

    protected static $orderStatus = [
        Order::UNASSIGNED_ORDER_STATUS,
        Order::ASSIGNED_ORDER_STATUS,
    ];

    protected function setUp()
    {
        parent::setUp();
        $this->faker = Faker\Factory::create();
        $this->distanceHelper = $this->createMock(\App\Helpers\DistanceHelper::class);
        $this->orderRepository = $this->createMock(\App\Repositories\OrderRepository::class);
        $this->distanceRepository = $this->createMock(\App\Repositories\DistanceRepository::class);
    }

    /**
     * @test
     */
    public function testCreateNewOrder_PositiveTestCase()
    {
        echo "\n *** Unit Test - Create New Order Service - Success (Valid Coordinates )*** \n";

        $distanceCoordinates = $this->generateGeoCordinates();
        $order = $this->generateDummyOrder();

        $origin =  $distanceCoordinates['origin'];
        $destination = $distanceCoordinates['destination'];

        $this->distanceHelper->method('getDistance')->with(
            $origin,
            $destination
        )->willReturn($distanceCoordinates['distance']);

        $distanceId = $this->faker->randomDigit();

        $params = [
            'status' => Order::UNASSIGNED_ORDER_STATUS,
            'distance_id' => $distanceId,
            'total_distance' => $distanceCoordinates['distance']
        ];

        $distance = new Distance();
        $distance->id = $distanceId;
        $distance->total_distance = $distanceCoordinates['distance'];
        $distance->start_lat = $origin[0];
        $distance->start_long = $origin[1];
        $distance->end_lat = $destination[0];
        $distance->end_long = $destination[1];

        $this->distanceRepository->method('getDistance')->with($origin, $destination)->willReturn($distance);

        $this->orderRepository->method('create')->with($params)->willReturn($order);

        $orderService = new \App\Http\Services\OrderService($this->orderRepository, $this->distanceRepository, $this->distanceHelper);

        $this->assertInstanceOf('\App\Http\Models\Order', $orderService->createNewOrder($origin, $destination));
    }

    /**
     * @test
     */
    public function testCreateOrder_NegativeCase_InvalidCoordinates()
    {
        echo "\n *** Unit Test - Create New Order Service - Failed (Invalid coordinates) *** \n";

        $distanceCoordinates = $this->generateGeoCordinates();

        $origin = implode(',', $distanceCoordinates['origin']);
        $destination = implode(',', $distanceCoordinates['destination']);

        $orderService = new \App\Http\Services\OrderService($this->orderRepository, $this->distanceRepository, $this->distanceHelper);

        $this->assertEquals(false, $orderService->createNewOrder($distanceCoordinates['origin'], $distanceCoordinates['destination']));
    }

    /**
     * @test
     */
    public function testCreateOrder_Negative_InValidDistanceCal()
    {
        echo "\n *** Unit Test - Create New Order Service - Failed (No Response from Google API) *** \n";

        $distanceCoordinates = $this->generateGeoCordinates();

        $origin = $distanceCoordinates['origin'];
        $destination = $distanceCoordinates['destination'];

        $this->distanceHelper->method('getDistance')->with(
            $origin,
            $destination
        )->willReturn('GOOGLE_API_NULL_RESPONSE');

        $orderService = new \App\Http\Services\OrderService($this->orderRepository, $this->distanceRepository, $this->distanceHelper);

        $this->assertEquals(false, $orderService->createNewOrder(
            $origin,
            $destination
        ));
    }

    /**
     * @test
     */
    public function testCreateNewDistance_Success()
    {
        echo "\n *** Unit Test - Create New Distance Service - Success (Valid Distance)*** \n";

        $distanceCoordinates = $this->generateGeoCordinates();
        $order = $this->generateDummyOrder();

        $origin =  $distanceCoordinates['origin'];
        $destination = $distanceCoordinates['destination'];

        $this->distanceHelper->method('getDistance')->with(
            $origin,
            $destination
        )->willReturn($distanceCoordinates['distance']);

        $distanceId = $this->faker->randomDigit();

        $params = [
            'status' => Order::UNASSIGNED_ORDER_STATUS,
            'distance_id' => $distanceId,
            'total_distance' => $distanceCoordinates['distance']
        ];

        $distance = new Distance();
        $distance->id = $distanceId;
        $distance->total_distance = $distanceCoordinates['distance'];
        $distance->start_lat = $origin[0];
        $distance->start_long = $origin[1];
        $distance->end_lat = $destination[0];
        $distance->end_long = $destination[1];

        $this->distanceRepository->method('getDistance')->with($origin, $destination)->willReturn(null);

        $distanceParam = [
            'start_lat' => $origin[0],
            'start_long' => $origin[1],
            'end_lat' => $destination[0],
            'end_long' => $destination[1],
            'total_distance' => $distanceCoordinates['distance'],
        ];
        $this->distanceRepository->method('create')->with(
            $distanceParam
        )->willReturn($distance);

        $this->orderRepository->method('create')->with($params)->willReturn($order);

        $orderService = new \App\Http\Services\OrderService($this->orderRepository, $this->distanceRepository, $this->distanceHelper);

        $this->assertInstanceOf('\App\Http\Models\Order', $orderService->createNewOrder($origin, $destination));
    }

    /**
     * @test
     */
    public function testCreateNewDistance_Failed()
    {
        echo "\n *** Unit Test - Create New Distance Service  - Failed*** \n";

        $distanceCoordinates = $this->generateGeoCordinates();
        $order = $this->generateDummyOrder();

        $origin =  $distanceCoordinates['origin'];
        $destination = $distanceCoordinates['destination'];

        $this->distanceHelper->method('getDistance')->with(
            $origin,
            $destination
        )->willReturn($distanceCoordinates['distance']);

        $distanceId = $this->faker->randomDigit();

        $params = [
            'status' => Order::UNASSIGNED_ORDER_STATUS,
            'distance_id' => $distanceId,
            'total_distance' => $distanceCoordinates['distance']
        ];

        $distance = new Distance();
        $distance->id = $distanceId;
        $distance->total_distance = $distanceCoordinates['distance'];
        $distance->start_lat = $origin[0];
        $distance->start_long = $origin[1];
        $distance->end_lat = $destination[0];
        $distance->end_long = $destination[1];

        $this->distanceRepository->method('getDistance')->with($origin, $destination)->willReturn(null);

        $distanceParam = [
            'start_lat' => $origin[0],
            'start_long' => $origin[1],
            'end_lat' => $destination[0],
            'end_long' => $destination[1],
            'total_distance' => $distanceCoordinates['distance'],
        ];
        $this->distanceRepository->method('create')->with(
            $distanceParam
        )->willReturn(null);

        $this->error = 'INVALID_PARAMETERS';
        $this->errorCode = JsonResponse::HTTP_UNPROCESSABLE_ENTITY;

        $this->orderRepository->method('create')->with($params)->willReturn($order);

        $orderService = new \App\Http\Services\OrderService($this->orderRepository, $this->distanceRepository, $this->distanceHelper);

        $this->assertEquals(false, $orderService->createNewOrder($origin, $destination));
    }

    /**
     * @test
     */
    public function testGetList()
    {
        $order = $this->generateDummyOrder();

        $orderRecordCollection = new \Illuminate\Database\Eloquent\Collection($order);

        //Order Service will return success
        $this->orderRepository
            ->method('all')
            ->with(1, 5)
            ->willReturn($orderRecordCollection);

        $orderService = new \App\Http\Services\OrderService($this->orderRepository, $this->distanceRepository, $this->distanceHelper);

        echo "\n *** Unit Test - List Orders Service - Success (With Valid page=1 and limit=5) *** \n";
        $response = $orderService->getAll(1, 5);

        echo "\n \t Response Type should be an array\n";
        $this->assertEquals($orderRecordCollection, $response);

        echo "\n \t Response should count less than or equal to 5\n";
        $this->assertLessThanOrEqual(5, count($response));
    }

    /**
     * @test
     */
    public function testAcceptOrder()
    {
        $id = $this->faker->numberBetween(1, 9999);

        $order = $this->generateDummyOrder($id);

        $orderRecordCollection = new \Illuminate\Database\Eloquent\Collection($order);

        //Order Service will return success
        $this->orderRepository
            ->method('acceptOrder')
            ->with($id)
            ->willReturn($orderRecordCollection);

        $orderService = new \App\Http\Services\OrderService($this->orderRepository, $this->distanceRepository, $this->distanceHelper);

        echo "\n *** Unit Test - Accept Order Service - Success *** \n";
        $response = $orderService->acceptOrder($id);

        echo "\n \t Response Type should be a Collection\n";
        $this->assertEquals($orderRecordCollection, $response);
    }

    /**
     * @test
     */
    public function testGetOrderById()
    {
        $id = $this->faker->numberBetween(1, 9999);

        $order = $this->generateDummyOrder($id);

        //Order Service will return success
        $this->orderRepository
            ->method('find')
            ->with($id)
            ->willReturn($order);

        $orderService = new \App\Http\Services\OrderService($this->orderRepository, $this->distanceRepository, $this->distanceHelper);

        echo "\n *** Unit Test - GetOrderById Service Method Test - Success *** \n";
        $response = $orderService->getOrderById($id);

        echo "\n \t Response Type should be a Order\n";
        $this->assertEquals($order, $response);
    }

    /**
     * @return array
     */
    protected function generateGeoCordinates()
    {
        $faker = Faker\Factory::create();

        $initialLatitude = $faker->latitude();
        $initialLongitude = $faker->latitude();
        $finalLatitude = $faker->longitude();
        $finalLongitude = $faker->longitude();

        $distance = $this->distance($initialLatitude, $initialLongitude, $finalLatitude, $finalLongitude);

        return [
            'origin' => [$initialLatitude, $initialLongitude],
            'destination' => [$finalLatitude, $finalLongitude],
            'distance' => $distance
        ];
    }

    /**
     * @param float $lat1
     * @param float $lon1
     * @param float $lat2
     * @param float $lon2
     *
     * @return int
     */
    public function distance($lat1, $lon1, $lat2, $lon2)
    {
        $theta = $lon1 - $lon2;
        $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
        $dist = acos($dist);
        $dist = rad2deg($dist);
        $distanceInMetre = $dist * 60 * 1.1515 * 1.609344 * 1000;

        return (int) $distanceInMetre;
    }

    /**
     * @param int|null $id
     *
     * @return Order
     */
    private function generateDummyOrder($id = null)
    {
        $id = $id?:$this->faker->randomDigit();
        $order = new Order();
        $order->id = $id;
        $order->status = $this->faker->randomElement(self::$orderStatus);
        $order->distance = $this->faker->randomDigit();
        $order->created_at = $this->faker->dateTimeBetween();
        $order->updated_at = $this->faker->dateTimeBetween();
        return $order;
    }
}
