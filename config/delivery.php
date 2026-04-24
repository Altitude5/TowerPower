<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Delivery Schedule Window
    |--------------------------------------------------------------------------
    |
    | This value determines how many days into the future the
    | DeliveryAssignmentService will look for an available delivery schedule.
    |
    */
    'schedule_window_days' => env('DELIVERY_SCHEDULE_WINDOW', 60),
];
