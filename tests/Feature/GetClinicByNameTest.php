<?php

namespace Tests\Feature;

use App\Models\Clinic;
use App\Models\User;
use App\Services\Clinic\ManageClinicService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;
use Illuminate\Http\Response;

class GetClinicByNameTest extends TestCase
{
    use DatabaseTransactions, WithFaker;

    public function test_show_by_name_request_with_admin_user_valid_inputs()
    {
        User::factory()->create([
            'email' => 'getclinicbyname@example.com',
            'password' => Hash::make('getclinicbynamepassword'),
            'role' => 'admin'
        ]);
        $loginBody = [
            'email' =>'getclinicbyname@example.com',
            'password' => 'getclinicbynamepassword'
        ];
        $loginResponse = $this->post('/api/login', $loginBody);
        $token = $loginResponse['token'];
        $headers = ['Authorization' => "Bearer $token"];

        $user = User::factory()->create([
            'email' => 'getclinicbyname2@example.com',
            'password' => Hash::make('getclinicbyname2password'),
            'role' => 'clinic'
        ]);
        $clinic = Clinic::create([
            'account_id' => 'C999998',
            'is_enabled' => true,
            'name' => $this->faker->name,
            'address' => $this->faker->address,
            'city' => $this->faker->city,
            'state' => $this->faker->state(),
            'zipcode' => $this->faker->postcode,
            'user_id' => $user['id'],
        ]);

        $response = $this->get('/api/clinics/name/'.$clinic->name, $headers);

        $response->assertOk();
    }

    public function test_show_by_name_request_for_exception()
    {
        User::factory()->create([
            'email' => 'getclinicbyname@example.com',
            'password' => Hash::make('getclinicbynamepassword'),
            'role' => 'admin'
        ]);
        $loginBody = [
            'email' =>'getclinicbyname@example.com',
            'password' => 'getclinicbynamepassword'
        ];
        $loginResponse = $this->post('/api/login', $loginBody);
        $token = $loginResponse['token'];
        $headers = ['Authorization' => "Bearer $token"];

        $this->mock(ManageClinicService::class, function ($mock) {
            $mock->shouldReceive('showByName')
                ->andThrow(new \Exception('Service exception'));
        });

        $user = User::factory()->create([
            'email' => 'getclinicbyname2@example.com',
            'password' => Hash::make('getclinicbyname2password'),
            'role' => 'clinic'
        ]);
        $clinic = Clinic::create([
            'account_id' => 'C999998',
            'is_enabled' => true,
            'name' => $this->faker->name,
            'address' => $this->faker->address,
            'city' => $this->faker->city,
            'state' => $this->faker->state(),
            'zipcode' => $this->faker->postcode,
            'user_id' => $user['id'],
        ]);

        $response = $this->get('/api/clinics/name/'.$clinic->name, $headers);

        $response->assertStatus(Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    public function test_clinic_dropdown_request_for_exception()
    {
        User::factory()->create([
            'email' => 'getclinicbyname@example.com',
            'password' => Hash::make('getclinicbynamepassword'),
            'role' => 'admin'
        ]);
        $loginBody = [
            'email' =>'getclinicbyname@example.com',
            'password' => 'getclinicbynamepassword'
        ];
        $loginResponse = $this->post('/api/login', $loginBody);
        $token = $loginResponse['token'];
        $headers = ['Authorization' => "Bearer $token"];

        $this->mock(ManageClinicService::class, function ($mock) {
            $mock->shouldReceive('getEnabledClinicNames')
                ->andThrow(new \Exception('Service exception'));
        });

        $response = $this->get('/api/clinics/dropdown', $headers);

        $response->assertStatus(Response::HTTP_INTERNAL_SERVER_ERROR);
    }
    public function test_show_by_name_request_with_clinic_user_valid_inputs()
    {
        $user = User::factory()->create([
            'email' => 'getclinicbyname@example.com',
            'password' => Hash::make('getclinicbynamepassword'),
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
            'email' =>'getclinicbyname@example.com',
            'password' => 'getclinicbynamepassword'
        ];
        $loginResponse = $this->post('/api/login', $loginBody);
        $token = $loginResponse['token'];
        $headers = ['Authorization' => "Bearer $token"];

        $response = $this->get('/api/clinics/name/', $headers);

        $response->assertOk();
    }

    public function test_show_by_name_request_made_by_clinic_user_invalid_input()
    {
        $user = User::factory()->create([
            'email' => 'getclinicbyname@example.com',
            'password' => Hash::make('getclinicbynamepassword'),
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
            'email' =>'getclinicbyname@example.com',
            'password' => 'getclinicbynamepassword'
        ];
        $loginResponse = $this->post('/api/login', $loginBody);
        $token = $loginResponse['token'];
        $headers = ['Authorization' => "Bearer $token"];

        $user = User::factory()->create([
            'email' => 'getclinicbyname2@example.com',
            'password' => Hash::make('getclinicbyname2password'),
            'role' => 'clinic'
        ]);
        $clinic = Clinic::create([
            'account_id' => 'C999998',
            'is_enabled' => true,
            'name' => $this->faker->name,
            'address' => $this->faker->address,
            'city' => $this->faker->city,
            'state' => $this->faker->state(),
            'zipcode' => $this->faker->postcode,
            'user_id' => $user['id'],
        ]);

        $response = $this->get('/api/clinics/name/'.$clinic->name, $headers);

        $response->assertUnauthorized();
        $response->assertExactJson([
            'message' => 'Unauthorized'
         ]);
    }

    public function test_show_by_name_request_not_found()
    {
        User::factory()->create([
            'email' => 'getclinicbyname@example.com',
            'password' => Hash::make('getclinicbynamepassword'),
            'role' => 'admin'
        ]);
        $loginBody = [
            'email' =>'getclinicbyname@example.com',
            'password' => 'getclinicbynamepassword'
        ];
        $loginResponse = $this->post('/api/login', $loginBody);
        $token = $loginResponse['token'];
        $headers = ['Authorization' => "Bearer $token"];

        $user = User::factory()->create([
            'email' => 'getclinicbyname2@example.com',
            'password' => Hash::make('getclinicbyname2password'),
            'role' => 'clinic'
        ]);
        Clinic::create([
            'account_id' => 'C999999',
            'is_enabled' => true,
            'name' => $this->faker->name,
            'address' => $this->faker->address,
            'city' => $this->faker->city,
            'state' => $this->faker->state(),
            'zipcode' => $this->faker->postcode,
            'user_id' => $user['id'],
        ]);

        $incorrectName = 'This is an Non Existing Clinic Name';
        $response = $this->get('/api/clinics/name/'.$incorrectName, $headers);

        $response->assertNotFound();
    }
}
