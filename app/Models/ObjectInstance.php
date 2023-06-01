<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ObjectInstance extends Model
{
    use HasFactory;

    protected $fillable = [
    ];

    public function getDescriptionAttribute(): ?string
    {
        if ($this->objectState) {
            return $this->objectState->description;
        }

        return $this->objectImprint->objectVersion->objectStates->first()?->description;
    }

    public function objectImprint(): BelongsTo
    {
        return $this->belongsTo(ObjectImprint::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function objectState(): BelongsTo
    {
        return $this->belongsTo(ObjectState::class);
    }
}
