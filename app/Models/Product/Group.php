<?php

namespace App\Models\Product;

use App\Models\Admin\Admin;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    use HasFactory;

    protected $table = 'product_groups';

    protected $fillable = [
        'name',
        'slug',
        'title',
        'description',
        'is_hidden',
        'order',
    ];

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
