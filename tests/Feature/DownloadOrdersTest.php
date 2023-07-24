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

class DownloadOrdersTest extends TestCase
{
    use DatabaseTransactions, WithFaker;

    public function test_success_for_clinic_user(){
        $user = User::factory()->create([
            'email' => 'listorders@example.com',
            'password' => Hash::make('listorderspassword'),
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
            'email' =>'listorders@example.com',
            'password' => 'listorderspassword'
        ];
        $loginResponse = $this->post('/api/login', $loginBody);
        $token = $loginResponse['token'];
        $headers = ['Authorization' => "Bearer $token"];

        $response = $this->get('/api/orders/download', $headers);

        $response->assertOk();
    }

    public function test_success_for_admin_user(){
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
            'email' => 'listorders2@example.com',
            'password' => Hash::make('listorders2password'),
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

        Order::create([
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

        $response = $this->get('/api/orders/download', $headers);

        $response->assertOk();
    }

    public function test_success_with_sort_by(){
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
            'email' => 'listorders2@example.com',
            'password' => Hash::make('listorders2password'),
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

        Order::create([
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

        $response = $this->get('/api/orders/download?sort_by=clinic_name', $headers);

        $response->assertOk();
    }

    public function test_success_for_no_records(){
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

        Order::query()->delete();

        $response = $this->get('/api/orders/download', $headers);

        $response->assertOk();
    }

    public function test_success_with_search_dog_name(){
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
            'email' => 'listorders2@example.com',
            'password' => Hash::make('listorders2password'),
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

        Order::create([
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

        $response = $this->get('/api/orders/download?search=Tom', $headers);

        $response->assertOk();
    }

    public function test_success_with_search_dog_breed(){
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
            'email' => 'listorders2@example.com',
            'password' => Hash::make('listorders2password'),
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

        Order::create([
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

        $response = $this->get('/api/orders/download?search=labrador', $headers);

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

        $response = $this->get('/api/orders/download?sort_by=invalid_column', $headers);

        $response->assertBadRequest();
        $response->assertJson([
            'message' => 'Invalid sort by parameter'
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

        $response = $this->get('/api/orders/download?sort_order=invalid_order', $headers);

        $response->assertBadRequest();
        $response->assertJson([
            'message' => 'Invalid sort order parameter'
        ]);
    }

    public function test_unauthenticated_for_invalid_token(){
        $token = 'invalidToken';
        $headers = ['Authorization' => "Bearer $token"];

        $response = $this->get('/api/orders/download', $headers);

        $response->assertUnauthorized();
        $response->assertExactJson([
           'message' => 'Unauthenticated.'
        ]);
    }

    public function test_with_exception(){
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
        $mock = $this->mock(OrderService::class, function (MockInterface $mock) {
            $mock->shouldReceive('downloadList')->andThrow(new Exception);
        });
        $response = $this->get('/api/orders/download', $headers);
        $response->assertInternalServerError();
        $response->assertExactJson([
            'message' => 'Something went wrong.'
        ]);
        $mock->mockery_teardown();
    }
}
