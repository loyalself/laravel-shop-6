{{-- 2.6-old 基础布局 新建--}}
<nav class="navbar navbar-default navbar-static-top">
    <div class="container">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#app-navbar-collapse">
                <span class="sr-only">Toggle Navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="{{ url('/') }}">
                Laravel Shop
            </a>
        </div>
        <div class="collapse navbar-collapse" id="app-navbar-collapse">
            <ul class="nav navbar-nav">
                <!-- 3.6. 前台类目菜单 添加: 顶部类目菜单开始
                这里我们使用了 Blade 的 each 语法，第一个参数是模板名称，第二个参数是要遍历的数组，第三个参数是遍历的项在模板中的变量名
                -->
                <!-- 判断模板是否有 $categoryTree 变量 -->
                @if(isset($categoryTree))
                    <li>
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown">所有类目 <b class="caret"></b></a>
                        <ul class="dropdown-menu multi-level">
                            <!-- 遍历 $categoryTree 集合，将集合中的每一项以 $category 变量注入 layouts._category_item 模板中并渲染 -->
                            @each('layouts._category_item', $categoryTree, 'category')
                        </ul>
                    </li>
                @endif
                 <!-- 顶部类目菜单结束 -->
            </ul>
            <ul class="nav navbar-nav navbar-right">
               {{-- <li><a href="#">登录</a></li>
                <li><a href="#">注册</a></li>--}}


                  <!-- 3.1-old 注册与登录 修改:登录注册链接开始 -->
                   @guest
                       <li><a href="{{ route('login') }}">登录</a></li>
                       <li><a href="{{ route('register') }}">注册</a></li>
                   @else
                      <!-- 6.2-old 查看购物车 添加-->
                       <li>
                           <a href="{{ route('cart.index') }}"><span class="glyphicon glyphicon-shopping-cart" aria-hidden="true"></span></a>
                       </li>


                       <li class="dropdown">
                           <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">
                            <span class="user-avatar pull-left" style="margin-right:8px; margin-top:-5px;">
                                <img src="https://cdn.learnku.com/uploads/images/201709/20/1/PtDKbASVcz.png?imageView2/1/w/60/h/60" class="img-responsive img-circle" width="30px" height="30px">
                            </span>
                               {{ Auth::user()->name }} <span class="caret"></span>
                           </a>

                           <ul class="dropdown-menu" role="menu">
                               {{-- 3.5. 收货地址列表 添加--}}
                               <li><a href="{{ route('user_addresses.index') }}">收货地址</a></li>
                                {{-- 6.6 用户订单列表  添加--}}
                               <li><a href="{{ route('orders.index') }}">我的订单</a></li>
                                <!--5.4-new.分期付款列表页 添加-->
                               <li><a href="{{ route('installments.index') }}">分期付款</a></li>

                               <!-- 5.7. 收藏商品 添加-->
                               <li><a href="{{ route('products.favorites') }}">我的收藏</a></li>

                               <li>
                                   <a href="{{ route('logout') }}"
                                      onclick="event.preventDefault();
                                             document.getElementById('logout-form').submit();">
                                       退出登录
                                   </a>
                                   <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                       {{ csrf_field() }}
                                   </form>
                               </li>
                           </ul>
                       </li>
               @endguest
               <!-- 登录注册链接结束 -->

            </ul>
        </div>
    </div>
</nav>