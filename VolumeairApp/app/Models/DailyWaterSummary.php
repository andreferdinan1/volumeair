<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailyWaterSummary extends Model
{
    protected $table = 'daily_water_summary';
    protected $fillable = [
        'date','total_volume','total_usage_time',
        'peak_flow_rate','average_flow_rate','usage_sessions'
    ];
    protected $casts = [
        'date' => 'date',
        'total_volume' => 'float',
        'peak_flow_rate' => 'float',
        'average_flow_rate' => 'float',
    ];
}
