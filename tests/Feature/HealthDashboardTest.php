<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HealthDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_users_can_open_each_health_module(): void
    {
        $user = User::factory()->create(['email_verified_at' => now(), 'diabetes_type' => 'type_2', 'glucose_target_min' => 90, 'glucose_target_max' => 130]);
        $routes = ['dashboard', 'glucose', 'meals', 'activity', 'profile.edit', 'meal-photo', 'medications', 'reports', 'alerts'];

        foreach ($routes as $route) {
            $this->actingAs($user)->get(route($route))->assertOk();
        }
    }

    public function test_health_profile_fields_can_be_saved(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        $this->actingAs($user)->patch(route('profile.update'), [
            'name' => 'Eaindra Kyaw Myat',
            'email' => $user->email,
            'diabetes_type' => 'type_2',
            'height_cm' => 170,
            'weight_kg' => 68.5,
        ])->assertSessionHasNoErrors()->assertRedirect(route('profile.edit'));

        $user->refresh();
        $this->assertSame('type_2', $user->diabetes_type);
        $this->assertSame(90, $user->glucose_target_min);
        $this->assertSame(130, $user->glucose_target_max);
        $this->assertSame('68.50', $user->weight_kg);
    }
}
