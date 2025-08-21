<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class SensorDataFactory extends Factory
{
    public function definition(): array
    {
        $jarak = $this->faker->randomFloat(2, 5, 50);     // 5–50 cm
        $flow  = $this->faker->randomFloat(3, 0.000, 3.000); // 0–3 L/min
        $status = $flow > 0.05 ? 'ON' : 'OFF';

        return [
            'jarak'         => $jarak,
            'flow'          => $flow,
            'status'        => $status,
            'active_prayer' => null,
            'timestamp'     => now()->subMinutes(rand(0, 24*60)), // acak 24 jam terakhir
        ];
    }
}
