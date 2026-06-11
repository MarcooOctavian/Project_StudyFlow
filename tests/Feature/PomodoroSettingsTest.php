<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PomodoroSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_update_pomodoro_settings(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson(route('dashboard.pomodoro.settings'), [
            'pomodoro_duration' => 45,
            'break_duration' => 10,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'pomodoro_duration' => 45,
                'break_duration' => 10,
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'pomodoro_duration' => 45,
            'break_duration' => 10,
        ]);
    }

    public function test_update_pomodoro_settings_validates_input(): void
    {
        $user = User::factory()->create();

        // Invalid: missing parameters
        $response = $this->actingAs($user)->postJson(route('dashboard.pomodoro.settings'), []);
        $response->assertStatus(422);

        // Invalid: bounds
        $response = $this->actingAs($user)->postJson(route('dashboard.pomodoro.settings'), [
            'pomodoro_duration' => 0, // too small
            'break_duration' => 70,   // too large
        ]);
        $response->assertStatus(422);
    }

    public function test_completed_pomodoro_uses_user_custom_duration(): void
    {
        $user = User::factory()->create([
            'pomodoro_duration' => 50,
        ]);

        $response = $this->actingAs($user)->postJson(route('dashboard.pomodoro.complete'));
        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $this->assertDatabaseHas('pomodoro_sessions', [
            'user_id' => $user->id,
            'duration_minutes' => 50,
        ]);
    }
}
