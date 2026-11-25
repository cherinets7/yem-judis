<?php

namespace App\Support;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class DocumentDisk
{
    public static function name(): string
    {
        $disk = (string) config('filesystems.documents', config('filesystems.default', 'local'));

        return $disk !== '' ? $disk : 'local';
    }

    public static function disk(?string $name = null): Filesystem
    {
        return Storage::disk($name ?: self::name());
    }

    public static function store(UploadedFile $file, string $directory = 'register-documents'): array
    {
        $disk = self::name();
        $path = $file->store($directory, $disk);

        return [
            'disk' => $disk,
            'file_path' => $path,
        ];
    }
}
