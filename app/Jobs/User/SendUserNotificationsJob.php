<?php

namespace App\Jobs\User;

use App\Http\Controllers\Admin\NotificationController;
use App\Models\User;
use App\Notifications\User\UserNotification;
use GeneaLabs\LaravelModelCaching\CachedBuilder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendUserNotificationsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected array $requests;

    protected User|CachedBuilder $users;

    protected string $title;

    protected string $content;

    protected bool $send_mail;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(array $requests, $title, $content, $send_mail = false)
    {
        $this->requests = $requests;
        $this->title = $title;
        $this->content = $content;
        $this->send_mail = $send_mail;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $notificationController = new NotificationController();

        $users = $notificationController->query($this->requests);

        // chunk
        $users->chunk(100, function ($users) {
            foreach ($users as $user) {
                $user->notify(new UserNotification($this->title, $this->content, $this->send_mail));
            }
        });
    }
}
