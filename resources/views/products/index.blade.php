{{-- 5.4. 商品列表页面 新建--}}
@extends('layouts.app')
@section('title', '商品列表')

@section('content')
    <div class="row">
        <div class="col-lg-10 col-lg-offset-1">
            <div class="panel panel-default">
                <div class="panel-body">

                    <!-- 5.5-old. 商品筛选、排序 添加:筛选组件开始 -->
                    <div class="row">
                        <form action="{{ route('products.index') }}" class="form-inline search-form">
                            <!-- 3.5. 完善前台商品列表页 添加:面包屑开始 -->
                            <!-- 添加一个名为 全部 的链接，直接跳转到商品列表页 -->
                            <a class="all-products" href="{{ route('products.index') }}">全部</a> &gt;
                            <!-- 如果当前是通过类目筛选的 -->
                            @if($category)
                            <!-- 遍历这个类目的所有祖先类目，我们在模型的访问器中已经排好序，因此可以直接使用 -->
                            @foreach($category->ancestors as $ancestor)
                                <!-- 添加一个名为该祖先类目名的链接 -->
                                    <span class="category">
                                         <a href="{{ route('products.index', ['category_id' => $ancestor->id]) }}">{{ $ancestor->name }}</a>
                                    </span>
                                    <span>></span>
                            @endforeach
                            <!-- 最后展示出当前类目名称 -->
                                <span class="category">{{ $category->name }}</span><span> ></span>
                                <!-- 当前类目的 ID，当用户调整排序方式时，可以保证 category_id 参数不丢失 -->
                                <input type="hidden" name="category_id" value="{{ $category->id }}">
                            @endif
                            <!-- 面包屑结束 -->
                            
                            <input type="text" class="form-control input-sm" name="search" placeholder="搜索">
                            <button class="btn btn-primary btn-sm">搜索</button>
                            <select name="order" class="form-control input-sm pull-right">
                                <option value="">排序方式</option>
                                <option value="price_asc">价格从低到高</option>
                                <option value="price_desc">价格从高到低</option>
                                <option value="sold_count_desc">销量从高到低</option>
                                <option value="sold_count_asc">销量从低到高</option>
                                <option value="rating_desc">评价从高到低</option>
                                <option value="rating_asc">评价从低到高</option>
                            </select>
                        </form>
                    </div>
                    <!-- 筛选组件结束 -->

                    <!-- 3.5 添加:展示子类目开始 -->
                    <div class="filters">
                        <!-- 如果当前是通过类目筛选，并且此类目是一个父类目 -->
                        @if ($category && $category->is_directory)
                            <div class="row">
                                <div class="col-xs-3 filter-key">子类目：</div>
                                <div class="col-xs-9 filter-values">
                                    <!-- 遍历直接子类目 -->
                                    @foreach($category->children as $child)
                                        <a href="{{ route('products.index', ['category_id' => $child->id]) }}">{{ $child->name }}</a>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                    <!-- 展示子类目结束 -->

                    <div class="row products-list">
                        @foreach($products as $product)
                            <div class="col-xs-3 product-item">
                                <div class="product-content">
                                    <div class="top">
                                    <!-- <div class="img"><img src="{{ $product->image }}" alt=""></div>-->
                                        <!-- 5.4. 商品列表页面 修改-->
                                        <div class="img"><img src="{{ $product->image_url }}" alt=""></div>
                                        <div class="price"><b>￥</b>{{ $product->price }}</div>
                                        <div class="title">{{ $product->title }}</div>
                                    </div>
                                    <div class="bottom">
                                        <div class="sold_count">销量 <span>{{ $product->sold_count }}笔</span></div>
                                        <div class="review_count">评价 <span>{{ $product->review_count }}</span></div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                <!-- <div class="pull-right">{{ $products->render() }}</div>  5.4. 添加 -->
                    <!--
                        5.5-old 修改: 把 $filters 变量传给分页组件来解决第二页不能显示搜索项内容问题
                    -->
                    <div class="pull-right">{{ $products->appends($filters)->render() }}</div>
                </div>
            </div>
        </div>
    </div>
@endsection
{{-- 5.5-old. 商品筛选、排序 添加
这里的 var filters = {!! json_encode($filters) !!}; 把控制器传过来的 $filters 变量变成一个 JSON 字符串，赋值给 JS 的 filters 变量。
--}}
@section('scriptsAfterJs')
    <script>
        var filters = {!! json_encode($filters) !!};
        $(document).ready(function () {
            $('.search-form input[name=search]').val(filters.search);
            $('.search-form select[name=order]').val(filters.order);

            /**
             * 现在每次选择排序方式之后都需要点搜索按钮才能生效，这个体验不是很好，我们可以通过监听下拉框的 change 事件来触发表单自动提交：
             */
            $('.search-form select[name=order]').on('change', function() {
                $('.search-form').submit();
            });
        })
    </script>
@endsection