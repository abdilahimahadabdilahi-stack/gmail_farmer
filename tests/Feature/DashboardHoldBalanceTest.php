<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardHoldBalanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_displays_zero_for_a_new_users_hold_balance(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('On hold')
            ->assertSee('$0.00');
    }

    public function test_dashboard_displays_the_users_hold_balance(): void
    {
        $user = User::factory()->create(['hold_balance' => 12.5]);

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('On hold')
            ->assertSee('$12.50');
    }
}
