<?php

namespace Tests\Feature;

use App\Models\Clinic;
use App\Models\User;
use App\Services\Clinic\ManageClinicService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;
class DownloadClinicTest extends TestCase
{
    use DatabaseTransactions, withFaker;

    public function test_unauthorised_for_clinic_user(){
        $user = User::factory()->create([
            'email' => 'listclinic@example.com',
            'password' => Hash::make('listclinicpassword'),
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
            'email' =>'listclinic@example.com',
            'password' => 'listclinicpassword'
        ];
        $loginResponse = $this->post('/api/login', $loginBody);
        $token = $loginResponse['token'];
        $headers = ['Authorization' => "Bearer $token"];

        $response = $this->get('/api/clinics/download', $headers);

        $response->assertUnauthorized();

    }

    public function test_success_for_admin_user(){
        User::factory()->create([
            'email' => 'listclinics@example.com',
            'password' => Hash::make('listclinicspassword'),
            'role' => 'admin'
        ]);
        $loginBody = [
            'email' =>'listclinics@example.com',
            'password' => 'listclinicspassword'
        ];
        $loginResponse = $this->post('/api/login', $loginBody);
        $token = $loginResponse['token'];
        $headers = ['Authorization' => "Bearer $token"];
        
        $user = User::factory()->create([
            'email' => 'listclinics2@example.com',
            'password' => Hash::make('listclinics2password'),
            'role' => 'clinic'
        ]);
        $clinic = Clinic::create([
            'account_id' => 'C999998',
            'name' => $this->faker->name,
            'address' => $this->faker->address,
            'city' => $this->faker->city,
            'state' => $this->faker->state(),
            'zipcode' => $this->faker->postcode,
            'user_id' => $user['id'],
            'is_enabled' => true
        ]);

        $response = $this->get('/api/clinics/download', $headers);

        $response->assertOk();
    }

    public function test_success_with_sort_by(){
        User::factory()->create([
            'email' => 'listclinics@example.com',
            'password' => Hash::make('listclinicspassword'),
            'role' => 'admin'
        ]);
        $loginBody = [
            'email' =>'listclinics@example.com',
            'password' => 'listclinicspassword'
        ];
        $loginResponse = $this->post('/api/login', $loginBody);
        $token = $loginResponse['token'];
        $headers = ['Authorization' => "Bearer $token"];
        
        $user = User::factory()->create([
            'email' => 'listclinics2@example.com',
            'password' => Hash::make('listclinics2password'),
            'role' => 'clinic'
        ]);
        $clinic = Clinic::create([
            'account_id' => 'C999998',
            'name' => $this->faker->name,
            'address' => $this->faker->address,
            'city' => $this->faker->city,
            'state' => $this->faker->state(),
            'zipcode' => $this->faker->postcode,
            'user_id' => $user['id'],
            'is_enabled' => true
        ]);

        
        $response = $this->get('/api/clinics/download?sort_by=name', $headers);

        $response->assertOk();
    }

    public function test_success_for_no_records(){
        User::factory()->create([
            'email' => 'listclinics@example.com',
            'password' => Hash::make('listclinicspassword'),
            'role' => 'admin'
        ]);
        $loginBody = [
            'email' =>'listclinics@example.com',
            'password' => 'listclinicspassword'
        ];
        $loginResponse = $this->post('/api/login', $loginBody);
        $token = $loginResponse['token'];
        $headers = ['Authorization' => "Bearer $token"];

        Clinic::query()->delete();

        $response = $this->get('/api/clinics/download', $headers);

        $response->assertOk();
    }

    public function test_success_with_clinic_name(){
        User::factory()->create([
            'email' => 'listclinics@example.com',
            'password' => Hash::make('listclinicspassword'),
            'role' => 'admin'
        ]);
        $loginBody = [
            'email' =>'listclinics@example.com',
            'password' => 'listclinicspassword'
        ];
        $loginResponse = $this->post('/api/login', $loginBody);
        $token = $loginResponse['token'];
        $headers = ['Authorization' => "Bearer $token"];

        $user = User::factory()->create([
            'email' => 'listclinics2@example.com',
            'password' => Hash::make('listclinics2password'),
            'role' => 'clinic'
        ]);
        $clinic = Clinic::create([
            'account_id' => 'C999998',
            'name' => 'downloadClinic1',
            'address' => $this->faker->address,
            'city' => $this->faker->city,
            'state' => $this->faker->state(),
            'zipcode' => $this->faker->postcode,
            'user_id' => $user['id'],
            'is_enabled' => true
        ]);

        $response = $this->get('/api/clinics/download?name=downloadClinic1', $headers);

        $response->assertOk();
    }

    public function test_bad_request_with_order_by_invalid_column(){
        User::factory()->create([
            'email' => 'listclinics@example.com',
            'password' => Hash::make('listclinicspassword'),
            'role' => 'admin'
        ]);
        $loginBody = [
            'email' =>'listclinics@example.com',
            'password' => 'listclinicspassword'
        ];
        $loginResponse = $this->post('/api/login', $loginBody);
        $token = $loginResponse['token'];
        $headers = ['Authorization' => "Bearer $token"];

        $response = $this->get('/api/clinics/download?sort_by=invalid_column', $headers);

        $response->assertBadRequest();
        $response->assertJson([
            'message' => 'Invalid sort by parameter.'
        ]);
    }

    public function test_bad_request_with_order_by_invalid_order(){
        User::factory()->create([
            'email' => 'listclinics@example.com',
            'password' => Hash::make('listclinicspassword'),
            'role' => 'admin'
        ]);
        $loginBody = [
            'email' =>'listclinics@example.com',
            'password' => 'listclinicspassword'
        ];
        $loginResponse = $this->post('/api/login', $loginBody);
        $token = $loginResponse['token'];
        $headers = ['Authorization' => "Bearer $token"];

        $response = $this->get('/api/clinics/download?sort_order=invalid_order', $headers);

        $response->assertBadRequest();
        $response->assertJson([
            'message' => 'Invalid sort order parameter.'
        ]);

    }
    public function test_unauthenticated_for_invalid_token(){
        $token = 'invalidToken';
        $headers = ['Authorization' => "Bearer $token"];

        $response = $this->get('/api/clinics/download', $headers);

        $response->assertUnauthorized();
        $response->assertExactJson([
           'message' => 'Unauthenticated.'
        ]);
    }

    public function test_store_with_exception()
    {
        User::factory()->create([
            'email' => 'addclinic@example.com',
            'password' => Hash::make('addclinicpassword'),
            'role' => 'admin'
        ]);
        $loginBody = [
            'email' => 'addclinic@example.com',
            'password' => 'addclinicpassword'
        ];
        $loginResponse = $this->post('/api/login', $loginBody);
        $token = $loginResponse['token'];
        $headers = ['Authorization' => "Bearer $token"];
        $this->mock(ManageClinicService::class, function ($mock) {
            $mock->shouldReceive('downloadList')
                ->andThrow(new \Exception('Service exception'));
        });
        $response = $this->get('/api/clinics/download', $headers);
        $response->assertStatus(Response::HTTP_INTERNAL_SERVER_ERROR);
    }
}
