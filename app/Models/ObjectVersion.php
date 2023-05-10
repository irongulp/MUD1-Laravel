<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ObjectVersion extends Model
{
    use HasFactory;

    protected $fillable = [
        'version',
    ];

    public function objectForm(): BelongsTo
    {
        return $this->belongsTo(ObjectForm::class);
    }

    public function objectInstances(): HasMany
    {
        return $this->hasMany(ObjectInstance::class);
    }

    public function objectStates(): HasMany
    {
        return $this->hasMany(ObjectState::class);
    }
}
