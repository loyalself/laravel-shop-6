<?php

/**
 * Laravel-admin - admin builder based on Laravel.
 * @author z-song <https://github.com/z-song>
 *
 * Bootstraper for Admin.
 *
 * Here you can remove builtin form field:
 * Encore\Admin\Form::forget(['map', 'editor']);
 *
 * Or extend custom form field:
 * Encore\Admin\Form::extend('php', PHPEditor::class);
 *
 * Or require js and css assets:
 * Admin::css('/packages/prettydocs/css/styles.css');
 * Admin::js('/packages/prettydocs/js/main.js');
 *
 */

//Encore\Admin\Form::forget(['map', 'editor']);

/**
 * 5.3. 后台创建和编辑商品 修改:
 * Laravel-Admin 为了避免加载太多前端静态文件，默认禁用了 editor 这个表单组件,如果不移除 editor 将会报错:
 *  FatalThrowableError In ProductsController.php line 138: Call to a member function rules() on null
 */
Encore\Admin\Form::forget(['map']);