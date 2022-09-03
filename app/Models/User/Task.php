<?php

namespace App\Models\User;

use App\Models\Host;
use App\Exceptions\CommonException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Ramsey\Uuid\Uuid;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'host_id',
        'title',
        'progress',
        'status',
    ];

    protected $casts = [
        'id' => 'string',
        'progress' => 'integer',
    ];

    public $incrementing = false;

    public function scopeUser($query) {
        return $query->where('user_id', auth()->id());
    }


    public function host() {
        return $this->belongsTo(Host::class);
    }

    // before create
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            // id 为 uuid
            $model->id = Uuid::uuid4()->toString();

            // host_id 和 user_id 至少存在一个
            if (!$model->host_id && !$model->user_id) {
                throw new CommonException('host_id 和 user_id 至少存在一个');
            }

            // if host_id
            if ($model->host_id) {
                $model->load('host');
                // dd($model);


                // dd($model->host_id);
                // $host = Host::where('id', $model->host_id)->first();

                // dd($host);

                $model->user_id = $model->host->user_id;
            }

        });

        // updateing
        static::updating(function ($model) {
            if ($model->progress == 100) {
                $model->status = 'done';
            }
        });
    }
}
