<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('sensor_data', function (Blueprint $table) {
            $table->id();
            $table->decimal('jarak', 5, 2);
            $table->decimal('flow', 6, 3);
            $table->string('status', 20);
            $table->string('active_prayer', 50)->nullable();
            $table->timestamp('timestamp')->useCurrent();
            $table->index('timestamp');
            $table->index('status');
        });

        Schema::create('daily_water_summary', function (Blueprint $table) {
            $table->id();
            $table->date('date')->unique();
            $table->decimal('total_volume', 10, 3)->default(0);
            $table->integer('total_usage_time')->default(0);
            $table->decimal('peak_flow_rate', 6, 3)->default(0);
            $table->decimal('average_flow_rate', 6, 3)->default(0);
            $table->integer('usage_sessions')->default(0);
            $table->timestamps();
        });

        Schema::create('shalat_water_reports', function (Blueprint $table) {
            $table->id();
            $table->string('prayer_name', 20);
            $table->decimal('total_volume', 8, 3);
            $table->date('date');
            $table->time('start_time');
            $table->time('end_time');
            $table->timestamp('timestamp')->useCurrent();
            $table->index(['date', 'prayer_name']);
        });

        Schema::create('weekly_water_view', function (Blueprint $table) {
            $table->id();
            $table->date('week_start')->unique();
            $table->date('week_end');
            $table->decimal('total_volume', 12, 3)->default(0);
            $table->decimal('daily_average', 8, 3)->default(0);
            $table->decimal('subuh_total', 8, 3)->default(0);
            $table->decimal('dzuhur_total', 8, 3)->default(0);
            $table->decimal('ashar_total', 8, 3)->default(0);
            $table->decimal('maghrib_total', 8, 3)->default(0);
            $table->decimal('isya_total', 8, 3)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('weekly_water_view');
        Schema::dropIfExists('shalat_water_reports');
        Schema::dropIfExists('daily_water_summary');
        Schema::dropIfExists('sensor_data');
    }
};
