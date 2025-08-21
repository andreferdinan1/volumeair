<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class VolumeAirController extends Controller
{
    // === HEALTH ===
    public function health()
    {
        return response()->json([
            "status"      => "success",
            "message"     => "Server is running",
            "server_time" => Carbon::now('Asia/Jakarta')->toDateTimeString(),
            "database"    => DB::connection()->getDatabaseName()
        ]);
    }

    // === ADD SENSOR DATA (setara add_data) ===
    public function addSensorData(Request $request)
    {
        $validated = $request->validate([
            'jarak'         => 'required|numeric',
            'flow'          => 'required|numeric',
            'status'        => 'required|string',
            'active_prayer' => 'nullable|string',
            // opsional: 'timestamp' => 'date'
        ]);

        $timestamp = $request->input('timestamp') ? Carbon::parse($request->input('timestamp')) : now();

        $id = DB::table('sensor_data')->insertGetId([
            'jarak'         => $validated['jarak'],
            'flow'          => $validated['flow'],
            'status'        => $validated['status'],
            'active_prayer' => $validated['active_prayer'] ?? null,
            'timestamp'     => $timestamp,
        ]);

        // update daily summary jika flow > threshold
        if ($validated['flow'] > 0.1) {
            $this->updateDailySummaryInternal($validated['flow'], $timestamp);
        }

        return response()->json([
            "status"  => "success",
            "message" => "Sensor data added",
            "id"      => $id
        ]);
    }

    // === ADD PRAYER VOLUME (setara add_prayer_volume) ===
    public function addPrayerVolume(Request $request)
    {
        $validated = $request->validate([
            'prayer_name'  => 'required|string|in:Subuh,Dzuhur,Ashar,Maghrib,Isya',
            'total_volume' => 'required|numeric',
            'timestamp'    => 'nullable|date'
        ]);

        $ts   = Carbon::parse($validated['timestamp'] ?? now());
        $date = $ts->toDateString();

        // window waktu (boleh kamu adjust)
        $prayerTimes = [
            'Subuh'   => ['start' => '03:30:00', 'end' => '05:30:00'],
            'Dzuhur'  => ['start' => '12:00:00', 'end' => '14:00:00'],
            'Ashar'   => ['start' => '16:30:00', 'end' => '18:30:00'],
            'Maghrib' => ['start' => '18:30:00', 'end' => '19:00:00'],
            'Isya'    => ['start' => '19:00:00', 'end' => '21:00:00'],
        ];

        $start = $prayerTimes[$validated['prayer_name']]['start'];
        $end   = $prayerTimes[$validated['prayer_name']]['end'];

        DB::table('shalat_water_reports')->insert([
            'prayer_name'  => $validated['prayer_name'],
            'total_volume' => $validated['total_volume'],
            'date'         => $date,
            'start_time'   => $start,
            'end_time'     => $end,
            'timestamp'    => $ts,
        ]);

        // update weekly view
        $this->updateWeeklyViewInternal($date);

        return response()->json([
            "status"  => "success",
            "message" => "Prayer volume data added"
        ]);
    }

    // === DAILY SUMMARY ===
    public function getDailySummary(Request $request)
    {
        $date = $request->get('date', Carbon::today('Asia/Jakarta')->toDateString());

        $summary = DB::table('daily_water_summary')->where('date', $date)->first();

        $prayers = DB::table('shalat_water_reports')
            ->select('prayer_name','total_volume','timestamp')
            ->where('date', $date)
            ->orderBy('timestamp')
            ->get();

        return response()->json([
            "status"         => "success",
            "date"           => $date,
            "daily_summary"  => $summary,
            "prayer_volumes" => $prayers
        ]);
    }

    // === PRAYER REPORTS ===
    public function getPrayerReports(Request $request)
    {
        $start = $request->get('start_date', Carbon::now('Asia/Jakarta')->subDays(7)->toDateString());
        $end   = $request->get('end_date',   Carbon::today('Asia/Jakarta')->toDateString());

        $reports = DB::table('shalat_water_reports')
            ->select('prayer_name','date','total_volume','start_time','end_time','timestamp')
            ->whereBetween('date', [$start, $end])
            ->orderBy('date', 'desc')
            ->orderByRaw("FIELD(prayer_name,'Subuh','Dzuhur','Ashar','Maghrib','Isya')")
            ->get();

        $summary = [
            'total_volume' => (float) $reports->sum('total_volume'),
            'by_prayer'    => [
                'Subuh' => (float) $reports->where('prayer_name','Subuh')->sum('total_volume'),
                'Dzuhur'=> (float) $reports->where('prayer_name','Dzuhur')->sum('total_volume'),
                'Ashar' => (float) $reports->where('prayer_name','Ashar')->sum('total_volume'),
                'Maghrib'=>(float) $reports->where('prayer_name','Maghrib')->sum('total_volume'),
                'Isya'  => (float) $reports->where('prayer_name','Isya')->sum('total_volume'),
            ]
        ];

        return response()->json([
            "status"  => "success",
            "period"  => ["start" => $start, "end" => $end],
            "reports" => $reports,
            "summary" => $summary
        ]);
    }

    // === WEEKLY VIEW ===
    public function getWeeklyView(Request $request)
    {
        $weeks = (int) $request->get('weeks', 4);

        $weekly = DB::table('weekly_water_view')
            ->orderByDesc('week_start')
            ->limit($weeks)
            ->get();

        return response()->json([
            "status"        => "success",
            "weeks_requested"=> $weeks,
            "weekly_data"   => $weekly,
            "trends"        => $this->calculateTrends($weekly->toArray())
        ]);
    }

    // === DIPAKAI DASHBOARD ===
    public function getCurrent()
    {
        $row = DB::table('sensor_data')->orderByDesc('timestamp')->first();
        if (!$row) return response()->json(['status'=>'success','data'=>null]);

        $todaySummary = DB::table('daily_water_summary')
            ->where('date', now('Asia/Jakarta')->toDateString())
            ->first();

        return response()->json([
            'status' => 'success',
            'data'   => [
                'id'            => $row->id,
                'jarak_cm'      => (float) $row->jarak,
                'flow_rate'     => (float) $row->flow,
                'status_kran'   => $row->status,
                'timestamp'     => $row->timestamp,
                'total_volume'  => $todaySummary->total_volume ?? 0,
                'session_volume'=> null,
            ]
        ]);
    }

    public function getStats()
    {
        $today = now('Asia/Jakarta')->toDateString();

        $todayCount = DB::table('sensor_data')
            ->whereDate('timestamp', $today)->count();

        $totalCount = DB::table('sensor_data')->count();

        $todayTotalVolume = (float) (DB::table('daily_water_summary')
            ->where('date', $today)->value('total_volume') ?? 0);

        return response()->json([
            'status' => 'success',
            'data'   => [
                'today_records'      => $todayCount,
                'total_records'      => $totalCount,
                'today_total_volume' => $todayTotalVolume,
            ]
        ]);
    }

    public function getLatest(Request $request)
    {
        $limit = (int) $request->get('limit', 10);
        $rows = DB::table('sensor_data')->orderByDesc('timestamp')->limit($limit)->get()
            ->map(fn($r)=>[
                'id'          => $r->id,
                'jarak_cm'    => (float)$r->jarak,
                'flow_rate'   => (float)$r->flow,
                'status_kran' => $r->status,
                'timestamp'   => $r->timestamp,
            ]);

        return response()->json(['status'=>'success','data'=>$rows]);
    }

    // === INTERNAL HELPERS ===
    private function updateDailySummaryInternal(float $flowRate, Carbon $timestamp)
    {
        $date = $timestamp->toDateString();

        $row = DB::table('daily_water_summary')->where('date', $date)->first();

        // volume increment = (flow L/min) * (interval detik / 60)
        // di API lama diasumsikan interval update 5 detik
        $intervalSeconds = 5;
        $volumeIncrement = $flowRate * ($intervalSeconds / 60);

        if ($row) {
            DB::table('daily_water_summary')->where('id', $row->id)->update([
                'total_volume'    => (float)$row->total_volume + $volumeIncrement,
                'peak_flow_rate'  => max((float)$row->peak_flow_rate, $flowRate),
                'total_usage_time'=> (int)$row->total_usage_time + $intervalSeconds,
                'updated_at'      => now(),
            ]);
        } else {
            DB::table('daily_water_summary')->insert([
                'date'             => $date,
                'total_volume'     => $volumeIncrement,
                'total_usage_time' => $intervalSeconds,
                'peak_flow_rate'   => $flowRate,
                'average_flow_rate'=> $flowRate,
                'usage_sessions'   => 1,
                'created_at'       => now(),
                'updated_at'       => now(),
            ]);
        }
    }

    private function updateWeeklyViewInternal(string $date)
    {
        $d = Carbon::parse($date);
        $weekStart = $d->copy()->startOfWeek(Carbon::MONDAY)->toDateString();
        $weekEnd   = $d->copy()->endOfWeek(Carbon::SUNDAY)->toDateString();

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

        $totalVol      = (float)($totals->total_volume ?? 0);
        $dailyAverage  = $totalVol / 7;

        DB::table('weekly_water_view')->updateOrInsert(
            ['week_start' => $weekStart],
            [
                'week_end'       => $weekEnd,
                'total_volume'   => $totalVol,
                'daily_average'  => $dailyAverage,
                'subuh_total'    => (float)($totals->subuh_total ?? 0),
                'dzuhur_total'   => (float)($totals->dzuhur_total ?? 0),
                'ashar_total'    => (float)($totals->ashar_total ?? 0),
                'maghrib_total'  => (float)($totals->maghrib_total ?? 0),
                'isya_total'     => (float)($totals->isya_total ?? 0),
                'updated_at'     => now(),
                'created_at'     => now(),
            ]
        );
    }

    private function calculateTrends(array $weekly)
    {
        if (count($weekly) < 2) {
            return ["message" => "Insufficient data for trend analysis"];
        }
        $latest   = (array)$weekly[0];
        $previous = (array)$weekly[1];

        $delta = (float)$latest['total_volume'] - (float)$previous['total_volume'];
        $pct   = ((float)$previous['total_volume'] > 0)
            ? ($delta / (float)$previous['total_volume']) * 100 : 0;

        $avg = 0;
        if (count($weekly) > 0) {
            $sum = array_sum(array_map(fn($x)=>(float)$x['total_volume'], $weekly));
            $avg = $sum / count($weekly);
        }

        return [
            "volume_change"        => round($delta, 3),
            "volume_change_percent"=> round($pct, 2),
            "trend_direction"      => $delta > 0 ? "increasing" : ($delta < 0 ? "decreasing" : "stable"),
            "average_weekly_volume"=> round($avg, 3)
        ];
    }
}
