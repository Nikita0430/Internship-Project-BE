<?php

namespace Tests\Feature;

use App\Models\Reactor;
use App\Models\ReactorCycle;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ScheduleTest extends TestCase
{
    use DatabaseTransactions, WithFaker;

    public function testReactorCycleArchival()
    {
        $reactor = Reactor::firstOrCreate([
            'name' => 'TestReactor'
        ]);
        
        $cycle = ReactorCycle::create([
            'name' => 'ExampleCycle',
            'reactor_id' => $reactor->id,
            'mass' => 120.50,
            'target_start_date' => date('Y-m-d', strtotime('today')),
            'expiration_date' => Carbon::now()->subDay()->format('Y-m-d'),
            'is_archived' => false,
            'is_enabled' => false
        ]);

        Carbon::setTestNow(Carbon::today());
        $this->artisan('schedule:run');

        $this->assertTrue($cycle->fresh()->is_archived);
    }
}
