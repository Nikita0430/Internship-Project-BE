<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\Auth\AuthService;
use Exception;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Mockery\MockInterface;
use Tests\TestCase;

class ForgotPasswordTest extends TestCase
{
    use DatabaseTransactions;

    public function test_unprocessable_content_on_missing_email(){
        $request = [];
        
        $response = $this->json('POST', 'api/forgot-password', $request);

        $response->assertUnprocessable();

        $json = json_decode($response->getContent(), true);

        $this->assertArrayHasKey('message', $json);
        $this->assertArrayHasKey('errors', $json);
        $this->assertArrayHasKey('email', $json['errors']);
    }

    public function test_unprocessable_content_on_invalid_email_format(){
        $request = [
            'email' => 'invalidtestformat'
        ];
        
        $response = $this->json('POST', 'api/forgot-password', $request);

        $response->assertUnprocessable();

        $json = json_decode($response->getContent(), true);

        $this->assertArrayHasKey('message', $json);
        $this->assertArrayHasKey('errors', $json);
        $this->assertArrayHasKey('email', $json['errors']);
    }

    public function test_bad_request_for_invalid_email(){
        $request = [
            'email' => 'invalidforgotpassword@test.com'
        ];
        
        $response = $this->json('POST', 'api/forgot-password', $request);

        $response->assertBadRequest();
    }

    public function test_success_for_valid_email(){
        User::factory()->create([
            'email' => 'forgotpassword@test.com',
            'password' => Hash::make('forgotpassword'),
            'role' => 'admin'
        ]);

        $request = [
            'email' => 'forgotpassword@test.com',
        ];
        
        $response = $this->json('POST', 'api/forgot-password', $request);

        $response->assertOk();
    }

    public function test_success_if_token_is_already_stored_and_not_used(){
        User::factory()->create([
            'email' => 'forgotpassword@test.com',
            'password' => Hash::make('forgotpassword'),
            'role' => 'admin'
        ]);

        $request = [
            'email' => 'forgotpassword@test.com',
        ];
        
        $response = $this->json('POST', 'api/forgot-password', $request);

        $response = $this->json('POST', 'api/forgot-password', $request);

        $response->assertOk();
    }

    public function test_internal_server_error_on_exception()
    {
        User::factory()->create([
            'email' => 'forgotpassword@test.com',
            'password' => Hash::make('forgotpassword'),
            'role' => 'admin'
        ]);
        $request = [
            'email' => 'forgotpassword@test.com',
            'password' => 'forgotpassword',
        ];
        $mock = $this->mock(AuthService::class, function (MockInterface $mock) {
            $mock->shouldReceive('forgotPassword')->andThrow(new Exception);
        });
        $response = $this->json('POST', 'api/forgot-password', $request);
        $response->assertInternalServerError();
        $response->assertExactJson([
            'message' => 'Something went wrong.'
        ]);
        $mock->mockery_teardown();
    }
}
