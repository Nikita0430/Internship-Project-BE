<?php

namespace Tests\Feature;

use App\Models\PasswordResetToken;
use App\Models\User;
use App\Services\Auth\AuthService;
use Carbon\Carbon;
use Exception;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Mockery\MockInterface;
use Tests\TestCase;

class ChangePasswordTest extends TestCase
{
    use DatabaseTransactions;

    public function test_unprocessable_content_on_missing_token(){
        $request = [
            'password' => 'newpass'
        ];
        
        $response = $this->json('POST', 'api/change-password', $request);

        $response->assertUnprocessable();

        $json = json_decode($response->getContent(), true);

        $this->assertArrayHasKey('message', $json);
        $this->assertArrayHasKey('errors', $json);
        $this->assertArrayHasKey('token', $json['errors']);
    }

    public function test_unprocessable_content_on_missing_password(){
        $request = [
            'token' => 'O1sVEkdDvtWLEmaAzLmEh6tTXdAcvZz6L7uXrA1HzIUDuUTMM1xCXRCtLR0o'
        ];
        
        $response = $this->json('POST', 'api/change-password', $request);

        $response->assertUnprocessable();

        $json = json_decode($response->getContent(), true);

        $this->assertArrayHasKey('message', $json);
        $this->assertArrayHasKey('errors', $json);
        $this->assertArrayHasKey('password', $json['errors']);
    }

    public function test_unauthorized_on_invalid_token(){
        $token = 'thisisarandomtoken';
        $request = [
            'token' => $token,
            'password' => 'newtestpass'
        ];
        
        $response = $this->json('POST', 'api/change-password', $request);

        $response->assertUnauthorized();
    }

    public function test_success_on_valid_token(){
        User::factory()->create([
            'email' => 'changepassword@test.com',
            'password' => Hash::make('changepassword'),
        ]);
        $request = [
            'email' => 'changepassword@test.com',
            'password' => 'cahngepassword'
        ];
        $response = $this->json('POST', 'api/forgot-password', $request);

        $token = PasswordResetToken::where('email',$request['email'])->first()->token;
        $request = [
            'token' => $token,
            'password' => $request['password']
        ];
        
        $response = $this->json('POST', 'api/change-password', $request);

        $response->assertOk();
    }

    public function test_internal_server_error_on_exception()
    {
        $request = [
            'token' => 'token',
            'password' => 'password',
        ];
        $mock = $this->mock(AuthService::class, function (MockInterface $mock) {
            $mock->shouldReceive('changePassword')->andThrow(new Exception);
        });
        $response = $this->json('POST', 'api/change-password', $request);
        $response->assertInternalServerError();
        $response->assertExactJson([
            'message' => 'Something went wrong.'
        ]);
        $mock->mockery_teardown();
    }

    public function test_unprocessable_content_on_missing_token_in_check_token_api(){        
        $response = $this->json('POST', 'api/check-change-password', []);
        $response->assertUnprocessable();
        $json = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('message', $json);
        $this->assertArrayHasKey('errors', $json);
        $this->assertArrayHasKey('token', $json['errors']);
    }

    public function test_invalid_token_in_check_token_api(){
        $token = 'thisisarandomtoken';
        $request = [
            'token' => $token,
        ];
        $response = $this->json('POST', 'api/check-change-password', $request);
        $response->assertUnauthorized();
    }

    public function test_valid_token_in_check_token_api(){
        User::factory()->create([
            'email' => 'changepassword@test.com',
            'password' => Hash::make('changepassword'),
        ]);
        $request = [
            'email' => 'changepassword@test.com',
            'password' => 'cahngepassword'
        ];
        $response = $this->json('POST', 'api/forgot-password', $request);
        $token = PasswordResetToken::where('email',$request['email'])->first()->token;
        $request = [
            'token' => $token
        ];
        $response = $this->json('POST', 'api/check-change-password', $request);
        $response->assertOk();
    }

    public function test_expired_token_in_check_token_api(){
        $token = PasswordResetToken::create([
            'token' => 'thisisarandomtoken',
            'email' => 'changepassword@test.com',
            'expires_at' => Carbon::now()->subDays(2)
        ])->token;
        $request = [
            'token' => $token
        ];
        $response = $this->json('POST', 'api/check-change-password', $request);
        $response->assertUnauthorized();
    }

    public function test_internal_server_error_on_exception_in_check_token_api()
    {
        $request = [
            'token' => 'token'
        ];
        $mock = $this->mock(AuthService::class, function (MockInterface $mock) {
            $mock->shouldReceive('checkChangePasswordToken')->andThrow(new Exception);
        });
        $response = $this->json('POST', 'api/check-change-password', $request);
        $response->assertInternalServerError();
        $response->assertExactJson([
            'message' => 'Something went wrong.'
        ]);
        $mock->mockery_teardown();
    }
}
