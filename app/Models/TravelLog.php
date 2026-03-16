<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TravelLog extends Model
{
    protected $table = 'travel_logs';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'location',
        'from_location',
        'traveled_to_date',
        'departure_timestamp',
    ];

    protected function casts(): array {
        return [
            'traveled_to_date' => 'datetime',
            'departure_timestamp' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
