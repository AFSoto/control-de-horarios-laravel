<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TimeAdjustment extends Model
{
    use HasFactory;

    protected $fillable = [
        'time_entry_id',
        'user_id',
        'old_minutes_owed',
        'new_minutes_owed',
        'reason',
    ];

    public function timeEntry()
    {
        return $this->belongsTo(TimeAdjustment::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
