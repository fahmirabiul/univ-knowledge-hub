<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\Sync\UserProfileSyncService;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;

class ListenUserProfileUpdated extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'redis:listen-users {--channel=university.user.profile_updated : Nama channel Redis Pub/Sub}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mendengarkan event perubahan profil pengguna dari univ-iam via Redis Pub/Sub dan menyinkronkan ke database lokal';

    /**
     * Execute the console command.
     */
    public function handle(UserProfileSyncService $syncService): int
    {
        $channel = (string) $this->option('channel');

        $this->info("Menghubungkan ke Redis Pub/Sub pada channel: [{$channel}]...");
        $this->comment('Daemon aktif. Tekan CTRL+C untuk menghentikan.');

        try {
            Redis::subscribe([$channel], function (string $message) use ($syncService): void {
                $this->line('['.now()->format('Y-m-d H:i:s')."] Pesan diterima: {$message}");

                try {
                    $user = $syncService->handle($message);

                    if ($user) {
                        $this->info(" -> Berhasil disinkronkan: [{$user->name}] ({$user->email}) - Status: {$user->academic_status->value}");
                    } else {
                        $this->warn(' -> Dilewati: Format data payload Redis tidak valid.');
                    }
                } catch (Exception $e) {
                    $this->error(" -> Kesalahan saat memproses payload: {$e->getMessage()}");
                }
            });
        } catch (Exception $e) {
            $this->error("Koneksi Redis subscriber gagal: {$e->getMessage()}");

            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
