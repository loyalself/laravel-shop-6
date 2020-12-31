<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(){
        // 3.2. 数据库填充 添加:
        $this->call(CategoriesSeeder::class);
        $this->call(ProductsSeeder::class);
    }
}
