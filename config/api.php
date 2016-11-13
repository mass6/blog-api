<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Rate Limiter
    |--------------------------------------------------------------------------
    |
    | Define rate limits for various aspects of the REST api
    |
    */

    'posts' => [
        'pagination' => [
            'perPageDefault' => 20,
            'PageDefaultMax' => 50,
        ],
        'daily-creation-quota' => 5,
    ],

];
