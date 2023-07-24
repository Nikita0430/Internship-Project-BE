<?php

namespace Tests\Feature;

use App\Models\Clinic;
use App\Models\Reactor;
use App\Models\ReactorCycle;
use App\Models\User;
use App\Services\ReactorCycle\ReactorCycleService;
use Carbon\Carbon;
use Exception;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Mockery\MockInterface;
use Tests\TestCase;

class ArchivedReactorCycleListTest extends TestCase
{
    use DatabaseTransactions, WithFaker;

    public function test_success_on_valid_token_and_no_query_params(){
        User::factory()->create([
            'email' => 'archivedreactorcycle@example.com',
            'password' => Hash::make('archivedreactorcyclepassword'),
            'role' => 'admin'
        ]);
        $loginBody = [
            'email' =>'archivedreactorcycle@example.com',
            'password' => 'archivedreactorcyclepassword'
        ];
        $loginResponse = $this->post('/api/login', $loginBody);
        $token = $loginResponse['token'];
        $headers = ['Authorization' => "Bearer $token"];

        ReactorCycle::query()->delete();
        $reactor = Reactor::create([
            'name' => 'TestReactor'
        ]);
        ReactorCycle::create([
            'name' => 'ExampleCycle',
            'reactor_id' => $reactor->id,
            'mass' => 120.50,
            'target_start_date' => Carbon::now()->subDays(15)->format('Y-m-d'),
            'expiration_date' => Carbon::now()->subDay()->format('Y-m-d'),
            'is_enabled' => false
        ]);

        $response = $this->get('/api/reactor-cycles/archived', $headers);

        $response->assertOk();
    }

    public function test_success_on_valid_token_and_per_page(){
        User::factory()->create([
            'email' => 'archivedreactorcycle@example.com',
            'password' => Hash::make('archivedreactorcyclepassword'),
            'role' => 'admin'
        ]);
        $loginBody = [
            'email' =>'archivedreactorcycle@example.com',
            'password' => 'archivedreactorcyclepassword'
        ];
        $loginResponse = $this->post('/api/login', $loginBody);
        $token = $loginResponse['token'];
        $headers = ['Authorization' => "Bearer $token"];

        ReactorCycle::query()->delete();
        $reactor = Reactor::create([
            'name' => 'TestReactor'
        ]);
        ReactorCycle::create([
            'name' => 'ExampleCycle',
            'reactor_id' => $reactor->id,
            'mass' => 120.50,
            'target_start_date' => Carbon::now()->subDays(15)->format('Y-m-d'),
            'expiration_date' => Carbon::now()->subDay()->format('Y-m-d'),
            'is_enabled' => false
        ]);

        $response = $this->get('/api/reactor-cycles/archived?per_page=5', $headers);

        $response->assertOk();
        $json = json_decode($response->getContent(), true);
        $this->assertEquals($json['reactor-cycles']['per_page'], 5);
    }

    public function test_success_on_valid_token_and_name(){
        User::factory()->create([
            'email' => 'archivedreactorcycle@example.com',
            'password' => Hash::make('archivedreactorcyclepassword'),
            'role' => 'admin'
        ]);
        $loginBody = [
            'email' =>'archivedreactorcycle@example.com',
            'password' => 'archivedreactorcyclepassword'
        ];
        $loginResponse = $this->post('/api/login', $loginBody);
        $token = $loginResponse['token'];
        $headers = ['Authorization' => "Bearer $token"];

        ReactorCycle::query()->delete();
        $reactor = Reactor::create([
            'name' => 'TestReactor'
        ]);
        ReactorCycle::create([
            'name' => 'ExampleCycle',
            'reactor_id' => $reactor->id,
            'mass' => 120.50,
            'target_start_date' => Carbon::now()->subDays(15)->format('Y-m-d'),
            'expiration_date' => Carbon::now()->subDay()->format('Y-m-d'),
            'is_enabled' => false
        ]);

        $response = $this->get('/api/reactor-cycles/archived?name=Cycle', $headers);

        $response->assertOk();
    }

    public function test_no_records_found(){
        User::factory()->create([
            'email' => 'archivedreactorcycle@example.com',
            'password' => Hash::make('archivedreactorcyclepassword'),
            'role' => 'admin'
        ]);
        $loginBody = [
            'email' =>'archivedreactorcycle@example.com',
            'password' => 'archivedreactorcyclepassword'
        ];
        $loginResponse = $this->post('/api/login', $loginBody);
        $token = $loginResponse['token'];
        $headers = ['Authorization' => "Bearer $token"];

        ReactorCycle::query()->delete();
        $reactor = Reactor::create([
            'name' => 'TestReactor'
        ]);
        ReactorCycle::create([
            'name' => 'ExampleCycle',
            'reactor_id' => $reactor->id,
            'mass' => 120.50,
            'target_start_date' => Carbon::now()->subDays(15)->format('Y-m-d'),
            'expiration_date' => Carbon::now()->subDay()->format('Y-m-d'),
            'is_enabled' => false
        ]);

        $response = $this->get('/api/reactor-cycles/archived?name=InvalidName', $headers);

        $response->assertOk();
        $response->assertJson([
           'message' => 'No Archived Reactor Cycles Exist'
        ]);
    }

    public function test_unauthorized_for_clinic_user(){
        $user = User::factory()->create([
            'email' => 'archivedreactorcycle@example.com',
            'password' => Hash::make('archivedreactorcyclepassword'),
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
            'email' =>'archivedreactorcycle@example.com',
            'password' => 'archivedreactorcyclepassword'
        ];
        $loginResponse = $this->post('/api/login', $loginBody);
        $token = $loginResponse['token'];
        $headers = ['Authorization' => "Bearer $token"];

        ReactorCycle::query()->delete();
        $reactor = Reactor::create([
            'name' => 'TestReactor'
        ]);
        ReactorCycle::create([
            'name' => 'ExampleCycle',
            'reactor_id' => $reactor->id,
            'mass' => 120.50,
            'target_start_date' => Carbon::now()->subDays(15)->format('Y-m-d'),
            'expiration_date' => Carbon::now()->subDay()->format('Y-m-d'),
            'is_enabled' => false
        ]);

        $response = $this->get('/api/reactor-cycles/archived', $headers);

        $response->assertUnauthorized();
        $response->assertExactJson([
           'message' => 'Unauthorized'
        ]);
    }

    public function test_unauthenticated_for_invalid_token(){
        $token = 'invalidToken';
        $headers = ['Authorization' => "Bearer $token"];

        $response = $this->get('/api/reactor-cycles/archived', $headers);

        $response->assertUnauthorized();
        $response->assertExactJson([
           'message' => 'Unauthenticated.'
        ]);
    }

    public function test_for_exception(){
        User::factory()->create([
            'email' => 'archivedreactorcycle@example.com',
            'password' => Hash::make('archivedreactorcyclepassword'),
            'role' => 'admin'
        ]);
        $loginBody = [
            'email' =>'archivedreactorcycle@example.com',
            'password' => 'archivedreactorcyclepassword'
        ];
        $loginResponse = $this->post('/api/login', $loginBody);
        $token = $loginResponse['token'];
        $headers = ['Authorization' => "Bearer $token"];
        ReactorCycle::query()->delete();
        $reactor = Reactor::create([
            'name' => 'TestReactor'
        ]);
        ReactorCycle::create([
            'name' => 'ExampleCycle',
            'reactor_id' => $reactor->id,
            'mass' => 120.50,
            'target_start_date' => Carbon::now()->subDays(15)->format('Y-m-d'),
            'expiration_date' => Carbon::now()->subDay()->format('Y-m-d'),
            'is_enabled' => false
        ]);
        $mock = $this->mock(ReactorCycleService::class, function (MockInterface $mock) {
            $mock->shouldReceive('getArchived')->andThrow(new Exception);
        });
        $response = $this->get('/api/reactor-cycles/archived', $headers);
        $response->assertInternalServerError();
        $response->assertExactJson([
            'message' => 'Something went wrong.'
        ]);
        $mock->mockery_teardown();
    }
}
