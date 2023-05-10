<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Travel extends Model
{
    use HasFactory;

    protected $fillable = [
        'order',
        'if_empty',
        'condition_type',
        'condition_id',
        'condition_only_if_has_object',
        'destination_type',
        'destination_id',
        'is_game_over',
        'is_fixed_direction',
        'if_forced',
    ];

    public function from(): BelongsTo
    {
        return $this->belongsTo(Room::class, 'room_id');
    }

    public function to(): MorphTo
    {
        return $this->morphTo('destination');
    }

    public function motions(): BelongsToMany
    {
        return $this->belongsToMany(Motion::class);
    }

    public function condition(): MorphTo
    {
        return $this->morphTo();
    }

    public function onlyIfFrom(): BelongsTo
    {
        return $this->belongsTo(Room::class, 'only_if_from_room_id');
    }

}
