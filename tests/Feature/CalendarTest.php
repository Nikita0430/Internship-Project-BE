<?php

namespace Tests\Feature;

use App\Models\Clinic;
use App\Models\Order;
use App\Models\Reactor;
use App\Models\ReactorCycle;
use App\Models\User;
use App\Services\Calendar\CalendarService;
use Carbon\Carbon;
use Exception;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Mockery\MockInterface;
use Tests\TestCase;

class CalendarTest extends TestCase
{
    use DatabaseTransactions, WithFaker;

    public function test_reactor_list_success_on_valid_request(){
        User::factory()->create([
            'email' => 'addclinic@example.com',
            'password' => Hash::make('addclinicpassword'),
            'role' => 'admin'
        ]);
        $loginBody = [
            'email' =>'addclinic@example.com',
            'password' => 'addclinicpassword'
        ];
        $loginResponse = $this->post('/api/login', $loginBody);
        $token = $loginResponse['token'];
        $headers = ['Authorization' => "Bearer $token"];

        Reactor::firstOrCreate([
            'name' => 'TestReactor1'
        ]);
        Reactor::firstOrCreate([
            'name' => 'TestReactor2'
        ]);
        Reactor::firstOrCreate([
            'name' => 'TestReactor3'
        ]);

        $response = $this->get('/api/reactors', $headers);

        $response->assertOk();
    }

    public function test_reactor_list_success_for_clinic(){
        $user = User::factory()->create([
            'email' => 'reactorlist@example.com',
            'password' => Hash::make('reactorlistpassword'),
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
            'email' =>'reactorlist@example.com',
            'password' => 'reactorlistpassword'
        ];
        $loginResponse = $this->post('/api/login', $loginBody);
        $token = $loginResponse['token'];
        $headers = ['Authorization' => "Bearer $token"];

        Reactor::firstOrCreate([
            'name' => 'TestReactor1'
        ]);
        Reactor::firstOrCreate([
            'name' => 'TestReactor2'
        ]);
        $reactor = Reactor::firstOrCreate([
            'name' => 'TestReactor3'
        ]);

        $reactorCycle = ReactorCycle::create([
            'name' => $this->faker->name(),
            'reactor_id' => $reactor->id,
            'mass' => $this->faker->randomFloat(2,40,60),
            'target_start_date' => $this->faker->dateTimeBetween("-1 years", "now")->format('Y-m-d'),
            'expiration_date' => $this->faker->dateTimeBetween("tomorrow", "+1 years")->format('Y-m-d')
        ]);

        Order::create([
            'clinic_id' => $clinic->id,
            'order_no' => 'WEBO99999',
            'email' => $this->faker->email(),
            'placed_at' => Carbon::now()->toDateTimeString(),
            'shipped_at' => Carbon::now()->addDay()->toDateTimeString(),
            'injection_date' => $this->faker->dateTimeBetween('tomorrow',$reactorCycle['expiration_date'])->format('Y-m-d'),
            'dog_name' => 'Tom',
            'dog_breed' => 'labrador',
            'dog_age' => 17,
            'dog_weight' => 20,
            'dog_gender' => 'male',
            'no_of_elbows' => 3,
            'dosage_per_elbow' => 1.5,
            'total_dosage' => 4.5,
            'reactor_id' => $reactor->id,
            'reactor_cycle_id' => $reactorCycle->id,
            'status' => config('global.orders.status.Pending')
        ]);

        $response = $this->get('/api/reactors', $headers);

        $response->assertOk();
    }

    public function test_reactor_list_on_exception(){
        User::factory()->create([
            'email' => 'reactorlist@example.com',
            'password' => Hash::make('reactorlistpassword'),
            'role' => 'admin'
        ]);
        $loginBody = [
            'email' =>'reactorlist@example.com',
            'password' => 'reactorlistpassword'
        ];
        $loginResponse = $this->post('/api/login', $loginBody);
        $token = $loginResponse['token'];
        $headers = ['Authorization' => "Bearer $token"];
        $mock = $this->mock(CalendarService::class, function (MockInterface $mock) {
            $mock->shouldReceive('getReactorList')->andThrow(new Exception);
        });
        $response = $this->get('/api/reactors', $headers);
        $response->assertInternalServerError();
        $response->assertExactJson([
            'message' => 'Something went wrong.'
        ]);
        $mock->mockery_teardown();
    }

    public function test_reactor_list_unauthenticated(){
        $token = 'Invalid Token';
        $headers = ['Authorization' => "Bearer $token"];

        $response = $this->get('/api/reactors', $headers);

        $response->assertUnauthorized();
    }

    public function test_availability_success_on_valid_request(){
        User::factory()->create([
            'email' => 'addclinic@example.com',
            'password' => Hash::make('addclinicpassword'),
            'role' => 'admin'
        ]);
        $loginBody = [
            'email' =>'addclinic@example.com',
            'password' => 'addclinicpassword'
        ];
        $loginResponse = $this->post('/api/login', $loginBody);
        $token = $loginResponse['token'];
        $headers = ['Authorization' => "Bearer $token"];

        $month = Carbon::now()->addMonth()->month;
        $year = Carbon::now()->addMonth()->year;
        $reactor = Reactor::firstOrCreate([
            'name' => 'TestReactor1'
        ]);
        ReactorCycle::create([
            'name' => 'TestReactorCycle1',
            'reactor_id' => $reactor->id,
            'mass' => 30,
            'target_start_date' => Carbon::createFromDate($year, $month, 21)->format('Y-m-d'),
            'expiration_date' => Carbon::createFromDate($year, $month, 16)->addMonth()->format('Y-m-d'),
        ]);
        ReactorCycle::create([
            'name' => 'TestReactorCycle2',
            'reactor_id' => $reactor->id,
            'mass' => 39,
            'target_start_date' => Carbon::createFromDate($year, $month, 12)->format('Y-m-d'),
            'expiration_date' => Carbon::createFromDate($year, $month, 18)->format('Y-m-d')
        ]);
        ReactorCycle::create([
            'name' => 'TestReactorCycle3',
            'reactor_id' => $reactor->id,
            'mass' => 0,
            'target_start_date' => Carbon::createFromDate($year, $month, 9)->format('Y-m-d'),
            'expiration_date' => Carbon::createFromDate($year, $month, 2)->addMonth()->format('Y-m-d')
        ]);

        $data = [
            'reactor_name' => $reactor->name,
            'month' => $month,
            'year' => $year
        ];

        $response = $this->post('/api/calendar', $data, $headers);

        $response->assertOk();
    }

    public function test_availability_for_past_dates_success_on_valid_request(){
        User::factory()->create([
            'email' => 'calendar@example.com',
            'password' => Hash::make('calendarpassword'),
            'role' => 'admin'
        ]);
        $loginBody = [
            'email' =>'calendar@example.com',
            'password' => 'calendarpassword'
        ];
        $loginResponse = $this->post('/api/login', $loginBody);
        $token = $loginResponse['token'];
        $headers = ['Authorization' => "Bearer $token"];

        $month = Carbon::now()->subMonth()->month;
        $year = Carbon::now()->subMonth()->year;
        $reactor = Reactor::firstOrCreate([
            'name' => 'TestReactor1'
        ]);
        $data = [
            'reactor_name' => $reactor->name,
            'month' => $month,
            'year' => $year
        ];

        $response = $this->post('/api/calendar', $data, $headers);

        $response->assertOk();
    }

    public function test_availability_unprocessable_content(){
        User::factory()->create([
            'email' => 'addclinic@example.com',
            'password' => Hash::make('addclinicpassword'),
            'role' => 'admin'
        ]);
        $loginBody = [
            'email' =>'addclinic@example.com',
            'password' => 'addclinicpassword'
        ];
        $loginResponse = $this->post('/api/login', $loginBody);
        $token = $loginResponse['token'];
        $headers = ['Authorization' => "Bearer $token"];

        $reactor = Reactor::firstOrCreate([
            'name' => 'TestReactor1'
        ]);
        ReactorCycle::create([
            'name' => 'TestReactorCycle1',
            'reactor_id' => $reactor->id,
            'mass' => 30,
            'target_start_date' => date('Y-m-d', strtotime('2023-04-21')),
            'expiration_date' => date('Y-m-d', strtotime('2023-05-16')),
        ]);
        ReactorCycle::create([
            'name' => 'TestReactorCycle2',
            'reactor_id' => $reactor->id,
            'mass' => 39,
            'target_start_date' => date('Y-m-d', strtotime('12 May 2023')),
            'expiration_date' => date('Y-m-d', strtotime('18 May 2023'))
        ]);
        ReactorCycle::create([
            'name' => 'TestReactorCycle3',
            'reactor_id' => $reactor->id,
            'mass' => 0,
            'target_start_date' => date('Y-m-d', strtotime('9 May 2023')),
            'expiration_date' => date('Y-m-d', strtotime('2 June 2023'))
        ]);

        $data = [];

        $response = $this->post('/api/calendar', $data, $headers);

        $response->assertUnprocessable();

        $json = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('message', $json);
        $this->assertArrayHasKey('errors', $json);
        $this->assertArrayHasKey('reactor_name', $json['errors']);
        $this->assertArrayHasKey('month', $json['errors']);
        $this->assertArrayHasKey('year', $json['errors']);
    }

    public function test_availability_unauthenticated(){
        $token = 'Invalid Token';
        $headers = ['Authorization' => "Bearer $token"];

        $reactor = Reactor::firstOrCreate([
            'name' => 'TestReactor1'
        ]);
        ReactorCycle::create([
            'name' => 'TestReactorCycle1',
            'reactor_id' => $reactor->id,
            'mass' => 30,
            'target_start_date' => date('Y-m-d', strtotime('2023-04-21')),
            'expiration_date' => date('Y-m-d', strtotime('2023-05-16')),
        ]);
        ReactorCycle::create([
            'name' => 'TestReactorCycle2',
            'reactor_id' => $reactor->id,
            'mass' => 39,
            'target_start_date' => date('Y-m-d', strtotime('12 May 2023')),
            'expiration_date' => date('Y-m-d', strtotime('18 May 2023'))
        ]);
        ReactorCycle::create([
            'name' => 'TestReactorCycle3',
            'reactor_id' => $reactor->id,
            'mass' => 0,
            'target_start_date' => date('Y-m-d', strtotime('9 May 2023')),
            'expiration_date' => date('Y-m-d', strtotime('2 June 2023'))
        ]);

        $data = [
            'reactor_name' => $reactor->name,
            'month' => 5,
            'year' => 2023
        ];

        $response = $this->post('/api/calendar', $data, $headers);

        $response->assertUnauthorized();
    }

    public function test_availability_reactor_not_found(){
        User::factory()->create([
            'email' => 'addclinic@example.com',
            'password' => Hash::make('addclinicpassword'),
            'role' => 'admin'
        ]);
        $loginBody = [
            'email' =>'addclinic@example.com',
            'password' => 'addclinicpassword'
        ];
        $loginResponse = $this->post('/api/login', $loginBody);
        $token = $loginResponse['token'];
        $headers = ['Authorization' => "Bearer $token"];

        $data = [
            'reactor_name' => 'NonExistantReactor',
            'month' => 5,
            'year' => 2023
        ];

        $response = $this->post('/api/calendar', $data, $headers);

        $response->assertBadRequest();
    }

    public function test_availability_on_exception(){
        User::factory()->create([
            'email' => 'addclinic@example.com',
            'password' => Hash::make('addclinicpassword'),
            'role' => 'admin'
        ]);
        $loginBody = [
            'email' =>'addclinic@example.com',
            'password' => 'addclinicpassword'
        ];
        $loginResponse = $this->post('/api/login', $loginBody);
        $token = $loginResponse['token'];
        $headers = ['Authorization' => "Bearer $token"];
        $mock = $this->mock(CalendarService::class, function (MockInterface $mock) {
            $mock->shouldReceive('getReactorAvail')->andThrow(new Exception);
        });
        $data = [
            'reactor_name' => 'Reactor',
            'month' => 5,
            'year' => 2023
        ];
        $response = $this->post('/api/calendar', $data, $headers);
        $response->assertInternalServerError();
        $response->assertExactJson([
            'message' => 'Something went wrong.'
        ]);
        $mock->mockery_teardown();
    }
}
