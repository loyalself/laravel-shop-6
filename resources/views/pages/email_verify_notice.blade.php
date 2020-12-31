{{-- 3.2 验证邮箱(上) 新建--}}
@extends('layouts.app')
@section('title', '提示')

@section('content')
    <div class="panel panel-default">
        <div class="panel-heading">提示</div>
        <div class="panel-body text-center">
            <h1>请先验证邮箱</h1>
           {{-- <a class="btn btn-primary" href="{{ route('root') }}">返回首页</a>--}}
            {{-- 3.3 验证邮箱(下) 修改--}}
            <a class="btn btn-primary" href="{{ route('email_verification.send') }}">重新发送验证邮件</a>
        </div>
    </div>
@endsection