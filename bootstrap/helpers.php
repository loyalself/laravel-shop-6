<?php
/**
 * 2.5 辅助函数 新建
 */

/**
 * 2.6 基础布局 添加
 */
function route_class(){
    return str_replace('.', '-', Route::currentRouteName());
}