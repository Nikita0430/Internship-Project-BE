<?php

namespace Tests\Feature;

use App\Models\Clinic;
use App\Models\User;
use App\Services\Clinic\ManageClinicService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AddClinicTest extends TestCase
{
    use DatabaseTransactions, WithFaker;

    public function test_store_with_valid_inputs()
    {
        User::factory()->create([
            'email' => 'addclinic@example.com',
            'password' => Hash::make('addclinicpassword'),
            'role' => 'admin'
        ]);
        $loginBody = [
            'email' => 'addclinic@example.com',
            'password' => 'addclinicpassword'
        ];
        $loginResponse = $this->post('/api/login', $loginBody);
        $token = $loginResponse['token'];
        $headers = ['Authorization' => "Bearer $token"];

        $data = [
            'email' => $this->faker->unique()->safeEmail,
            'password' => 'password',
            'is_enabled' => true,
            'name' => $this->faker->name,
            'address' => $this->faker->address,
            'city' => $this->faker->city,
            'state' => $this->faker->state(),
            'zipcode' => $this->faker->postcode,
        ];

        $response = $this->post('/api/clinics', $data, $headers);

        $response->assertOk();
    }

    public function test_store_with_existing_email()
    {
        User::factory()->create([
            'email' => 'addclinic@example.com',
            'password' => Hash::make('addclinicpassword'),
            'role' => 'admin'
        ]);
        $loginBody = [
            'email' => 'addclinic@example.com',
            'password' => 'addclinicpassword'
        ];
        $loginResponse = $this->post('/api/login', $loginBody);
        $token = $loginResponse['token'];
        $headers = ['Authorization' => "Bearer $token"];

        $data = [
            'email' => 'addclinic@example.com',
            'password' => 'password',
            'is_enabled' => true,
            'name' => $this->faker->name,
            'address' => $this->faker->address,
            'city' => $this->faker->city,
            'state' => $this->faker->state(),
            'zipcode' => $this->faker->postcode,
        ];

        $response = $this->post('/api/clinics', $data, $headers);

        $response->assertBadRequest();
    }

    public function test_store_with_existing_name()
    {
        User::factory()->create([
            'email' => 'addclinic@example.com',
            'password' => Hash::make('addclinicpassword'),
            'role' => 'admin'
        ]);
        $loginBody = [
            'email' => 'addclinic@example.com',
            'password' => 'addclinicpassword'
        ];
        $loginResponse = $this->post('/api/login', $loginBody);
        $token = $loginResponse['token'];
        $headers = ['Authorization' => "Bearer $token"];

        $user = User::factory()->create([
            'email' => 'addclinic2@example.com',
            'password' => Hash::make('addclinic2password'),
            'role' => 'clinic'
        ]);
        Clinic::create([
            'account_id' => 'C999999',
            'name' => 'TestingClinicName',
            'address' => $this->faker->address,
            'city' => $this->faker->city,
            'state' => $this->faker->state(),
            'zipcode' => $this->faker->postcode,
            'user_id' => $user['id'],
            'is_enabled' => true
        ]);

        $data = [
            'email' => 'addclinic3@example.com',
            'password' => 'password',
            'is_enabled' => true,
            'name' => 'TestingClinicName',
            'address' => $this->faker->address,
            'city' => $this->faker->city,
            'state' => $this->faker->state(),
            'zipcode' => $this->faker->postcode,
        ];

        $response = $this->post('/api/clinics', $data, $headers);

        $response->assertBadRequest();
    }

    public function test_store_with_invalid_token()
    {
        $token = 'Invalid Token';
        $headers = ['Authorization' => "Bearer $token"];

        $data = [
            'email' => $this->faker->unique()->safeEmail,
            'password' => 'password',
            'is_enabled' => true,
            'name' => $this->faker->name,
            'address' => $this->faker->address,
            'city' => $this->faker->city,
            'state' => $this->faker->state(),
            'zipcode' => $this->faker->postcode,
        ];

        $response = $this->post('/api/clinics', $data, $headers);

        $response->assertUnauthorized();
    }

    public function test_store_with_exception()
    {
        User::factory()->create([
            'email' => 'addclinic@example.com',
            'password' => Hash::make('addclinicpassword'),
            'role' => 'admin'
        ]);
        $loginBody = [
            'email' => 'addclinic@example.com',
            'password' => 'addclinicpassword'
        ];
        $loginResponse = $this->post('/api/login', $loginBody);
        $token = $loginResponse['token'];
        $headers = ['Authorization' => "Bearer $token"];
        $this->mock(ManageClinicService::class, function ($mock) {
            $mock->shouldReceive('addClinic')
                ->andThrow(new \Exception('Service exception'));
        });

        $data = [
            'email' => $this->faker->unique()->safeEmail,
            'password' => 'password',
            'name' => $this->faker->name,
            'address' => $this->faker->address,
            'city' => $this->faker->city,
            'state' => $this->faker->state(),
            'zipcode' => $this->faker->postcode,
        ];

        $response = $this->post('/api/clinics', $data);

        $response->assertStatus(Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    public function test_store_request_made_by_clinic_user()
    {
        $user = User::factory()->create([
            'email' => 'addclinic@example.com',
            'password' => Hash::make('addclinicpassword'),
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
            'email' => 'addclinic@example.com',
            'password' => 'addclinicpassword'
        ];
        $loginResponse = $this->post('/api/login', $loginBody);
        $token = $loginResponse['token'];
        $headers = ['Authorization' => "Bearer $token"];

        $data = [
            'email' => $this->faker->unique()->safeEmail,
            'password' => 'password',
            'name' => $this->faker->name,
            'address' => $this->faker->address,
            'city' => $this->faker->city,
            'state' => $this->faker->state(),
            'zipcode' => $this->faker->postcode,
        ];

        $response = $this->post('/api/clinics', $data, $headers);

        $response->assertUnauthorized();
        $response->assertExactJson([
            'message' => 'Unauthorized'
        ]);
    }
}