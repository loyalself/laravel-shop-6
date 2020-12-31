{{-- 3.4. 优雅地处理异常 新建--}}
@extends('layouts.app')
@section('title', '错误')

@section('content')
    <div class="panel panel-default">
        <div class="panel-heading">错误</div>
        <div class="panel-body text-center">
            <h1>{{ $msg }}</h1>
            <a class="btn btn-primary" href="{{ route('root') }}">返回首页</a>
        </div>
    </div>
@endsection