<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SensorData extends Model
{
    public $timestamps = false; // kita pakai kolom timestamp custom
    protected $table = 'sensor_data';
    protected $fillable = ['jarak','flow','status','active_prayer','timestamp'];
    protected $casts = [
        'jarak' => 'float',
        'flow'  => 'float',
        'timestamp' => 'datetime',
    ];
}
