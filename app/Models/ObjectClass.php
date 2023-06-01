<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
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

    public function getObjectInstances(): array
    {
        $objectInstances = array();
        foreach ($this->objectForms as $objectForm) {
            foreach ($objectForm->objectVersions as $objectVersion) {
                foreach ($objectVersion->objectInstances as $objectInstance) {
                    $objectInstances[] = $objectInstance;
                }
            }
        }

        return $objectInstances;
    }
}
