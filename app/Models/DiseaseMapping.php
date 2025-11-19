<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DiseaseMapping extends Model
{
    protected $fillable = [
        'disease', 'medicine', 'confidence'
    ];
}
