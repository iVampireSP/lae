<?php

namespace App\Observers;

use App\Events\Users;
use App\Exceptions\CommonException;
use App\Models\Task;
use Ramsey\Uuid\Uuid;

class TaskObserver
{
    /**
     * @throws CommonException
     */
    public function creating(Task $task): void
    {
        // id 为 uuid
        $task->id = Uuid::uuid4()->toString();

        // 如果是模块创建的任务
        if (auth('module')->check()) {
            $task->module_id = auth('module')->id();
        }

        // host_id 和 user_id 至少存在一个
        if (! $task->host_id && ! $task->user_id) {
            throw new CommonException('host_id 和 user_id 至少存在一个');
        }

        // if host_id
        if ($task->host_id) {
            $task->load('host');

            if ($task->host === null) {
                throw new CommonException('host_id 不存在');
            }

            $task->user_id = $task->host->user_id;
        }
    }

    public function created(Task $task): void
    {
        $task->load('host');
        broadcast(new Users($task->user_id, 'tasks.created', $task));
    }

    // updating
    public function updating(Task $task): void
    {
        if ($task->progress == 100) {
            $task->status = 'done';
        }
    }

    public function updated(Task $task): void
    {
        $task->load('host');
        broadcast(new Users($task->user_id, 'tasks.updated', $task));
    }

    public function deleted(Task $task): void
    {
        broadcast(new Users($task->user_id, 'tasks.deleted', $task));
    }
}
