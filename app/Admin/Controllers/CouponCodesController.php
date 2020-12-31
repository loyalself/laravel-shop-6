<?php

namespace App\Admin\Controllers;

use App\Models\CouponCode;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
//9.1. 管理后台 - 优惠券列表 新建
class CouponCodesController extends Controller
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
        /*return $content
            ->header('Index')
            ->description('description')
            ->body($this->grid());*/

        return Admin::content(function (Content $content) {
            $content->header('优惠券列表');
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


    public function edit($id, Content $content)
    {
       /* return $content
            ->header('Edit')
            ->description('description')
            ->body($this->form()->edit($id));*/

        //9.2. 修改: 优惠券
        return Admin::content(function (Content $content) use ($id) {
            $content->header('编辑优惠券');
            $content->body($this->form()->edit($id));
        });
    }


    public function create(Content $content){
        /*return $content
            ->header('Create')
            ->description('description')
            ->body($this->form());*/

        //9.2. 管理后台 - 添加、修改、删除优惠券 修改
        return Admin::content(function (Content $content) {
            $content->header('新增优惠券');
            $content->body($this->form());
        });
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        /*$grid = new Grid(new CouponCode);

        $grid->id('Id');
        $grid->name('Name');
        $grid->code('Code');
        $grid->type('Type');
        $grid->value('Value');
        $grid->total('Total');
        $grid->used('Used');
        $grid->min_amount('Min amount');
        $grid->not_before('Not before');
        $grid->not_after('Not after');
        $grid->enabled('Enabled');
        $grid->created_at('Created at');
        $grid->updated_at('Updated at');

        return $grid;*/

       /* return Admin::grid(CouponCode::class, function (Grid $grid) {
            // 默认按创建时间倒序排序
            $grid->model()->orderBy('created_at', 'desc');
            $grid->id('ID')->sortable();
            $grid->name('名称');
            $grid->code('优惠码');
            $grid->type('类型')->display(function($value) {
                return CouponCode::$typeMap[$value];
            });
            // 根据不同的折扣类型用对应的方式来展示
            $grid->value('折扣')->display(function($value) {
                return $this->type === CouponCode::TYPE_FIXED ? '￥'.$value : $value.'%';
            });
            $grid->min_amount('最低金额');
            $grid->total('总量');
            $grid->used('已用');
            $grid->enabled('是否启用')->display(function($value) {
                return $value ? '是' : '否';
            });
            $grid->created_at('创建时间');

            $grid->actions(function ($actions) {
                $actions->disableView();
            });
        });*/

        //9.1 同章修改 其中 $grid->column('usage', '用量') 是我们虚拟出来的一个字段，然后通过 display() 来输出这个虚拟字段的内容
        return Admin::grid(CouponCode::class, function (Grid $grid) {
            $grid->model()->orderBy('created_at', 'desc');
            $grid->id('ID')->sortable();
            $grid->name('名称');
            $grid->code('优惠码');
            $grid->description('描述');
            $grid->column('usage', '用量')->display(function ($value) {
                return "{$this->used} / {$this->total}";
            });
            $grid->enabled('是否启用')->display(function ($value) {
                return $value ? '是' : '否';
            });
            $grid->created_at('创建时间');
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
        $show = new Show(CouponCode::findOrFail($id));

        $show->id('Id');
        $show->name('Name');
        $show->code('Code');
        $show->type('Type');
        $show->value('Value');
        $show->total('Total');
        $show->used('Used');
        $show->min_amount('Min amount');
        $show->not_before('Not before');
        $show->not_after('Not after');
        $show->enabled('Enabled');
        $show->created_at('Created at');
        $show->updated_at('Updated at');

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
       /* $form = new Form(new CouponCode);

        $form->text('name', 'Name');
        $form->text('code', 'Code');
        $form->text('type', 'Type');
        $form->decimal('value', 'Value');
        $form->number('total', 'Total');
        $form->number('used', 'Used');
        $form->decimal('min_amount', 'Min amount');
        $form->datetime('not_before', 'Not before')->default(date('Y-m-d H:i:s'));
        $form->datetime('not_after', 'Not after')->default(date('Y-m-d H:i:s'));
        $form->switch('enabled', 'Enabled');

        return $form;*/

        /**
         * 9.2. 修改: 代码解析
         * 对于优惠码 code 字段，我们的第一个校验规则是 nullable，允许用户不填，不填的情况优惠码将由系统生成。
        对于折扣 value 字段，我们的校验规则是一个匿名函数，当我们的校验规则比较复杂，或者需要根据用户提交的其他字段来判断时就可以用匿名函数的方式来定义校验规则。
        $form->saving() 方法用来注册一个事件处理器，在表单的数据被保存前会被触发，这里我们判断如果用户没有输入优惠码，就通过 findAvailableCode() 来自动生成
         */
        return Admin::form(CouponCode::class, function (Form $form) {
            $form->display('id', 'ID');
            $form->text('name', '名称')->rules('required');
            $form->text('code', '优惠码')->rules('nullable|unique:coupon_codes');
            $form->radio('type', '类型')->options(CouponCode::$typeMap)->rules('required');
            $form->text('value', '折扣')->rules(function ($form) {
                if ($form->type === CouponCode::TYPE_PERCENT) {
                    // 如果选择了百分比折扣类型，那么折扣范围只能是 1 ~ 99
                    return 'required|numeric|between:1,99';
                } else {
                    // 否则只要大等于 0.01 即可
                    return 'required|numeric|min:0.01';
                }
            });
            $form->text('total', '总量')->rules('required|numeric|min:0');
            $form->text('min_amount', '最低金额')->rules('required|numeric|min:0');
            $form->datetime('not_before', '开始时间');
            $form->datetime('not_after', '结束时间');
            $form->radio('enabled', '启用')->options(['1' => '是', '0' => '否']);

            //9.2 编辑的时候,若没有修改优惠码,不验证
            $form->text('code', '优惠码')->rules(function($form) {
                // 如果 $form->model()->id 不为空，代表是编辑操作
                if ($id = $form->model()->id) {
                    return 'nullable|unique:coupon_codes,code,'.$id.',id';
                } else {
                    return 'nullable|unique:coupon_codes';
                }
            });

            $form->saving(function (Form $form) {
                if (!$form->code) {
                    $form->code = CouponCode::findAvailableCode();
                }
            });
        });
    }
}
