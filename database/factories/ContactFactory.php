<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Contact;
use Faker\Generator as Faker;

$factory->define(Contact::class, function (Faker $faker) {
    return [
        'prefix' => $faker->title('male'),
        'first_name' => $faker->firstName,
        'middle_name' => '',
        'last_name' => $faker->lastName,
        'suffix' => '',
        'title' => $faker->jobTitle,
        'department' => '',
        'email_work' => $faker->companyEmail,
        'email_personal' => $faker->email,
        'email_other' => '',
        'phone_work' => $faker->phoneNumber,
        'phone_work_fax' => '',
        'phone_mobile' => '',
        'phone_direct' => $faker->phoneNumber,
        'phone_other' => '',
        'country' => $faker->country,
        'address1' => $faker->streetAddress,
        'address2' => '',
        'city' => $faker->city,
        'state' => '',
        'zip_code' => $faker->postcode,
        'birthday' => $faker->date('m-d-Y'),
        'website' => '',
        'notes' => ''
    ];
});
