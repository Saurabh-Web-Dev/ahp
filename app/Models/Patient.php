<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Patient extends Model
{
    use HasFactory;

    protected $fillable = [
        'manager_id',
        'patient_id',
        'name',
        'dob',
        'age',
        'gender',
        'parent',
        'location',
    ];

    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            // Generate a unique patient ID, e.g., PAT-20251102-ABC123
            $model->patient_id = 'PAT-' . date('Ymd') . '-' . Str::upper(Str::random(6));
        });
    }

    public function appointments()
    {
        return $this->hasMany(\App\Models\Appointment::class, 'patient_id');
    }
}
