<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Auto User
    |--------------------------------------------------------------------------
    |
    | This value represents the ID of the user in IQ responsible for creating new
    | records.
    |
    */

    'auto_user' => env('AUTO_USER', 1342),

    /*
    |--------------------------------------------------------------------------
    | Timezone
    |--------------------------------------------------------------------------
    |
    | IQ stores local wall-clock times. Every date the package stamps on a new
    | customer is taken in this timezone. Null uses the application's timezone.
    |
    */

    'timezone' => env('IQ_TIMEZONE'),

];
