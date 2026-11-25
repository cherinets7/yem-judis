<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Throwable;

class OpsHealthCommand extends Command
{
    protected $signature = 'justice:ops-check {--mail : Send a test mail to MAIL_FROM_ADDRESS}';

    protected $description = 'Check queue, mail, documents disk, Scout, and backup readiness';

    public function handle(): int
    {
        $ok = true;

        $ok = $this->check('Queue connection', function (): string {
            $driver = (string) config('queue.default');
            if ($driver === 'database' && ! Schema::hasTable('jobs')) {
                throw new \RuntimeException('jobs table missing');
            }

            return $driver;
        }) && $ok;

        $ok = $this->check('Mailer', function (): string {
            return (string) config('mail.default').' from '.config('mail.from.address');
        }) && $ok;

        $ok = $this->check('Documents disk', function (): string {
            $name = (string) config('filesystems.documents');
            $disk = Storage::disk($name);
            $path = 'ops-check/'.now()->format('YmdHis').'.txt';
            $disk->put($path, 'ok');
            $exists = $disk->exists($path);
            $disk->delete($path);
            if (! $exists) {
                throw new \RuntimeException('write failed');
            }

            return $name;
        }) && $ok;

        $ok = $this->check('Scout driver', function (): string {
            $driver = (string) config('scout.driver');
            if ($driver === 'meilisearch') {
                $host = rtrim((string) config('scout.meilisearch.host'), '/');
                $response = @file_get_contents($host.'/health');
                if ($response === false || ! str_contains($response, 'available')) {
                    throw new \RuntimeException('Meilisearch not reachable at '.$host);
                }
            }

            return $driver;
        }) && $ok;

        $ok = $this->check('Backup directory', function (): string {
            $dir = storage_path('app/private/backups');
            if (! is_dir($dir) && ! mkdir($dir, 0755, true) && ! is_dir($dir)) {
                throw new \RuntimeException('cannot create '.$dir);
            }

            return $dir;
        }) && $ok;

        $ok = $this->check('Database', function (): string {
            DB::select('select 1');

            return (string) config('database.default');
        }) && $ok;

        if ($this->option('mail')) {
            $ok = $this->check('Test mail', function (): string {
                $to = (string) config('mail.from.address');
                Mail::raw('የየም ዞን ፍትህ — የኦፕሬሽን የሙከራ መልዕክት።', function ($message) use ($to): void {
                    $message->to($to)->subject('justice:ops-check');
                });

                return 'sent to '.$to;
            }) && $ok;
        }

        $this->newLine();
        $this->info($ok ? 'ሁሉም ኦፕሬሽን ፍተሻ አልፏል።' : 'አንዳንድ ፍተሻዎች አልተሳኩም።');

        return $ok ? self::SUCCESS : self::FAILURE;
    }

    private function check(string $label, callable $callback): bool
    {
        try {
            $detail = $callback();
            $this->line("<info>✓</info> {$label}: {$detail}");

            return true;
        } catch (Throwable $e) {
            $this->line("<error>✗</error> {$label}: {$e->getMessage()}");

            return false;
        }
    }
}
