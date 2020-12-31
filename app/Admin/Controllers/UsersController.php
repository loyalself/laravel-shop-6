<?php

namespace App\Admin\Controllers;

use App\Models\User;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
// 4.2. 用户列表 新建
class UsersController extends Controller
{
    use HasResourceActions;


    public function index(Content $content){
        /*return $content
            ->header('Index')
            ->description('description')
            ->body($this->grid());*/

        // 4.2 修改
        return Admin::content(function (Content $content) {
            // 页面标题
            $content->header('用户列表');
            $content->body($this->grid());
        });
    }

    public function show($id, Content $content)
    {
        return $content
            ->header('Detail')
            ->description('description')
            ->body($this->detail($id));
    }


    protected function grid(){
       /* $grid = new Grid(new User);
        $grid->id('Id');
        $grid->name('Name');
        $grid->email('Email');
        $grid->password('Password');
        $grid->remember_token('Remember token');
        $grid->email_verified('Email verified');
        $grid->created_at('Created at');
        $grid->updated_at('Updated at');
        return $grid;*/

        /**
         * 4.2 修改:根据回调函数，在页面上用表格的形式展示用户记录:
         * 1.Admin::content() 会根据回调函数来渲染页面，它会自动渲染页面顶部、菜单、底部等公共元素，而我们可以调用 $content 的方法在页面上添加元素来设置不同页面的内容。
           2.$content->body() 用来渲染页面的核心内容，可以接受任何可字符串化的对象作为参数，比如字符串、Laravel 的视图等。
           3.由于 email_verified 字段在数据库中是 0 和 1，直接展示出来并不直观，所以我们调用了 display() 方法来更优雅地展示。display() 方法接受一个匿名函数作为参数，在展示时会把对应字段值当成参数传给匿名函数，把匿名函数的返回值作为页面输出的内容。在这个例子里就是当 email_verified 为 true 时展示 是 否则展示 否
         */
        return Admin::grid(User::class, function (Grid $grid) {

            // 创建一个列名为 ID 的列，内容是用户的 id 字段，并且可以在前端页面点击排序
            $grid->id('ID')->sortable();

            // 创建一个列名为 用户名 的列，内容是用户的 name 字段。下面的 email() 和 created_at() 同理
            $grid->name('用户名');

            $grid->email('邮箱');

            $grid->email_verified('已验证邮箱')->display(function ($value) {
                return $value ? '是' : '否';
            });

            $grid->created_at('注册时间');

            // 不在页面显示 `新建` 按钮，因为我们不需要在后台新建用户
            $grid->disableCreateButton();

            $grid->actions(function ($actions) {
                // 不在每一行后面展示查看按钮
                $actions->disableView();

                // 不在每一行后面展示删除按钮
                $actions->disableDelete();

                // 不在每一行后面展示编辑按钮
                $actions->disableEdit();
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
        $show = new Show(User::findOrFail($id));

        $show->id('Id');
        $show->name('Name');
        $show->email('Email');
        $show->password('Password');
        $show->remember_token('Remember token');
        $show->email_verified('Email verified');
        $show->created_at('Created at');
        $show->updated_at('Updated at');

        return $show;
    }


  /*
    4.2. 用户列表:
    由于我们不会在管理后台去新增、编辑和删除用户，因此我们可以把这里面的 edit()、create() 和 form() 方法删除
    public function edit($id, Content $content)
    {
        return $content
            ->header('Edit')
            ->description('description')
            ->body($this->form()->edit($id));
    }


    public function create(Content $content)
    {
        return $content
            ->header('Create')
            ->description('description')
            ->body($this->form());
    }


    protected function form()
    {
        $form = new Form(new User);

        $form->text('name', 'Name');
        $form->email('email', 'Email');
        $form->password('password', 'Password');
        $form->text('remember_token', 'Remember token');
        $form->switch('email_verified', 'Email verified');

        return $form;
    }*/
}
