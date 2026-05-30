<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'first_name',
        'last_name',
        'gender',
        'position',
        'hire_date',
        'is_active',
    ];

    protected $casts = [
        'hire_time' => 'date',
        'is_active' => 'boolean',
    ];

    public function timeEntries()
    {
        return $this->hasMany(TimeEntry::class);
    }

    public function getFullNameAttribute()
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function getAllowedminutes($type)
    {
        if($this->gender === 'female'){
            return $type === 'breakfast' ? 15 : 30;
        }
        return $type === 'breakfast' ? 30 : 60;
    }

}
