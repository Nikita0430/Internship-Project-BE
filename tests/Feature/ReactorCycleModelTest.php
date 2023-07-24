<?php

namespace Tests\Feature;

use App\Models\Reactor;
use App\Models\ReactorCycle;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ReactorCycleModelTest extends TestCase
{
    use DatabaseTransactions;

    public function test_reactor_cycle_has_one_reactor()
    {
        $reactor = Reactor::create([
            'name' => 'TestReactor1'
        ]);

        $cycle1 = ReactorCycle::create([
            'name' => 'TestCycle1',
            'reactor_id' => $reactor->id,
            'mass' => 120.50,
            'target_start_date' => date('Y-m-d', strtotime('today')),
            'expiration_date' => date('Y-m-d', strtotime('tomorrow'))
        ]);

        $cycle2 = ReactorCycle::create([
            'name' => 'TestCycle2',
            'reactor_id' => $reactor->id,
            'mass' => 120.50,
            'target_start_date' => date('Y-m-d', strtotime('today')),
            'expiration_date' => date('Y-m-d', strtotime('tomorrow'))
        ]);

        $cycles = $reactor->reactorCycles;
        $this->assertCount(2, $cycles);
        $this->assertTrue($cycles->contains($cycle1));
        $this->assertTrue($cycles->contains($cycle2));
    }

    public function test_reactor_cycle_belongs_to_a_reactor()
    {
        $reactor = Reactor::create([
            'name' => 'TestReactor1'
        ]);

        $cycle = ReactorCycle::create([
            'name' => 'TestCycle1',
            'reactor_id' => $reactor->id,
            'mass' => 120.50,
            'target_start_date' => date('Y-m-d', strtotime('today')),
            'expiration_date' => date('Y-m-d', strtotime('tomorrow'))
        ]);

        $reactor = $cycle->reactor;
        $this->assertInstanceOf(Reactor::class, $reactor);
        $this->assertEquals($reactor->id,$cycle->reactor_id);
    }
}
