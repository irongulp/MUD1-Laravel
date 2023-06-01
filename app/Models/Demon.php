<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

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

    public function action(): MorphOne
    {
        return $this->morphOne(Action::class, 'type');
    }
}
