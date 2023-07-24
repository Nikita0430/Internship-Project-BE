<?php

namespace Tests\Feature;

use App\Models\Clinic;
use App\Models\Order;
use App\Models\Reactor;
use App\Models\ReactorCycle;
use App\Models\User;
use App\Services\Order\OrderService;
use Carbon\Carbon;
use Exception;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Mockery\MockInterface;
use Tests\TestCase;

class EditOrderStatusTest extends TestCase
{
    use DatabaseTransactions, WithFaker;
    public function test_success_for_admin_user(){
        User::factory()->create([
            'email' => 'editorder@example.com',
            'password' => Hash::make('editorderpassword'),
            'role' => 'admin'
        ]);
        
        $loginBody = [
            'email' =>'editorder@example.com',
            'password' => 'editorderpassword'
        ];
        $loginResponse = $this->post('/api/login', $loginBody);
        $token = $loginResponse['token'];
        $headers = ['Authorization' => "Bearer $token"];

        $reactor = Reactor::create([
            'name' => $this->faker->name(),
        ]);
        $reactorCycle = ReactorCycle::create([
            'name' => $this->faker->name(),
            'reactor_id' => $reactor->id,
            'mass' => $this->faker->randomFloat(2,40,60),
            'target_start_date' => $this->faker->dateTimeBetween("-1 years", "now")->format('Y-m-d'),
            'expiration_date' => $this->faker->dateTimeBetween("tomorrow", "+1 years")->format('Y-m-d')
        ]);

        $user = User::factory()->create([
            'email' => 'editorder2@example.com',
            'password' => Hash::make('editorder2password'),
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

        $requestBody = [
            'status' => config('global.orders.status.Confirmed'),
        ];

        $response = $this->patch('/api/orders/'.$order->id, $requestBody, $headers);

        $response->assertOk();
    }

    public function test_bad_request_on_confirmed_to_pending_for_admin_user(){
        User::factory()->create([
            'email' => 'editorder@example.com',
            'password' => Hash::make('editorderpassword'),
            'role' => 'admin'
        ]);
        
        $loginBody = [
            'email' =>'editorder@example.com',
            'password' => 'editorderpassword'
        ];
        $loginResponse = $this->post('/api/login', $loginBody);
        $token = $loginResponse['token'];
        $headers = ['Authorization' => "Bearer $token"];

        $reactor = Reactor::create([
            'name' => $this->faker->name(),
        ]);
        $reactorCycle = ReactorCycle::create([
            'name' => $this->faker->name(),
            'reactor_id' => $reactor->id,
            'mass' => $this->faker->randomFloat(2,40,60),
            'target_start_date' => $this->faker->dateTimeBetween("-1 years", "now")->format('Y-m-d'),
            'expiration_date' => $this->faker->dateTimeBetween("tomorrow", "+1 years")->format('Y-m-d')
        ]);

        $user = User::factory()->create([
            'email' => 'editorder2@example.com',
            'password' => Hash::make('editorder2password'),
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
            'status' => config('global.orders.status.Confirmed')
        ]);

        $requestBody = [
            'status' => config('global.orders.status.Pending'),
        ];

        $response = $this->patch('/api/orders/'.$order->id, $requestBody, $headers);

        $response->assertBadRequest();
    }

    public function test_bad_request_on_confirmed_to_confirmed_for_admin_user(){
        User::factory()->create([
            'email' => 'editorder@example.com',
            'password' => Hash::make('editorderpassword'),
            'role' => 'admin'
        ]);
        
        $loginBody = [
            'email' =>'editorder@example.com',
            'password' => 'editorderpassword'
        ];
        $loginResponse = $this->post('/api/login', $loginBody);
        $token = $loginResponse['token'];
        $headers = ['Authorization' => "Bearer $token"];

        $reactor = Reactor::create([
            'name' => $this->faker->name(),
        ]);
        $reactorCycle = ReactorCycle::create([
            'name' => $this->faker->name(),
            'reactor_id' => $reactor->id,
            'mass' => $this->faker->randomFloat(2,40,60),
            'target_start_date' => $this->faker->dateTimeBetween("-1 years", "now")->format('Y-m-d'),
            'expiration_date' => $this->faker->dateTimeBetween("tomorrow", "+1 years")->format('Y-m-d')
        ]);

        $user = User::factory()->create([
            'email' => 'editorder2@example.com',
            'password' => Hash::make('editorder2password'),
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
            'status' => config('global.orders.status.Confirmed')
        ]);

        $requestBody = [
            'status' => config('global.orders.status.Confirmed'),
        ];

        $response = $this->patch('/api/orders/'.$order->id, $requestBody, $headers);

        $response->assertBadRequest();
    }

    public function test_success_on_pending_to_cancelled_for_admin_user(){
        User::factory()->create([
            'email' => 'editorder@example.com',
            'password' => Hash::make('editorderpassword'),
            'role' => 'admin'
        ]);
        
        $loginBody = [
            'email' =>'editorder@example.com',
            'password' => 'editorderpassword'
        ];
        $loginResponse = $this->post('/api/login', $loginBody);
        $token = $loginResponse['token'];
        $headers = ['Authorization' => "Bearer $token"];

        $reactor = Reactor::create([
            'name' => $this->faker->name(),
        ]);
        $reactorCycle = ReactorCycle::create([
            'name' => $this->faker->name(),
            'reactor_id' => $reactor->id,
            'mass' => $this->faker->randomFloat(2,40,60),
            'target_start_date' => $this->faker->dateTimeBetween("-1 years", "now")->format('Y-m-d'),
            'expiration_date' => $this->faker->dateTimeBetween("tomorrow", "+1 years")->format('Y-m-d')
        ]);

        $user = User::factory()->create([
            'email' => 'editorder2@example.com',
            'password' => Hash::make('editorder2password'),
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

        $requestBody = [
            'status' => config('global.orders.status.Cancelled'),
        ];

        $response = $this->patch('/api/orders/'.$order->id, $requestBody, $headers);

        $response->assertOk();
        $order->refresh();
        $this->assertEquals($order->confirmed_at, null);
        $this->assertNotEquals($order->cancelled_at, null);
    }

    public function test_unprocessable_content_on_validation_failed(){
        User::factory()->create([
            'email' => 'editorder@example.com',
            'password' => Hash::make('editorderpassword'),
            'role' => 'admin'
        ]);
        
        $loginBody = [
            'email' =>'editorder@example.com',
            'password' => 'editorderpassword'
        ];
        $loginResponse = $this->post('/api/login', $loginBody);
        $token = $loginResponse['token'];
        $headers = ['Authorization' => "Bearer $token"];

        $reactor = Reactor::create([
            'name' => $this->faker->name(),
        ]);
        $reactorCycle = ReactorCycle::create([
            'name' => $this->faker->name(),
            'reactor_id' => $reactor->id,
            'mass' => $this->faker->randomFloat(2,40,60),
            'target_start_date' => $this->faker->dateTimeBetween("-1 years", "now")->format('Y-m-d'),
            'expiration_date' => $this->faker->dateTimeBetween("tomorrow", "+1 years")->format('Y-m-d')
        ]);

        $user = User::factory()->create([
            'email' => 'editorder2@example.com',
            'password' => Hash::make('editorder2password'),
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
            'status' => config('global.orders.status.Confirmed')
        ]);

        $requestBody = [
            'status' => 'invalidStatus',
        ];

        $response = $this->patch('/api/orders/'.$order->id, $requestBody, $headers);

        $response->assertUnprocessable();
    }

    public function test_for_order_id_not_found(){
        User::factory()->create([
            'email' => 'editorder@example.com',
            'password' => Hash::make('editorderpassword'),
            'role' => 'admin'
        ]);
        
        $loginBody = [
            'email' =>'editorder@example.com',
            'password' => 'editorderpassword'
        ];
        $loginResponse = $this->post('/api/login', $loginBody);
        $token = $loginResponse['token'];
        $headers = ['Authorization' => "Bearer $token"];

        $reactor = Reactor::create([
            'name' => $this->faker->name(),
        ]);
        $reactorCycle = ReactorCycle::create([
            'name' => $this->faker->name(),
            'reactor_id' => $reactor->id,
            'mass' => $this->faker->randomFloat(2,40,60),
            'target_start_date' => $this->faker->dateTimeBetween("-1 years", "now")->format('Y-m-d'),
            'expiration_date' => $this->faker->dateTimeBetween("tomorrow", "+1 years")->format('Y-m-d')
        ]);

        $user = User::factory()->create([
            'email' => 'editorder2@example.com',
            'password' => Hash::make('editorder2password'),
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

        $requestBody = [
            'status' => config('global.orders.status.Confirmed'),
        ];

        $invalidOrderId = 12345;
        $response = $this->patch('/api/orders/'.$invalidOrderId, $requestBody, $headers);

        $response->assertNotFound();
    }

    public function test_unauthorized_for_clinic_user(){
        $user = User::factory()->create([
            'email' => 'editorder@example.com',
            'password' => Hash::make('editorderpassword'),
            'role' => 'clinic'
        ]);
        $clinic = Clinic::create([
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
            'email' =>'editorder@example.com',
            'password' => 'editorderpassword'
        ];
        $loginResponse = $this->post('/api/login', $loginBody);
        $token = $loginResponse['token'];
        $headers = ['Authorization' => "Bearer $token"];

        $reactor = Reactor::create([
            'name' => $this->faker->name(),
        ]);
        $reactorCycle = ReactorCycle::create([
            'name' => $this->faker->name(),
            'reactor_id' => $reactor->id,
            'mass' => $this->faker->randomFloat(2,40,60),
            'target_start_date' => $this->faker->dateTimeBetween("-1 years", "now")->format('Y-m-d'),
            'expiration_date' => $this->faker->dateTimeBetween("tomorrow", "+1 years")->format('Y-m-d')
        ]);

        $user = User::factory()->create([
            'email' => 'editorder2@example.com',
            'password' => Hash::make('editorder2password'),
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

        $requestBody = [
            'status' => config('global.orders.status.Confirmed'),
        ];

        $response = $this->patch('/api/orders/'.$order->id, $requestBody, $headers);

        $response->assertUnauthorized();
    }

    public function test_for_invalid_token(){
        $token = 'invalidToken';
        $headers = ['Authorization' => "Bearer $token"];

        $reactor = Reactor::create([
            'name' => $this->faker->name(),
        ]);
        $reactorCycle = ReactorCycle::create([
            'name' => $this->faker->name(),
            'reactor_id' => $reactor->id,
            'mass' => $this->faker->randomFloat(2,40,60),
            'target_start_date' => $this->faker->dateTimeBetween("-1 years", "now")->format('Y-m-d'),
            'expiration_date' => $this->faker->dateTimeBetween("tomorrow", "+1 years")->format('Y-m-d')
        ]);

        $user = User::factory()->create([
            'email' => 'editorder2@example.com',
            'password' => Hash::make('editorder2password'),
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

        $requestBody = [
            'status' => config('global.orders.status.Confirmed'),
        ];

        $response = $this->patch('/api/orders/'.$order->id, $requestBody, $headers);

        $response->assertUnauthorized();
    }

    public function test_with_exception(){
        User::factory()->create([
            'email' => 'editorder@example.com',
            'password' => Hash::make('editorderpassword'),
            'role' => 'admin'
        ]);
        
        $loginBody = [
            'email' =>'editorder@example.com',
            'password' => 'editorderpassword'
        ];
        $loginResponse = $this->post('/api/login', $loginBody);
        $token = $loginResponse['token'];
        $headers = ['Authorization' => "Bearer $token"];
        $mock = $this->mock(OrderService::class, function (MockInterface $mock) {
            $mock->shouldReceive('updateStatus')->andThrow(new Exception);
        });
        $requestBody = [
            'status' => config('global.orders.status.Confirmed'),
        ];
        $response = $this->patch('/api/orders/1', $requestBody, $headers);
        $response->assertInternalServerError();
        $response->assertExactJson([
            'message' => 'Something went wrong.'
        ]);
        $mock->mockery_teardown();
    }
}
