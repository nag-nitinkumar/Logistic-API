<?php

namespace App\Test\Feature\ApiController;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\JsonResponse;

class OrderIntegrationTest extends TestCase
{
    public function testNewOrderCreateIncorrectParameters()
    {
        echo "\n *** Starting Integration Test Cases *** \n";

        echo "\n > Create New Order - Failed Test - With Invalid Parameter Keys - Should get 422 - Unprocessable Entity";

        $invalidData1 = [
            'origin1' => ['28.704060', '77.102493'],
            'destination' => ['28.535517','77.391029'],
        ];

        $response = $this->json('POST', '/orders', $invalidData1);

        $response->assertStatus(422);
    }

    public function testNewOrderCreateEmptyParameters()
    {
        echo "\n\n > Create New Order - Failed Test - With Empty Parameter - Should get 422";

        $invalidData1 = [
            'origin' => ['28.704060', ''],
            'destination' => [
                '28.535517',
                '77.391029',
            ],
        ];

        $response = $this->json('POST', '/orders', $invalidData1);

        $response->assertStatus(422);
    }


    public function testNewOrderCreateAdditionalParameters()
    {
        echo "\n\n > Create New Order - Failed Test - With Additional Parameter - Should get 422";

        $invalidData1 = [
            'origin' => ['28.704060', '77.391029', '28.123435'],
            'destination' => [
                '28.535517',
                '77.391029',
            ],
        ];

        $response = $this->json('POST', '/orders', $invalidData1);

        $response->assertStatus(422);
    }


    public function testOrderCreateInvalidData()
    {
        echo "\n\n > Create New Order - Failed Test - Invalid Data - should get 422";
        $invalidData = [
            'origin' => ['100.968046', '44.968046'],
            'destination' => [
                '28.535517',
                '77.391029',
            ],
        ];

        $response = $this->json('POST', '/orders', $invalidData);

        $response->assertStatus(422);
    }

    public function testOrderCreationPositiveScenario()
    {
        echo "\n\n > Create New Order - Success Test - Valid Data - should have status 200";

        $validData = [
            'origin' => ['28.704061', '77.102493'],
            'destination' => [
                '28.535517',
                '77.391029',
            ],
        ];

        $response = $this->json('POST', '/orders', $validData);
        $data = (array) $response->getData();

        $response->assertStatus(200);

        echo "\n\t > Response should have order details - id, status and distance";
        $this->assertArrayHasKey('id', $data);
        $this->assertArrayHasKey('status', $data);
        $this->assertArrayHasKey('distance', $data);
    }

    public function testAssignOrder()
    {
        echo "\n \n \n*** Executing Accept Order Scenario (Success and Failed) *** \n";

        echo "\n > Accept Order - Success - Valid Data \n";

        echo "\n > Creating an new order first to test update ";
        $validData = [
            'origin' => ['28.704060', '77.102493'],
            'destination' => [
                '28.535517',
                '77.391029',
            ],
        ];

        $updateData = ['status' => 'TAKEN'];

        $response = $this->json('POST', '/orders', $validData);
        $data = (array) $response->getData();
        $orderId = $data['id'];

        echo "\n > Order has been created with id : ".$orderId;

        echo "\n\n > Update Order - Success Test - Valid Params - should have status 200 & `status` key";
        $response = $this->json('PATCH', '/orders/'. $orderId, $updateData);
        $data = (array) $response->getData();
        $response->assertStatus(200);
        $this->assertArrayHasKey('status', $data);

        echo "\n\n > Update Order - Failed Test - For Already updated order & response should has key `error`";

        $updateData = ['status' => 'TAKEN'];
        $response = $this->json('PATCH', '/orders/'. $orderId, $updateData);
        $data = (array) $response->getData();
        $response->assertStatus(409);
        $this->assertArrayHasKey('error', $data);

        echo "\n\n > Order Update - Failed Test - Invalid Params key 'statsu'";
        $this->orderUpdateFailureInvalidParams($orderId, ['statsu' => 'TAKEN'], $expectedCode = 422);

        echo "\n\n > Order Update - Failed Test - Invalid Param value (TEKEN)";
        $this->orderUpdateFailureInvalidParams($orderId, ['status' => 'TEKEN'], $expectedCode = 422);

        echo "\n\n > Order Update - Failed Test - Empty Param value";
        $this->orderUpdateFailureInvalidParams($orderId, ['status' => ''], $expectedCode = 422);

        echo "\n\n > Order Update - Failed Test - Invalid Order id";
        $this->orderUpdateFailureInvalidParams(9999999, ['status' => 'TAKEN'], $expectedCode = 404);
    }

    protected function orderUpdateFailureInvalidParams($orderId, $params, $expectedCode)
    {
        $response = $this->json('PATCH', '/orders/'. $orderId, $params);
        $data = (array) $response->getData();

        $response->assertStatus($expectedCode);

        $this->assertArrayHasKey('error', $data);
    }

    public function testOrderListSuccessCount()
    {
        echo "\n \n \n*** Executing List Orders Scenario (Success and Failed) *** \n";

        echo "\n > List Orders - Success Test - Valid Data Count(page=1&limit=4) - Should have less than or equal to 4";

        $query = 'page=1&limit=4';
        $response = $this->json('GET', "/orders?$query", []);
        $data = (array) $response->getData();

        $response->assertStatus(200);

        $this->assertLessThan(5, count($data));
    }

    public function testOrderListSuccessData()
    {
        echo "\n\n > List Orders - Success Test - Valid Data Keys (page=1&limit=4) - Status should be 200";

        $query = 'page=1&limit=4';
        $response = $this->json('GET', "/orders?$query", []);
        $data = (array) $response->getData();

        $response->assertStatus(200);

        foreach ($data as $order) {
            $order = (array) $order;
            $this->assertArrayHasKey('id', $order);
            $this->assertArrayHasKey('distance', $order);
            $this->assertArrayHasKey('status', $order);
        }
    }


    public function testOrderListSuccessNoData()
    {
        echo "\n\n > List Orders - Success Test - Valid Data Keys (page=99999&limit=4) - Should return blank array & 200 Status";

        $query = 'page=99999&limit=4';
        $response = $this->json('GET', "/orders?$query", []);
        $data = (array) $response->getData();

        $response->assertStatus(200);
    }

    public function testOrderListFailure()
    {
        echo "\n > List Orders - Failed Test - Invalid Params (page1) - Should get 422\n";
        $query = 'page1=1&limit=4';
        $this->orderListFailure($query, 422);

        echo "\n > List Orders - Failed Test - Invalid Params (limit1) - Should get 422\n";
        $query = 'page=1&limit1=4';
        $this->orderListFailure($query, 422);

        echo "\n > List Orders - Failed Test - Invalid Params Value (page = 0) - Should get 422\n";
        $query = 'page=0&limit=4';
        $this->orderListFailure($query, 422);

        echo "\n > List Orders - Failed Test - Invalid Params Value (limit = 0) - Should get 422\n";
        $query = 'page=1&limit=0';
        $this->orderListFailure($query, 422);

        echo "\n > List Orders - Failed Test - Invalid Params Value (limit = -1) - Should get 422\n";
        $query = 'page=1&limit=-1';
        $this->orderListFailure($query, 422);

        echo "\n > List Orders - Failed Test - Invalid Params Value (page = -1) - Should get 422\n";
        $query = 'page=-1&limit=0';
        $this->orderListFailure($query, 422);
    }

    protected function orderListFailure($query, $expectedCode)
    {
        $response = $this->json('GET', "/orders?$query", []);
        $data = (array) $response->getData();

        $response->assertStatus($expectedCode);
    }
}
