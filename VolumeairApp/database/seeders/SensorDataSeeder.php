<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SensorDataSeeder extends Seeder
{
    public function run(): void
    {
        // generate 200 baris data acak 24 jam terakhir
        $now = Carbon::now('Asia/Jakarta');

        for ($i=0; $i<200; $i++) {
            $ts    = $now->copy()->subMinutes(rand(0, 24*60));
            $flow  = round(mt_rand(0, 3000)/1000, 3); // 0.000 - 3.000
            $jarak = round(mt_rand(500, 5000)/100, 2); // 5.00 - 50.00
            $stat  = $flow > 0.05 ? 'ON' : 'OFF';

            DB::table('sensor_data')->insert([
                'jarak'     => $jarak,
                'flow'      => $flow,
                'status'    => $stat,
                'timestamp' => $ts,
            ]);

            // update daily summary jika flow signifikan (simulasi interval 5 detik)
            if ($flow > 0.1) {
                $date = $ts->toDateString();
                $row = DB::table('daily_water_summary')->where('date', $date)->first();

                $interval = 5;
                $inc = $flow * ($interval/60);

                if ($row) {
                    DB::table('daily_water_summary')->where('id', $row->id)->update([
                        'total_volume'     => (float)$row->total_volume + $inc,
                        'peak_flow_rate'   => max((float)$row->peak_flow_rate, $flow),
                        'total_usage_time' => (int)$row->total_usage_time + $interval,
                        'updated_at'       => now(),
                    ]);
                } else {
                    DB::table('daily_water_summary')->insert([
                        'date'              => $date,
                        'total_volume'      => $inc,
                        'total_usage_time'  => $interval,
                        'peak_flow_rate'    => $flow,
                        'average_flow_rate' => $flow,
                        'usage_sessions'    => 1,
                        'created_at'        => now(),
                        'updated_at'        => now(),
                    ]);
                }
            }
        }

        // seed laporan shalat 7 hari terakhir
        $prayers = ['Subuh','Dzuhur','Ashar','Maghrib','Isya'];
        for ($d=0; $d<7; $d++) {
            $date = $now->copy()->subDays($d)->toDateString();
            foreach ($prayers as $p) {
                $vol = round(mt_rand(0, 4000)/1000, 3); // 0–4 L
                if ($vol == 0) continue;

                [$start, $end] = match($p){
                    'Subuh'   => ['03:30:00', '05:30:00'],
                    'Dzuhur'  => ['12:00:00', '14:00:00'],
                    'Ashar'   => ['16:30:00', '18:30:00'],
                    'Maghrib' => ['18:30:00', '19:00:00'],
                    'Isya'    => ['19:00:00', '21:00:00'],
                };

                DB::table('shalat_water_reports')->insert([
                    'prayer_name'  => $p,
                    'total_volume' => $vol,
                    'date'         => $date,
                    'start_time'   => $start,
                    'end_time'     => $end,
                    'timestamp'    => $date.' '.$start,
                ]);
            }
        }

        // hitung weekly_view untuk 4 minggu terakhir
        for ($w=0; $w<4; $w++) {
            $dt = $now->copy()->subWeeks($w);
            $weekStart = $dt->copy()->startOfWeek(Carbon::MONDAY)->toDateString();
            $weekEnd   = $dt->copy()->endOfWeek(Carbon::SUNDAY)->toDateString();

            $totals = DB::table('shalat_water_reports')
                ->selectRaw("
                    SUM(total_volume) as total_volume,
                    SUM(CASE WHEN prayer_name='Subuh'   THEN total_volume ELSE 0 END) as subuh_total,
                    SUM(CASE WHEN prayer_name='Dzuhur'  THEN total_volume ELSE 0 END) as dzuhur_total,
                    SUM(CASE WHEN prayer_name='Ashar'   THEN total_volume ELSE 0 END) as ashar_total,
                    SUM(CASE WHEN prayer_name='Maghrib' THEN total_volume ELSE 0 END) as maghrib_total,
                    SUM(CASE WHEN prayer_name='Isya'    THEN total_volume ELSE 0 END) as isya_total
                ")
                ->whereBetween('date', [$weekStart, $weekEnd])
                ->first();

            $totalVol = (float)($totals->total_volume ?? 0);
            DB::table('weekly_water_view')->updateOrInsert(
                ['week_start' => $weekStart],
                [
                    'week_end'      => $weekEnd,
                    'total_volume'  => $totalVol,
                    'daily_average' => $totalVol/7,
                    'subuh_total'   => (float)($totals->subuh_total ?? 0),
                    'dzuhur_total'  => (float)($totals->dzuhur_total ?? 0),
                    'ashar_total'   => (float)($totals->ashar_total ?? 0),
                    'maghrib_total' => (float)($totals->maghrib_total ?? 0),
                    'isya_total'    => (float)($totals->isya_total ?? 0),
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ]
            );
        }
    }
}
