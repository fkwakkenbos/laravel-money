<?php

return [
    /*
     |--------------------------------------------------------------------------
     | Laravel money
     |--------------------------------------------------------------------------
     */
    'locale'          => config('app.locale', 'nl_NL'),
    'default_currency' => 'EUR',
    'currencies'      => [
        'iso' => [
            'EUR',
        ],
    ],
];
