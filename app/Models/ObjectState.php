<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ObjectState extends Model
{
    use HasFactory;

    protected $fillable = [
        'number',
        'description',
    ];

    public function objectVersion(): BelongsTo
    {
        return $this->belongsTo(ObjectVersion::class);
    }

    public function objectInstances(): HasMany
    {
        return $this->hasMany(ObjectInstance::class);
    }
}
