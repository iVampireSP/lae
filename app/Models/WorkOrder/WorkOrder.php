<?php

namespace App\Models\WorkOrder;

use App\Exceptions\CommonException;
use App\Jobs\WorkOrder\WorkOrder as WorkOrderJob;
use App\Models\Host;
use App\Models\Module;
use App\Models\User;
use App\Notifications\WorkOrder\WorkOrder as WorkOrderNotification;
use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class WorkOrder extends Model
{
    use Cachable, Notifiable;

    protected $table = 'work_orders';

    protected $fillable = [
        'title',
        'content',
        'host_id',
        'user_id',
        'module_id',
        'status',
        'notify',
    ];

    protected $hidden = [
        'ip',
    ];

    protected $casts = [
        'notify' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function (self $model) {
            $model->uuid = Str::uuid()->toString();

            if ($model->host_id) {
                $model->load(['host']);
                $model->module_id = $model->host->module_id;
            }

            if (auth('sanctum')->check()) {
                $model->user_id = auth()->id();

                if ($model->host_id) {
                    if (! $model->user_id == $model->host->user_id) {
                        throw new CommonException('user_id not match host user_id');
                    }
                }
            } else {
                if (! $model->user_id) {
                    throw new CommonException('user_id is required');
                }
            }

            if ($model->host_id) {
                $model->host->load('module');
                $module = $model->host->module;

                if ($module === null) {
                    $model->status = 'open';
                } else {
                    $model->status = 'pending';
                }
            }

            $model->notify = true;

            $model->ip = request()->ip();
        });

        // updated
        static::updated(function (self $model) {
            dispatch(new WorkOrderJob($model, 'put'));

            if ($model->notify && $model->isDirty('status')) {
                $model->notify(new WorkOrderNotification($model));
            }
        });
    }

    public function scopeThisModule($query)
    {
        return $query->where('module_id', auth('module')->id());
    }

    public function scopeThisUser($query)
    {
        return $query->where('user_id', auth()->id());
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function replies(): HasMany
    {
        return $this->hasMany(Reply::class);
    }

    public function host(): BelongsTo
    {
        return $this->belongsTo(Host::class);
    }

    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }

    public function isFailure(): bool
    {
        return $this->status === 'pending' || $this->status === 'error';
    }

    public function isOpen(): bool
    {
        return $this->status !== 'closed' && $this->status !== 'error' && $this->status !== 'pending';
    }

    public function isClosed(): bool
    {
        return $this->status === 'closed';
    }

    public function markAsRead(): bool
    {
        if (! $this->isWaitingForResponse()) {
            return false;
        }

        if (auth('admin')->check() && $this->status !== 'replied') {
            $this->status = 'read';
        } elseif (
            auth('sanctum')->check()
            &&
            $this->status !== 'user_replied'
        ) {
            $this->status = 'user_read';
        }

        $this->save();

        return true;
    }

    public function isWaitingForResponse(): bool
    {
        return $this->status === 'replied' || $this->status === 'user_replied';
    }

    /**
     * @throws CommonException
     */
    public function safeDelete(): bool
    {
        if ($this->status == 'pending') {
            throw new CommonException('工单状态是 pending，无法删除');
        }

        if ($this->isPlatform()) {
            $this->delete();
        } else {
            dispatch(new WorkOrderJob($this, 'delete'));
        }

        return true;
    }

    public function isPlatform(): bool
    {
        return $this->module_id === null && $this->host_id === null;
    }

    public function routeNotificationForMail(): array
    {
        $user = $this->user;

        return [$user->email => $user->name];
    }
}
