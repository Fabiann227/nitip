<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileStorageService
{
    public function privateDisk(): string
    {
        return config('nitip.uploads.private_disk', 'local');
    }

    public function publicDisk(): string
    {
        return config('nitip.uploads.public_disk', 'public');
    }

    /**
     * Store a file on the private disk with a random, safe file name.
     */
    public function storePrivate(UploadedFile $file, string $directory): string
    {
        return $file->storeAs($directory, $this->safeName($file), ['disk' => $this->privateDisk()]);
    }

    /**
     * Store a new private file and remove the previous one (if any).
     */
    public function replacePrivate(?string $oldPath, UploadedFile $file, string $directory): string
    {
        $path = $this->storePrivate($file, $directory);

        if ($oldPath && $oldPath !== $path) {
            $this->deletePrivate($oldPath);
        }

        return $path;
    }

    public function storePublic(UploadedFile $file, string $directory): string
    {
        return $file->storeAs($directory, $this->safeName($file), ['disk' => $this->publicDisk()]);
    }

    public function deletePrivate(?string $path): void
    {
        if ($path && Storage::disk($this->privateDisk())->exists($path)) {
            Storage::disk($this->privateDisk())->delete($path);
        }
    }

    public function deletePublic(?string $path): void
    {
        if ($path && Storage::disk($this->publicDisk())->exists($path)) {
            Storage::disk($this->publicDisk())->delete($path);
        }
    }

    private function safeName(UploadedFile $file): string
    {
        $extension = Str::lower($file->getClientOriginalExtension() ?: $file->extension() ?: 'bin');
        $extension = preg_replace('/[^a-z0-9]/', '', $extension) ?: 'bin';

        return Str::uuid()->toString().'.'.$extension;
    }
}
