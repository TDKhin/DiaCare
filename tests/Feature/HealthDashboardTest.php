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
        $user = User::factory()->create(['email_verified_at' => now()]);
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
            'glucose_target_min' => 80,
            'glucose_target_max' => 130,
            'height_cm' => 170,
            'weight_kg' => 68.5,
            'emergency_contact_name' => 'Care Partner',
            'emergency_contact_phone' => '+1 555 0100',
            'doctor_name' => 'Dr. Example',
            'medical_notes' => 'Keep a regular meal schedule.',
            'health_goals' => 'Walk for 30 minutes each day.',
        ])->assertSessionHasNoErrors()->assertRedirect(route('profile.edit'));

        $user->refresh();
        $this->assertSame(80, $user->glucose_target_min);
        $this->assertSame(130, $user->glucose_target_max);
        $this->assertSame('68.50', $user->weight_kg);
        $this->assertSame('Walk for 30 minutes each day.', $user->health_goals);
    }
}
