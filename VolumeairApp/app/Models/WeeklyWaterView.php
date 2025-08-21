<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WeeklyWaterView extends Model
{
    protected $table = 'weekly_water_view';
    protected $fillable = [
        'week_start','week_end','total_volume','daily_average',
        'subuh_total','dzuhur_total','ashar_total','maghrib_total','isya_total'
    ];
    protected $casts = [
        'week_start' => 'date',
        'week_end' => 'date',
        'total_volume' => 'float',
        'daily_average' => 'float',
    ];
}
