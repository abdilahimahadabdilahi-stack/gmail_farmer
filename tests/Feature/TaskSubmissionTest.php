<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskSubmissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_submit_task_details_and_see_the_email_reward_and_status(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/tasks')
            ->assertOk()
            ->assertSee('Submit Task Details')
            ->assertSee('name="account_email"', false)
            ->assertSee('placeholder="example@gmail.com"', false)
            ->assertSee('name="account_code"', false)
            ->assertSee('placeholder="Enter details"', false);

        $this->actingAs($user)
            ->post('/tasks', [
                'account_email' => 'example@gmail.com',
                'account_code' => 'Task completion details',
            ])
            ->assertRedirect();

        $task = Task::query()->where('user_id', $user->id)->firstOrFail();

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'account_email' => 'example@gmail.com',
            'account_code' => 'Task completion details',
            'reward_amount' => 0.25,
            'status' => 'pending',
        ]);

        $this->actingAs($user)
            ->get('/tasks')
            ->assertOk()
            ->assertSee('example@gmail.com')
            ->assertSee('$0.25')
            ->assertSee('pending');
    }

    public function test_task_email_must_be_valid(): void
    {
        $this->actingAs(User::factory()->create())
            ->post('/tasks', [
                'account_email' => 'not-an-email',
                'account_code' => 'Task completion details',
            ])
            ->assertSessionHasErrors('account_email');
    }
}
