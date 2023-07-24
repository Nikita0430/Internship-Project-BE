<?php

namespace Tests\Feature;

use App\Models\Clinic;
use App\Models\Notification;
use App\Models\Order;
use App\Models\Reactor;
use App\Models\ReactorCycle;
use App\Models\User;
use App\Services\Notification\NotificationService;
use Carbon\Carbon;
use Exception;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Mockery\MockInterface;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use DatabaseTransactions, WithFaker;

    public function test_success_on_valid_request(){
        $user = User::factory()->create([
            'email' => 'notification@example.com',
            'password' => Hash::make('notificationpassword'),
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
            'email' =>'notification@example.com',
            'password' => 'notificationpassword'
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

        Notification::create([
            'order_id' => $order->id,
            'status_change' => config('global.orders.status.Confirmed')
        ]);

        $response = $this->get('/api/notifications', $headers);

        $response->assertOk();
    }

    public function test_unauthenticated_for_wrong_token(){
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
            'email' => 'notification@example.com',
            'password' => Hash::make('notificationpassword'),
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

        Notification::create([
            'order_id' => $order->id,
            'status_change' => config('global.orders.status.Confirmed')
        ]);

        $response = $this->get('/api/notifications', $headers);

        $response->assertUnauthorized();
    }

    public function test_for_admin_user(){
        $user = User::factory()->create([
            'email' => 'notification@example.com',
            'password' => Hash::make('notificationpassword'),
            'role' => 'admin'
        ]);
        $loginBody = [
            'email' =>'notification@example.com',
            'password' => 'notificationpassword'
        ];
        $loginResponse = $this->post('/api/login', $loginBody);
        $token = $loginResponse['token'];
        $headers = ['Authorization' => "Bearer $token"];

        $response = $this->get('/api/notifications', $headers);

        $response->assertUnauthorized();
    }

    public function test_for_no_notifications_found(){
        $user = User::factory()->create([
            'email' => 'notification@example.com',
            'password' => Hash::make('notificationpassword'),
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
            'email' =>'notification@example.com',
            'password' => 'notificationpassword'
        ];
        $loginResponse = $this->post('/api/login', $loginBody);
        $token = $loginResponse['token'];
        $headers = ['Authorization' => "Bearer $token"];

        $response = $this->get('/api/notifications', $headers);

        $response->assertOk();
    }

    public function test_update_seen_success_on_valid_request(){
        $user = User::factory()->create([
            'email' => 'notification@example.com',
            'password' => Hash::make('notificationpassword'),
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
            'email' =>'notification@example.com',
            'password' => 'notificationpassword'
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

        $notification = Notification::create([
            'order_id' => $order->id,
            'status_change' => config('global.orders.status.Confirmed')
        ]);

        $response = $this->patch('/api/notifications', $headers);

        $response->assertOk();
        $notification->refresh();
        $this->assertEquals($notification->is_seen, true);
    }

    public function test_update_seen_unauthenticated_for_wrong_token(){
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
            'email' => 'notification@example.com',
            'password' => Hash::make('notificationpassword'),
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

        Notification::create([
            'order_id' => $order->id,
            'status_change' => config('global.orders.status.Confirmed')
        ]);

        $response = $this->patch('/api/notifications', $headers);

        $response->assertUnauthorized();
    }

    public function test_update_seen_for_admin_user(){
        $user = User::factory()->create([
            'email' => 'notification@example.com',
            'password' => Hash::make('notificationpassword'),
            'role' => 'admin'
        ]);
        $loginBody = [
            'email' =>'notification@example.com',
            'password' => 'notificationpassword'
        ];
        $loginResponse = $this->post('/api/login', $loginBody);
        $token = $loginResponse['token'];
        $headers = ['Authorization' => "Bearer $token"];

        $response = $this->patch('/api/notifications', $headers);

        $response->assertUnauthorized();
    }

    public function test_with_exception(){
        $user = User::factory()->create([
            'email' => 'notification@example.com',
            'password' => Hash::make('notificationpassword'),
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
            'email' =>'notification@example.com',
            'password' => 'notificationpassword'
        ];
        $loginResponse = $this->post('/api/login', $loginBody);
        $token = $loginResponse['token'];
        $headers = ['Authorization' => "Bearer $token"];
        $mock = $this->mock(NotificationService::class, function (MockInterface $mock) {
            $mock->shouldReceive('index')->andThrow(new Exception);
        });
        $response = $this->get('/api/notifications', $headers);
        $response->assertInternalServerError();
        $response->assertExactJson([
            'message' => 'Something went wrong.'
        ]);
        $mock->mockery_teardown();
    }

    public function test_update_seen_with_exception(){
        $user = User::factory()->create([
            'email' => 'notification@example.com',
            'password' => Hash::make('notificationpassword'),
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
            'email' =>'notification@example.com',
            'password' => 'notificationpassword'
        ];
        $loginResponse = $this->post('/api/login', $loginBody);
        $token = $loginResponse['token'];
        $headers = ['Authorization' => "Bearer $token"];
        $mock = $this->mock(NotificationService::class, function (MockInterface $mock) {
            $mock->shouldReceive('updateSeen')->andThrow(new Exception);
        });
        $response = $this->patch('/api/notifications', $headers);
        $response->assertInternalServerError();
        $response->assertExactJson([
            'message' => 'Something went wrong.'
        ]);
        $mock->mockery_teardown();
    }
}
