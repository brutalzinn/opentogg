<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Goal extends BaseModel
{
    protected $fillable = [
        'user_id',
        'vector_id',
        'operator',
        'target_hours',
        'period',
        'webhook_url',
        'last_achieved_at',
    ];

    protected function casts(): array
    {
        return [
            'target_hours' => 'decimal:2',
            'last_achieved_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function vector(): BelongsTo
    {
        return $this->belongsTo(Vector::class);
    }
}
