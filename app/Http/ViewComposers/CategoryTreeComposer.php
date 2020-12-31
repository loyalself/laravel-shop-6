<?php
namespace App\Http\ViewComposers;
use App\Services\CategoryService;
use Illuminate\View\View;
/**
 * 3.6. 前台类目菜单 新建:
 * 定义好 ViewComposer 之后我们还需要告诉 Laravel 要把这个 ViewComposer 应用到哪些模板文件里.
 * 去 app/Providers/AppServiceProvider.php 里注册
 */
class CategoryTreeComposer
{
    protected $categoryService;

    // 使用 Laravel 的依赖注入，自动注入我们所需要的 CategoryService 类
    public function __construct(CategoryService $categoryService)
    {
        $this->categoryService = $categoryService;
    }

    // 当渲染指定的模板时，Laravel 会调用 compose 方法
    public function compose(View $view)
    {
        // 使用 with 方法注入变量
        $view->with('categoryTree', $this->categoryService->getCategoryTree());
    }
}