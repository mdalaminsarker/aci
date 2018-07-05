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

$factory->define(App\User::class, function (Faker\Generator $faker) {
    return [
        'name' => $faker->name,
        'email' => $faker->email,
    ];
});
$factory->define(App\Order::class, function (Faker\Generator $faker) {
    $random =str_random(40);
    return [
        'order_number' => $faker->reaText(100),
        'membership_number' => $faker->realText(10),
        'delivery_date' => $faker->DateTime,
        'user_id' => function () {
        // We take the first random author from the table
            return App\User::inRandomOrder()->first()->id;
        },
        'outlet_id' => function () {
        // We take the first random outlet from the table
            return App\Outlet::inRandomOrder()->first()->id;
        },
        'delivery_slot_id' => function () {
        // We take the first random slot from the table
            return App\Slot::inRandomOrder()->first()->id;
        },
    ];
});
