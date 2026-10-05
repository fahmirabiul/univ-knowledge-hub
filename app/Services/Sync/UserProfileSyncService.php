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
        $email = $data['email'] ?? null;

        if (! $ssoId || ! $email) {
            Log::warning('Redis User Sync: Payload tidak memiliki sso_id atau email yang valid.', ['data' => $data]);

            return null;
        }

        return $data;
    }

    /**
     * Update or create the local user record based on sso_id.
     */
    protected function syncUser(array $data): User
    {
        $ssoId = (string) ($data['sso_id'] ?? $data['id']);
        $email = (string) $data['email'];
        $name = (string) ($data['name'] ?? $data['nama'] ?? 'Pengguna IAM');

        $roleValue = (string) ($data['role'] ?? $data['role_lokal'] ?? UserRole::MAHASISWA->value);
        $role = UserRole::tryFrom($roleValue) ?? UserRole::MAHASISWA;

        $statusValue = (string) ($data['academic_status'] ?? $data['status_akademik'] ?? AcademicStatus::AKTIF->value);
        $academicStatus = AcademicStatus::tryFrom($statusValue) ?? AcademicStatus::AKTIF;

        $faculty = $data['faculty'] ?? $data['fakultas'] ?? null;
        $studyProgram = $data['study_program'] ?? $data['program_studi'] ?? null;
        $avatarUrl = $data['avatar_url'] ?? null;

        $user = User::updateOrCreate(
            ['sso_id' => $ssoId],
            [
                'email' => $email,
                'name' => $name,
                'role' => $role,
                'academic_status' => $academicStatus,
                'faculty' => $faculty !== null ? (string) $faculty : null,
                'study_program' => $studyProgram !== null ? (string) $studyProgram : null,
                'avatar_url' => $avatarUrl !== null ? (string) $avatarUrl : null,
            ]
        );

        Log::info('Redis User Sync: Berhasil memperbarui data profil pengguna.', [
            'sso_id' => $user->sso_id,
            'email' => $user->email,
            'role' => $user->role->value,
            'academic_status' => $user->academic_status->value,
        ]);

        return $user;
    }
}
