<?php

namespace Tests\Feature;

use App\Models\Clinic;
use App\Models\Notification;
use App\Models\Order;
use App\Models\Reactor;
use App\Models\ReactorCycle;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class NotificationModelTest extends TestCase
{
    use DatabaseTransactions, WithFaker;

    public function test_orders_have_many_notifications()
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

        $reactor = Reactor::create([
            'name' => 'testReactor',
        ]);
        $reactorCycle = ReactorCycle::firstOrCreate([
            'name' => $this->faker->name(),
            'reactor_id' => $reactor->id,
            'mass' => $this->faker->randomFloat(2,40,60),
            'target_start_date' => $this->faker->dateTimeBetween("-1 years", "now")->format('Y-m-d'),
            'expiration_date' => $this->faker->dateTimeBetween("+31 days", "+1 years")->format('Y-m-d')
        ]);

        $order = Order::create([
            'clinic_id' => $clinic->id,
            'order_no' => 'WEBO99999',
            'email' => $this->faker->email(),
            'placed_at' => Carbon::now()->toDateTimeString(),
            'shipped_at' => Carbon::now()->addDay()->toDateTimeString(),
            'injection_date' => $this->faker->dateTimeBetween('tomorrow','+30 days')->format('Y-m-d'),
            'dog_name' => $this->faker->name(),
            'dog_breed' => $this->faker->name(),
            'dog_age' => $this->faker->numberBetween(1,20),
            'dog_weight' => $this->faker->randomFloat(2,0,50),
            'dog_gender' => $this->faker->randomElement(['male','female']),
            'reactor_id' => $reactor->id,
            'reactor_cycle_id' => $reactorCycle->id,
            'no_of_elbows' => $this->faker->numberBetween(1,10),
            'dosage_per_elbow' => 1,
            'total_dosage' => $this->faker->randomFloat(2,1,5),
        ]);

        $notification1 = Notification::create([
            'order_id' => $order->id,
            'status_change' => config('global.orders.status.Confirmed'),
        ]);

        $notification2 = Notification::create([
            'order_id' => $order->id,
            'status_change' => config('global.orders.status.Cancelled'),
        ]);

        $notifications = $order->notifications;
        $this->assertCount(2, $notifications);
        $this->assertTrue($notifications->contains($notification1));
        $this->assertTrue($notifications->contains($notification2));
    }

    public function test_notification_belong_to_a_order()
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

        $reactor = Reactor::create([
            'name' => 'testReactor',
        ]);
        $reactorCycle = ReactorCycle::firstOrCreate([
            'name' => $this->faker->name(),
            'reactor_id' => $reactor->id,
            'mass' => $this->faker->randomFloat(2,40,60),
            'target_start_date' => $this->faker->dateTimeBetween("-1 years", "now")->format('Y-m-d'),
            'expiration_date' => $this->faker->dateTimeBetween("+31 days", "+1 years")->format('Y-m-d')
        ]);

        $order = Order::create([
            'clinic_id' => $clinic->id,
            'order_no' => 'WEBO99999',
            'email' => $this->faker->email(),
            'placed_at' => Carbon::now()->toDateTimeString(),
            'shipped_at' => Carbon::now()->addDay()->toDateTimeString(),
            'injection_date' => $this->faker->dateTimeBetween('tomorrow','+30 days')->format('Y-m-d'),
            'dog_name' => $this->faker->name(),
            'dog_breed' => $this->faker->name(),
            'dog_age' => $this->faker->numberBetween(1,20),
            'dog_weight' => $this->faker->randomFloat(2,0,50),
            'dog_gender' => $this->faker->randomElement(['male','female']),
            'reactor_id' => $reactor->id,
            'reactor_cycle_id' => $reactorCycle->id,
            'no_of_elbows' => $this->faker->numberBetween(1,10),
            'dosage_per_elbow' => 1,
            'total_dosage' => $this->faker->randomFloat(2,1,5),
        ]);

        $notification = Notification::create([
            'order_id' => $order->id,
            'status_change' => config('global.orders.status.Confirmed'),
        ]);

        $order = $notification->order;
        $this->assertInstanceOf(Order::class, $order);
        $this->assertEquals($order->id,$notification->order_id);
    }
}
