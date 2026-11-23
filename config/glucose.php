<?php

return [
    // WHO management guidance. These are general references, not a personal prescription.
    'profiles' => [
        'type_1' => ['label' => 'Type 1 diabetes'],
        'type_2' => ['label' => 'Type 2 diabetes'],
        'other_or_unsure' => ['label' => 'Other / not sure'],
    ],
    // Plasma glucose, mg/dL. A clinician-set care plan always takes precedence.
    'time_slots' => [
        'fasting' => ['label' => 'Fasting / before breakfast', 'min' => 90, 'max' => 130],
        'before_meal' => ['label' => 'Before meal', 'min' => 90, 'max' => 130],
        'after_meal' => ['label' => '2 hours after meal', 'min' => 80, 'max' => 145],
        'bedtime' => ['label' => 'Bedtime', 'min' => 110, 'max' => 150],
        'exercise_before' => ['label' => 'Before exercise', 'min' => 90, 'max' => 130],
        'exercise_after' => ['label' => 'After exercise', 'min' => 90, 'max' => 130],
    ],
];
