<?php

namespace App\Jobs;

use App\Http\Controllers\Admin\NotificationController;
use App\Models\User;
use App\Notifications\Common;
use GeneaLabs\LaravelModelCaching\CachedBuilder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendCommonNotificationsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected array $requests;
    protected User|CachedBuilder $users;
    protected string $title, $content;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(array $requests, $title, $content)
    {
        $this->requests = $requests;
        $this->title = $title;
        $this->content = $content;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        //

        $notificationController = new NotificationController();

        $users = $notificationController->query($this->requests);

        // chunk
        $users->chunk(100, function ($users) {
            foreach ($users as $user) {
                $user->notify(new Common($this->title, $this->content));
            }
        });
    }
}
