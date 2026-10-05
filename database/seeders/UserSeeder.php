<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\AcademicStatus;
use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            // Admin LPPM
            [
                'sso_id' => 'a0000000-0000-0000-0000-000000000001',
                'name' => 'Dr. Ir. Hendra Gunawan, M.T.',
                'email' => 'admin.lppm@univ.ac.id',
                'role' => UserRole::ADMIN_LPPM,
                'academic_status' => AcademicStatus::AKTIF,
                'faculty' => 'Lembaga Penelitian dan Pengabdian kepada Masyarakat',
                'study_program' => 'Pusat Manajemen Riset & Inovasi',
                'avatar_url' => 'https://ui-avatars.com/api/?name=Hendra+Gunawan&background=7367F0&color=fff',
            ],
            // Dosen 1
            [
                'sso_id' => 'a0000000-0000-0000-0000-000000000002',
                'name' => 'Prof. Dr. Siti Rahmawati, S.Kom., M.Cs.',
                'email' => 'siti.rahmawati@univ.ac.id',
                'role' => UserRole::DOSEN,
                'academic_status' => AcademicStatus::AKTIF,
                'faculty' => 'Fakultas Ilmu Komputer',
                'study_program' => 'Teknik Informatika',
                'avatar_url' => 'https://ui-avatars.com/api/?name=Siti+Rahmawati&background=00CFE8&color=fff',
            ],
            // Dosen 2
            [
                'sso_id' => 'a0000000-0000-0000-0000-000000000003',
                'name' => 'Dr. Budi Santoso, S.T., M.Eng.',
                'email' => 'budi.santoso@univ.ac.id',
                'role' => UserRole::DOSEN,
                'academic_status' => AcademicStatus::AKTIF,
                'faculty' => 'Fakultas Teknik',
                'study_program' => 'Teknik Elektro',
                'avatar_url' => 'https://ui-avatars.com/api/?name=Budi+Santoso&background=28C76F&color=fff',
            ],
            // Dosen 3 (Cuti)
            [
                'sso_id' => 'a0000000-0000-0000-0000-000000000004',
                'name' => 'Dr. Dewi Lestari, S.Si., M.Biotech.',
                'email' => 'dewi.lestari@univ.ac.id',
                'role' => UserRole::DOSEN,
                'academic_status' => AcademicStatus::CUTI,
                'faculty' => 'Fakultas Kedokteran',
                'study_program' => 'Ilmu Biomedis',
                'avatar_url' => 'https://ui-avatars.com/api/?name=Dewi+Lestari&background=FF9F43&color=fff',
            ],
            // Mahasiswa 1
            [
                'sso_id' => 'a0000000-0000-0000-0000-000000000005',
                'name' => 'Ahmad Fauzi',
                'email' => 'ahmad.fauzi@student.univ.ac.id',
                'role' => UserRole::MAHASISWA,
                'academic_status' => AcademicStatus::AKTIF,
                'faculty' => 'Fakultas Ilmu Komputer',
                'study_program' => 'Teknik Informatika',
                'avatar_url' => 'https://ui-avatars.com/api/?name=Ahmad+Fauzi&background=4B465C&color=fff',
            ],
        ];

        foreach ($users as $userData) {
            User::updateOrCreate(
                ['sso_id' => $userData['sso_id']],
                $userData
            );
        }
    }
}
