<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

class HealthController extends Controller
{
    public function dashboard()
    {
        return view('dashboard', [
            'title' => 'Dashboard · DiaCare',
            'latestReading' => 134,
            'averageReading' => 119,
            'activityMinutes' => 0,
            'medications' => [
                ['name' => 'Metformin', 'dose' => '500 mg · Once daily', 'time' => '8:00 AM', 'status' => 'Upcoming'],
                ['name' => 'Glimepiride', 'dose' => '2 mg · Once daily', 'time' => '8:00 PM', 'status' => 'Upcoming'],
            ],
        ]);
    }

    public function glucose()
    {
        return view('health.glucose', [
            'title' => 'Glucose · DiaCare',
            'readings' => [
                ['period' => 'Before dinner', 'date' => 'Sep 03 · 6:18 PM', 'value' => 134, 'tag' => 'High'],
                ['period' => 'After lunch', 'date' => 'Sep 03 · 2:05 PM', 'value' => 98, 'tag' => 'In range'],
                ['period' => 'Before dinner', 'date' => 'Sep 02 · 6:10 PM', 'value' => 108, 'tag' => 'In range'],
                ['period' => 'Before breakfast', 'date' => 'Sep 02 · 8:06 AM', 'value' => 155, 'tag' => 'High'],
                ['period' => 'After dinner', 'date' => 'Sep 01 · 8:30 PM', 'value' => 106, 'tag' => 'In range'],
                ['period' => 'After breakfast', 'date' => 'Sep 01 · 10:02 AM', 'value' => 68, 'tag' => 'Low'],
            ],
        ]);
    }

    public function meals()
    {
        return view('health.meals', [
            'title' => 'Meals · DiaCare',
            'meals' => [
                ['name' => 'Dinner', 'description' => 'Grilled chicken, greens, avocado', 'time' => '7:10 PM', 'calories' => 590],
                ['name' => 'Lunch', 'description' => 'Quinoa bowl with vegetables', 'time' => '1:05 PM', 'calories' => 430],
                ['name' => 'Breakfast', 'description' => 'Greek yogurt, berries, almonds', 'time' => '8:00 AM', 'calories' => 310],
            ],
        ]);
    }

    public function activity()
    {
        return view('health.activity', ['title' => 'Activity · DiaCare']);
    }

    public function mealPhoto()
    {
        return view('health.meal-photo', ['title' => 'Meal Photo AI · DiaCare']);
    }

    public function medications()
    {
        return view('health.medications', [
            'title' => 'Medications · DiaCare',
            'medications' => [
                ['name' => 'Glimepiride', 'dose' => '2 mg · Once daily', 'instructions' => 'Take with dinner', 'next' => 'Today, 8:00 PM', 'status' => 'Taken'],
                ['name' => 'Metformin', 'dose' => '500 mg · Twice daily', 'instructions' => 'With breakfast and dinner', 'next' => 'Today, 8:00 PM', 'status' => 'Taken'],
            ],
        ]);
    }

    public function reports()
    {
        return view('health.reports', ['title' => 'Reports · DiaCare']);
    }

    public function alerts()
    {
        return view('health.alerts', [
            'title' => 'Alerts · DiaCare',
            'alerts' => [
                ['title' => 'Glucose Safety', 'message' => 'Your latest reading is a little above your target range. Consider logging your next meal.', 'time' => 'Today, 6:22 PM', 'color' => 'orange'],
                ['title' => 'Medication Reminder', 'message' => 'Your evening medication is due in about 30 minutes.', 'time' => 'Today, 7:30 PM', 'color' => 'blue'],
                ['title' => 'Activity Reminder', 'message' => 'A short walk after dinner can help support your daily activity goal.', 'time' => 'Today, 7:00 PM', 'color' => 'green'],
            ],
        ]);
    }
}
