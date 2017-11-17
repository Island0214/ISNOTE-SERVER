<?php

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| Here you may define all of your model factories. Model factories give
| you a convenient way to create models for testing and seeding your
| database. Just tell the factory how a default model should look.
|
*/

/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(App\User::class, function (Faker\Generator $faker) {
    static $password;

    return [
        'name' => $faker->name,
        'email' => $faker->unique()->safeEmail,
        'phone' => $faker->unique()->phoneNumber,
        'password' => $password ?: $password = bcrypt('123456'),
        'intro' => '哈哈哈哈',
        'gender' => '男',
        'icon' => '',
        'see' => '所有人',
        'modify' => '所有人',
        'search' => '所有人',
        'info' => '所有人'
    ];
});
