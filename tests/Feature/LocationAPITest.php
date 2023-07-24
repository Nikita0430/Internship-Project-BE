<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\Location\LocationAPIService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class LocationAPITest extends TestCase
{
    use DatabaseTransactions;
    public function testGetLocationsSuccess()
    {
        User::factory()->create([
            'email' => 'listreactorcycle@example.com',
            'password' => Hash::make('listreactorcyclepassword'),
            'role' => 'admin'
        ]);
        $loginBody = [
            'email' =>'listreactorcycle@example.com',
            'password' => 'listreactorcyclepassword'
        ];
        $loginResponse = $this->post('/api/login', $loginBody);
        $token = $loginResponse['token'];
        $headers = ['Authorization' => "Bearer $token"];

        Http::fake([
            '*' => Http::response([
                'results' => [
                    ['name' => 'Location 1'],
                    ['name' => 'Location 2'],
                    ['name' => 'Location 3'],
                ]
            ], 200),
        ]);
        $response = $this->withHeaders($headers)->get('/api/get-locations', ['query' => 'sample']);
        $response->assertOk();
        $response->assertJson(function (AssertableJson $json) {
            $json->has('results', 3)
                ->where('results.0.name', 'Location 1')
                ->where('results.1.name', 'Location 2')
                ->where('results.2.name', 'Location 3');
        });
    }

    public function testGetLocationsFailure()
    {
        User::factory()->create([
            'email' => 'listreactorcycle@example.com',
            'password' => Hash::make('listreactorcyclepassword'),
            'role' => 'admin'
        ]);
        $loginBody = [
            'email' =>'listreactorcycle@example.com',
            'password' => 'listreactorcyclepassword'
        ];
        $loginResponse = $this->post('/api/login', $loginBody);
        $token = $loginResponse['token'];
        $headers = ['Authorization' => "Bearer $token"];

        Http::fake([
            '*' => Http::response([], 500),
        ]);
        $response = $this->withHeaders($headers)->get('/api/get-locations', ['query' => '']);
        $response->assertStatus(500);
        $response->assertJson([
            'error' => 'Failed to fetch geocoding data',
        ]);
    }

    public function testInternalServerErrorOnException()
    {
        User::factory()->create([
            'email' => 'listreactorcycle@example.com',
            'password' => Hash::make('listreactorcyclepassword'),
            'role' => 'admin'
        ]);
        $loginBody = [
            'email' =>'listreactorcycle@example.com',
            'password' => 'listreactorcyclepassword'
        ];
        $loginResponse = $this->post('/api/login', $loginBody);
        $token = $loginResponse['token'];
        $headers = ['Authorization' => "Bearer $token"];
        
        // Mock the LocationService and throw an exception
        $this->mock(LocationAPIService::class, function ($mock) {
            $mock->shouldReceive('getLocations')
                ->andThrow(new \Exception('Something went wrong.'));
        });

        // Make a request to the controller action
        $response = $this->get('/api/get-locations', ['query' => 'New York']);

        // Assert the response status code and JSON structure
        $response->assertStatus(Response::HTTP_INTERNAL_SERVER_ERROR)
            ->assertJson([
                'message' => 'Something went wrong.'
            ]);
    }
}
