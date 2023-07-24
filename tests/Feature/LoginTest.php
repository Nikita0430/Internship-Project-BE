<?php

namespace Tests\Feature;

use App\Models\Clinic;
use App\Models\User;
use App\Services\Auth\AuthService;
use Exception;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Mockery\MockInterface;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use DatabaseTransactions, WithFaker;

    public function test_unprocessable_content_on_missing_email(){
        $request = [
            'password' => 'login',
            'remember_me' => false
        ];
        
        $response = $this->json('POST', 'api/login', $request);

        $response->assertUnprocessable();

        $json = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('message', $json);
        $this->assertArrayHasKey('errors', $json);
        $this->assertArrayHasKey('email', $json['errors']);
    }

    public function test_unprocessable_content_on_missing_password(){
        $request = [
            'email' => 'login@test.com',
            'remember_me' => false
        ];
        
        $response = $this->json('POST', 'api/login', $request);

        $response->assertUnprocessable();

        $json = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('message', $json);
        $this->assertArrayHasKey('errors', $json);
        $this->assertArrayHasKey('password', $json['errors']);
    }

    public function test_unprocessable_content_on_invalid_email_format(){
        $request = [
            'email' => 'invalidemailformats',
            'password' => 'login',
            'remember_me' => false
        ];
        
        $response = $this->json('POST', 'api/login', $request);

        $response->assertUnprocessable();

        $json = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('message', $json);
        $this->assertArrayHasKey('errors', $json);
        $this->assertArrayHasKey('email', $json['errors']);
    }

    public function test_unauthorized_for_incorrect_password(){
        User::factory()->create([
            'email' => 'login@test.com',
            'password' => Hash::make('login'),
            'role' => 'admin'
        ]);

        $request = [
            'email' => 'login@test.com',
            'password' => 'invalidlogin'
        ];
        
        $response = $this->json('POST', 'api/login', $request);

        $response->assertUnauthorized();
        $response->assertExactJson([
            'message' => 'Incorrect password.'
        ]);
    }

    public function test_unauthorized_for_not_registered_email(){
        $request = [
            'email' => 'login@test.com',
            'password' => 'loginpass'
        ];
        
        $response = $this->json('POST', 'api/login', $request);

        $response->assertUnauthorized();
        $response->assertExactJson([
            'message' => 'User not registered.'
        ]);
    }

    public function test_success_for_admin_user_valid_credentials(){
        User::factory()->create([
            'email' => 'login@test.com',
            'password' => Hash::make('loginpassword'),
            'role' => 'admin'
        ]);

        $request = [
            'email' => 'login@test.com',
            'password' => 'loginpassword'
        ];
        
        $response = $this->json('POST', 'api/login', $request);

        $response->assertOk();
        $json = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('user', $json);
        $this->assertArrayHasKey('token', $json);
    }

    public function test_success_for_clinic_user_valid_credentials(){
        $user = User::factory()->create([
            'email' => 'login@example.com',
            'password' => Hash::make('loginpassword'),
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

        $request = [
            'email' => 'login@example.com',
            'password' => 'loginpassword'
        ];
        
        $response = $this->json('POST', 'api/login', $request);

        $response->assertOk();
        
        $json = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('user', $json);
        $this->assertArrayHasKey('clinic', $json['user']);
        $this->assertArrayHasKey('token', $json);
    }

    public function test_unauthorized_for_disabled_clinic_user_credentials(){
        $user = User::factory()->create([
            'email' => 'login@example.com',
            'password' => Hash::make('loginpassword'),
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
            'is_enabled' => false
        ]);

        $request = [
            'email' => 'login@example.com',
            'password' => 'loginpassword'
        ];
        
        $response = $this->json('POST', 'api/login', $request);
        $response->assertUnauthorized();

        $response->assertExactJson([
            'message' => 'Clinic is disabled.'
        ]);
    }

    public function test_internal_server_error_on_exception()
    {
        $request = [
            'email' => 'login@test.com',
            'password' => 'loginpass',
        ];
        
        $mock = $this->mock(AuthService::class, function (MockInterface $mock) {
            $mock->shouldReceive('login')->andThrow(new Exception);
        });

        $response = $this->json('POST', 'api/login', $request);

        $response->assertInternalServerError();
        $response->assertExactJson([
            'message' => 'Something went wrong.'
        ]);
        $mock->mockery_teardown();
    }
}
