<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Hash Driver
    |--------------------------------------------------------------------------
    |
    | Driver yang dipakai untuk membuat dan memverifikasi hash password.
    | Secara default menggunakan bcrypt karena hash di DB kamu berformat
    | "$2b$..." yang sesuai dengan bcrypt.
    |
    */
    'default' => env('HASH_DRIVER', 'bcrypt'),

    /*
    |--------------------------------------------------------------------------
    | Bcrypt Options
    |--------------------------------------------------------------------------
    |
    | Opsi untuk bcrypt. Kamu bisa menaikkan rounds untuk keamanan lebih, tapi
    | akan membuat hashing lebih lambat.
    |
    */
    'bcrypt' => [
        'rounds' => env('BCRYPT_ROUNDS', 10),
    ],

    /*
    |--------------------------------------------------------------------------
    | Argon Options
    |--------------------------------------------------------------------------
    |
    | Opsi untuk Argon2 (Laravel menggunakan Argon2id secara default ketika
    | dipilih via driver "argon").
    |
    */
    'argon' => [
        'memory' => env('ARGON_MEMORY', 1024),
        'threads' => env('ARGON_THREADS', 2),
        'time' => env('ARGON_TIME', 2),
    ],

];
