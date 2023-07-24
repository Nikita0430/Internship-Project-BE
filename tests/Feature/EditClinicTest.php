<?php

namespace Tests\Feature;

use App\Models\Clinic;
use App\Models\User;
use App\Services\Clinic\ManageClinicService;
use Exception;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Mockery\MockInterface;
use Illuminate\Http\Response;
use Tests\TestCase;

class EditClinicTest extends TestCase
{
    use DatabaseTransactions, WithFaker;

    public function test_show_request_with_valid_inputs()
    {
        User::factory()->create([
            'email' => 'editclinic@example.com',
            'password' => Hash::make('editclinicpassword'),
            'role' => 'admin'
        ]);
        $loginBody = [
            'email' =>'editclinic@example.com',
            'password' => 'editclinicpassword'
        ];
        $loginResponse = $this->post('/api/login', $loginBody);
        $token = $loginResponse['token'];
        $headers = ['Authorization' => "Bearer $token"];

        $user = User::factory()->create([
            'email' => 'editclinic2@example.com',
            'password' => Hash::make('editclinic2password'),
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

        $response = $this->get('/api/clinics/'.$clinic->id, $headers);

        $response->assertOk();
    }

    public function test_show_request_made_by_clinic_user()
    {
        $user = User::factory()->create([
            'email' => 'editclinic@example.com',
            'password' => Hash::make('editclinicpassword'),
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
            'email' =>'editclinic@example.com',
            'password' => 'editclinicpassword'
        ];
        $loginResponse = $this->post('/api/login', $loginBody);
        $token = $loginResponse['token'];
        $headers = ['Authorization' => "Bearer $token"];

        $user = User::factory()->create([
            'email' => 'editclinic2@example.com',
            'password' => Hash::make('editclinic2password'),
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

        $response = $this->get('/api/clinics/'.$clinic->id, $headers);

        $response->assertUnauthorized();
        $response->assertExactJson([
            'message' => 'Unauthorized'
         ]);
    }

    public function test_show_request_not_found()
    {
        User::factory()->create([
            'email' => 'editclinic@example.com',
            'password' => Hash::make('editclinicpassword'),
            'role' => 'admin'
        ]);
        $loginBody = [
            'email' =>'editclinic@example.com',
            'password' => 'editclinicpassword'
        ];
        $loginResponse = $this->post('/api/login', $loginBody);
        $token = $loginResponse['token'];
        $headers = ['Authorization' => "Bearer $token"];

        $user = User::factory()->create([
            'email' => 'editclinic2@example.com',
            'password' => Hash::make('editclinic2password'),
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

        $incorrectId = '9000';
        $response = $this->get('/api/clinics/'.$incorrectId, $headers);

        $response->assertNotFound();
    }

    public function test_show_for_exception()
    {
        User::factory()->create([
            'email' => 'editclinic@example.com',
            'password' => Hash::make('editclinicpassword'),
            'role' => 'admin'
        ]);
        $loginBody = [
            'email' =>'editclinic@example.com',
            'password' => 'editclinicpassword'
        ];
        $loginResponse = $this->post('/api/login', $loginBody);
        $token = $loginResponse['token'];
        $headers = ['Authorization' => "Bearer $token"];
        $mock = $this->mock(ManageClinicService::class, function (MockInterface $mock) {
            $mock->shouldReceive('show')->andThrow(new Exception);
        });
        $response = $this->get('/api/clinics/1', $headers);
        $response->assertInternalServerError();
        $response->assertExactJson([
            'message' => 'Something went wrong.'
        ]);
        $mock->mockery_teardown();
    }

    public function test_update_request_with_valid_inputs()
    {
        User::factory()->create([
            'email' => 'editclinic@example.com',
            'password' => Hash::make('editclinicpassword'),
            'role' => 'admin'
        ]);
        $loginBody = [
            'email' =>'editclinic@example.com',
            'password' => 'editclinicpassword'
        ];
        $loginResponse = $this->post('/api/login', $loginBody);
        $token = $loginResponse['token'];
        $headers = ['Authorization' => "Bearer $token"];

        $data = [
            'name' => $this->faker->name,
            'address' => $this->faker->address,
            'city' => $this->faker->city,
            'state' => $this->faker->state(),
            'zipcode' => $this->faker->postcode,
        ];

        $user = User::factory()->create([
            'email' => 'editclinic2@example.com',
            'password' => Hash::make('editclinic2password'),
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

        $response = $this->put('/api/clinics/'.$clinic->id, $data, $headers);

        $response->assertOk();
    }

    public function test_update_with_invalid_inputs()
    {
        User::factory()->create([
            'email' => 'editclinic@example.com',
            'password' => Hash::make('editclinicpassword'),
            'role' => 'admin'
        ]);
        $loginBody = [
            'email' =>'editclinic@example.com',
            'password' => 'editclinicpassword'
        ];
        $loginResponse = $this->post('/api/login', $loginBody);
        $token = $loginResponse['token'];
        $headers = ['Authorization' => "Bearer $token"];

        $data = [
            'name' => '',
            'address' => '',
            'city' => '',
            'state' => '',
            'zipcode' => '',
        ];

        $user = User::factory()->create([
            'email' => 'editclinic2@example.com',
            'password' => Hash::make('editclinic2password'),
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

        $response = $this->put('/api/clinics/'.$clinic->id, $data, $headers);

        $response->assertUnprocessable();

        $json = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('message', $json);
        $this->assertArrayHasKey('errors', $json);
        $this->assertArrayHasKey('name', $json['errors']);
        $this->assertArrayHasKey('address', $json['errors']);
        $this->assertArrayHasKey('city', $json['errors']);
        $this->assertArrayHasKey('state', $json['errors']);
        $this->assertArrayHasKey('zipcode', $json['errors']);
    }


    public function test_update_request_made_by_clinic_user()
    {
        $user = User::factory()->create([
            'email' => 'editclinic@example.com',
            'password' => Hash::make('editclinicpassword'),
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
            'email' =>'editclinic@example.com',
            'password' => 'editclinicpassword'
        ];
        $loginResponse = $this->post('/api/login', $loginBody);
        $token = $loginResponse['token'];
        $headers = ['Authorization' => "Bearer $token"];

        $data = [
            'name' => $this->faker->name,
            'address' => $this->faker->address,
            'city' => $this->faker->city,
            'state' => $this->faker->state(),
            'zipcode' => $this->faker->postcode,
        ];

        $user = User::factory()->create([
            'email' => 'editclinic2@example.com',
            'password' => Hash::make('editclinic2password'),
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

        $response = $this->put('/api/clinics/'.$clinic->id, $data, $headers);

        $response->assertUnauthorized();
        $response->assertExactJson([
           'message' => 'Unauthorized'
        ]);
    }

    public function test_update_request_not_found()
    {
        User::factory()->create([
            'email' => 'editclinic@example.com',
            'password' => Hash::make('editclinicpassword'),
            'role' => 'admin'
        ]);
        $loginBody = [
            'email' =>'editclinic@example.com',
            'password' => 'editclinicpassword'
        ];
        $loginResponse = $this->post('/api/login', $loginBody);
        $token = $loginResponse['token'];
        $headers = ['Authorization' => "Bearer $token"];

        $data = [
            'name' => $this->faker->name,
            'address' => $this->faker->address,
            'city' => $this->faker->city,
            'state' => $this->faker->state(),
            'zipcode' => $this->faker->postcode,
        ];

        $user = User::factory()->create([
            'email' => 'editclinic2@example.com',
            'password' => Hash::make('editclinic2password'),
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

        $incorrectId = '9000';
        $response = $this->put('/api/clinics/'.$incorrectId, $data, $headers);

        $response->assertNotFound();
    }

    public function test_update_request_with_existing_clinic()
    {
        User::factory()->create([
            'email' => 'editclinic@example.com',
            'password' => Hash::make('editclinicpassword'),
            'role' => 'admin'
        ]);
        $loginBody = [
            'email' =>'editclinic@example.com',
            'password' => 'editclinicpassword'
        ];
        $loginResponse = $this->post('/api/login', $loginBody);
        $token = $loginResponse['token'];
        $headers = ['Authorization' => "Bearer $token"];
        $user = User::factory()->create([
            'email' => 'editclinic2@example.com',
            'password' => Hash::make('editclinic2password'),
            'role' => 'clinic'
        ]);
        Clinic::create([
            'account_id' => 'C999999',
            'is_enabled' => true,
            'name' => 'Existing Clinic Name',
            'address' => $this->faker->address,
            'city' => $this->faker->city,
            'state' => $this->faker->state(),
            'zipcode' => $this->faker->postcode,
            'user_id' => $user['id'],
        ]);
        $data = [
            'name' => 'Existing Clinic Name',
            'address' => $this->faker->address,
            'city' => $this->faker->city,
            'state' => $this->faker->state(),
            'zipcode' => $this->faker->postcode,
        ];
        $user = User::factory()->create([
            'email' => 'editclinic3@example.com',
            'password' => Hash::make('editclinic3password'),
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
        $response = $this->put('/api/clinics/'.$clinic->id, $data, $headers);
        $response->assertBadRequest();
    }

    public function test_update_with_exception()
    {
        User::factory()->create([
            'email' => 'editclinic@example.com',
            'password' => Hash::make('editclinicpassword'),
            'role' => 'admin'
        ]);
        $loginBody = [
            'email' =>'editclinic@example.com',
            'password' => 'editclinicpassword'
        ];
        $loginResponse = $this->post('/api/login', $loginBody);
        $token = $loginResponse['token'];
        $headers = ['Authorization' => "Bearer $token"];
        $data = [
            'name' => $this->faker->name,
            'address' => $this->faker->address,
            'city' => $this->faker->city,
            'state' => $this->faker->state(),
            'zipcode' => $this->faker->postcode,
        ];
        $mock = $this->mock(ManageClinicService::class, function (MockInterface $mock) {
            $mock->shouldReceive('update')->andThrow(new Exception);
        });
        $response = $this->put('/api/clinics/1', $data, $headers);
        $response->assertInternalServerError();
        $response->assertExactJson([
            'message' => 'Something went wrong.'
        ]);
        $mock->mockery_teardown();
    }

    public function test_destroy_request_with_valid_inputs()
    {
        User::factory()->create([
            'email' => 'editclinic@example.com',
            'password' => Hash::make('editclinicpassword'),
            'role' => 'admin'
        ]);
        $loginBody = [
            'email' =>'editclinic@example.com',
            'password' => 'editclinicpassword'
        ];
        $loginResponse = $this->post('/api/login', $loginBody);
        $token = $loginResponse['token'];
        $headers = ['Authorization' => "Bearer $token"];

        $user = User::factory()->create([
            'email' => 'editclinic2@example.com',
            'password' => Hash::make('editclinic2password'),
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

        $response = $this->delete('/api/clinics/'.$clinic->id, $headers);

        $clinic = Clinic::where('account_id', 'C999999')->first();
        $this->assertNull($clinic);

        $response->assertOk();
    }

    public function test_destroy_request_made_by_clinic_user()
    {
        $user = User::factory()->create([
            'email' => 'editclinic@example.com',
            'password' => Hash::make('editclinicpassword'),
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
            'email' =>'editclinic@example.com',
            'password' => 'editclinicpassword'
        ];
        $loginResponse = $this->post('/api/login', $loginBody);
        $token = $loginResponse['token'];
        $headers = ['Authorization' => "Bearer $token"];

        $user = User::factory()->create([
            'email' => 'editclinic2@example.com',
            'password' => Hash::make('editclinic2password'),
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

        $response = $this->delete('/api/clinics/'.$clinic->id, $headers);

        $response->assertUnauthorized();
        $response->assertExactJson([
           'message' => 'Unauthorized'
        ]);
    }

    public function test_destroy_request_not_found()
    {
        User::factory()->create([
            'email' => 'editclinic@example.com',
            'password' => Hash::make('editclinicpassword'),
            'role' => 'admin'
        ]);
        $loginBody = [
            'email' =>'editclinic@example.com',
            'password' => 'editclinicpassword'
        ];
        $loginResponse = $this->post('/api/login', $loginBody);
        $token = $loginResponse['token'];
        $headers = ['Authorization' => "Bearer $token"];

        $user = User::factory()->create([
            'email' => 'editclinic2@example.com',
            'password' => Hash::make('editclinic2password'),
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

        $incorrectId = '9000';
        $response = $this->delete('/api/clinics/'.$incorrectId, $headers);

        $response->assertNotFound();
    }

    public function test_destroy_for_exception()
    {
        User::factory()->create([
            'email' => 'editclinic@example.com',
            'password' => Hash::make('editclinicpassword'),
            'role' => 'admin'
        ]);
        $loginBody = [
            'email' =>'editclinic@example.com',
            'password' => 'editclinicpassword'
        ];
        $loginResponse = $this->post('/api/login', $loginBody);
        $token = $loginResponse['token'];
        $headers = ['Authorization' => "Bearer $token"];
        $mock = $this->mock(ManageClinicService::class, function (MockInterface $mock) {
            $mock->shouldReceive('destroy')->andThrow(new Exception);
        });
        $response = $this->delete('/api/clinics/1', $headers);
        $response->assertInternalServerError();
        $response->assertExactJson([
            'message' => 'Something went wrong.'
        ]);
        $mock->mockery_teardown();
    }

    public function test_update_status_request_with_valid_inputs()
    {
        User::factory()->create([
            'email' => 'editclinic@example.com',
            'password' => Hash::make('editclinicpassword'),
            'role' => 'admin'
        ]);
        $loginBody = [
            'email' =>'editclinic@example.com',
            'password' => 'editclinicpassword'
        ];
        $loginResponse = $this->post('/api/login', $loginBody);
        $token = $loginResponse['token'];
        $headers = ['Authorization' => "Bearer $token"];

        $data = [
            'is_enabled' => false
        ];

        $user = User::factory()->create([
            'email' => 'editclinic2@example.com',
            'password' => Hash::make('editclinic2password'),
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

        $response = $this->patch('/api/clinics/'.$clinic->id.'/status', $data, $headers);

        $response->assertOk();
    }

    public function test_update_status_request_with_exception()
    {
        $user = User::factory()->create([
            'email' => 'editclinic@example.com',
            'password' => Hash::make('editclinicpassword'),
            'role' => 'admin'
        ]);
        $loginBody = [
            'email' =>'editclinic@example.com',
            'password' => 'editclinicpassword'
        ];
        $loginResponse = $this->post('/api/login', $loginBody);
        $token = $loginResponse['token'];
        $headers = ['Authorization' => "Bearer $token"];

        $this->mock(ManageClinicService::class, function ($mock) {
            $mock->shouldReceive('updateStatus')
                ->andThrow(new \Exception('Service exception'));
        });

        $data = [
            'is_enabled' => false
        ];

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

        $response = $this->patch('/api/clinics/'.$clinic->id.'/status', $data, $headers);
        $response->assertStatus(Response::HTTP_INTERNAL_SERVER_ERROR);

    }


    public function test_update_status_with_invalid_inputs()
    {
        User::factory()->create([
            'email' => 'editclinic@example.com',
            'password' => Hash::make('editclinicpassword'),
            'role' => 'admin'
        ]);
        $loginBody = [
            'email' =>'editclinic@example.com',
            'password' => 'editclinicpassword'
        ];
        $loginResponse = $this->post('/api/login', $loginBody);
        $token = $loginResponse['token'];
        $headers = ['Authorization' => "Bearer $token"];

        $data = [];

        $user = User::factory()->create([
            'email' => 'editclinic2@example.com',
            'password' => Hash::make('editclinic2password'),
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

        $response = $this->patch('/api/clinics/'.$clinic->id.'/status', $data, $headers);

        $response->assertUnprocessable();

        $json = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('message', $json);
        $this->assertArrayHasKey('errors', $json);
        $this->assertArrayHasKey('is_enabled', $json['errors']);
    }


    public function test_update_status_request_made_by_clinic_user()
    {
        $user = User::factory()->create([
            'email' => 'editclinic@example.com',
            'password' => Hash::make('editclinicpassword'),
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
            'email' =>'editclinic@example.com',
            'password' => 'editclinicpassword'
        ];
        $loginResponse = $this->post('/api/login', $loginBody);
        $token = $loginResponse['token'];
        $headers = ['Authorization' => "Bearer $token"];

        $data = [
            'is_enabled' => false
        ];

        $user = User::factory()->create([
            'email' => 'editclinic2@example.com',
            'password' => Hash::make('editclinic2password'),
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

        $response = $this->patch('/api/clinics/'.$clinic->id.'/status', $data, $headers);

        $response->assertUnauthorized();
        $response->assertExactJson([
           'message' => 'Unauthorized'
        ]);
    }

    public function test_update_status_request_not_found()
    {
        User::factory()->create([
            'email' => 'editclinic@example.com',
            'password' => Hash::make('editclinicpassword'),
            'role' => 'admin'
        ]);
        $loginBody = [
            'email' =>'editclinic@example.com',
            'password' => 'editclinicpassword'
        ];
        $loginResponse = $this->post('/api/login', $loginBody);
        $token = $loginResponse['token'];
        $headers = ['Authorization' => "Bearer $token"];

        $data = [
            'is_enabled' => false
        ];

        $user = User::factory()->create([
            'email' => 'editclinic2@example.com',
            'password' => Hash::make('editclinic2password'),
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

        $incorrectId = '9000';
        $response = $this->patch('/api/clinics/'.$incorrectId.'/status', $data, $headers);

        $response->assertNotFound();
    }
}
