<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\Auth\AuthService;
use Exception;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Mockery\MockInterface;
use Tests\TestCase;

class LogoutTest extends TestCase
{
    use DatabaseTransactions;

    public function test_unauthorized_on_invalid_token(){
        $token = 'invalidToken';
        $headers = ['Authorization' => "Bearer $token"];

        $response = $this->post('/api/logout', $headers);

        $response->assertUnauthorized();
    }

    public function test_success_on_valid_token(){
        User::factory()->create([
            'email' => 'logout@example.com',
            'password' => Hash::make('logoutpassword'),
            'role' => 'admin'
        ]);
        $loginBody = [
            'email' =>'logout@example.com',
            'password' => 'logoutpassword'
        ];
        $loginResponse = $this->post('/api/login', $loginBody);
        $token = $loginResponse['token'];
        $headers = ['Authorization' => "Bearer $token"];

        $response = $this->post('/api/logout', $headers);

        $response->assertOk();
    }

    public function test_internal_server_error_on_exception(){
        User::factory()->create([
            'email' => 'logout@example.com',
            'password' => Hash::make('logoutpassword'),
            'role' => 'admin'
        ]);
        $loginBody = [
            'email' =>'logout@example.com',
            'password' => 'logoutpassword'
        ];
        $loginResponse = $this->post('/api/login', $loginBody);
        $token = $loginResponse['token'];
        $headers = ['Authorization' => "Bearer $token"];
        $mock = $this->mock(AuthService::class, function (MockInterface $mock) {
            $mock->shouldReceive('logout')->andThrow(new Exception);
        });
        $response = $this->post('/api/logout', $headers);
        $response->assertInternalServerError();
        $mock->mockery_teardown();
    }
}
