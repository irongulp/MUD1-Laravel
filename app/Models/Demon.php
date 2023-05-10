<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Demon extends Model
{
    use HasFactory;

    protected $fillable = [
        'number',
        'name',
        'system_attribute_1',
        'system_attribute_2',
        'delay_minimum',
        'delay_maximum',
    ];

    public function attributes(): BelongsToMany {
        return $this->belongsToMany(Attribute::class);
    }
}
