<?php

declare(strict_types=1);

namespace App\Services\Sync;

use App\Enums\AcademicStatus;
use App\Enums\UserRole;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Log;

class UserProfileSyncService
{
    /**
     * Process an incoming Redis payload and synchronize the local user mirror.
     */
    public function handle(string|array $payload): ?User
    {
        $data = $this->parsePayload($payload);

        if ($data === null) {
            return null;
        }

        return $this->syncUser($data);
    }

    /**
     * Parse and validate payload from Redis.
     */
    protected function parsePayload(string|array $payload): ?array
    {
        if (is_string($payload)) {
            try {
                $decoded = json_decode($payload, true, 512, JSON_THROW_ON_ERROR);
            } catch (Exception $e) {
                Log::warning('Redis User Sync: Gagal mem-parsing payload JSON.', [
                    'payload' => $payload,
                    'error' => $e->getMessage(),
                ]);

                return null;
            }
        } else {
            $decoded = $payload;
        }

        if (! is_array($decoded)) {
            Log::warning('Redis User Sync: Format payload bukan array.', ['payload' => $payload]);

            return null;
        }

        // Support event wrapper payload e.g. {"event": "...", "data": {...}} or direct object
        $data = isset($decoded['data']) && is_array($decoded['data']) ? $decoded['data'] : $decoded;

        $ssoId = $data['sso_id'] ?? $data['id'] ?? null;

        if (! $ssoId) {
            Log::warning('Redis User Sync: Payload tidak memiliki sso_id yang valid.', ['data' => $data]);

            return null;
        }

        return $data;
    }

    /**
     * Update or create the local user record based on sso_id.
     */
    protected function syncUser(array $data): ?User
    {
        $ssoId = (string) ($data['sso_id'] ?? $data['id']);

        // Support reconciling pre-seeded local users matching on sso_id OR email
        $user = User::where('sso_id', $ssoId)
            ->when(! empty($data['email']), fn ($q) => $q->orWhere('email', (string) $data['email']))
            ->first();

        $email = (string) ($data['email'] ?? $user?->email ?? '');

        if (empty($email)) {
            Log::warning('Redis User Sync: User baru tidak dapat dibuat tanpa email.', ['data' => $data]);

            return null;
        }

        $profilData = isset($data['profil']) && is_array($data['profil'])
            ? $data['profil']
            : [];

        $assignedRoles = array_map('strval', (array) ($data['roles'] ?? []));
        $globalRole = (string) ($data['role_global'] ?? $data['role'] ?? $data['role_lokal'] ?? '');
        $isAdmin = (bool) ($data['is_admin'] ?? false);
        $isSuperAdmin = (bool) ($data['is_superadmin'] ?? false);

        $unitName = '';
        if (isset($data['unit']) && is_array($data['unit'])) {
            $unitName = (string) ($data['unit']['nama'] ?? $data['unit']['name'] ?? '');
        } elseif (isset($profilData['unit_kerja'])) {
            $unitName = (string) $profilData['unit_kerja'];
        }

        $isLppmAdmin = ($isAdmin || $isSuperAdmin) && str_contains(strtoupper($unitName), 'LPPM');

        $role = match (true) {
            $isLppmAdmin || in_array('admin_lppm', $assignedRoles, true) || $globalRole === 'admin_lppm' => UserRole::ADMIN_LPPM,
            in_array('dosen', $assignedRoles, true) || $globalRole === 'dosen' => UserRole::DOSEN,
            in_array('mahasiswa', $assignedRoles, true) || $globalRole === 'mahasiswa' => UserRole::MAHASISWA,
            default => UserRole::tryFrom($globalRole) ?? ($user?->role ?? UserRole::MAHASISWA),
        };

        $rawStatus = (string) ($profilData['status_akademik'] ?? $data['academic_status'] ?? $data['status_akademik'] ?? ($user?->academic_status?->value ?? 'aktif'));
        $academicStatus = match ($rawStatus) {
            'nonaktif', 'non_aktif', 'inactive' => AcademicStatus::NONAKTIF,
            'cuti' => AcademicStatus::CUTI,
            default => AcademicStatus::tryFrom($rawStatus) ?? AcademicStatus::AKTIF,
        };

        $name = (string) ($profilData['nama_lengkap'] ?? $data['nama_lengkap'] ?? $data['name'] ?? $data['nama'] ?? ($user?->name ?? 'Pengguna IAM'));
        $faculty = $data['fakultas'] ?? $profilData['fakultas'] ?? $data['faculty'] ?? ($user?->faculty ?? null);
        $studyProgram = $data['program_studi'] ?? $profilData['program_studi'] ?? $data['study_program'] ?? ($user?->study_program ?? null);
        $avatarUrl = $data['avatar_url'] ?? $profilData['avatar_url'] ?? ($user?->avatar_url ?? null);

        $attributes = [
            'sso_id' => $ssoId,
            'email' => $email,
            'name' => $name,
            'role' => $role,
            'academic_status' => $academicStatus,
            'faculty' => $faculty !== null ? (string) $faculty : null,
            'study_program' => $studyProgram !== null ? (string) $studyProgram : null,
            'avatar_url' => $avatarUrl !== null ? (string) $avatarUrl : null,
        ];

        if ($user) {
            $user->update($attributes);
        } else {
            $user = User::create($attributes);
        }

        Log::info('Redis User Sync: Berhasil memperbarui data profil pengguna.', [
            'sso_id' => $user->sso_id,
            'email' => $user->email,
            'role' => $user->role->value,
            'academic_status' => $user->academic_status->value,
        ]);

        return $user;
    }
}
