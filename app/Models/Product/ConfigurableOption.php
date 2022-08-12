<?php

namespace App\Models\Product;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConfigurableOption extends Model
{
    use HasFactory;

    protected $table = 'product_configurable_options';

    protected $fillable = [
        'display_name',
        'name',
        'is_hidden',
        'type',
        'min_qty',
        'max_qty',
        'unit',
        'qty_step',
        'is_allow_degrade',
        'order',
        'notes',
        'group_id',
    ];

    public function group()
    {
        return $this->belongsTo(ConfigOptionGroup::class);
    }



}
