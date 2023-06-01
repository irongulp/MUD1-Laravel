<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InternalFunction extends Model
{
    private const TRANSPORT = 'trans';

    use HasFactory;

    protected $fillable = [
        'name',
    ];

    public function getIsTransportAttribute(): bool
    {
        return $this->name == self::TRANSPORT;
    }
}
