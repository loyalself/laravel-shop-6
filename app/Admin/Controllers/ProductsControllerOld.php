<?php

namespace App\Admin\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
// 5.1. 商品的数据结构设计 新建
class ProductsController extends Controller
{
    use HasResourceActions;

    public function index(Content $content){
       /* return $content
            ->header('Index')
            ->description('description')
            ->body($this->grid());*/

        //5.2. 后台商品列表 添加
        return Admin::content(function (Content $content) {
            $content->header('商品列表');
            $content->body($this->grid());
        });
    }

    /**
     * Show interface.
     *
     * @param mixed   $id
     * @param Content $content
     * @return Content
     */
    public function show($id, Content $content)
    {
        return $content
            ->header('Detail')
            ->description('description')
            ->body($this->detail($id));
    }


    public function edit($id, Content $content){
        /*return $content
            ->header('Edit')
            ->description('description')
            ->body($this->form()->edit($id));*/

        // 5.3. 后台创建和编辑商品 修改
        return Admin::content(function (Content $content) use ($id) {
            $content->header('编辑商品');
            $content->body($this->form()->edit($id));
        });
    }

    /**
     * 5.3. 修改
     */
    public function create(Content $content){
      /* return $content
            ->header('Create')
            ->description('description')
            ->body($this->form());*/

        return Admin::content(function (Content $content) {
            $content->header('创建商品');
            $content->body($this->form());
        });
    }


    protected function grid(){
        /*$grid = new Grid(new Product);
        return $grid;*/

        return Admin::grid(Product::class, function (Grid $grid) {
            //$grid->model()->with(['category']);  //3.4 添加:使用 with 来预加载商品类目数据，减少 SQL 查询
            $grid->model()->where('type', Product::TYPE_NORMAL)->with(['category']); //4.3. 后台众筹商品管理 修改

            $grid->id('ID')->sortable();
            $grid->title('商品名称');

            $grid->column('category.name', '类目');  //3.4 添加: Laravel-Admin 支持用符号 . 来展示关联关系的字段

            $grid->on_sale('已上架')->display(function ($value) {
                return $value ? '是' : '否';
            });
            $grid->price('价格');
            $grid->rating('评分');
            $grid->sold_count('销量');
            $grid->review_count('评论数');

            $grid->actions(function ($actions) {
                $actions->disableView();
                $actions->disableDelete();
            });
            $grid->tools(function ($tools) {
                // 禁用批量删除按钮
                $tools->batch(function ($batch) {
                    $batch->disableDelete();
                });
            });
        });
    }

    /**
     * Make a show builder.
     *
     * @param mixed   $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(Product::findOrFail($id));

        return $show;
    }

    /**
     * 5.3.  修改
     */
    protected function form(){
       /* $form = new Form(new Product);
        return $form;*/

        // 创建一个表单
        return Admin::form(Product::class, function (Form $form) {
            // 创建一个输入框，第一个参数 title 是模型的字段名，第二个参数是该字段描述
            $form->text('title', '商品名称')->rules('required');

            // 4.3 添加:在表单中添加一个名为 type，值为 Product::TYPE_NORMAL 的隐藏字段
            $form->hidden('type')->value(Product::TYPE_NORMAL);

            // 3.4 添加: 添加一个类目字段，与之前类目管理类似，使用 Ajax 的方式来搜索添加
            $form->select('category_id', '类目')->options(function ($id) {
                //其中 ->options() 用于编辑商品时展示该商品的类目，Laravel-Admin 会把 category_id 字段值传给匿名函数，匿名函数需要返回 [id => value] 格式的返回值。
                $category = Category::query()->find($id);
                if ($category) {
                    return [$category->id => $category->full_name];
                }
            //})->ajax('/admin/api/categories');
            })->ajax('/admin/api/categories?is_directory=0'); //3.4 同章修改

            // 创建一个选择图片的框
            $form->image('image', '封面图片')->rules('required|image');

            // 创建一个富文本编辑器
            $form->editor('description', '商品描述')->rules('required');

            // 创建一组单选框
            $form->radio('on_sale', '上架')->options(['1' => '是', '0'=> '否'])->default('0');

            // 直接添加一对多的关联模型
            $form->hasMany('skus', 'SKU 列表', function (Form\NestedForm $form) {
                $form->text('title', 'SKU 名称')->rules('required');
                $form->text('description', 'SKU 描述')->rules('required');
                $form->text('price', '单价')->rules('required|numeric|min:0.01');
                $form->text('stock', '剩余库存')->rules('required|integer|min:0');
            });

            // 定义事件回调，当模型即将保存时会触发这个回调
            $form->saving(function (Form $form) {
                $form->model()->price = collect($form->input('skus'))->where(Form::REMOVE_FLAG_NAME, 0)->min('price') ?: 0;
            });
        });
    }
}
