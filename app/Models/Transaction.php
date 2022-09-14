<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model;


class Transaction extends Model
{
    // $t = (new App\Models\Transaction)->create(['name' => 1])

    protected $connection = 'mongodb';
    protected $collection = 'transactions';

    protected $dates = [
        'created_at',
        'updated_at',
        'time',
        'created_at'
    ];

    protected $fillable = [
        'name'
    ];
}
