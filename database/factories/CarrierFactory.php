<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Carrier;
use Faker\Generator as Faker;

$factory->define(Carrier::class, function (Faker $faker) {
    return [
        'code' => $faker->unique()->numberBetween(1, 50),
        'name' => $faker->name,
        'address1' => $faker->streetAddress,
        'address2' => $faker->streetAddress,
        'city' => $faker->city,
        'state' => $faker->stateAbbr,
        'zip' => $faker->numberBetween(10000, 79999),
        'contact_name' => $faker->name,
        'contact_phone' => $faker->tollFreePhoneNumber,
        'ext' => $faker->numberBetween(100, 999),
        'email' => $faker->unique()->safeEmail
    ];
});
