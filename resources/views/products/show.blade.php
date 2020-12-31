{{-- 5.6. 商品详情页 新建: 代码解析
1.<div class="btn-group" data-toggle="buttons"> ... </div> 这里使用了 Bootstrap 的按钮组来输出 SKU 列表。
2.<ul class="nav nav-tabs" role="tablist"> ... </ul> 以及 <div class="tab-content"> ... </div> 是 Bootstrap 的 Tab 插件，我们用来输出商品详情和评价列表，由于我们尚未涉及评价相关的逻辑因此这里暂时留空。
3.{!! $product->description !!} 因为我们后台编辑商品详情用的是富文本编辑器，提交的内容是 Html 代码，此处需要原样输出而不需要进行 Html 转义
--}}
@extends('layouts.app')
@section('title', $product->title)

@section('content')
    <div class="row">
        <div class="col-lg-10 col-lg-offset-1">
            <div class="panel panel-default">
                <div class="panel-body product-info">
                    <div class="row">
                        <div class="col-sm-5">
                            <img class="cover" src="{{ $product->image_url }}" alt="">
                        </div>
                        <div class="col-sm-7">
                            <div class="title">{{ $product->title }}</div>

                            <!-- 4.5. 下单逻辑 添加: 众筹商品模块开始 -->
                            @if($product->type === \App\Models\Product::TYPE_CROWDFUNDING)
                                <div class="crowdfunding-info">
                                    <div class="have-text">已筹到</div>
                                    <div class="total-amount"><span class="symbol">￥</span>{{ $product->crowdfunding->total_amount }}</div>
                                    <!-- 这里使用了 Bootstrap 的进度条组件 -->
                                    <div class="progress">
                                        <div class="progress-bar progress-bar-success progress-bar-striped"
                                             role="progressbar"
                                             aria-valuenow="{{ $product->crowdfunding->percent }}"
                                             aria-valuemin="0"
                                             aria-valuemax="100"
                                             style="min-width: 1em; width: {{ min($product->crowdfunding->percent, 100) }}%">
                                        </div>
                                    </div>
                                    <div class="progress-info">
                                        <span class="current-progress">当前进度：{{ $product->crowdfunding->percent }}%</span>
                                        <span class="pull-right user-count">{{ $product->crowdfunding->user_count }}名支持者</span>
                                    </div>
                                    <!-- 如果众筹状态是众筹中，则输出提示语 -->
                                    @if ($product->crowdfunding->status === \App\Models\CrowdfundingProduct::STATUS_FUNDING)
                                        <div>此项目必须在
                                            <span class="text-red">{{ $product->crowdfunding->end_at->format('Y-m-d H:i:s') }}</span>
                                            前得到
                                            <span class="text-red">￥{{ $product->crowdfunding->target_amount }}</span>
                                            的支持才可成功，
                                            <!-- Carbon 对象的 diffForHumans() 方法可以计算出与当前时间的相对时间，更人性化 -->
                                            筹款将在<span class="text-red">{{ $product->crowdfunding->end_at->diffForHumans(now()) }}</span>结束！
                                        </div>
                                    @endif
                                </div>
                        @else
                            <!-- 原普通商品模块开始 -->
                                <div class="price"><label>价格</label><em>￥</em><span>{{ $product->price }}</span></div>
                                <div class="sales_and_reviews">
                                    <div class="sold_count">累计销量 <span class="count">{{ $product->sold_count }}</span></div>
                                    <div class="review_count">累计评价 <span class="count">{{ $product->review_count }}</span></div>
                                    <div class="rating" title="评分 {{ $product->rating }}">评分
                                        <span class="count">{{ str_repeat('★', floor($product->rating)) }}{{ str_repeat('☆', 5 - floor($product->rating)) }}</span>
                                    </div>
                                </div>
                                <!-- 原普通商品模块结束 -->
                        @endif
                        <!-- 众筹商品模块结束 -->

                        <!-- 4.5  注释 <div class="price"><label>价格</label><em>￥</em><span>$product->price</span></div>
                            <div class="sales_and_reviews">
                                <div class="sold_count">累计销量 <span class="count">$product->sold_count</span></div>
                                <div class="review_count">累计评价 <span class="count">$product->review_count </span></div>
                                <div class="rating" title="评分  $product->rating">评分 <span class="count">str_repeat('★', floor($product->rating))
                                 str_repeat('☆', 5 - floor($product->rating)) }}</span></div>
                            </div>-->
                            <div class="skus">

                            <!--
                               <label>选择</label>
                                <div class="btn-group" data-toggle="buttons">
                                    foreach($product->skus as $sku)
                                        <label class="btn btn-default sku-btn" title="$sku->description" >
                                            <input type="radio" name="skus" autocomplete="off" value="$sku->id"> $sku->title
                                        </label>
                                    endforeach
                                </div>-->

                                <!-- 5.6. 商品详情页 修改
                                 在输出 SKU 的按钮组的时候，我们通过 data-* 属性把对应 SKU 的价格和剩余库存放在了 Html 标签里。
                                同时加上了 data-toggle="tooltip" 这个属性来启用 Bootstrap 的工具提示来美化样式。
                                在 JS 代码中我们监听了 .sku-btn 的点击事件，当用户点击 SKU 时，我们从对应按钮的 data-* 属性取出价格和库存并输出到对应的 Html 标签中
                                -->
                                <label>选择</label>
                                @foreach($product->skus as $sku)
                                    <label
                                            class="btn btn-default sku-btn"
                                            data-price="{{ $sku->price }}"
                                            data-stock="{{ $sku->stock }}"
                                            data-toggle="tooltip"
                                            title="{{ $sku->description }}"
                                            data-placement="bottom">
                                        <input type="radio" name="skus" autocomplete="off" value="{{ $sku->id }}"> {{ $sku->title }}
                                    </label>
                                @endforeach

                            </div>
                            <div class="cart_amount"><label>数量</label><input type="text" class="form-control input-sm" value="1"><span>件</span><span class="stock"></span></div>

                            <!-- <div class="buttons">
                                <button class="btn btn-success btn-favor">❤ 收藏</button>
                                <button class="btn btn-primary btn-add-to-cart">加入购物车</button>
                            </div>-->
                            <!--5.7 修改-->
                            <div class="buttons">
                                @if($favored)
                                    <button class="btn btn-danger btn-disfavor">取消收藏</button>
                                @else
                                    <button class="btn btn-success btn-favor">❤ 收藏</button>
                                @endif
                               {{-- 4.5 注释<button class="btn btn-primary btn-add-to-cart">加入购物车</button>--}}

                                    <!-- 4.5 添加: 众筹商品下单按钮开始 -->
                                    @if($product->type === \App\Models\Product::TYPE_CROWDFUNDING)
                                        @if(Auth::check())
                                            @if($product->crowdfunding->status === \App\Models\CrowdfundingProduct::STATUS_FUNDING)
                                                <button class="btn btn-primary btn-crowdfunding">参与众筹</button>
                                            @else
                                                <button class="btn btn-primary disabled">
                                                    {{ \App\Models\CrowdfundingProduct::$statusMap[$product->crowdfunding->status] }}
                                                </button>
                                            @endif
                                        @else
                                            <a class="btn btn-primary" href="{{ route('login') }}">请先登录</a>
                                        @endif
                                    @else
                                        <button class="btn btn-primary btn-add-to-cart">加入购物车</button>
                                @endif
                                <!-- 众筹商品下单按钮结束 -->

                            </div>
                        </div>
                    </div>
                    <div class="product-detail">
                        <ul class="nav nav-tabs" role="tablist">
                            <li role="presentation" class="active"><a href="#product-detail-tab" aria-controls="product-detail-tab" role="tab" data-toggle="tab">商品详情</a></li>
                            <li role="presentation"><a href="#product-reviews-tab" aria-controls="product-reviews-tab" role="tab" data-toggle="tab">用户评价</a></li>
                        </ul>
                        <div class="tab-content">
                            <div role="tabpanel" class="tab-pane active" id="product-detail-tab">
                                {!! $product->description !!}
                            </div>
                            <div role="tabpanel" class="tab-pane" id="product-reviews-tab">
                                <!-- 8.5. 评价商品 添加:评论列表开始 -->
                                <table class="table table-bordered table-striped">
                                    <thead>
                                    <tr>
                                        <td>用户</td>
                                        <td>商品</td>
                                        <td>评分</td>
                                        <td>评价</td>
                                        <td>时间</td>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($reviews as $review)
                                        <tr>
                                            <td>{{ $review->order->user->name }}</td>
                                            <td>{{ $review->productSku->title }}</td>
                                            <td>{{ str_repeat('★', $review->rating) }}{{ str_repeat('☆', 5 - $review->rating) }}</td>
                                            <td>{{ $review->review }}</td>
                                            <td>{{ $review->reviewed_at->format('Y-m-d H:i') }}</td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                                <!-- 评论列表结束 -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

{{-- 5.6. 商品详情页 添加--}}
@section('scriptsAfterJs')
    <script>
        $(document).ready(function () {
            $('[data-toggle="tooltip"]').tooltip({trigger: 'hover'});
            $('.sku-btn').click(function () {
                $('.product-info .price span').text($(this).data('price'));
                $('.product-info .stock').text('库存：' + $(this).data('stock') + '件');
            });

            // 5.7 添加: 监听收藏按钮的点击事件
            $('.btn-favor').click(function () {
                // 发起一个 post ajax 请求，请求 url 通过后端的 route() 函数生成。
                axios.post('{{ route('products.favor', ['product' => $product->id]) }}')
                    .then(function () { // 请求成功会执行这个回调
                        swal('操作成功', '', 'success')
                        .then(function () {  // 5.7 这里加了一个 then() 方法
                            location.reload();
                        });
                    }, function(error) { // 请求失败会执行这个回调
                        // 如果返回码是 401 代表没登录
                        if (error.response && error.response.status === 401) {
                            swal('请先登录', '', 'error');
                        } else if (error.response && error.response.data.msg) {
                            // 其他有 msg 字段的情况，将 msg 提示给用户
                            swal(error.response.data.msg, '', 'error');
                        }  else {
                            // 其他情况应该是系统挂了
                            swal('系统错误', '', 'error');
                        }
                    });
            });
            /**
             * 5.7 添加: 监听取消收藏按钮的点击事件
             * 我们在 swal() 的回调里调用了 location.reload() 刷新页面来刷新收藏按钮的状态，当用户点击弹出框的 OK 按钮时这个回调会被触发
             */
            $('.btn-disfavor').click(function () {
                axios.delete('{{ route('products.disfavor', ['product' => $product->id]) }}')
                    .then(function () {
                        swal('操作成功', '', 'success')
                            .then(function () {
                                location.reload();
                            });
                    });
            });

            /**
             * 6.1 添加: 加入购物车按钮点击事件
             * 1.当用户点击 加入购物车 按钮时，通过 $('label.active input[name=skus]') 这个 CSS 选择器取得当前被选中的 SKU，并取得对应的 ID。
               2.如果后端返回的 Http 状态是 200，则进入到 then() 方法的第一个回调函数里，即弹框告知用户操作成功。
               3.如果后端返回的 Http 状态不是 200，则进入到 then() 方法的第二个回调函数里，可以通过 error.response.status 来取得 Http 状态码，返回的数据可以通过 error.response.data 来取得。
               4.在 Laravel 里输入参数校验不通过抛出的异常所对应的 Http 状态码是 422，具体错误信息会放在返回结果的 errors 数组里，所以这里我们通过 error.response.data.errors 来拿到所有的错误信息。最后把所有的错误信息拼接成 Html 代码并弹框告知用户。
               5.如果状态码既不是 200 也不是 422，那说明是我们系统其他地方出问题了，直接弹框告知用户系统错误.
             */
            $('.btn-add-to-cart').click(function () {
                //console.log($('input[name=skus]').val())
                //console.log($('.cart_amount input').val());
                //return
                // 请求加入购物车接口
                axios.post('{{ route('cart.add') }}', {
                    //sku_id: $('label.active input[name=skus]').val(),
                    sku_id: $('label input[name=skus]').val(),   //获取选中sku的product_id
                    amount: $('.cart_amount input').val(),      //获取加入购物车的数量
                })
                    .then(function () { // 请求成功执行此回调
                        //swal('加入购物车成功', '', 'success');
                        // 6.2 添加:用户添加商品到购物车成功后跳转到购物车页面
                        swal('加入购物车成功', '', 'success').then(function() {
                             location.href = '{{ route('cart.index') }}';
                        });
                    }, function (error) { // 请求失败执行此回调
                        if (error.response.status === 401) {
                            // http 状态码为 401 代表用户未登陆
                            swal('请先登录', '', 'error');
                        } else if (error.response.status === 422) {
                            // http 状态码为 422 代表用户输入校验失败
                            var html = '<div>';
                            _.each(error.response.data.errors, function (errors) {
                                _.each(errors, function (error) {
                                    html += error+'<br>';
                                })
                            });
                            html += '</div>';
                            swal({content: $(html)[0], icon: 'error'})
                        } else {
                            // 其他情况应该是系统挂了
                            swal('系统错误', '', 'error');
                        }
                    })
            });

            //4.5-new 添加: 参与众筹 按钮点击事件
            $('.btn-crowdfunding').click(function () {
                // 判断是否选中 SKU
                //if (!$('label.active input[name=skus]').val()) {
                if (!$('label input[name=skus]').val()) {
                    swal('请先选择商品');
                    return;
                }
                //console.log($('label input[name=skus]').val())
                //return;

                // 把用户的收货地址以 JSON 的形式放入页面，赋值给 addresses 变量
                var addresses = {!! json_encode(Auth::check() ? Auth::user()->addresses : []) !!};
                // 使用 jQuery 动态创建一个表单
                var $form = $('<form class="form-horizontal" role="form"></form>');
                // 表单中添加一个收货地址的下拉框
                $form.append('<div class="form-group">' +
                    '<label class="control-label col-sm-3">选择地址</label>' +
                    '<div class="col-sm-9">' +
                    '<select class="form-control" name="address_id"></select>' +
                    '</div></div>');
                // 循环每个收货地址
                addresses.forEach(function (address) {
                    // 把当前收货地址添加到收货地址下拉框选项中
                    $form.find('select[name=address_id]')
                        .append("<option value='" + address.id + "'>" +
                            address.full_address + ' ' + address.contact_name + ' ' + address.contact_phone +
                            '</option>');
                });
                // 在表单中添加一个名为 购买数量 的输入框
                $form.append('<div class="form-group">' +
                    '<label class="control-label col-sm-3">购买数量</label>' +
                    '<div class="col-sm-9"><input class="form-control" name="amount">' +
                    '</div></div>');
                // 调用 SweetAlert 弹框
                swal({
                    text: '参与众筹',
                    content: $form[0], // 弹框的内容就是刚刚创建的表单
                    buttons: ['取消', '确定']
                }).then(function (ret) {
                    // 如果用户没有点确定按钮，则什么也不做
                    if (!ret) {
                        return;
                    }
                    // 构建请求参数
                    var req = {
                        address_id: $form.find('select[name=address_id]').val(),
                        amount: $form.find('input[name=amount]').val(),
                        //sku_id: $('label.active input[name=skus]').val()
                        sku_id: $('label input[name=skus]').val()
                    };
                    // 调用众筹商品下单接口
                    axios.post('{{ route('crowdfunding_orders.store') }}', req)
                        .then(function (response) {
                            // 订单创建成功，跳转到订单详情页
                            swal('订单提交成功', '', 'success')
                                .then(() => {
                                    location.href = '/orders/' + response.data.id;
                                });
                        }, function (error) {
                            // 输入参数校验失败，展示失败原因
                            if (error.response.status === 422) {
                                var html = '<div>';
                                _.each(error.response.data.errors, function (errors) {
                                    _.each(errors, function (error) {
                                        html += error+'<br>';
                                    })
                                });
                                html += '</div>';
                                swal({content: $(html)[0], icon: 'error'})
                            } else if (error.response.status === 403) {
                                swal(error.response.data.msg, '', 'error');
                            } else {
                                swal('系统错误', '', 'error');
                            }
                        });
                });
            });

        });
    </script>
@endsection