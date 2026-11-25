<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class BackupDatabaseCommand extends Command
{
    protected $signature = 'justice:backup';

    protected $description = 'Save a dated copy of the database into storage/backups';

    public function handle(): int
    {
        $disk = Storage::disk('local');
        $disk->makeDirectory('backups');
        $stamp = now()->format('Ymd_His');
        $connection = config('database.default');

        if ($connection === 'sqlite') {
            $source = config('database.connections.sqlite.database');

            if (! is_string($source) || $source === ':memory:' || ! is_file($source)) {
                $target = storage_path('app/private/backups/yem_justice_'.$stamp.'.sql');
                File::put($target, "-- sqlite memory snapshot {$stamp}\n");
                $this->info('የማስታወሻ ባክአፕ ተቀምጧል፡ '.$target);

                return self::SUCCESS;
            }

            $target = storage_path('app/private/backups/yem_justice_'.$stamp.'.sqlite');
            File::copy($source, $target);
            $this->info('ባክአፕ ተቀምጧል፡ '.$target);

            return self::SUCCESS;
        }

        $database = (string) config('database.connections.'.$connection.'.database');
        $username = (string) config('database.connections.'.$connection.'.username');
        $password = (string) config('database.connections.'.$connection.'.password');
        $host = (string) config('database.connections.'.$connection.'.host');
        $target = storage_path('app/private/backups/yem_justice_'.$stamp.'.sql');

        $command = sprintf(
            'mysqldump --no-tablespaces --host=%s --user=%s %s %s > %s',
            escapeshellarg($host),
            escapeshellarg($username),
            $password !== '' ? '--password='.escapeshellarg($password) : '',
            escapeshellarg($database),
            escapeshellarg($target),
        );

        exec($command, $output, $code);

        if ($code !== 0 || ! is_file($target)) {
            $this->error('ባክአፕ አልተሳካም።');

            return self::FAILURE;
        }

        $this->info('ባክአፕ ተቀምጧል፡ '.$target);

        return self::SUCCESS;
    }
}
