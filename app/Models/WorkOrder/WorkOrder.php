<?php

namespace App\Models\WorkOrder;

use App\Exceptions\CommonException;
use App\Jobs\Module\WorkOrder\WorkOrder as WorkOrderJob;
use App\Models\Host;
use App\Models\Module;
use App\Models\User;
use App\Notifications\WorkOrder as WorkOrderNotification;
use Eloquent;
use GeneaLabs\LaravelModelCaching\CachedBuilder;
use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
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
        'notify'
    ];

    protected $casts = [
        'notify' => 'boolean'
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
                    if (!$model->user_id == $model->host->user_id) {
                        throw new CommonException('user_id not match host user_id');
                    }
                }
            } else {
                if (!$model->user_id) {
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
        });

        // updated
        static::updated(function (self $model) {
            dispatch(new WorkOrderJob($model, 'put'));

            $model->notify(new WorkOrderNotification($model));
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

    /**
     * @throws CommonException
     */
    public function safeDelete(): bool
    {
        if ($this->status == 'pending') {
            throw new CommonException('工单状态是 pending，无法删除');
        }

        dispatch(new WorkOrderJob($this, 'delete'));

        return true;
    }
}
