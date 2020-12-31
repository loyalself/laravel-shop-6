{{--3.3 验证邮箱(下) 新建 --}}
@extends('layouts.app')
@section('title', '操作成功')

@section('content')
    <div class="panel panel-default">
        <div class="panel-heading">操作成功</div>
        <div class="panel-body text-center">
            <h1>{{ $msg }}</h1>
            <a class="btn btn-primary" href="{{ route('root') }}">返回首页</a>
        </div>
    </div>
@endsection