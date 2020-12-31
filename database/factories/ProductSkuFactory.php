<?php
// 5.4. 商品列表页面 新建:
use Faker\Generator as Faker;

$factory->define(App\Models\ProductSku::class, function (Faker $faker) {
    return [
        'title'       => $faker->word,
        'description' => $faker->sentence,
        'price'       => $faker->randomNumber(4),
        'stock'       => $faker->randomNumber(5),
    ];
});
