<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory;

    const CREATED_AT = 'createAt';

    const UPDATED_AT = 'updateAt';

    protected $fillable = [
        'name',
        'email',
        'avatar',
        'google_id',
        'external_id',
        'locale',
        'timezone',
        'currency',
        'hourly_rate',
    ];

    protected $hidden = [
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'hourly_rate' => 'decimal:2',
        ];
    }

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

            // Apply personalization defaults on every creation path
            // (OAuth, factory, seeder) regardless of DB column defaults.
            foreach (config('preferences.defaults') as $key => $default) {
                if ($model->{$key} === null || $model->{$key} === '') {
                    $model->{$key} = $default;
                }
            }
        });
    }

    public function vectors(): HasMany
    {
        return $this->hasMany(Vector::class);
    }

    public function tags(): HasMany
    {
        return $this->hasMany(Tag::class);
    }

    public function timeEntries(): HasMany
    {
        return $this->hasMany(TimeEntry::class);
    }

    public function goals(): HasMany
    {
        return $this->hasMany(Goal::class);
    }
}
