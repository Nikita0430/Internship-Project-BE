<?php

namespace Tests\Feature;

use App\Models\Clinic;
use App\Models\Reactor;
use App\Models\ReactorCycle;
use App\Models\User;
use App\Services\ReactorCycle\ReactorCycleService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Mockery\MockInterface;
use Exception;
use Tests\TestCase;

class EditReactorCycleTest extends TestCase
{
    use DatabaseTransactions, WithFaker;

    public function test_show_request_with_valid_inputs()
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
        
        $cycle = ReactorCycle::create([
            'name' => 'ExampleCycle',
            'reactor_id' => $reactor->id,
            'mass' => 120.50,
            'target_start_date' => date('Y-m-d', strtotime('today')),
            'expiration_date' => date('Y-m-d', strtotime('tomorrow'))
        ]);

        $response = $this->get('/api/reactor-cycles/'.$cycle->id, $headers);

        $response->assertOk();
    }

    public function test_show_request_made_by_clinic_user()
    {
        $user = User::factory()->create([
            'email' => 'editcycle@example.com',
            'password' => Hash::make('editcyclepassword'),
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
            'email' =>'editcycle@example.com',
            'password' => 'editcyclepassword'
        ];
        $loginResponse = $this->post('/api/login', $loginBody);
        $token = $loginResponse['token'];
        $headers = ['Authorization' => "Bearer $token"];

        $reactor = Reactor::firstOrCreate([
            'name' => 'ExampleReactor'
        ]);
        
        $cycle = ReactorCycle::create([
            'name' => 'ExampleCycle',
            'reactor_id' => $reactor->id,
            'mass' => 120.50,
            'target_start_date' => date('Y-m-d', strtotime('today')),
            'expiration_date' => date('Y-m-d', strtotime('tomorrow'))
        ]);

        $response = $this->get('/api/reactor-cycles/'.$cycle->id, $headers);

        $response->assertUnauthorized();
        $response->assertExactJson([
            'message' => 'Unauthorized'
        ]);
    }

    public function test_show_request_not_found()
    {
        $user = User::factory()->create([
            'email' => 'editcycle@example.com',
            'password' => Hash::make('editcyclepassword'),
            'role' => 'admin'
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
            'email' =>'editcycle@example.com',
            'password' => 'editcyclepassword'
        ];
        $loginResponse = $this->post('/api/login', $loginBody);
        $token = $loginResponse['token'];
        $headers = ['Authorization' => "Bearer $token"];

        $reactor = Reactor::firstOrCreate([
            'name' => 'ExampleReactor'
        ]);
        
        $cycle = ReactorCycle::create([
            'name' => 'ExampleCycle',
            'reactor_id' => $reactor->id,
            'mass' => 120.50,
            'target_start_date' => date('Y-m-d', strtotime('today')),
            'expiration_date' => date('Y-m-d', strtotime('tomorrow'))
        ]);

        $incorrectId = '9000';
        $response = $this->get('/api/reactor-cycles/'.$incorrectId, $headers);

        $response->assertNotFound();
    }

    public function test_update_request_with_changed_expiration_date()
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

        $data = [
            'cycle_name' => 'newExampleCycle',
            'reactor_name' => 'NewExampleReactor',
            'mass' => 115,
            'target_start_date' => date('Y-m-d', strtotime('-1 months')),
            'expiration_date' => date('Y-m-d', strtotime('tomorrow')),
        ];

        $reactor = Reactor::firstOrCreate([
            'name' => 'ExampleReactor'
        ]);
        
        $cycle = ReactorCycle::create([
            'name' => 'ExampleCycle',
            'reactor_id' => $reactor->id,
            'mass' => 120.50,
            'target_start_date' => date('Y-m-d', strtotime('-1 months')),
            'expiration_date' => date('Y-m-d', strtotime('-1 months +7 days'))
        ]);

        $response = $this->put('/api/reactor-cycles/'.$cycle->id, $data, $headers);

        $response->assertOk();
    }

    public function test_update_request_with_changed_target_start_date()
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

        $data = [
            'cycle_name' => 'newExampleCycle',
            'reactor_name' => 'NewExampleReactor',
            'mass' => 115,
            'target_start_date' => date('Y-m-d', strtotime('today')),
            'expiration_date' => date('Y-m-d', strtotime('tomorrow')),
        ];

        $reactor = Reactor::firstOrCreate([
            'name' => 'ExampleReactor'
        ]);
        
        $cycle = ReactorCycle::create([
            'name' => 'ExampleCycle',
            'reactor_id' => $reactor->id,
            'mass' => 120.50,
            'target_start_date' => date('Y-m-d', strtotime('-1 months')),
            'expiration_date' => date('Y-m-d', strtotime('-1 months +7 days'))
        ]);

        $response = $this->put('/api/reactor-cycles/'.$cycle->id, $data, $headers);

        $response->assertOk();
    }

    public function test_update_status_request_with_exception()
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

        $this->mock(ReactorCycleService::class, function ($mock) {
            $mock->shouldReceive('updateStatus')
                ->andThrow(new \Exception('Service exception'));
        });

        $data = [
            'is_enabled' => false,
        ];

        $reactor = Reactor::firstOrCreate([
            'name' => 'ExampleReactor'
        ]);
        
        $cycle = ReactorCycle::create([
            'name' => 'ExampleCycle',
            'reactor_id' => $reactor->id,
            'mass' => 120.50,
            'target_start_date' => date('Y-m-d', strtotime('-1 months')),
            'expiration_date' => date('Y-m-d', strtotime('-1 months +7 days'))
        ]);

        $response = $this->patch('/api/reactor-cycles/'.$cycle->id.'/status', $data, $headers);
        $response->assertStatus(Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    public function test_update_with_invalid_inputs()
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

        $data = [
            'cycle_name' => '',
            'reactor_name' => '',
            'mass' => 115,
            'target_start_date' => date('Y-m-d', strtotime('-1 months +1 days')),
            'expiration_date' => date('Y-m-d', strtotime('-1 months +7 days')),
        ];

        $reactor = Reactor::firstOrCreate([
            'name' => 'ExampleReactor'
        ]);
        
        $cycle = ReactorCycle::create([
            'name' => 'ExampleCycle',
            'reactor_id' => $reactor->id,
            'mass' => 120.50,
            'target_start_date' => date('Y-m-d', strtotime('-1 months')),
            'expiration_date' => date('Y-m-d', strtotime('-1 months +7 days'))
        ]);

        $response = $this->put('/api/reactor-cycles/'.$cycle->id, $data, $headers);

        $response->assertUnprocessable();

        $json = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('message', $json);
        $this->assertArrayHasKey('errors', $json);
        $this->assertArrayHasKey('cycle_name', $json['errors']);
        $this->assertArrayHasKey('reactor_name', $json['errors']);
    }


    public function test_update_request_made_by_clinic_user()
    {
        $user = User::factory()->create([
            'email' => 'editcycle@example.com',
            'password' => Hash::make('editcyclepassword'),
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
            'email' =>'editcycle@example.com',
            'password' => 'editcyclepassword'
        ];
        $loginResponse = $this->post('/api/login', $loginBody);
        $token = $loginResponse['token'];
        $headers = ['Authorization' => "Bearer $token"];

        $data = [
            'cycle_name' => 'newExampleCycle',
            'reactor_name' => 'NewExampleReactor',
            'mass' => 115,
            'target_start_date' => date('Y-m-d', strtotime('tomorrow')),
            'expiration_date' => date('Y-m-d', strtotime('tomorrow + 1day'))
        ];

        $reactor = Reactor::firstOrCreate([
            'name' => 'ExampleReactor'
        ]);
        
        $cycle = ReactorCycle::create([
            'name' => 'ExampleCycle',
            'reactor_id' => $reactor->id,
            'mass' => 120.50,
            'target_start_date' => date('Y-m-d', strtotime('today')),
            'expiration_date' => date('Y-m-d', strtotime('tomorrow'))
        ]);

        $response = $this->put('/api/reactor-cycles/'.$cycle->id, $data, $headers);

        $response->assertUnauthorized();
        $response->assertExactJson([
           'message' => 'Unauthorized'
        ]);
    }

    public function test_update_request_not_found()
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

        $data = [
            'cycle_name' => 'newExampleCycle',
            'reactor_name' => 'NewExampleReactor',
            'mass' => 115,
            'target_start_date' => date('Y-m-d', strtotime('tomorrow')),
            'expiration_date' => date('Y-m-d', strtotime('tomorrow + 1day'))
        ];

        $reactor = Reactor::firstOrCreate([
            'name' => 'ExampleReactor'
        ]);
        
        ReactorCycle::create([
            'name' => 'ExampleCycle',
            'reactor_id' => $reactor->id,
            'mass' => 120.50,
            'target_start_date' => date('Y-m-d', strtotime('today')),
            'expiration_date' => date('Y-m-d', strtotime('tomorrow'))
        ]);

        $incorrectId = '9000';
        $response = $this->put('/api/reactor-cycles/'.$incorrectId, $data, $headers);

        $response->assertNotFound();
    }

    public function test_destroy_request_with_valid_inputs()
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
        
        $cycle = ReactorCycle::create([
            'name' => 'ExampleCycle',
            'reactor_id' => $reactor->id,
            'mass' => 120.50,
            'target_start_date' => date('Y-m-d', strtotime('today')),
            'expiration_date' => date('Y-m-d', strtotime('tomorrow'))
        ]);

        $response = $this->delete('/api/reactor-cycles/'.$cycle->id, $headers);

        $cycle = ReactorCycle::where('name', 'ExampleCycle')->first();
        $this->assertNull($cycle);

        $response->assertOk();
    }

    public function test_destroy_request_made_by_clinic_user()
    {
        $user = User::factory()->create([
            'email' => 'editcycle@example.com',
            'password' => Hash::make('editcyclepassword'),
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
            'email' =>'editcycle@example.com',
            'password' => 'editcyclepassword'
        ];
        $loginResponse = $this->post('/api/login', $loginBody);
        $token = $loginResponse['token'];
        $headers = ['Authorization' => "Bearer $token"];

        $reactor = Reactor::firstOrCreate([
            'name' => 'ExampleReactor'
        ]);
        
        $cycle = ReactorCycle::create([
            'name' => 'ExampleCycle',
            'reactor_id' => $reactor->id,
            'mass' => 120.50,
            'target_start_date' => date('Y-m-d', strtotime('today')),
            'expiration_date' => date('Y-m-d', strtotime('tomorrow'))
        ]);

        $response = $this->delete('/api/reactor-cycles/'.$cycle->id, $headers);

        $response->assertUnauthorized();
        $response->assertExactJson([
           'message' => 'Unauthorized'
        ]);
    }

    public function test_destroy_request_not_found()
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
        
        $cycle = ReactorCycle::create([
            'name' => 'ExampleCycle',
            'reactor_id' => $reactor->id,
            'mass' => 120.50,
            'target_start_date' => date('Y-m-d', strtotime('today')),
            'expiration_date' => date('Y-m-d', strtotime('tomorrow'))
        ]);

        $incorrectId = '9000';
        $response = $this->delete('/api/reactor-cycles/'.$incorrectId, $headers);

        $response->assertNotFound();
    }

    public function test_update_status_request_with_valid_inputs()
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

        $data = [
            'is_enabled' => false
        ];

        $reactor = Reactor::firstOrCreate([
            'name' => 'ExampleReactor'
        ]);
        
        $cycle = ReactorCycle::create([
            'name' => 'ExampleCycle',
            'reactor_id' => $reactor->id,
            'mass' => 120.50,
            'target_start_date' => date('Y-m-d', strtotime('today')),
            'expiration_date' => date('Y-m-d', strtotime('tomorrow'))
        ]);

        $response = $this->patch('/api/reactor-cycles/'.$cycle->id.'/status', $data, $headers);

        $response->assertOk();
    }

    public function test_update_status_with_invalid_inputs()
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

        $data = [];

        $reactor = Reactor::firstOrCreate([
            'name' => 'ExampleReactor'
        ]);
        
        $cycle = ReactorCycle::create([
            'name' => 'ExampleCycle',
            'reactor_id' => $reactor->id,
            'mass' => 120.50,
            'target_start_date' => date('Y-m-d', strtotime('today')),
            'expiration_date' => date('Y-m-d', strtotime('tomorrow'))
        ]);

        $response = $this->patch('/api/reactor-cycles/'.$cycle->id.'/status', $data, $headers);

        $response->assertUnprocessable();

        $json = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('message', $json);
        $this->assertArrayHasKey('errors', $json);
        $this->assertArrayHasKey('is_enabled', $json['errors']);
    }


    public function test_update_status_request_made_by_clinic_user()
    {
        $user = User::factory()->create([
            'email' => 'editcycle@example.com',
            'password' => Hash::make('editcyclepassword'),
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
            'email' =>'editcycle@example.com',
            'password' => 'editcyclepassword'
        ];
        $loginResponse = $this->post('/api/login', $loginBody);
        $token = $loginResponse['token'];
        $headers = ['Authorization' => "Bearer $token"];

        $data = [
            'is_enabled' => false
        ];

        $reactor = Reactor::firstOrCreate([
            'name' => 'ExampleReactor'
        ]);
        
        $cycle = ReactorCycle::create([
            'name' => 'ExampleCycle',
            'reactor_id' => $reactor->id,
            'mass' => 120.50,
            'target_start_date' => date('Y-m-d', strtotime('today')),
            'expiration_date' => date('Y-m-d', strtotime('tomorrow'))
        ]);

        $response = $this->patch('/api/reactor-cycles/'.$cycle->id.'/status', $data, $headers);

        $response->assertUnauthorized();
        $response->assertExactJson([
           'message' => 'Unauthorized'
        ]);
    }

    public function test_update_status_request_not_found()
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

        $data = [
            'is_enabled' => false
        ];

        $reactor = Reactor::firstOrCreate([
            'name' => 'ExampleReactor'
        ]);
        
        $cycle = ReactorCycle::create([
            'name' => 'ExampleCycle',
            'reactor_id' => $reactor->id,
            'mass' => 120.50,
            'target_start_date' => date('Y-m-d', strtotime('today')),
            'expiration_date' => date('Y-m-d', strtotime('tomorrow'))
        ]);

        $incorrectId = '9000';
        $response = $this->patch('/api/reactor-cycles/'.$incorrectId.'/status', $data, $headers);

        $response->assertNotFound();
    }

    public function test_destroy_request_with_exception()
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
        
        $cycle = ReactorCycle::create([
            'name' => 'ExampleCycle',
            'reactor_id' => $reactor->id,
            'mass' => 120.50,
            'target_start_date' => date('Y-m-d', strtotime('today')),
            'expiration_date' => date('Y-m-d', strtotime('tomorrow'))
        ]);
        $mock = $this->mock(ReactorCycleService::class, function (MockInterface $mock) {
            $mock->shouldReceive('destroy')->andThrow(new Exception);
        });

        $response = $this->delete('/api/reactor-cycles/'.$cycle->id, $headers);

        $response->assertInternalServerError();
        $response->assertExactJson([
            'message' => 'Something went wrong.'
        ]);
        $mock->mockery_teardown();
    }
}
