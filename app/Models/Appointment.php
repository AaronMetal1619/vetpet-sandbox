<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    protected $fillable = ['pet_id', 'date', 'time', 'reason', 'status'];

    public function pet()
    {
        return $this->belongsTo(Pet::class);
    }
}
