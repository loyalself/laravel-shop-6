<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Product extends Model
{
    protected $fillable = [
        'title', 'description', 'image', 'on_sale',
        'rating', 'sold_count', 'review_count', 'price'
    ];
    protected $casts = [
        'on_sale' => 'boolean', // on_sale 是一个布尔类型的字段
    ];
    // 与商品SKU关联,一对多,一个商品可能会有多个 SKU
    public function skus()
    {
        return $this->hasMany(ProductSku::class);
    }

    public function getImageUrlAttribute(){
        // 如果 image 字段本身就已经是完整的 url 就直接返回
        if (Str::startsWith($this->attributes['image'], ['http://', 'https://'])) {
            return $this->attributes['image'];
        }
        // \Storage::disk('public') 的参数 public 需要和我们在 config/admin.php 里面的 upload.disk 配置一致。
        return \Storage::disk('public')->url($this->attributes['image']);
    }

    //3.1. 商品类目数据库结构设计 新建
    public function category(){
        return $this->belongsTo(Category::class);
    }
}
