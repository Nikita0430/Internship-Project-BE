<?php

namespace Tests\Feature;

use App\Exceptions\Handler;
use App\Http\Middleware\RedirectIfAuthenticated;
use App\Http\Middleware\TrustHosts;
use App\Models\User;
use App\Providers\BroadcastServiceProvider;
use Exception;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;
use Throwable;


class ExampleTest extends TestCase
{
    use DatabaseTransactions;
    
    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertViewIs('welcome');
    }

    public function test_redirects_authenticated_user()
    {
        $user = User::factory()->create([
            'email' => 'exampletest@example.com',
            'password' => Hash::make('exampletestpass'),
            'role' => 'admin'
        ]);
        $this->actingAs($user);

        $request = Request::create('/api/login', 'POST', [
            'email' => $user['email'],
            'password' => 'exampletestpass',
        ]);

        $middleware = new RedirectIfAuthenticated;
        $response = $middleware->handle($request, function () {});

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertEquals(route('home'), $response->getTargetUrl());
    }

    public function test_redirects_unauthenticated_user()
    {
        $request = Request::create('/api/login', 'POST', [
            'email' => 'exampletest@example.com',
            'password' => 'pass',
        ]);

        $middleware = new RedirectIfAuthenticated;
        $response = $middleware->handle($request, function () {
            return new Response('Next Middleware');
        });

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals('Next Middleware', $response->getContent());
    }

    public function test_trust_host_middleware()
    {
        $obj = new TrustHosts(app());

        $obj->hosts();

        $header = [
            'host' => config('app.url')
        ];

        $response = $this->json('GET','http://localhost:8000',[],$header);

        $response->assertOk();
    }

    public function test_brodcast_provider()
    {
        $obj = new BroadcastServiceProvider(app());
        $this->assertNull($obj->boot());
    }

    public function test_authenticate_middleware()
    {
        $headers = [
            'Accept' => 'application/json',
        ];

        $response = $this->json('GET',route('testroute'),[],$headers);
        $response->assertUnauthorized();
      
    }

    public function testReportable()
    {
        $handler = $this->app->make(Handler::class);
        $logger = $this->getMockBuilder('Illuminate\Log\Logger')->disableOriginalConstructor()->getMock();
        Log::swap($logger);
        $exception = new Exception('Test exception');
        $handler->reportable(function (Throwable $e) use ($exception) {
            $this->assertEquals($exception, $e);
        }, $exception);
        Log::shouldReceive('error')->once()->withArgs(function ($message, $context) use ($exception) {
            return $message === 'Test exception' && $context['exception'] === $exception;
        });
        $handler->report($exception);
    }
}
