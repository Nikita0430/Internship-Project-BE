<?php

namespace Tests\Feature;

use App\Models\Clinic;
use App\Models\Reactor;
use App\Models\ReactorCycle;
use App\Models\User;
use App\Services\ReactorCycle\ReactorCycleService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Response;
use Tests\TestCase;
use Mockery\MockInterface;
use Exception;

class AddReactorCycleTest extends TestCase
{
    use DatabaseTransactions, WithFaker;

    public function test_store_with_valid_inputs()
    {
        User::factory()->create([
            'email' => 'addcycle@example.com',
            'password' => Hash::make('addcyclepassword'),
            'role' => 'admin'
        ]);
        $loginBody = [
            'email' =>'addcycle@example.com',
            'password' => 'addcyclepassword'
        ];
        $loginResponse = $this->post('/api/login', $loginBody);
        $token = $loginResponse['token'];
        $headers = ['Authorization' => "Bearer $token"];

        $data = [
            'cycle_name' => 'ReactorCycleName',
            'reactor_name' => 'ReactorName',
            'mass' => 120.50,
            'target_start_date' => date('Y-m-d', strtotime('today')),
            'expiration_date' => date('Y-m-d', strtotime('tomorrow'))
        ];

        $response = $this->post('/api/reactor-cycles', $data, $headers);

        $response->assertOk();
    }

    public function test_store_with_invalid_inputs()
    {
        User::factory()->create([
            'email' => 'addcycle@example.com',
            'password' => Hash::make('addcyclepassword'),
            'role' => 'admin'
        ]);
        $loginBody = [
            'email' =>'addcycle@example.com',
            'password' => 'addcyclepassword'
        ];
        $loginResponse = $this->post('/api/login', $loginBody);
        $token = $loginResponse['token'];
        $headers = ['Authorization' => "Bearer $token"];

        $data = [];

        $response = $this->post('/api/reactor-cycles', $data, $headers);

        $response->assertUnprocessable();

        $json = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('message', $json);
        $this->assertArrayHasKey('errors', $json);
        $this->assertArrayHasKey('cycle_name', $json['errors']);
        $this->assertArrayHasKey('reactor_name', $json['errors']);
        $this->assertArrayHasKey('mass', $json['errors']);
        $this->assertArrayHasKey('target_start_date', $json['errors']);
        $this->assertArrayHasKey('expiration_date', $json['errors']);
    }

    public function test_store_request_made_by_clinic_user()
    {
        $user = User::factory()->create([
            'email' => 'addcycle@example.com',
            'password' => Hash::make('addcyclepassword'),
            'role' => 'clinic'
        ]);
        Clinic::create([
            'account_id' => 'C999999',
            'name' => $this->faker->name,
            'address' => $this->faker->address,
            'city' => $this->faker->city,
            'state' => $this->faker->state(),
            'zipcode' => $this->faker->postcode,
            'user_id' => $user['id'],
            'is_enabled' => true
        ]);
        $loginBody = [
            'email' =>'addcycle@example.com',
            'password' => 'addcyclepassword'
        ];
        $loginResponse = $this->post('/api/login', $loginBody);
        $token = $loginResponse['token'];
        $headers = ['Authorization' => "Bearer $token"];

        $data = [
            'cycle_name' => 'ReactorCycleName',
            'reactor_name' => 'ReactorName',
            'mass' => 120.50,
            'target_start_date' => date('Y-m-d', strtotime('today')),
            'expiration_date' => date('Y-m-d', strtotime('tomorrow'))
        ];

        $response = $this->post('/api/reactor-cycles', $data, $headers);

        $response->assertUnauthorized();
        $response->assertExactJson([
           'message' => 'Unauthorized'
        ]);
    }
    
    public function test_store_with_exception()
    {
        User::factory()->create([
            'email' => 'addcycle@example.com',
            'password' => Hash::make('addcyclepassword'),
            'role' => 'admin'
        ]);
        $loginBody = [
            'email' =>'addcycle@example.com',
            'password' => 'addcyclepassword'
        ];
        $loginResponse = $this->post('/api/login', $loginBody);
        $token = $loginResponse['token'];
        $headers = ['Authorization' => "Bearer $token"];

        $this->mock(ReactorCycleService::class, function ($mock) {
            $mock->shouldReceive('addReactorCycle')
                ->andThrow(new \Exception('Service exception'));
        });

        $data = [
            'cycle_name' => 'ReactorCycleName',
            'reactor_name' => 'ReactorName',
            'mass' => 120.50,
            'target_start_date' => date('Y-m-d', strtotime('today')),
            'expiration_date' => date('Y-m-d', strtotime('tomorrow'))
        ];

        $response = $this->post('/api/reactor-cycles', $data, $headers);

        $response->assertStatus(Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    public function testAddReactorCycleNameTaken()
    {
        User::factory()->create([
            'email' => 'editcycle@example.com',
            'password' => Hash::make('editcyclepassword'),
            'role' => 'admin'
        ]);
        $loginBody = [
            'email' =>'editcycle@example.com',
            'password' => 'editcyclepassword'
        ];
        $loginResponse = $this->post('/api/login', $loginBody);
        $token = $loginResponse['token'];
        $headers = ['Authorization' => "Bearer $token"];

        $reactor = Reactor::firstOrCreate([
            'name' => 'ExampleReactor'
        ]);

        $data = [
            'cycle_name' => 'ReactorCycle1',
            'reactor_name' => "Reactor1",
            'reactor_id' => $reactor->id,
            'mass' => 120.50,
            'target_start_date' => date('Y-m-d', strtotime('today')),
            'expiration_date' => date('Y-m-d', strtotime('tomorrow'))
        ];

        $response = $this->post('/api/reactor-cycles/', $data, $headers);

        $response->assertBadRequest();
        $response->assertStatus(400)
            ->assertJson([
                'message' => 'Cycle Name has already been taken',
            ]);
    }

    public function testUpdateReactorCycleNameTaken()
    {
        User::factory()->create([
            'email' => 'editcycle@example.com',
            'password' => Hash::make('editcyclepassword'),
            'role' => 'admin'
        ]);
        $loginBody = [
            'email' =>'editcycle@example.com',
            'password' => 'editcyclepassword'
        ];
        $loginResponse = $this->post('/api/login', $loginBody);
        $token = $loginResponse['token'];
        $headers = ['Authorization' => "Bearer $token"];

        $reactor = Reactor::firstOrCreate([
            'name' => 'ExampleReactor'
        ]);

        $data = [
            'cycle_name' => 'ReactorCycle1',
            'reactor_name' => "Reactor1",
            'reactor_id' => $reactor->id,
            'mass' => 120.50,
            'target_start_date' => date('Y-m-d', strtotime('today')),
            'expiration_date' => date('Y-m-d', strtotime('tomorrow'))
        ];

        $cycle = ReactorCycle::create([
            'name' => 'ExampleCycle',
            'reactor_id' => $reactor->id,
            'mass' => 120.50,
            'target_start_date' => date('Y-m-d', strtotime('-1 months')),
            'expiration_date' => date('Y-m-d', strtotime('-1 months +7 days'))
        ]);

        $response = $this->put('/api/reactor-cycles/'.$cycle->id, $data, $headers);

        $response->assertBadRequest();
        $response->assertStatus(400)
            ->assertJson([
                'message' => 'Cycle Name has already been taken',
            ]);
    }

    public function test_update_with_exception()
    {
        User::factory()->create([
            'email' => 'addcycle@example.com',
            'password' => Hash::make('addcyclepassword'),
            'role' => 'admin'
        ]);
        $loginBody = [
            'email' =>'addcycle@example.com',
            'password' => 'addcyclepassword'
        ];
        $loginResponse = $this->post('/api/login', $loginBody);
        $token = $loginResponse['token'];
        $headers = ['Authorization' => "Bearer $token"];

        $mock = $this->mock(ReactorCycleService::class, function (MockInterface $mock) {
            $mock->shouldReceive('update')->andThrow(new Exception);
        });

        $data = [
            'cycle_name' => 'ReactorCycleName',
            'reactor_name' => 'ReactorName',
            'mass' => 120.50,
            'target_start_date' => date('Y-m-d', strtotime('today')),
            'expiration_date' => date('Y-m-d', strtotime('tomorrow'))
        ];

        $response = $this->put('/api/reactor-cycles/1', $data, $headers);

        $response->assertInternalServerError();
        $response->assertExactJson([
            'message' => 'Something went wrong.'
        ]);
        $mock->mockery_teardown();
    }

    public function test_show_with_exception()
    {
        User::factory()->create([
            'email' => 'addcycle@example.com',
            'password' => Hash::make('addcyclepassword'),
            'role' => 'admin'
        ]);
        $loginBody = [
            'email' =>'addcycle@example.com',
            'password' => 'addcyclepassword'
        ];
        $loginResponse = $this->post('/api/login', $loginBody);
        $token = $loginResponse['token'];
        $headers = ['Authorization' => "Bearer $token"];

        $mock = $this->mock(ReactorCycleService::class, function (MockInterface $mock) {
            $mock->shouldReceive('show')->andThrow(new Exception);
        });

        $response = $this->get('/api/reactor-cycles/1', $headers);

        $response->assertInternalServerError();
        $response->assertExactJson([
            'message' => 'Something went wrong.'
        ]);
        $mock->mockery_teardown();
    }
}
