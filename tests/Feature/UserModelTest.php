<?php

namespace Tests\Feature;

use App\Models\Clinic;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UserModelTest extends TestCase
{
    use DatabaseTransactions, WithFaker;

    public function test_user_has_one_clinic()
    {
        $user = User::factory()->create([
            'email' => 'model@test.com',
            'password' => 'model'
        ]);
        $clinic = Clinic::create([
            'account_id' => 'C900000',
            'name' => 'modelName',
            'address' => 'modelAddress',
            'city' => 'modelCity',
            'state' => 'modelState',
            'zipcode' => '123456',
            'user_id' => $user['id']
        ]);

        $this->assertInstanceOf(Clinic::class, $user['clinic']);
        $clinic = $user->clinic()->getResults();
        $this->assertEquals($clinic['user_id'], $user['id']);
    }

    public function test_clinic_has_one_user()
    {
        $user = User::factory()->create([
            'email' => 'model@test.com',
            'password' => 'model'
        ]);
        $clinic = Clinic::create([
            'account_id' => 'C900000',
            'name' => 'modelName',
            'address' => 'modelAddress',
            'city' => 'modelCity',
            'state' => 'modelState',
            'zipcode' => '123456',
            'user_id' => $user['id']
        ]);

        $user = $clinic->user()->getResults();
        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals($clinic->user_id, $user->id);
    }

    public function test_isAdmin_method()
    {
        $user = User::factory()->create(['role' => 'admin']);
        $this->assertTrue($user->isAdmin());

        $user = User::factory()->create(['role' => 'clinic']);
        $this->assertFalse($user->isAdmin());
    }
}
