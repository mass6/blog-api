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

    'rate-limit' => [
        'posts' => 5, // daily
    ],

    'posts' => [
        'pagination' => [
            'perPageDefault' => 20,
            'PageDefaultMax' => 50,
        ],
    ],

];
