<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ObjectClass extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    public function objectForms(): HasMany
    {
        return $this->hasMany(ObjectForm::class);
    }
}
