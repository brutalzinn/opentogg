<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

abstract class BaseModel extends Model
{
    const CREATED_AT = 'createAt';
    const UPDATED_AT = 'updateAt';

    public function getRouteKeyName(): string
    {
        return 'external_id';
    }

    protected static function booted(): void
    {
        static::creating(function (self $model) {
            if (empty($model->external_id)) {
                $model->external_id = Str::uuid()->toString();
            }
        });
    }
}
