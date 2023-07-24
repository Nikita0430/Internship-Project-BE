<?php

namespace Tests\Feature;

use App\Models\Clinic;
use App\Services\User\UserDetailService;
use Exception;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;
use App\Models\User;

class UserDetailsControllerTest extends TestCase
{
    use DatabaseTransactions, WithFaker;

    public function test_unauthorized_access_to_profile()
    {
        $user = User::factory()->create([
            'email' => 'userdetails@example.com',
            'password' => Hash::make('userdetailspassword'),
            'role' => 'clinic'
        ]);
        $user->clinic()->create([
            'account_id' => '123456',
            'is_enabled' => false,
            'name' => 'clinicTest',
            'address' => 'TestAddress',
            'city' => 'TestCity',
            'state' => 'TestState',
            'zipcode' => '123456',
        ]);

        $response = $this->getJson('/api/user-details');
        $response->assertStatus(Response::HTTP_UNAUTHORIZED)
            ->assertJson([
                'message' => 'Unauthenticated.',
            ]);
    }
    public function test_admin_access_to_profile()
    {
        $user = User::factory()->create([
            'email' => 'userdetails@example.com',
            'password' => Hash::make('userdetailspassword'),
            'role' => 'admin'
        ]);
        $loginBody = [
            'email' => 'userdetails@example.com',
            'password' => 'userdetailspassword'
        ];
        $loginResponse = $this->post('/api/login', $loginBody);
        $token = $loginResponse['token'];
        $headers = ['Authorization' => "Bearer $token"];

        $response = $this->getJson('/api/user-details', $headers);

        $response->assertStatus(Response::HTTP_UNAUTHORIZED)
            ->assertJson([
                'message' => 'Unauthorized',
            ]);
    }

    public function test_show()
    {
        $user = User::factory()->create([
            'email' => 'userdetails@example.com',
            'password' => Hash::make('userdetailspassword'),
            'role' => 'clinic'
        ]);
        $user->clinic()->create([
            'account_id' => '123456',
            'is_enabled' => true,
            'name' => 'clinicTest',
            'address' => 'TestAddress',
            'city' => 'TestCity',
            'state' => 'TestState',
            'zipcode' => '123456',
        ]);

        $loginBody = [
            'email' => 'userdetails@example.com',
            'password' => 'userdetailspassword'
        ];
        $loginResponse = $this->post('/api/login', $loginBody);
        $token = $loginResponse['token'];
        $headers = ['Authorization' => "Bearer $token"];

        $response = $this->getJson('/api/user-details', $headers);
        $response->assertStatus(Response::HTTP_OK);

        $response->assertJson([
            'message' => 'Profile found',
            'profile' => [
                'email' => 'userdetails@example.com',
                'account_id' => '123456',
                'name' => 'clinicTest',
                'address' => 'TestAddress',
                'city' => 'TestCity',
                'state' => 'TestState',
                'zipcode' => '123456',
            ],
        ]);
    }

    public function test_update_user_profile()
    {
        $user = User::factory()->create([
            'email' => 'userdetails@example.com',
            'password' => Hash::make('userdetailspassword'),
            'role' => 'clinic'
        ]);
        $user->clinic()->create([
            'account_id' => '123456',
            'name' => 'clinicTest',
            'address' => 'TestAddress',
            'city' => 'TestCity',
            'state' => 'TestState',
            'zipcode' => '123456',
        ]);

        $loginBody = [
            'email' => 'userdetails@example.com',
            'password' => 'userdetailspassword'
        ];
        $loginResponse = $this->post('/api/login', $loginBody);
        $token = $loginResponse['token'];
        $headers = ['Authorization' => "Bearer $token"];

        $updatedData = [
            'account_id' => '123456',
            'name' => 'clinicTestNew',
            'address' => 'TestAddressNew',
            'city' => 'TestCityNew',
            'state' => 'TestStateNew',
            'zipcode' => '123456',
            'password' => 'TestPasswordNew',
        ];

        $response = $this->put("/api/user-details", $updatedData, $headers);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Profile updated successfully',
            ]);

        $this->assertDatabaseHas('clinics', [
            'account_id' => $updatedData['account_id'],
            'name' => $updatedData['name'],
            'address' => $updatedData['address'],
            'city' => $updatedData['city'],
            'state' => $updatedData['state'],
            'zipcode' => $updatedData['zipcode'],
        ]);
    }

    public function test_profile_update_validation_failure()
    {
        $user = User::factory()->create([
            'email' => 'userdetails@example.com',
            'password' => Hash::make('userdetailspassword'),
            'role' => 'clinic'
        ]);
        $user->clinic()->create([
            'account_id' => '123456',
            'name' => 'clinicTest',
            'address' => 'TestAddress',
            'city' => 'TestCity',
            'state' => 'TestState',
            'zipcode' => '123456',
        ]);

        $loginBody = [
            'email' => 'userdetails@example.com',
            'password' => 'userdetailspassword'
        ];
        $loginResponse = $this->post('/api/login', $loginBody);
        $token = $loginResponse['token'];
        $headers = ['Authorization' => "Bearer $token"];

        $updatedData = [
            'account_id' => 123456,
            'name' => 'clinicTestNew',
            'address' => 'TestAddressNew',
            'city' => 'TestCityNew',
            'state' => 'TestStateNew',
            'zipcode' => 123456,
        ];

        $response = $this->put("/api/user-details", $updatedData, $headers);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testInternalServerErrorOnException()
    {
        // Create a user for testing
        $user = User::factory()->create([
            'email' => 'userdetails@example.com',
            'password' => Hash::make('userdetailspassword'),
            'role' => 'clinic'
        ]);
        $user->clinic()->create([
            'account_id' => '123456',
            'name' => 'clinicTest',
            'address' => 'TestAddress',
            'city' => 'TestCity',
            'state' => 'TestState',
            'zipcode' => '123456',
        ]);

        $loginBody = [
            'email' => 'userdetails@example.com',
            'password' => 'userdetailspassword'
        ];
        $loginResponse = $this->post('/api/login', $loginBody);
        $token = $loginResponse['token'];
        $headers = ['Authorization' => "Bearer $token"];

        $mock = $this->mock(UserDetailService::class, function ($mock) use ($user) {
            $mock->shouldReceive('getUserDetail')
                ->with($user)
                ->andThrow(new Exception('Something went wrong.'));
        });

        $response = $this->actingAs($user)
            ->get('/api/user-details');

        $response->assertInternalServerError();
        $response->assertExactJson([
            'message' => 'Something went wrong.'
        ]);
        $mock->mockery_teardown();
    }

    public function testInternalServerErrorOnExceptionForUpdateProfile()
    {
        // Create a user for testing
        $user = User::factory()->create([
            'email' => 'userdetails@example.com',
            'password' => Hash::make('userdetailspassword'),
            'role' => 'clinic'
        ]);
        $user->clinic()->create([
            'account_id' => '123456',
            'name' => 'clinicTest',
            'address' => 'TestAddress',
            'city' => 'TestCity',
            'state' => 'TestState',
            'zipcode' => '123456',
        ]);

        $loginBody = [
            'email' => 'userdetails@example.com',
            'password' => 'userdetailspassword'
        ];
        $loginResponse = $this->post('/api/login', $loginBody);
        $token = $loginResponse['token'];
        $headers = ['Authorization' => "Bearer $token"];

        // Mock the ProfileService and throw an exception
        $mock = $this->mock(UserDetailService::class, function ($mock) use ($user) {
            $mock->shouldReceive('updateUserProfile')
                ->with($user, [
                    'name' => 'clinicTest',
                    'address' => 'TestAddress',
                    'city' => 'TestCity',
                    'state' => 'TestState',
                    'zipcode' => '123456',
                ])
                ->andThrow(new Exception('Something went wrong.'));
        });

        // Make a request to the controller action
        $response = $this->actingAs($user)
            ->put('/api/user-details', [
                'name' => 'John Doe',
                'address' => 'TestAddress',
                'city' => 'TestCity',
                'state' => 'TestState',
                'zipcode' => '123456'
            ]);

        // Assert the response status code and JSON structure
        $response->assertStatus(Response::HTTP_INTERNAL_SERVER_ERROR)
            ->assertJson([
                'message' => 'Something went wrong.'
            ]);
    }

    public function testUpdateUserProfileClinicNameTaken()
    {
        $user = User::factory()->create([
            'email' => 'userdetails@example.com',
            'password' => Hash::make('userdetailspassword'),
            'role' => 'clinic'
        ]);
        $user->clinic()->create([
            'account_id' => '123456',
            'name' => 'clinicTest',
            'address' => 'TestAddress',
            'city' => 'TestCity',
            'state' => 'TestState',
            'zipcode' => '123456',
        ]);

        $loginBody = [
            'email' => 'userdetails@example.com',
            'password' => 'userdetailspassword'
        ];
        $loginResponse = $this->post('/api/login', $loginBody);
        $token = $loginResponse['token'];
        $headers = ['Authorization' => "Bearer $token"];

        $user = User::factory()->create([
            'email' => 'userdetails2@example.com',
            'password' => Hash::make('userdetails2password'),
            'role' => 'clinic'
        ]);
        $user->clinic()->create([
            'account_id' => '1234567',
            'name' => 'Existing Clinic Name',
            'address' => 'TestAddress',
            'city' => 'TestCity',
            'state' => 'TestState',
            'zipcode' => '123456',
        ]);
        $updatedData = [
            'account_id' => '123456',
            'name' => 'Existing Clinic Name',
            'address' => 'TestAddressNew',
            'city' => 'TestCityNew',
            'state' => 'TestStateNew',
            'zipcode' => '123456',
            'password' => 'TestPasswordNew',
        ];

        $response = $this->put("/api/user-details", $updatedData, $headers);

        $response->assertBadRequest();
        $response->assertStatus(400)
            ->assertJson([
                'message' => 'Clinic name has already been taken.',
            ]);

    }
}