<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Action extends Model
{
    use HasFactory;

    protected $fillable = [
        'order',
        'function_value',
    ];

    public function type(): MorphTo
    {
        return $this->morphTo();
    }

    public function toObjectClass(): BelongsTo
    {
        return $this->belongsTo(ObjectClass::class, 'to_object_class_id');
    }

    public function withObjectClass(): BelongsTo
    {
        return $this->belongsTo(ObjectClass::class, 'with_object_class_id');
    }

    public function internalCommand(): BelongsTo
    {
        return $this->belongsTo(InternalCommand::class);
    }

    public function internalFunction(): BelongsTo
    {
        return $this->belongsTo(InternalFunction::class);
    }

    public function functionObjectForm(): BelongsTo
    {
        return $this->belongsTo(ObjectForm::class, 'function_object_form_id');
    }

    public function functionRoom(): BelongsTo
    {
        return $this->belongsTo(Room::class, 'function_room_id');
    }

    public function playerText(): BelongsTo
    {
        return $this->belongsTo(Travel::class, 'player_text_id');
    }

    public function localText(): BelongsTo
    {
        return $this->belongsTo(Travel::class, 'local_text_id');
    }

    public function globalText(): BelongsTo
    {
        return $this->belongsTo(Travel::class, 'global_text_id');
    }

    public function demon(): BelongsTo
    {
        return $this->belongsTo(Demon::class);
    }
}
