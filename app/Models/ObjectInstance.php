<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ObjectInstance extends Model
{
    use HasFactory;

    protected $fillable = [
        'speed',
        'attack_probability',
        'score',
        'stamina',
        'is_light_source',
        'is_getable',
        'is_it',
        'can_carry_weight',
        'is_disguised_container',
        'is_always_open_container',
        'is_transparent_container',
        'is_no_summon',
        'is_fixed',
        'maximum_state_number',
    ];

    public function rooms(): BelongsToMany
    {
        return $this->belongsToMany(Room::class);
    }

    public function objectState(): BelongsTo
    {
        return $this->belongsTo(ObjectState::class);
    }

    public function demon(): BelongsTo
    {
        return $this->belongsTo(Demon::class);
    }

    public function objectVersion(): BelongsTo
    {
        return $this->belongsTo(ObjectForm::class);
    }
}
