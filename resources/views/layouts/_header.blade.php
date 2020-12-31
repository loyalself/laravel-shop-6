{{-- 2.6 基础布局 新建--}}
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
            </ul>
            <ul class="nav navbar-nav navbar-right">
               {{-- <li><a href="#">登录</a></li>
                <li><a href="#">注册</a></li>--}}


                  <!-- 3.1 注册与登录 修改:登录注册链接开始 -->
                   @guest
                       <li><a href="{{ route('login') }}">登录</a></li>
                       <li><a href="{{ route('register') }}">注册</a></li>
                   @else
                      <!-- 6.2 查看购物车 添加-->
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