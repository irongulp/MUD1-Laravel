<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ObjectForm extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'weight',
        'value',
    ];

    public function objectClass(): BelongsTo
    {
        return $this->belongsTo(ObjectClass::class);
    }

    public function objectVersions(): HasMany
    {
        return $this->HasMany(ObjectVersion::class);
    }
}
