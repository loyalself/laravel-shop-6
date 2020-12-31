<?php
use Illuminate\Database\Seeder;
//10.2. 完善假数据填充 新建
class CouponCodesSeeder extends Seeder
{
    public function run(){
        factory(\App\Models\CouponCode::class, 20)->create();
    }
}
