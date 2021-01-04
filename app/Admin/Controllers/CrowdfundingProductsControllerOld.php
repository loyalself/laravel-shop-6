<?php

namespace App\Admin\Controllers;

use App\Models\Category;
use App\Models\CrowdfundingProduct;
use App\Models\Product;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
//4.3. 后台众筹商品管理 新建:
class CrowdfundingProductsController extends Controller
{
    use HasResourceActions;

    public function index(Content $content){
        /*return $content
            ->header('Index')
            ->description('description')
            ->body($this->grid());*/

        //4.3 修改
        return $content
            ->header('众筹商品列表')
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

        //4.3
        return $content
            ->header('编辑众筹商品')
            ->body($this->form()->edit($id));
    }

    /**
     * Create interface.
     *
     * @param Content $content
     * @return Content
     */
    public function create(Content $content)
    {
       /* return $content
            ->header('Create')
            ->description('description')
            ->body($this->form());*/

        //4.3
        return $content
            ->header('创建众筹商品')
            ->body($this->form());
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        /*$grid = new Grid(new Product);
        $grid->id('Id');
        $grid->type('Type');
        $grid->category_id('Category id');
        $grid->title('Title');
        $grid->description('Description');
        $grid->image('Image');
        $grid->on_sale('On sale');
        $grid->rating('Rating');
        $grid->sold_count('Sold count');
        $grid->review_count('Review count');
        $grid->price('Price');
        $grid->created_at('Created at');
        $grid->updated_at('Updated at');
        return $grid;*/

        //4.3
        $grid = new Grid(new Product);

        // 只展示 type 为众筹类型的商品
        $grid->model()->where('type', Product::TYPE_CROWDFUNDING);
        $grid->id('ID')->sortable();
        $grid->title('商品名称');
        $grid->on_sale('已上架')->display(function ($value) {
            return $value ? '是' : '否';
        });
        $grid->price('价格');
        // 展示众筹相关字段
        $grid->column('crowdfunding.target_amount', '目标金额');
        $grid->column('crowdfunding.end_at', '结束时间');
        $grid->column('crowdfunding.total_amount', '目前金额');
        $grid->column('crowdfunding.status', ' 状态')->display(function ($value) {
            return CrowdfundingProduct::$statusMap[$value];
        });

        $grid->actions(function ($actions) {
            $actions->disableView();
            $actions->disableDelete();
        });
        $grid->tools(function ($tools) {
            $tools->batch(function ($batch) {
                $batch->disableDelete();
            });
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
        $show = new Show(Product::findOrFail($id));

        $show->id('Id');
        $show->type('Type');
        $show->category_id('Category id');
        $show->title('Title');
        $show->description('Description');
        $show->image('Image');
        $show->on_sale('On sale');
        $show->rating('Rating');
        $show->sold_count('Sold count');
        $show->review_count('Review count');
        $show->price('Price');
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
        /*$form = new Form(new Product);
        $form->text('type', 'Type')->default('normal');
        $form->number('category_id', 'Category id');
        $form->text('title', 'Title');
        $form->textarea('description', 'Description');
        $form->image('image', 'Image');
        $form->switch('on_sale', 'On sale')->default(1);
        $form->decimal('rating', 'Rating')->default(5.00);
        $form->number('sold_count', 'Sold count');
        $form->number('review_count', 'Review count');
        $form->decimal('price', 'Price');
        return $form;*/

        //4.3
        $form = new Form(new Product);

        // 在表单中添加一个名为 type，值为 Product::TYPE_CROWDFUNDING 的隐藏字段
        $form->hidden('type')->value(Product::TYPE_CROWDFUNDING);
        $form->text('title', '商品名称')->rules('required');
        $form->select('category_id', '类目')->options(function ($id) {
            $category = Category::query()->find($id);
            if ($category) {
                return [$category->id => $category->full_name];
            }
        })->ajax('/admin/api/categories?is_directory=0');
        $form->image('image', '封面图片')->rules('required|image');
        $form->editor('description', '商品描述')->rules('required');
        $form->radio('on_sale', '上架')->options(['1' => '是', '0' => '否'])->default('0');
        // 添加众筹相关字段
        $form->text('crowdfunding.target_amount', '众筹目标金额')->rules('required|numeric|min:0.01');
        $form->datetime('crowdfunding.end_at', '众筹结束时间')->rules('required|date');
        $form->hasMany('skus', '商品 SKU', function (Form\NestedForm $form) {
            $form->text('title', 'SKU 名称')->rules('required');
            $form->text('description', 'SKU 描述')->rules('required');
            $form->text('price', '单价')->rules('required|numeric|min:0.01');
            $form->text('stock', '剩余库存')->rules('required|integer|min:0');
        });
        $form->saving(function (Form $form) {
            $form->model()->price = collect($form->input('skus'))->where(Form::REMOVE_FLAG_NAME, 0)->min('price');
        });

        return $form;
    }
}