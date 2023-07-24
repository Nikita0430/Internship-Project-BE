<?php

namespace Tests\Feature;

use App\Models\Clinic;
use App\Models\Reactor;
use App\Models\ReactorCycle;
use App\Models\User;
use App\Services\Order\OrderService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Exception;
use Mockery\MockInterface;
use Tests\TestCase;

class PlaceOrderTest extends TestCase
{
    use DatabaseTransactions, WithFaker;

    public function test_store_with_admin_user_valid_inputs()
    {
        User::factory()->create([
            'email' => 'placeorder@example.com',
            'password' => Hash::make('placeorderpassword'),
            'role' => 'admin'
        ]);
        $loginBody = [
            'email' =>'placeorder@example.com',
            'password' => 'placeorderpassword'
        ];
        $loginResponse = $this->post('/api/login', $loginBody);
        $token = $loginResponse['token'];
        $headers = ['Authorization' => "Bearer $token"];

        $user = User::factory()->create([
            'email' => 'placeorder2@example.com',
            'password' => Hash::make('placeorder2password'),
            'role' => 'clinic'
        ]);
        $clinic = Clinic::create([
            'account_id' => 'C999999',
            'name' => $this->faker->name,
            'address' => $this->faker->address(),
            'city' => $this->faker->city(),
            'state' => $this->faker->state(),
            'zipcode' => $this->faker->postcode(),
            'user_id' => $user['id'],
            'is_enabled' => true
        ]);
        $reactor = Reactor::create([
            'name' => $this->faker->name(),
        ]);
        $reactorCycle = ReactorCycle::firstOrCreate([
            'name' => $this->faker->name(),
            'reactor_id' => $reactor->id,
            'mass' => $this->faker->randomFloat(2,40,60),
            'target_start_date' => $this->faker->dateTimeBetween("-1 years", "now")->format('Y-m-d'),
            'expiration_date' => $this->faker->dateTimeBetween("tomorrow", "+1 years")->format('Y-m-d')
        ]);

        $requestBody = [
            'clinic_id' => $clinic['id'],
            'email' => $this->faker->email(),
            'injection_date' => $this->faker->dateTimeBetween('tomorrow',$reactorCycle['expiration_date'])->format('Y-m-d'),
            'dog_name' => $this->faker->name(),
            'dog_breed' => $this->faker->name(),
            'dog_age' => $this->faker->numberBetween(1,20),
            'dog_weight' => $this->faker->randomFloat(2,0,50),
            'dog_gender' => $this->faker->randomElement(['male','female']),
            'reactor_name' => $reactor->name,
            'reactor_cycle_id' => $reactorCycle->id,
            'no_of_elbows' => $this->faker->numberBetween(1,10),
            'dosage_per_elbow' => 1,
            'order_instructions' => "Test Instructions"
        ];

        $response = $this->post('/api/orders', $requestBody, $headers);

        $response->assertOk();
    }

    public function test_store_with_clinic_user_valid_inputs()
    {
        $user = User::factory()->create([
            'email' => 'placeorder@example.com',
            'password' => Hash::make('placeorderpassword'),
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
            'email' =>'placeorder@example.com',
            'password' => 'placeorderpassword'
        ];
        $loginResponse = $this->post('/api/login', $loginBody);
        $token = $loginResponse['token'];
        $headers = ['Authorization' => "Bearer $token"];

        $reactor = Reactor::create([
            'name' => $this->faker->name(),
        ]);
        $reactorCycle = ReactorCycle::firstOrCreate([
            'name' => $this->faker->name(),
            'reactor_id' => $reactor->id,
            'mass' => $this->faker->randomFloat(2,40,60),
            'target_start_date' => $this->faker->dateTimeBetween("-1 years", "now")->format('Y-m-d'),
            'expiration_date' => $this->faker->dateTimeBetween("tomorrow", "+1 years")->format('Y-m-d')
        ]);

        $requestBody = [
            'clinic_id' => $clinic['id'],
            'email' => $this->faker->email(),
            'injection_date' => $this->faker->dateTimeBetween('tomorrow',$reactorCycle['expiration_date'])->format('Y-m-d'),
            'dog_name' => $this->faker->name(),
            'dog_breed' => $this->faker->name(),
            'dog_age' => $this->faker->numberBetween(1,20),
            'dog_weight' => $this->faker->randomFloat(2,0,50),
            'dog_gender' => $this->faker->randomElement(['male','female']),
            'reactor_name' => $reactor->name,
            'reactor_cycle_id' => $reactorCycle->id,
            'no_of_elbows' => $this->faker->numberBetween(1,10),
            'dosage_per_elbow' => 1,
            'order_instructions' => 'Test Instructions'
        ];

        $response = $this->post('/api/orders', $requestBody, $headers);

        $response->assertOk();
    }

    public function test_store_with_clinic_user_invalid_clinic_id()
    {
        $user = User::factory()->create([
            'email' => 'placeorder@example.com',
            'password' => Hash::make('placeorderpassword'),
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
            'email' =>'placeorder@example.com',
            'password' => 'placeorderpassword'
        ];
        $loginResponse = $this->post('/api/login', $loginBody);
        $token = $loginResponse['token'];
        $headers = ['Authorization' => "Bearer $token"];

        $user = User::factory()->create([
            'email' => 'placeorder2@example.com',
            'password' => Hash::make('placeorder2password'),
            'role' => 'clinic'
        ]);
        $clinic = Clinic::create([
            'account_id' => 'C999998',
            'name' => $this->faker->name,
            'address' => $this->faker->address(),
            'city' => $this->faker->city(),
            'state' => $this->faker->state(),
            'zipcode' => $this->faker->postcode(),
            'user_id' => $user['id'],
            'is_enabled' => true
        ]);
        $reactor = Reactor::create([
            'name' => $this->faker->name(),
        ]);
        $reactorCycle = ReactorCycle::firstOrCreate([
            'name' => $this->faker->name(),
            'reactor_id' => $reactor->id,
            'mass' => $this->faker->randomFloat(2,40,60),
            'target_start_date' => $this->faker->dateTimeBetween("-1 years", "now")->format('Y-m-d'),
            'expiration_date' => $this->faker->dateTimeBetween("tomorrow", "+1 years")->format('Y-m-d')
        ]);

        $requestBody = [
            'clinic_id' => $clinic['id'],
            'email' => $this->faker->email(),
            'injection_date' => $this->faker->dateTimeBetween('tomorrow',$reactorCycle['expiration_date'])->format('Y-m-d'),
            'dog_name' => $this->faker->name(),
            'dog_breed' => $this->faker->name(),
            'dog_age' => $this->faker->numberBetween(1,20),
            'dog_weight' => $this->faker->randomFloat(2,0,50),
            'dog_gender' => $this->faker->randomElement(['male','female']),
            'reactor_name' => $reactor->name,
            'reactor_cycle_id' => $reactorCycle->id,
            'no_of_elbows' => $this->faker->numberBetween(1,10),
            'dosage_per_elbow' => 1,
        ];

        $response = $this->post('/api/orders', $requestBody, $headers);

        $response->assertUnauthorized();
    }

    public function test_store_for_unprocessable_contents()
    {
        User::factory()->create([
            'email' => 'placeorder@example.com',
            'password' => Hash::make('placeorderpassword'),
            'role' => 'admin'
        ]);
        $loginBody = [
            'email' =>'placeorder@example.com',
            'password' => 'placeorderpassword'
        ];
        $loginResponse = $this->post('/api/login', $loginBody);
        $token = $loginResponse['token'];
        $headers = ['Authorization' => "Bearer $token"];

        $requestBody = [];

        $response = $this->post('/api/orders', $requestBody, $headers);

        $response->assertUnprocessable();
    }

    public function test_store_for_invalid_token()
    {
        $token = 'invalidToken';
        $headers = ['Authorization' => "Bearer $token"];

        $user = User::factory()->create([
            'email' => 'placeorder2@example.com',
            'password' => Hash::make('placeorder2password'),
            'role' => 'clinic'
        ]);
        $clinic = Clinic::create([
            'account_id' => 'C999999',
            'name' => $this->faker->name,
            'address' => $this->faker->address(),
            'city' => $this->faker->city(),
            'state' => $this->faker->state(),
            'zipcode' => $this->faker->postcode(),
            'user_id' => $user['id'],
            'is_enabled' => true
        ]);
        $reactor = Reactor::create([
            'name' => $this->faker->name(),
        ]);
        $reactorCycle = ReactorCycle::firstOrCreate([
            'name' => $this->faker->name(),
            'reactor_id' => $reactor->id,
            'mass' => $this->faker->randomFloat(2,40,60),
            'target_start_date' => $this->faker->dateTimeBetween("-1 years", "now")->format('Y-m-d'),
            'expiration_date' => $this->faker->dateTimeBetween("tomorrow", "+1 years")->format('Y-m-d')
        ]);

        $requestBody = [
            'clinic_id' => $clinic['id'],
            'email' => $this->faker->email(),
            'injection_date' => $this->faker->dateTimeBetween('tomorrow',$reactorCycle['expiration_date'])->format('Y-m-d'),
            'dog_name' => $this->faker->name(),
            'dog_breed' => $this->faker->name(),
            'dog_age' => $this->faker->numberBetween(1,20),
            'dog_weight' => $this->faker->randomFloat(2,0,50),
            'dog_gender' => $this->faker->randomElement(['male','female']),
            'reactor_name' => $reactor->name,
            'reactor_cycle_id' => $reactorCycle->id,
            'no_of_elbows' => $this->faker->numberBetween(1,10),
            'dosage_per_elbow' => 1,
        ];

        $response = $this->post('/api/orders', $requestBody, $headers);

        $response->assertUnauthorized();
        $response->assertJson([
            'message' => 'Unauthenticated.'
        ]);
    }

    public function test_store_for_invalid_clinic_id()
    {
        User::factory()->create([
            'email' => 'placeorder@example.com',
            'password' => Hash::make('placeorderpassword'),
            'role' => 'admin'
        ]);
        $loginBody = [
            'email' =>'placeorder@example.com',
            'password' => 'placeorderpassword'
        ];
        $loginResponse = $this->post('/api/login', $loginBody);
        $token = $loginResponse['token'];
        $headers = ['Authorization' => "Bearer $token"];

        $reactor = Reactor::create([
            'name' => $this->faker->name(),
        ]);
        $reactorCycle = ReactorCycle::firstOrCreate([
            'name' => $this->faker->name(),
            'reactor_id' => $reactor->id,
            'mass' => $this->faker->randomFloat(2,40,60),
            'target_start_date' => $this->faker->dateTimeBetween("-1 years", "now")->format('Y-m-d'),
            'expiration_date' => $this->faker->dateTimeBetween("tomorrow", "+1 years")->format('Y-m-d')
        ]);

        $requestBody = [
            'clinic_id' => 12345,
            'email' => $this->faker->email(),
            'injection_date' => $this->faker->dateTimeBetween('tomorrow',$reactorCycle['expiration_date'])->format('Y-m-d'),
            'dog_name' => $this->faker->name(),
            'dog_breed' => $this->faker->name(),
            'dog_age' => $this->faker->numberBetween(1,20),
            'dog_weight' => $this->faker->randomFloat(2,0,50),
            'dog_gender' => $this->faker->randomElement(['male','female']),
            'reactor_name' => $reactor->name,
            'reactor_cycle_id' => $reactorCycle->id,
            'no_of_elbows' => $this->faker->numberBetween(1,10),
            'dosage_per_elbow' => 1,
        ];

        $response = $this->post('/api/orders', $requestBody, $headers);

        $response->assertBadRequest();
        $response->assertJson([
            'message' => 'Clinic Not Found'
        ]);
    }

    public function test_store_with_invalid_reactor_name()
    {
        User::factory()->create([
            'email' => 'placeorder@example.com',
            'password' => Hash::make('placeorderpassword'),
            'role' => 'admin'
        ]);
        $loginBody = [
            'email' =>'placeorder@example.com',
            'password' => 'placeorderpassword'
        ];
        $loginResponse = $this->post('/api/login', $loginBody);
        $token = $loginResponse['token'];
        $headers = ['Authorization' => "Bearer $token"];

        $user = User::factory()->create([
            'email' => 'placeorder2@example.com',
            'password' => Hash::make('placeorder2password'),
            'role' => 'clinic'
        ]);
        $clinic = Clinic::create([
            'account_id' => 'C999999',
            'name' => $this->faker->name,
            'address' => $this->faker->address(),
            'city' => $this->faker->city(),
            'state' => $this->faker->state(),
            'zipcode' => $this->faker->postcode(),
            'user_id' => $user['id'],
            'is_enabled' => true
        ]);
        $reactor = Reactor::create([
            'name' => $this->faker->name(),
        ]);
        $reactorCycle = ReactorCycle::firstOrCreate([
            'name' => $this->faker->name(),
            'reactor_id' => $reactor->id,
            'mass' => $this->faker->randomFloat(2,40,60),
            'target_start_date' => $this->faker->dateTimeBetween("-1 years", "now")->format('Y-m-d'),
            'expiration_date' => $this->faker->dateTimeBetween("tomorrow", "+1 years")->format('Y-m-d')
        ]);

        $requestBody = [
            'clinic_id' => $clinic['id'],
            'email' => $this->faker->email(),
            'injection_date' => $this->faker->dateTimeBetween('tomorrow',$reactorCycle['expiration_date'])->format('Y-m-d'),
            'dog_name' => $this->faker->name(),
            'dog_breed' => $this->faker->name(),
            'dog_age' => $this->faker->numberBetween(1,20),
            'dog_weight' => $this->faker->randomFloat(2,0,50),
            'dog_gender' => $this->faker->randomElement(['male','female']),
            'reactor_name' => 'invalidReactorName',
            'reactor_cycle_id' => $reactorCycle->id,
            'no_of_elbows' => $this->faker->numberBetween(1,10),
            'dosage_per_elbow' => 1,
        ];

        $response = $this->post('/api/orders', $requestBody, $headers);

        $response->assertBadRequest();
        $response->assertJson([
            'message' => 'Reactor Not Found'
        ]);
    }

    public function test_store_for_invalid_reactor_cycle()
    {
        User::factory()->create([
            'email' => 'placeorder@example.com',
            'password' => Hash::make('placeorderpassword'),
            'role' => 'admin'
        ]);
        $loginBody = [
            'email' =>'placeorder@example.com',
            'password' => 'placeorderpassword'
        ];
        $loginResponse = $this->post('/api/login', $loginBody);
        $token = $loginResponse['token'];
        $headers = ['Authorization' => "Bearer $token"];

        $user = User::factory()->create([
            'email' => 'placeorder2@example.com',
            'password' => Hash::make('placeorder2password'),
            'role' => 'clinic'
        ]);
        $clinic = Clinic::create([
            'account_id' => 'C999999',
            'name' => $this->faker->name,
            'address' => $this->faker->address(),
            'city' => $this->faker->city(),
            'state' => $this->faker->state(),
            'zipcode' => $this->faker->postcode(),
            'user_id' => $user['id'],
            'is_enabled' => true
        ]);
        
        $reactor = Reactor::create([
            'name' => $this->faker->name(),
        ]);
        $reactorCycle = ReactorCycle::firstOrCreate([
            'name' => $this->faker->name(),
            'reactor_id' => $reactor->id,
            'mass' => $this->faker->randomFloat(2,40,60),
            'target_start_date' => $this->faker->dateTimeBetween("-1 years", "now")->format('Y-m-d'),
            'expiration_date' => $this->faker->dateTimeBetween("tomorrow", "+1 years")->format('Y-m-d')
        ]);

        $requestBody = [
            'clinic_id' => $clinic->id,
            'order_no' => 'WEBO99999',
            'email' => $this->faker->email(),
            'injection_date' => $this->faker->dateTimeBetween('tomorrow',$reactorCycle['expiration_date'])->format('Y-m-d'),
            'dog_name' => $this->faker->name(),
            'dog_breed' => $this->faker->name(),
            'dog_age' => $this->faker->numberBetween(1,20),
            'dog_weight' => $this->faker->randomFloat(2,0,50),
            'dog_gender' => $this->faker->randomElement(['male','female']),
            'reactor_name' => $reactor->name,
            'reactor_cycle_id' => 12345,
            'no_of_elbows' => $this->faker->numberBetween(1,10),
            'dosage_per_elbow' => 1,
        ];

        $response = $this->post('/api/orders', $requestBody, $headers);

        $response->assertBadRequest();
        $response->assertJson([
            'message' => 'Reactor Cycle Not Found'
        ]);
    }

    public function test_store_for_reactor_and_reactor_cycle_not_match()
    {
        User::factory()->create([
            'email' => 'placeorder@example.com',
            'password' => Hash::make('placeorderpassword'),
            'role' => 'admin'
        ]);
        $loginBody = [
            'email' =>'placeorder@example.com',
            'password' => 'placeorderpassword'
        ];
        $loginResponse = $this->post('/api/login', $loginBody);
        $token = $loginResponse['token'];
        $headers = ['Authorization' => "Bearer $token"];

        $user = User::factory()->create([
            'email' => 'placeorder2@example.com',
            'password' => Hash::make('placeorder2password'),
            'role' => 'clinic'
        ]);
        $clinic = Clinic::create([
            'account_id' => 'C999999',
            'name' => $this->faker->name,
            'address' => $this->faker->address(),
            'city' => $this->faker->city(),
            'state' => $this->faker->state(),
            'zipcode' => $this->faker->postcode(),
            'user_id' => $user['id'],
            'is_enabled' => true
        ]);
        
        $reactor = Reactor::create([
            'name' => $this->faker->name(),
        ]);
        $reactorCycle = ReactorCycle::firstOrCreate([
            'name' => $this->faker->name(),
            'reactor_id' => $reactor->id,
            'mass' => $this->faker->randomFloat(2,40,60),
            'target_start_date' => $this->faker->dateTimeBetween("-1 years", "now")->format('Y-m-d'),
            'expiration_date' => $this->faker->dateTimeBetween("tomorrow", "+1 years")->format('Y-m-d')
        ]);

        $reactor = Reactor::create([
            'name' => $this->faker->name(),
        ]);

        $requestBody = [
            'clinic_id' => $clinic->id,
            'order_no' => 'WEBO99999',
            'email' => $this->faker->email(),
            'injection_date' => $this->faker->dateTimeBetween('tomorrow',$reactorCycle['expiration_date'])->format('Y-m-d'),
            'dog_name' => $this->faker->name(),
            'dog_breed' => $this->faker->name(),
            'dog_age' => $this->faker->numberBetween(1,20),
            'dog_weight' => $this->faker->randomFloat(2,0,50),
            'dog_gender' => $this->faker->randomElement(['male','female']),
            'reactor_name' => $reactor->name,
            'reactor_cycle_id' => $reactorCycle->id,
            'no_of_elbows' => $this->faker->numberBetween(1,10),
            'dosage_per_elbow' => 1,
        ];

        $response = $this->post('/api/orders', $requestBody, $headers);

        $response->assertBadRequest();
        $response->assertJson([
            'message' => 'Reactor does not match selected reactor cycle'
        ]);
    }

    public function test_store_with_not_available_date()
    {
        User::factory()->create([
            'email' => 'placeorder@example.com',
            'password' => Hash::make('placeorderpassword'),
            'role' => 'admin'
        ]);
        $loginBody = [
            'email' =>'placeorder@example.com',
            'password' => 'placeorderpassword'
        ];
        $loginResponse = $this->post('/api/login', $loginBody);
        $token = $loginResponse['token'];
        $headers = ['Authorization' => "Bearer $token"];

        $user = User::factory()->create([
            'email' => 'placeorder2@example.com',
            'password' => Hash::make('placeorder2password'),
            'role' => 'clinic'
        ]);
        $clinic = Clinic::create([
            'account_id' => 'C999999',
            'name' => $this->faker->name,
            'address' => $this->faker->address(),
            'city' => $this->faker->city(),
            'state' => $this->faker->state(),
            'zipcode' => $this->faker->postcode(),
            'user_id' => $user['id'],
            'is_enabled' => true
        ]);
        $reactor = Reactor::create([
            'name' => $this->faker->name(),
        ]);
        $reactorCycle = ReactorCycle::firstOrCreate([
            'name' => $this->faker->name(),
            'reactor_id' => $reactor->id,
            'mass' => $this->faker->randomFloat(2,40,60),
            'target_start_date' => $this->faker->dateTimeBetween("-10 days", "-5 days")->format('Y-m-d'),
            'expiration_date' => $this->faker->dateTimeBetween("-5 days", "today")->format('Y-m-d')
        ]);

        $requestBody = [
            'clinic_id' => $clinic['id'],
            'email' => $this->faker->email(),
            'injection_date' => $this->faker->dateTimeBetween("+1 days","+1 years")->format('Y-m-d'),
            'dog_name' => $this->faker->name(),
            'dog_breed' => $this->faker->name(),
            'dog_age' => $this->faker->numberBetween(1,20),
            'dog_weight' => $this->faker->randomFloat(2,0,50),
            'dog_gender' => $this->faker->randomElement(['male','female']),
            'reactor_name' => $reactor->name,
            'reactor_cycle_id' => $reactorCycle->id,
            'no_of_elbows' => $this->faker->numberBetween(1,10),
            'dosage_per_elbow' => 1,
        ];

        $response = $this->post('/api/orders', $requestBody, $headers);

        $response->assertBadRequest();
        $response->assertJson([
            'message' => 'Reactor cycle is unavailable for injection date and total dosage'
        ]);
    }

    public function test_store_with_not_available_mass()
    {
        User::factory()->create([
            'email' => 'placeorder@example.com',
            'password' => Hash::make('placeorderpassword'),
            'role' => 'admin'
        ]);
        $loginBody = [
            'email' =>'placeorder@example.com',
            'password' => 'placeorderpassword'
        ];
        $loginResponse = $this->post('/api/login', $loginBody);
        $token = $loginResponse['token'];
        $headers = ['Authorization' => "Bearer $token"];

        $user = User::factory()->create([
            'email' => 'placeorder2@example.com',
            'password' => Hash::make('placeorder2password'),
            'role' => 'clinic'
        ]);
        $clinic = Clinic::create([
            'account_id' => 'C999999',
            'name' => $this->faker->name,
            'address' => $this->faker->address(),
            'city' => $this->faker->city(),
            'state' => $this->faker->state(),
            'zipcode' => $this->faker->postcode(),
            'user_id' => $user['id'],
            'is_enabled' => true
        ]);
        $reactor = Reactor::create([
            'name' => $this->faker->name(),
        ]);
        $reactorCycle = ReactorCycle::firstOrCreate([
            'name' => $this->faker->name(),
            'reactor_id' => $reactor->id,
            'mass' => $this->faker->randomFloat(2,40,60),
            'target_start_date' => $this->faker->dateTimeBetween("-1 years", "now")->format('Y-m-d'),
            'expiration_date' => $this->faker->dateTimeBetween("tomorrow", "+1 years")->format('Y-m-d')
        ]);

        $requestBody = [
            'clinic_id' => $clinic['id'],
            'email' => $this->faker->email(),
            'injection_date' => $this->faker->dateTimeBetween("+1 years +1 days","+2years")->format('Y-m-d'),
            'dog_name' => $this->faker->name(),
            'dog_breed' => $this->faker->name(),
            'dog_age' => $this->faker->numberBetween(1,20),
            'dog_weight' => $this->faker->randomFloat(2,0,50),
            'dog_gender' => $this->faker->randomElement(['male','female']),
            'reactor_name' => $reactor->name,
            'reactor_cycle_id' => $reactorCycle->id,
            'no_of_elbows' => $this->faker->numberBetween(61,100),
            'dosage_per_elbow' => 1,
        ];

        $response = $this->post('/api/orders', $requestBody, $headers);

        $response->assertBadRequest();
        $response->assertJson([
            'message' => 'Reactor cycle is unavailable for injection date and total dosage'
        ]);
    }

    public function test_store_with_exception()
    {
        User::factory()->create([
            'email' => 'placeorder@example.com',
            'password' => Hash::make('placeorderpassword'),
            'role' => 'admin'
        ]);
        $loginBody = [
            'email' =>'placeorder@example.com',
            'password' => 'placeorderpassword'
        ];
        $loginResponse = $this->post('/api/login', $loginBody);
        $token = $loginResponse['token'];
        $headers = ['Authorization' => "Bearer $token"];
        $mock = $this->mock(OrderService::class, function (MockInterface $mock) {
            $mock->shouldReceive('store')->andThrow(new Exception);
        });
        $user = User::factory()->create([
            'email' => 'placeorder2@example.com',
            'password' => Hash::make('placeorder2password'),
            'role' => 'clinic'
        ]);
        $clinic = Clinic::create([
            'account_id' => 'C999999',
            'name' => $this->faker->name,
            'address' => $this->faker->address(),
            'city' => $this->faker->city(),
            'state' => $this->faker->state(),
            'zipcode' => $this->faker->postcode(),
            'user_id' => $user['id'],
            'is_enabled' => true
        ]);
        $reactor = Reactor::create([
            'name' => $this->faker->name(),
        ]);
        $reactorCycle = ReactorCycle::firstOrCreate([
            'name' => $this->faker->name(),
            'reactor_id' => $reactor->id,
            'mass' => $this->faker->randomFloat(2,40,60),
            'target_start_date' => $this->faker->dateTimeBetween("-1 years", "now")->format('Y-m-d'),
            'expiration_date' => $this->faker->dateTimeBetween("tomorrow", "+1 years")->format('Y-m-d')
        ]);

        $requestBody = [
            'clinic_id' => $clinic['id'],
            'email' => $this->faker->email(),
            'injection_date' => $this->faker->dateTimeBetween('tomorrow',$reactorCycle['expiration_date'])->format('Y-m-d'),
            'dog_name' => $this->faker->name(),
            'dog_breed' => $this->faker->name(),
            'dog_age' => $this->faker->numberBetween(1,20),
            'dog_weight' => $this->faker->randomFloat(2,0,50),
            'dog_gender' => $this->faker->randomElement(['male','female']),
            'reactor_name' => $reactor->name,
            'reactor_cycle_id' => $reactorCycle->id,
            'no_of_elbows' => $this->faker->numberBetween(1,10),
            'dosage_per_elbow' => 1,
        ];

        $response = $this->post('/api/orders', $requestBody, $headers);

        $response->assertInternalServerError();
        $response->assertExactJson([
            'message' => 'Something went wrong.'
        ]);
        $mock->mockery_teardown();
    }
}
