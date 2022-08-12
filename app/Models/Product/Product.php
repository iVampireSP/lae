<?php

namespace App\Models\Product;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $table = 'products';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'is_hidden',
        'product_group_id',
        'is_stock_control',
        'stock',
        'module',
        'server_group_id',
        'order',
        'price',
        'is_retired',
        'is_recommended',
        'setup_fee',
        'monthly_fee',
        'quarterly_fee',
        'half_yearly_fee',
        'yearly_fee',
        'hourly_fee',
        'configurable_option_group_id'
    ];

    public function productGroup()
    {
        return $this->belongsTo(ProductGroup::class);
    }

    // public function configurableOptionGroup()
    // {
    //     return $this->belongsTo(ConfigurableOptionGroup::class);
    // }

    // public function configurableOptions()
    // {
    //     return $this->hasMany(ConfigurableOption::class);
    // }

}
