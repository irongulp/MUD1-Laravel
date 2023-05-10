<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Room extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'short_description',
        'long_description',
        'chain',
    ];

    public function getShortAttribute(): ?string
    {
        return $this->short_description ?? $this->shortDescription->short_description ?? null;
    }

    public function sameShortDescriptionAs(): BelongsTo
    {
        return $this->belongsTo(Room::class, 'short_description_room_id');
    }

    public function droppedObjectsMovedTo(): BelongsTo
    {
        return $this->belongsTo(Room::class, 'drop_move_room_id');
    }

    public function attributes(): BelongsToMany
    {
        return $this->belongsToMany(Attribute::class);
    }

    public function travel(): HasMany
    {
        return $this->hasMany(Travel::class);
    }

    public function shortDescription(): BelongsTo
    {
        return $this->belongsTo(Room::class, 'short_description_room_id');
    }

    public function objectInstances(): BelongsToMany
    {
        return $this->belongsToMany(ObjectInstance::class);
    }
}
