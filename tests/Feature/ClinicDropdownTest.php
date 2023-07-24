<?php

namespace Tests\Feature;

use App\Models\Clinic;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ClinicDropdownTest extends TestCase
{

    use DatabaseTransactions, WithFaker;

    public function testClinicDropdown()
    {
        $clinics = [
            Clinic::create([
                'account_id' => '123456',
                'is_enabled' => true,
                'name' => 'Clinic A',
                'address' => '123 Main Street',
                'city' => 'City A',
                'state' => 'State A',
                'zipcode' => '12345',
                'user_id' => 1,
            ]),
            Clinic::create([
                'account_id' => '234567',
                'is_enabled' => true,
                'name' => 'Clinic B',
                'address' => '456 Elm Street',
                'city' => 'City B',
                'state' => 'State B',
                'zipcode' => '23456',
                'user_id' => 2,
            ]),
            Clinic::create([
                'account_id' => '345678',
                'is_enabled' => true,
                'name' => 'Clinic C',
                'address' => '789 Oak Street',
                'city' => 'City C',
                'state' => 'State C',
                'zipcode' => '34567',
                'user_id' => 3,
            ]),
        ];

        User::factory()->create([
            'email' => 'clinicdropdown@example.com',
            'password' => Hash::make('clinicdropdownpassword'),
            'role' => 'admin'
        ]);
        $loginBody = [
            'email' =>'clinicdropdown@example.com',
            'password' => 'clinicdropdownpassword'
        ];
        $loginResponse = $this->post('/api/login', $loginBody);
        $token = $loginResponse['token'];
        $headers = ['Authorization' => "Bearer $token"];
    
        $response = $this->get('api/clinics/dropdown', $headers);

        $response->assertStatus(200);
    }

    public function test_clinic_dropdown_with_name()
    {
        $clinics = [
            Clinic::create([
                'account_id' => '123456',
                'is_enabled' => true,
                'name' => 'Clinic A',
                'address' => '123 Main Street',
                'city' => 'City A',
                'state' => 'State A',
                'zipcode' => '12345',
                'user_id' => 1,
            ]),
            Clinic::create([
                'account_id' => '234567',
                'is_enabled' => true,
                'name' => 'Clinic B',
                'address' => '456 Elm Street',
                'city' => 'City B',
                'state' => 'State B',
                'zipcode' => '23456',
                'user_id' => 2,
            ]),
            Clinic::create([
                'account_id' => '345678',
                'is_enabled' => true,
                'name' => 'Clinic C',
                'address' => '789 Oak Street',
                'city' => 'City C',
                'state' => 'State C',
                'zipcode' => '34567',
                'user_id' => 3,
            ]),
        ];

        User::factory()->create([
            'email' => 'clinicdropdown@example.com',
            'password' => Hash::make('clinicdropdownpassword'),
            'role' => 'admin'
        ]);
        $loginBody = [
            'email' =>'clinicdropdown@example.com',
            'password' => 'clinicdropdownpassword'
        ];
        $loginResponse = $this->post('/api/login', $loginBody);
        $token = $loginResponse['token'];
        $headers = ['Authorization' => "Bearer $token"];
        
        $clinicName = 'Clinic C';
        $response = $this->get('api/clinics/dropdown?name='.$clinicName, $headers);

        $response->assertStatus(200);
    }

    public function test_clinic_dropdown_with_non_existing_clinic()
    {
        $clinics = [
            Clinic::create([
                'account_id' => '123456',
                'is_enabled' => true,
                'name' => 'Clinic A',
                'address' => '123 Main Street',
                'city' => 'City A',
                'state' => 'State A',
                'zipcode' => '12345',
                'user_id' => 1,
            ]),
            Clinic::create([
                'account_id' => '234567',
                'is_enabled' => true,
                'name' => 'Clinic B',
                'address' => '456 Elm Street',
                'city' => 'City B',
                'state' => 'State B',
                'zipcode' => '23456',
                'user_id' => 2,
            ]),
            Clinic::create([
                'account_id' => '345678',
                'is_enabled' => true,
                'name' => 'Clinic C',
                'address' => '789 Oak Street',
                'city' => 'City C',
                'state' => 'State C',
                'zipcode' => '34567',
                'user_id' => 3,
            ]),
        ];

        User::factory()->create([
            'email' => 'clinicdropdown@example.com',
            'password' => Hash::make('clinicdropdownpassword'),
            'role' => 'admin'
        ]);
        $loginBody = [
            'email' =>'clinicdropdown@example.com',
            'password' => 'clinicdropdownpassword'
        ];
        $loginResponse = $this->post('/api/login', $loginBody);
        $token = $loginResponse['token'];
        $headers = ['Authorization' => "Bearer $token"];
        
        $clinicName = 'Junk Clinic';
        $response = $this->get('api/clinics/dropdown?name='.$clinicName, $headers);

        $response->assertStatus(200);
    }
}