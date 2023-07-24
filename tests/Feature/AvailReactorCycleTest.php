<?php

namespace Tests\Feature;

use App\Models\Clinic;
use App\Models\Order;
use App\Models\Reactor;
use App\Models\ReactorCycle;
use App\Models\User;
use App\Services\ReactorCycle\ReactorCycleService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Response;
use Mockery\MockInterface;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AvailReactorCycleTest extends TestCase
{
    use DatabaseTransactions, WithFaker;

    public function test_success_with_correct_input(){
        User::factory()->create([
            'email' => 'availreactorcycle@example.com',
            'password' => Hash::make('availreactorcyclepassword'),
            'role' => 'admin'
        ]);
        $loginBody = [
            'email' =>'availreactorcycle@example.com',
            'password' => 'availreactorcyclepassword'
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
            'target_start_date' => date('Y-m-d', strtotime('-30 days')),
            'expiration_date' => date('Y-m-d', strtotime('+15 days')),
            'is_enabled' => true
        ]);

        ReactorCycle::create([
            'name' => 'TestReactorCycle2',
            'reactor_id' => $reactor->id,
            'mass' => rand(1,20)*10,
            'target_start_date' => date('Y-m-d', strtotime('-5 days')),
            'expiration_date' => date('Y-m-d', strtotime('+30 days')),
            'is_enabled' => true
        ]);

        $url = route('getAvailableReactorCycle', [
            'reactor_name' => $reactor->name,
            'injection_date' => date('Y-m-d', strtotime('tomorrow'))
        ]);

        $response = $this->get($url, $headers);

        $response->assertOk();
    }

    public function test_success_with_correct_input_for_view_order(){
        User::factory()->create([
            'email' => 'availreactorcycle@example.com',
            'password' => Hash::make('availreactorcyclepassword'),
            'role' => 'admin'
        ]);
        $loginBody = [
            'email' =>'availreactorcycle@example.com',
            'password' => 'availreactorcyclepassword'
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
            'target_start_date' => date('Y-m-d', strtotime('-30 days')),
            'expiration_date' => date('Y-m-d', strtotime('+15 days')),
            'is_enabled' => true
        ]);

        $reactorCycle = ReactorCycle::create([
            'name' => 'TestReactorCycle2',
            'reactor_id' => $reactor->id,
            'mass' => rand(1,20)*10,
            'target_start_date' => date('Y-m-d', strtotime('-5 days')),
            'expiration_date' => date('Y-m-d', strtotime('+30 days')),
            'is_enabled' => true
        ]);

        $user = User::factory()->create([
            'email' => 'availreactorcycle2@example.com',
            'password' => Hash::make('availreactorcycle2password'),
            'role' => 'clinic'
        ]);
        $clinic = Clinic::create([
            'account_id' => 'C999998',
            'name' => $this->faker->name,
            'address' => $this->faker->address,
            'city' => $this->faker->city,
            'state' => $this->faker->state(),
            'zipcode' => $this->faker->postcode,
            'user_id' => $user['id'],
            'is_enabled' => true
        ]);

        $order = Order::create([
            'clinic_id' => $clinic->id,
            'order_no' => 'WEBO99999',
            'email' => $this->faker->email(),
            'placed_at' => Carbon::now()->toDateTimeString(),
            'shipped_at' => Carbon::now()->addDay()->toDateTimeString(),
            'injection_date' => $this->faker->dateTimeBetween('tomorrow',$reactorCycle['expiration_date'])->format('Y-m-d'),
            'dog_name' => 'Tom',
            'dog_breed' => 'labrador',
            'dog_age' => 17,
            'dog_weight' => 20,
            'dog_gender' => 'male',
            'no_of_elbows' => 3,
            'dosage_per_elbow' => 1.5,
            'total_dosage' => 4.5,
            'reactor_id' => $reactor->id,
            'reactor_cycle_id' => $reactorCycle->id,
            'status' => config('global.orders.status.Pending')
        ]);

        $reactorCycle->delete();

        $url = route('getAvailableReactorCycle', [
            'reactor_name' => $reactor->name,
            'injection_date' => date('Y-m-d', strtotime('tomorrow')),
            'order_id' => $order->id,
        ]);

        $response = $this->get($url, $headers);

        $response->assertOk();
    }

    public function test_success_with_correct_input_for_view_order_when_logged_in_as_clinic(){
        $user = User::factory()->create([
            'email' => 'availreactorcycle@example.com',
            'password' => Hash::make('availreactorcyclepassword'),
            'role' => 'clinic'
        ]);
        $clinic = Clinic::create([
            'account_id' => 'C999998',
            'name' => $this->faker->name,
            'address' => $this->faker->address,
            'city' => $this->faker->city,
            'state' => $this->faker->state(),
            'zipcode' => $this->faker->postcode,
            'user_id' => $user['id'],
            'is_enabled' => true
        ]);
        $loginBody = [
            'email' =>'availreactorcycle@example.com',
            'password' => 'availreactorcyclepassword'
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
            'target_start_date' => date('Y-m-d', strtotime('-30 days')),
            'expiration_date' => date('Y-m-d', strtotime('+15 days')),
            'is_enabled' => true
        ]);

        $reactorCycle = ReactorCycle::create([
            'name' => 'TestReactorCycle2',
            'reactor_id' => $reactor->id,
            'mass' => rand(1,20)*10,
            'target_start_date' => date('Y-m-d', strtotime('-5 days')),
            'expiration_date' => date('Y-m-d', strtotime('+30 days')),
            'is_enabled' => true
        ]);

        $order = Order::create([
            'clinic_id' => $clinic->id,
            'order_no' => 'WEBO99999',
            'email' => $this->faker->email(),
            'placed_at' => Carbon::now()->toDateTimeString(),
            'shipped_at' => Carbon::now()->addDay()->toDateTimeString(),
            'injection_date' => $this->faker->dateTimeBetween('tomorrow',$reactorCycle['expiration_date'])->format('Y-m-d'),
            'dog_name' => 'Tom',
            'dog_breed' => 'labrador',
            'dog_age' => 17,
            'dog_weight' => 20,
            'dog_gender' => 'male',
            'no_of_elbows' => 3,
            'dosage_per_elbow' => 1.5,
            'total_dosage' => 4.5,
            'reactor_id' => $reactor->id,
            'reactor_cycle_id' => $reactorCycle->id,
            'status' => config('global.orders.status.Pending')
        ]);

        $reactorCycle->delete();

        $url = route('getAvailableReactorCycle', [
            'reactor_name' => $reactor->name,
            'injection_date' => date('Y-m-d', strtotime('tomorrow')),
            'order_id' => $order->id,
        ]);

        $response = $this->get($url, $headers);

        $response->assertOk();
    }

    public function test_success_with_correct_input_by_clinic_user(){
        $user = User::factory()->create([
            'email' => 'availreactorcycle@example.com',
            'password' => Hash::make('availreactorcyclepassword'),
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
            'email' =>'availreactorcycle@example.com',
            'password' => 'availreactorcyclepassword'
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
            'target_start_date' => date('Y-m-d', strtotime('-30 days')),
            'expiration_date' => date('Y-m-d', strtotime('+15 days')),
            'is_enabled' => true
        ]);

        ReactorCycle::create([
            'name' => 'TestReactorCycle2',
            'reactor_id' => $reactor->id,
            'mass' => rand(1,20)*10,
            'target_start_date' => date('Y-m-d', strtotime('-5 days')),
            'expiration_date' => date('Y-m-d', strtotime('+30 days')),
            'is_enabled' => true
        ]);

        $url = route('getAvailableReactorCycle', [
            'reactor_name' => $reactor->name,
            'injection_date' => date('Y-m-d', strtotime('tomorrow'))
        ]);

        $response = $this->get($url, $headers);

        $response->assertOk();
    }

    public function test_no_records_found(){
        User::factory()->create([
            'email' => 'availreactorcycle@example.com',
            'password' => Hash::make('availreactorcyclepassword'),
            'role' => 'admin'
        ]);
        $loginBody = [
            'email' =>'availreactorcycle@example.com',
            'password' => 'availreactorcyclepassword'
        ];
        $loginResponse = $this->post('/api/login', $loginBody);
        $token = $loginResponse['token'];
        $headers = ['Authorization' => "Bearer $token"];

        ReactorCycle::query()->delete();

        Reactor::create([
            'name' => 'TestReactor'
        ]);

        $url = route('getAvailableReactorCycle', [
            'reactor_name' => 'TestReactor',
            'injection_date' => date('Y-m-d', strtotime('tomorrow'))
        ]);

        $response = $this->get($url, $headers);

        $response->assertNotFound();
        $response->assertJson([
           'message' => 'No Reactor Cycle Exists'
        ]);
    }

    public function test_bad_request_for_invalid_reactor(){
        User::factory()->create([
            'email' => 'availreactorcycle@example.com',
            'password' => Hash::make('availreactorcyclepassword'),
            'role' => 'admin'
        ]);
        $loginBody = [
            'email' =>'availreactorcycle@example.com',
            'password' => 'availreactorcyclepassword'
        ];
        $loginResponse = $this->post('/api/login', $loginBody);
        $token = $loginResponse['token'];
        $headers = ['Authorization' => "Bearer $token"];

        $url = route('getAvailableReactorCycle', [
            'reactor_name' => 'InvalidReactor',
            'injection_date' => date('Y-m-d', strtotime('tomorrow'))
        ]);

        $response = $this->get($url, $headers);

        $response->assertBadRequest();
        $response->assertJson([
           'message' => 'Reactor does not exist'
        ]);
    }

    public function test_bad_request_for_missing_params(){
        User::factory()->create([
            'email' => 'availreactorcycle@example.com',
            'password' => Hash::make('availreactorcyclepassword'),
            'role' => 'admin'
        ]);
        $loginBody = [
            'email' =>'availreactorcycle@example.com',
            'password' => 'availreactorcyclepassword'
        ];
        $loginResponse = $this->post('/api/login', $loginBody);
        $token = $loginResponse['token'];
        $headers = ['Authorization' => "Bearer $token"];

        $response = $this->get('api/reactor-cycles/avail', $headers);

        $response->assertBadRequest();
        $response->assertJson([
           'message' => 'Missing required query parameter(s)'
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

    public function test_getAvailable_for_exception(){
        User::factory()->create([
            'email' => 'availreactorcycle@example.com',
            'password' => Hash::make('availreactorcyclepassword'),
            'role' => 'admin'
        ]);
        $loginBody = [
            'email' =>'availreactorcycle@example.com',
            'password' => 'availreactorcyclepassword'
        ];
        $loginResponse = $this->post('/api/login', $loginBody);
        $token = $loginResponse['token'];
        $headers = ['Authorization' => "Bearer $token"];
        $mock = $this->mock(ReactorCycleService::class, function (MockInterface $mock) {
            $mock->shouldReceive('getAvailable')->andThrow(new Exception);
        });
        
        $reactor = Reactor::create([
            'name' => 'TestReactor'
        ]);

        ReactorCycle::query()->delete();
        ReactorCycle::create([
            'name' => 'TestReactorCycle1',
            'reactor_id' => $reactor->id,
            'mass' => rand(1,20)*10,
            'target_start_date' => date('Y-m-d', strtotime('-30 days')),
            'expiration_date' => date('Y-m-d', strtotime('+15 days')),
            'is_enabled' => true
        ]);

        ReactorCycle::create([
            'name' => 'TestReactorCycle2',
            'reactor_id' => $reactor->id,
            'mass' => rand(1,20)*10,
            'target_start_date' => date('Y-m-d', strtotime('-5 days')),
            'expiration_date' => date('Y-m-d', strtotime('+30 days')),
            'is_enabled' => true
        ]);

        $url = route('getAvailableReactorCycle', [
            'reactor_name' => $reactor->name,
            'injection_date' => date('Y-m-d', strtotime('tomorrow'))
        ]);

        $response = $this->get($url, $headers);

        $response->assertStatus(Response::HTTP_INTERNAL_SERVER_ERROR);

    }
}
