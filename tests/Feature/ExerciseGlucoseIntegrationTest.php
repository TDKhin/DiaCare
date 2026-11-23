<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\GlucoseReading;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExerciseGlucoseIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_exercise_links_the_latest_prior_glucose_reading_and_accepts_an_actual_post_reading(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $before = GlucoseReading::create([
            'user_id' => $user->id,
            'value' => 155,
            'context' => 'Before exercise',
            'recorded_at' => now()->subMinutes(10),
        ]);

        $this->actingAs($user)->post(route('activity.store'), [
            'activity' => 'Walking',
            'intensity' => 'moderate',
            'duration_minutes' => 30,
        ])->assertSessionHasNoErrors();

        $activity = ActivityLog::firstOrFail();
        $this->assertSame($before->id, $activity->pre_glucose_reading_id);
        $this->assertSame('moderate', $activity->intensity);
        $this->assertGreaterThan(0, $activity->calories_burned);

        $this->actingAs($user)->post(route('glucose.store'), [
            'value' => 132,
            'activity_log_id' => $activity->id,
        ])->assertRedirect(route('glucose'));

        $activity->refresh();
        $this->assertNotNull($activity->post_glucose_reading_id);
        $this->assertDatabaseHas('glucose_readings', [
            'id' => $activity->post_glucose_reading_id,
            'user_id' => $user->id,
            'value' => 132,
            'context' => 'After Exercise: Walking',
        ]);
    }

    public function test_a_user_cannot_attach_a_post_exercise_reading_to_another_users_activity(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $otherUser = User::factory()->create(['email_verified_at' => now()]);
        $activity = ActivityLog::create([
            'user_id' => $otherUser->id,
            'activity' => 'Cycling',
            'intensity' => 'moderate',
            'duration_minutes' => 20,
            'calories_burned' => 100,
            'logged_at' => now(),
        ]);

        $this->actingAs($user)->post(route('glucose.store'), [
            'value' => 120,
            'activity_log_id' => $activity->id,
        ])->assertNotFound();

        $this->assertNull($activity->fresh()->post_glucose_reading_id);
    }
}
