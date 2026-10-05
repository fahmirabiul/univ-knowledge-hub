<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\DownloadLog;
use App\Models\Publication;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DownloadLog>
 */
class DownloadLogFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<DownloadLog>
     */
    protected $model = DownloadLog::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $hasUser = fake()->boolean(60);

        return [
            'publication_id' => Publication::factory(),
            'user_id' => $hasUser ? User::factory()->mahasiswa() : null,
            'ip_address' => fake()->ipv4(),
            'user_agent' => fake()->userAgent(),
            'downloaded_at' => now()->subDays(fake()->numberBetween(0, 30)),
        ];
    }

    /**
     * Guest download state.
     */
    public function guest(): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => null,
        ]);
    }
}
