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
//7.6. 完善支付后逻辑 新建: 支付成功的事件
class OrderPaid
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    protected $order; //7.6

    //7.6
    public function __construct(Order $order){
        $this->order = $order;
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

    /**
     * 7.6 添加:
     * 事件本身不需要有逻辑，只需要包含相关的信息即可，在我们这个场景里就只需要一个订单对象
     */
    public function getOrder(){
        return $this->order;
    }
}
