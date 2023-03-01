<?php

namespace App\Notifications\WorkOrder;

use App\Models\WorkOrder\Reply as ReplyModel;
use App\Models\WorkOrder\WorkOrder as WorkOrderModel;
use App\Notifications\Channels\WebChannel;
use App\Notifications\Channels\WeComChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;

class Reply extends Notification implements ShouldQueue
{
    use Queueable;

    public ReplyModel $reply_model;

    protected WorkOrderModel $work_order;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(ReplyModel $reply)
    {
        $this->reply_model = $reply;
        $this->work_order = $reply->workOrder;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(): array
    {
        if (! $this->work_order->notify) {
            return [];
        }

        $channels = [WeComChannel::class, WebChannel::class];

        if ($this->work_order->status === 'replied') {
            $channels[] = 'mail';
        }

        return $channels;
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(ReplyModel $reply): MailMessage
    {
        $url = URL::format(config('settings.dashboard.base_url'), config('settings.dashboard.work_order_path').'/'.$this->work_order->uuid);

        return (new MailMessage)
            ->subject('工单: '.$this->work_order->title.' 需要您处理。')
            ->line('我们的回复: ')
            ->line($reply->content)
            ->action('查看工单', $url);
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(ReplyModel $reply): array
    {
        $array = $reply->toArray();

        $array['event'] = 'work-order.reply.created';

        return $array;
    }

    public function toWeCom(ReplyModel $reply): false|array
    {
        $this->work_order->load(['module', 'user']);

        return [
            'key' => $this->work_order->wecom_key,
            'view' => 'notifications.work_order.reply',
            'data' => $reply,
        ];
    }
}
