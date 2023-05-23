<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Verb extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    public function actions(): MorphMany
    {
        return $this->morphMany(Action::class, 'type');
    }
}
