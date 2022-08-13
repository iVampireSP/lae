<?php

namespace App\Models\Admin;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Admin extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name', 'email', 'password',
    ];

    protected $hidden = [
        'password'
    ];


    // protect password
    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = bcrypt($value);
    }

    // before create admin, generate api_token
    public static function boot()
    {
        parent::boot();
        self::creating(function ($admin) {

            if (app()->environment('production')) {
                $admin->api_token = Str::random(60);
            } else {
                $admin->api_token = 123456;

            }

        });
    }
}
