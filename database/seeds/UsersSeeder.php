<?php

use Illuminate\Database\Seeder;
//10.2. 完善假数据填充 新建
class UsersSeeder extends Seeder
{
    public function run(){
        // 通过 factory 方法生成 100 个用户并保存到数据库中
        factory(\App\Models\User::class, 100)->create();
    }
}
