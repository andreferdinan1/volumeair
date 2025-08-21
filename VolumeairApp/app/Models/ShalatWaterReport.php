<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShalatWaterReport extends Model
{
    public $timestamps = false;
    protected $table = 'shalat_water_reports';
    protected $fillable = ['prayer_name','total_volume','date','start_time','end_time','timestamp'];
    protected $casts = [
        'total_volume' => 'float',
        'date' => 'date',
        'timestamp' => 'datetime',
    ];
}
