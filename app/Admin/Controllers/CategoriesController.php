<?php

namespace App\Admin\Controllers;

use App\Models\Category;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use Illuminate\Http\Request;

//3.3. 商品类目管理后台 新建
class CategoriesController extends Controller
{
    use HasResourceActions;

    /**
     * Index interface.
     *
     * @param Content $content
     * @return Content
     */
    public function index(Content $content)
    {
      /*  return $content
            ->header('Index')
            ->description('description')
            ->body($this->grid());*/

        return $content
            ->header('商品类目列表')
            ->body($this->grid());
    }

    /**
     * Show interface.
     *
     * @param mixed $id
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

    /**
     * Edit interface.
     *
     * @param mixed $id
     * @param Content $content
     * @return Content
     */
    public function edit($id, Content $content)
    {
        /*return $content
            ->header('Edit')
            ->description('description')
            ->body($this->form()->edit($id));*/

        return $content
            ->header('编辑商品类目')
            ->body($this->form(true)->edit($id));

    }

    /**
     * Create interface.
     *
     * @param Content $content
     * @return Content
     */
    public function create(Content $content)
    {
        /*return $content
            ->header('Create')
            ->description('description')
            ->body($this->form());*/

        return $content
            ->header('创建商品类目')
            ->body($this->form(false));
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        /*$grid = new Grid(new Category);

        $grid->id('Id');
        $grid->name('Name');
        $grid->parent_id('Parent id');
        $grid->is_directory('Is directory');
        $grid->level('Level');
        $grid->path('Path');
        $grid->created_at('Created at');
        $grid->updated_at('Updated at');

        return $grid;*/

        $grid = new Grid(new Category);

        $grid->id('ID')->sortable();
        $grid->name('名称');
        $grid->level('层级');
        $grid->is_directory('是否目录')->display(function ($value) {
            return $value ? '是' : '否';
        });
        $grid->path('类目路径');
        $grid->actions(function ($actions) {
            // 不展示 Laravel-Admin 默认的查看按钮
            $actions->disableView();
        });

        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(Category::findOrFail($id));

        $show->id('Id');
        $show->name('Name');
        $show->parent_id('Parent id');
        $show->is_directory('Is directory');
        $show->level('Level');
        $show->path('Path');
        $show->created_at('Created at');
        $show->updated_at('Updated at');

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    //protected function form()
    protected function form($isEditing = false)
    {
        /*$form = new Form(new Category);
        $form->text('name', 'Name');
        $form->number('parent_id', 'Parent id');
        $form->switch('is_directory', 'Is directory');
        $form->number('level', 'Level');
        $form->text('path', 'Path');
        return $form;*/

        $form = new Form(new Category);
        $form->text('name', '类目名称')->rules('required');
        // 如果是编辑的情况
        if ($isEditing) {
            // 不允许用户修改『是否目录』和『父类目』字段的值
            // 用 display() 方法来展示值，with() 方法接受一个匿名函数，会把字段值传给匿名函数并把返回值展示出来
            $form->display('is_directory', '是否目录')->with(function ($value) {
                return $value ? '是' :'否';
            });
            // 支持用符号 . 来展示关联关系的字段
            $form->display('parent.name', '父类目');
        } else {
            // 定义一个名为『是否目录』的单选框
            $form->radio('is_directory', '是否目录')
                ->options(['1' => '是', '0' => '否'])
                ->default('0')
                ->rules('required');
            // 定义一个名为父类目的下拉框
            $form->select('parent_id', '父类目')->ajax('/admin/api/categories');
        }
        return $form;
    }

    /**
     * 3.3 添加: 定义下拉框搜索接口
     * 1.这里我们详细解释一下父类目这个下拉框，由于类目可能会很多，如果直接把所有的类目都输出给下拉框，再由运营人员去找，体验比较差，而搜索类目名称的方式则会方便很多。
       2.->ajax(xxx) 代表下拉框的值通过 /admin/api/categories 接口搜索获取，Laravel-Admin 会把用户输入的值以 q 参数传给接口，这个接口需要返回的数据格式为分页格式，并且 data 字段的格式为：
     * [
            ["id" => 1, "name" => "手机配件"],
            ["id" => 2, "name" => "耳机"],
       ]
     */
    public function apiIndex(Request $request){
        // 用户输入的值通过 q 参数获取
        $search = $request->input('q');
        $result = Category::query()
            ->where('is_directory', true)  // 由于这里选择的是父类目，因此需要限定 is_directory 为 true
            ->where('name', 'like', '%'.$search.'%')
            ->paginate();
        // 把查询出来的结果重新组装成 Laravel-Admin 需要的格式
        $result->setCollection($result->getCollection()->map(function (Category $category) {
            return ['id' => $category->id, 'text' => $category->full_name];
        }));
        return $result;
    }
}
