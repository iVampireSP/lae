<?php

namespace App\Models;

use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Application extends Authenticatable
{
    use Cachable;

    public $fillable = [
        'name',
        'description',
        'api_token',
    ];
}
