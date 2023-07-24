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
use Tests\TestCase;
use Illuminate\Support\Str;

class ManageClinicTest extends TestCase
{
    use WithFaker, DatabaseTransactions;

    public function test_success_on_valid_token_and_no_query_params(){
        User::factory()->create([
            'email' => 'manageclinic@example.com',
            'password' => Hash::make('manageclinicpassword'),
            'role' => 'admin'
        ]);
        $loginbody = [
            'email' =>'manageclinic@example.com',
            'password' => 'manageclinicpassword'
        ];
        $loginResponse = $this->post('/api/login', $loginbody);
        $token = $loginResponse['token'];
        $headers = ['Authorization' => "Bearer $token"];

        $response = $this->get('/api/clinics', $headers);

        $response->assertOk();
    }

    public function test_success_with_order_by_name(){
        User::factory()->create([
            'email' => 'manageclinic@example.com',
            'password' => Hash::make('manageclinicpassword'),
            'role' => 'admin'
        ]);
        $loginbody = [
            'email' =>'manageclinic@example.com',
            'password' => 'manageclinicpassword'
        ];
        $loginResponse = $this->post('/api/login', $loginbody);
        $token = $loginResponse['token'];
        $headers = ['Authorization' => "Bearer $token"];

        Clinic::query()->delete();
        $user1 = User::create([
            'email' => fake()->email(),
            'password' => Hash::make(fake()->password()),
            'role' => 'clinic'
        ]);
        Clinic::create([
            'name' => 'TestClinic1',
            'address' => 'address',
            'city' => 'city',
            'state' => 'state',
            'zipcode' => '123456',
            'account_id' => 'C'.Str::padLeft($user1->id-1,6,'0'),
            'user_id' => $user1->id,
            'is_enabled' => false
        ]);

        $user2 = User::create([
            'email' => fake()->email(),
            'password' => Hash::make(fake()->password()),
            'role' => 'clinic'
        ]);
        Clinic::create([
            'name' => 'TestClinic2',
            'address' => 'address',
            'city' => 'city',
            'state' => 'state',
            'zipcode' => '123456',
            'account_id' => 'C'.Str::padLeft($user2->id-1,6,'0'),
            'user_id' => $user2->id,
            'is_enabled' => false
        ]);

        $response = $this->get('/api/clinics?sort_by=name&status=disabled', $headers);

        $response->assertOk();

        $json = json_decode($response->getContent(), true);
        $this->assertTrue($json['clinics']['data'][0]['name'] < $json['clinics']['data'][1]['name']);
    }

    public function test_success_with_order_by_name_desc(){
        User::factory()->create([
            'email' => 'manageclinic@example.com',
            'password' => Hash::make('manageclinicpassword'),
            'role' => 'admin'
        ]);
        $loginbody = [
            'email' =>'manageclinic@example.com',
            'password' => 'manageclinicpassword'
        ];
        $loginResponse = $this->post('/api/login', $loginbody);
        $token = $loginResponse['token'];
        $headers = ['Authorization' => "Bearer $token"];

        Clinic::query()->delete();
        $user1 = User::create([
            'email' => fake()->email(),
            'password' => Hash::make(fake()->password()),
            'role' => 'clinic'
        ]);
        Clinic::create([
            'name' => 'TestClinic1',
            'address' => 'address',
            'city' => 'city',
            'state' => 'state',
            'zipcode' => '123456',
            'account_id' => 'C'.Str::padLeft($user1->id-1,6,'0'),
            'user_id' => $user1->id,
            'is_enabled' => false
        ]);

        $user2 = User::create([
            'email' => fake()->email(),
            'password' => Hash::make(fake()->password()),
            'role' => 'clinic'
        ]);
        Clinic::create([
            'name' => 'TestClinic2',
            'address' => 'address',
            'city' => 'city',
            'state' => 'state',
            'zipcode' => '123456',
            'account_id' => 'C'.Str::padLeft($user2->id-1,6,'0'),
            'user_id' => $user2->id,
            'is_enabled' => false
        ]);

        $response = $this->get('/api/clinics?sort_by=name&sort_order=desc', $headers);

        $response->assertOk();

        $json = json_decode($response->getContent(), true);
        $this->assertTrue($json['clinics']['data'][0]['name'] > $json['clinics']['data'][1]['name']);
    }


    public function test_success_on_valid_token_and_per_page(){
        User::factory()->create([
            'email' => 'manageclinic@example.com',
            'password' => Hash::make('manageclinicpassword'),
            'role' => 'admin'
        ]);
        $loginbody = [
            'email' =>'manageclinic@example.com',
            'password' => 'manageclinicpassword'
        ];
        $loginResponse = $this->post('/api/login', $loginbody);
        $token = $loginResponse['token'];
        $headers = ['Authorization' => "Bearer $token"];

        $response = $this->get('/api/clinics?per_page=5', $headers);

        $response->assertOk();
        $json = json_decode($response->getContent(), true);
        $this->assertEquals($json['clinics']['per_page'], 5);
    }

    public function test_success_on_valid_token_and_name(){
        User::factory()->create([
            'email' => 'manageclinic@example.com',
            'password' => Hash::make('manageclinicpassword'),
            'role' => 'admin'
        ]);
        $loginbody = [
            'email' =>'manageclinic@example.com',
            'password' => 'manageclinicpassword'
        ];
        $loginResponse = $this->post('/api/login', $loginbody);
        $token = $loginResponse['token'];
        $headers = ['Authorization' => "Bearer $token"];

        $response = $this->get('/api/clinics?name=Clinic', $headers);

        $response->assertOk();
    }

    public function test_bad_request_with_order_by_invalid_column(){
        User::factory()->create([
            'email' => 'listorders@example.com',
            'password' => Hash::make('listorderspassword'),
            'role' => 'admin'
        ]);
        $loginBody = [
            'email' =>'listorders@example.com',
            'password' => 'listorderspassword'
        ];
        $loginResponse = $this->post('/api/login', $loginBody);
        $token = $loginResponse['token'];
        $headers = ['Authorization' => "Bearer $token"];

        $response = $this->get('/api/clinics?sort_by=invalid_column', $headers);

        $response->assertBadRequest();
        $response->assertJson([
            'message' => 'Invalid sort by parameter.'
        ]);
    }

    public function test_bad_request_with_order_by_invalid_order(){
        User::factory()->create([
            'email' => 'listorders@example.com',
            'password' => Hash::make('listorderspassword'),
            'role' => 'admin'
        ]);
        $loginBody = [
            'email' =>'listorders@example.com',
            'password' => 'listorderspassword'
        ];
        $loginResponse = $this->post('/api/login', $loginBody);
        $token = $loginResponse['token'];
        $headers = ['Authorization' => "Bearer $token"];

        $response = $this->get('/api/clinics?sort_order=invalid_order', $headers);

        $response->assertBadRequest();
        $response->assertJson([
            'message' => 'Invalid sort order parameter.'
        ]);
    }

    public function test_no_records_found(){
        User::factory()->create([
            'email' => 'manageclinic@example.com',
            'password' => Hash::make('manageclinicpassword'),
            'role' => 'admin'
        ]);
        $loginbody = [
            'email' =>'manageclinic@example.com',
            'password' => 'manageclinicpassword'
        ];
        $loginResponse = $this->post('/api/login', $loginbody);
        $token = $loginResponse['token'];
        $headers = ['Authorization' => "Bearer $token"];

        $response = $this->get('/api/clinics?name=InvalidName', $headers);

        $response->assertOk();
        $response->assertJson([
           'message' => 'No Records Found.'
        ]);
    }

    public function test_unauthorized_for_clinic_user(){
        $user = User::factory()->create([
            'email' => 'manageclinic@example.com',
            'password' => Hash::make('manageclinicpassword'),
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
        $loginbody = [
            'email' =>'manageclinic@example.com',
            'password' => 'manageclinicpassword'
        ];
        $loginResponse = $this->post('/api/login', $loginbody);
        $token = $loginResponse['token'];
        $headers = ['Authorization' => "Bearer $token"];

        $response = $this->get('/api/clinics', $headers);

        $response->assertUnauthorized();
        $response->assertExactJson([
           'message' => 'Unauthorized'
        ]);
    }

    public function test_unauthenticated_for_invaid_token(){
        $token = 'invalidToken';
        $headers = ['Authorization' => "Bearer $token"];

        $response = $this->get('/api/clinics', $headers);

        $response->assertUnauthorized();
        $response->assertExactJson([
           'message' => 'Unauthenticated.'
        ]);
    }

    public function test_internal_server_error_on_exception()
    {
        User::factory()->create([
            'email' => 'manageclinic@example.com',
            'password' => Hash::make('manageclinicpassword'),
            'role' => 'admin'
        ]);
        $loginbody = [
            'email' =>'manageclinic@example.com',
            'password' => 'manageclinicpassword'
        ];
        $loginResponse = $this->post('/api/login', $loginbody);
        $token = $loginResponse['token'];
        $headers = ['Authorization' => "Bearer $token"];
        
        $mock = $this->mock(ManageClinicService::class, function (MockInterface $mock) {
            $mock->shouldReceive('index')->andThrow(new Exception);
        });

        $response = $this->get('/api/clinics?name=InvalidName', $headers);

        $response->assertInternalServerError();
        $response->assertExactJson([
            'message' => 'Something went wrong.'
        ]);
        $mock->mockery_teardown();
    }
}