<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\PublicationStatus;
use App\Models\AuditLog;
use App\Models\Publication;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AuditLog>
 */
class AuditLogFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<AuditLog>
     */
    protected $model = AuditLog::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'publication_id' => Publication::factory(),
            'actor_id' => User::factory()->adminLppm(),
            'status_before' => PublicationStatus::PENDING->value,
            'status_after' => PublicationStatus::APPROVED->value,
            'notes' => 'Dokumen publikasi dan kelayakan DOI telah diverifikasi oleh Admin LPPM.',
            'ip_address' => fake()->ipv4(),
            'user_agent' => fake()->userAgent(),
            'created_at' => now(),
        ];
    }

    /**
     * Approval log state.
     */
    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status_before' => PublicationStatus::PENDING->value,
            'status_after' => PublicationStatus::APPROVED->value,
            'notes' => 'Publikasi disetujui untuk dipublikasikan pada repositori kampus.',
        ]);
    }

    /**
     * Rejection log state.
     */
    public function rejected(string $notes = 'File PDF tidak terbaca atau rusak.'): static
    {
        return $this->state(fn (array $attributes) => [
            'status_before' => PublicationStatus::PENDING->value,
            'status_after' => PublicationStatus::REJECTED->value,
            'notes' => $notes,
        ]);
    }
}
