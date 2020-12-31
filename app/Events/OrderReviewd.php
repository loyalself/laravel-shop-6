<?php

namespace App\Events;

use App\Models\Order;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
// 8.5 评价商品 新建:用户给商品打完分之后，系统需要重新计算对应商品的评分数据，
class OrderReviewd
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    protected $order; //8.5

    public function __construct(Order $order) //8.5
    {
        $this->order = $order;
    }

    public function getOrder()
    {
        return $this->order;
    }
    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }
}
