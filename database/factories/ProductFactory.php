<?php
// 5.4. 商品列表页面 新建:
use Faker\Generator as Faker;

$factory->define(App\Models\Product::class, function (Faker $faker) {
    $image = $faker->randomElement([
        "https://cdn.learnku.com/uploads/images/201806/01/5320/7kG1HekGK6.jpg",
        "https://cdn.learnku.com/uploads/images/201806/01/5320/1B3n0ATKrn.jpg",
        "https://cdn.learnku.com/uploads/images/201806/01/5320/r3BNRe4zXG.jpg",
        "https://cdn.learnku.com/uploads/images/201806/01/5320/C0bVuKB2nt.jpg",
        "https://cdn.learnku.com/uploads/images/201806/01/5320/82Wf2sg8gM.jpg",
        "https://cdn.learnku.com/uploads/images/201806/01/5320/nIvBAQO5Pj.jpg",
        "https://cdn.learnku.com/uploads/images/201806/01/5320/XrtIwzrxj7.jpg",
        "https://cdn.learnku.com/uploads/images/201806/01/5320/uYEHCJ1oRp.jpg",
        "https://cdn.learnku.com/uploads/images/201806/01/5320/2JMRaFwRpo.jpg",
        "https://cdn.learnku.com/uploads/images/201806/01/5320/pa7DrV43Mw.jpg",
    ]);

    // 3.4. 调整后台商品管理 添加: 从数据库中随机取一个类目
    $category = \App\Models\Category::query()->where('is_directory', false)->inRandomOrder()->first();

    return [
        'title'        => $faker->word,
        'description'  => $faker->sentence,
        'image'        => $image,
        'on_sale'      => true,
        'rating'       => $faker->numberBetween(0, 5),
        'sold_count'   => 0,
        'review_count' => 0,
        'price'        => 0,
        /**
         * 3.4 添加:
         * 将取出的类目 ID 赋给 category_id 字段.
           如果数据库中没有类目则 $category 为 null，同样 category_id 也设成 null
         */
        'category_id'  => $category ? $category->id : null,
    ];
});