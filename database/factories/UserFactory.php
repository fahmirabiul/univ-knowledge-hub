<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\AcademicStatus;
use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<User>
     */
    protected $model = User::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'sso_id' => (string) Str::uuid(),
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'role' => UserRole::DOSEN,
            'academic_status' => AcademicStatus::AKTIF,
            'faculty' => fake()->randomElement([
                'Fakultas Ilmu Komputer',
                'Fakultas Teknik',
                'Fakultas Kedokteran',
                'Fakultas Ekonomi dan Bisnis',
                'Fakultas MIPA',
            ]),
            'study_program' => fake()->randomElement([
                'Teknik Informatika',
                'Sistem Informasi',
                'Teknik Elektro',
                'Ilmu Kedokteran',
                'Manajemen',
            ]),
            'avatar_url' => 'https://ui-avatars.com/api/?name='.urlencode(fake()->name()).'&background=7367F0&color=fff',
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Dosen state.
     */
    public function dosen(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => UserRole::DOSEN,
        ]);
    }

    /**
     * Admin LPPM state.
     */
    public function adminLppm(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => UserRole::ADMIN_LPPM,
            'faculty' => 'Lembaga Penelitian dan Pengabdian kepada Masyarakat (LPPM)',
            'study_program' => 'Pusat Manajemen Riset & Inovasi',
        ]);
    }

    /**
     * Mahasiswa state.
     */
    public function mahasiswa(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => UserRole::MAHASISWA,
        ]);
    }

    /**
     * Inactive / Cuti state.
     */
    public function cuti(): static
    {
        return $this->state(fn (array $attributes) => [
            'academic_status' => AcademicStatus::CUTI,
        ]);
    }
}
