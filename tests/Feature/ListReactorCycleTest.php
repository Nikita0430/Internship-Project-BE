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
use Tests\TestCase;
use Mockery\MockInterface;
use Exception;

class ListReactorCycleTest extends TestCase
{
    use DatabaseTransactions, WithFaker;

    public function test_success_on_valid_token_and_no_query_params(){
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

        $response = $this->get('/api/reactor-cycles', $headers);

        $response->assertOk();
    }

    public function test_internal_server_error_on_exception(){
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


        $mock = $this->mock(ReactorCycleService::class, function (MockInterface $mock) {
            $mock->shouldReceive('index')->andThrow(new Exception);
        });

        $response = $this->get('/api/reactor-cycles', $headers);
        $response->assertInternalServerError();
        $response->assertExactJson([
            'message' => 'Something went wrong.'
        ]);
    }

    public function test_success_with_order_by_cycle_name(){
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

        $reactor = Reactor::create([
            'name' => 'TestReactor'
        ]);

        ReactorCycle::query()->delete();
        ReactorCycle::create([
            'name' => 'TestReactorCycle1',
            'reactor_id' => $reactor->id,
            'mass' => rand(1,20)*10,
            'target_start_date' => date('Y-m-d', strtotime('09 May 2023')),
            'expiration_date' => date('Y-m-d', strtotime('19 May 2023')),
            'is_enabled' => false
        ]);

        ReactorCycle::create([
            'name' => 'TestReactorCycle2',
            'reactor_id' => $reactor->id,
            'mass' => rand(1,20)*10,
            'target_start_date' => date('Y-m-d', strtotime('09 May 2023')),
            'expiration_date' => date('Y-m-d', strtotime('19 May 2023')),
            'is_enabled' => false
        ]);

        $response = $this->get('/api/reactor-cycles?sort_by=name&status=disabled&from_date=2023-01-01&to_date=2024-01-01', $headers);

        $response->assertOk();
        $json = json_decode($response->getContent(), true);
        $this->assertTrue($json['reactor-cycles']['data'][0]['name'] < $json['reactor-cycles']['data'][1]['name']);
    }

    public function test_success_on_valid_token_and_per_page(){
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

        $response = $this->get('/api/reactor-cycles?per_page=5', $headers);

        $response->assertOk();
        $json = json_decode($response->getContent(), true);
        $this->assertEquals($json['reactor-cycles']['per_page'], 5);
    }

    public function test_success_on_valid_token_and_name(){
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

        $response = $this->get('/api/reactor-cycles?name=Cycle', $headers);

        $response->assertOk();
    }

    public function test_bad_request_with_order_by_invalid_column(){
        User::factory()->create([
            'email' => 'listorders@example.com',
            'password' => Hash::make('listorderspassword'),
            'role' => 'admin'
        ]);
        $loginBody = [
            'email' =>'listorders@example.com',
            'password' => 'listorderspassword'
        ];
        $loginResponse = $this->post('/api/login', $loginBody);
        $token = $loginResponse['token'];
        $headers = ['Authorization' => "Bearer $token"];

        $response = $this->get('/api/reactor-cycles?sort_by=invalid_column', $headers);

        $response->assertBadRequest();
        $response->assertJson([
            'message' => 'Invalid sort by parameter.'
        ]);
    }

    public function test_bad_request_with_order_by_invalid_order(){
        User::factory()->create([
            'email' => 'listorders@example.com',
            'password' => Hash::make('listorderspassword'),
            'role' => 'admin'
        ]);
        $loginBody = [
            'email' =>'listorders@example.com',
            'password' => 'listorderspassword'
        ];
        $loginResponse = $this->post('/api/login', $loginBody);
        $token = $loginResponse['token'];
        $headers = ['Authorization' => "Bearer $token"];

        $response = $this->get('/api/reactor-cycles?sort_order=invalid_order', $headers);

        $response->assertBadRequest();
        $response->assertJson([
            'message' => 'Invalid sort order parameter.'
        ]);
    }

    public function test_no_records_found(){
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

        $response = $this->get('/api/reactor-cycles?name=InvalidName', $headers);

        $response->assertOk();
    }

    public function test_unauthorized_for_clinic_user(){
        $user = User::factory()->create([
            'email' => 'listreactorcycle@example.com',
            'password' => Hash::make('listreactorcyclepassword'),
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
            'email' =>'listreactorcycle@example.com',
            'password' => 'listreactorcyclepassword'
        ];
        $loginResponse = $this->post('/api/login', $loginBody);
        $token = $loginResponse['token'];
        $headers = ['Authorization' => "Bearer $token"];

        $response = $this->get('/api/reactor-cycles', $headers);

        $response->assertUnauthorized();
        $response->assertExactJson([
           'message' => 'Unauthorized'
        ]);
    }

    public function test_unauthenticated_for_invalid_token(){
        $token = 'invalidToken';
        $headers = ['Authorization' => "Bearer $token"];

        $response = $this->get('/api/reactor-cycles', $headers);

        $response->assertUnauthorized();
        $response->assertExactJson([
           'message' => 'Unauthenticated.'
        ]);
    }
}
