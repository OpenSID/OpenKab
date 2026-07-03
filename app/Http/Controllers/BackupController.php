<?php

namespace App\Http\Controllers;

use App\Http\Requests\RestoreBackupRequest;
use App\Traits\UploadedFile;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use ZipArchive;

class BackupController extends AppBaseController
{
    use UploadedFile;

    protected $permission = 'pengaturan-backup';

    protected string $diskName = 'backups';

    public function index(): \Illuminate\View\View
    {
        $listPermission = $this->generateListPermission();

        $backups = collect(Storage::disk($this->diskName)->files())
            ->filter(fn (string $file): bool => str_ends_with($file, '.zip'))
            ->map(fn (string $file): array => [
                'filename' => basename($file),
                'size' => Storage::disk($this->diskName)->size($file),
                'last_modified' => Storage::disk($this->diskName)->lastModified($file),
            ])
            ->sortByDesc('last_modified')
            ->values();

        return view('backup.index', compact('backups'))->with($listPermission);
    }

    public function run(): JsonResponse
    {
        try {
            $exitCode = Artisan::call('backup:run', [
                '--disable-notifications' => true,
            ]);

            if ($exitCode === 0) {
                Log::info('Backup database dan storage berhasil dibuat oleh ' . (auth()->user()?->username ?? 'system'));

                return $this->sendSuccess('Backup berhasil dibuat.');
            }

            return $this->sendError('Backup gagal: ' . Artisan::output(), 500);
        } catch (Exception $e) {
            Log::error('Backup gagal: ' . $e->getMessage());

            return $this->sendError('Backup gagal: ' . $e->getMessage(), 500);
        }
    }

    public function download(string $filename): BinaryFileResponse|RedirectResponse|StreamedResponse
    {
        abort_if(! $this->isValidFilename($filename), 400, 'Nama file tidak valid.');

        $disk = Storage::disk($this->diskName);

        abort_if(! $disk->exists($filename), 404, 'File backup tidak ditemukan.');

        return $disk->download($filename);
    }

    public function destroy(string $filename): JsonResponse
    {
        abort_if(! $this->isValidFilename($filename), 400, 'Nama file tidak valid.');

        $disk = Storage::disk($this->diskName);

        if (! $disk->exists($filename)) {
            return $this->sendError('File backup tidak ditemukan.', 404);
        }

        $disk->delete($filename);

        Log::info('Backup ' . $filename . ' dihapus oleh ' . (auth()->user()?->username ?? 'system'));

        return $this->sendSuccess('Backup berhasil dihapus.');
    }

    public function restore(string $filename): JsonResponse
    {
        abort_if(! $this->isValidFilename($filename), 400, 'Nama file tidak valid.');

        $disk = Storage::disk($this->diskName);

        if (! $disk->exists($filename)) {
            return $this->sendError('File backup tidak ditemukan.', 404);
        }

        $tempPath = storage_path('app/backups/temp_restore_' . uniqid());
        $zipPath = $disk->path($filename);

        try {
            $zip = new ZipArchive;
            if ($zip->open($zipPath) !== true) {
                throw new Exception('Tidak dapat membuka file backup.');
            }

            for ($i = 0; $i < $zip->numFiles; $i++) {
                $entry = $zip->getNameIndex($i);
                if (str_contains($entry, '..') || str_starts_with($entry, '/')) {
                    $zip->close();
                    throw new Exception('File backup tidak valid.');
                }
            }

            $zip->extractTo($tempPath);
            $zip->close();

            $this->restoreDatabase($tempPath);
            $this->restoreStorageFiles($tempPath);

            $this->cleanTempDir($tempPath);

            Log::warning('Restore database dan storage dari ' . $filename . ' oleh ' . (auth()->user()?->username ?? 'system'));

            return $this->sendSuccess('Restore database dan storage berhasil.');
        } catch (Exception $e) {
            $this->cleanTempDir($tempPath);

            Log::error('Restore gagal dari ' . $filename . ': ' . $e->getMessage());

            return $this->sendError('Restore gagal: ' . $e->getMessage(), 500);
        }
    }

    public function uploadAndRestore(RestoreBackupRequest $request): JsonResponse
    {
        $this->pathFolder = 'backups_upload';
        $relativePath = $this->uploadFile($request, 'file');

        if ($relativePath === false) {
            return $this->sendError('Gagal mengupload file.', 500);
        }

        $publicPath = storage_path('app/public/' . $relativePath);
        $tempZipPath = storage_path('app/backups/temp_upload_' . uniqid() . '.zip');
        $tempPath = storage_path('app/backups/temp_restore_' . uniqid());

        rename($publicPath, $tempZipPath);

        try {
            $zip = new ZipArchive;
            if ($zip->open($tempZipPath) !== true) {
                throw new Exception('Tidak dapat membuka file backup.');
            }

            for ($i = 0; $i < $zip->numFiles; $i++) {
                $entry = $zip->getNameIndex($i);
                if (str_contains($entry, '..') || str_starts_with($entry, '/')) {
                    $zip->close();
                    throw new Exception('File backup tidak valid.');
                }
            }

            $zip->extractTo($tempPath);
            $zip->close();

            $this->restoreDatabase($tempPath);
            $this->restoreStorageFiles($tempPath);

            $this->cleanTempDir($tempPath);
            @unlink($tempZipPath);

            Log::warning('Restore database dan storage dari upload oleh ' . (auth()->user()?->username ?? 'system'));

            return $this->sendSuccess('Restore database dan storage berhasil.');
        } catch (Exception $e) {
            $this->cleanTempDir($tempPath);
            @unlink($tempZipPath);

            Log::error('Restore dari upload gagal: ' . $e->getMessage());

            return $this->sendError('Restore gagal: ' . $e->getMessage(), 500);
        }
    }

    private function restoreDatabase(string $tempPath): void
    {
        $sqlFile = $this->findSqlFile($tempPath);

        if ($sqlFile === null) {
            throw new Exception('File SQL backup tidak ditemukan dalam arsip.');
        }

        $sql = file_get_contents($sqlFile);

        if ($sql === false || trim($sql) === '') {
            throw new Exception('File SQL backup kosong atau tidak dapat dibaca.');
        }

        DB::unprepared($sql);
    }

    private function restoreStorageFiles(string $tempPath): void
    {
        $storageSource = $tempPath . '/storage/app';

        if (! is_dir($storageSource)) {
            Log::info('Direktori storage/app tidak ditemukan dalam backup, melewati.');

            return;
        }

        $target = storage_path('app');

        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($storageSource, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($files as $file) {
            $relativePath = substr($file->getRealPath(), strlen($storageSource));
            $destPath = $target . $relativePath;

            if ($file->isDir()) {
                if (! is_dir($destPath)) {
                    mkdir($destPath, 0755, true);
                }
            } else {
                copy($file->getRealPath(), $destPath);
            }
        }
    }

    private function findSqlFile(string $tempPath): ?string
    {
        $sqlFiles = glob($tempPath . '/*.sql');

        if (! empty($sqlFiles)) {
            return $sqlFiles[0];
        }

        $dumperFiles = glob($tempPath . '/*/db-dumps/*.sql');

        if (! empty($dumperFiles)) {
            return $dumperFiles[0];
        }

        return null;
    }

    private function cleanTempDir(string $tempPath): void
    {
        if (! is_dir($tempPath)) {
            return;
        }

        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($tempPath, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($files as $file) {
            $file->isDir() ? @rmdir($file->getRealPath()) : @unlink($file->getRealPath());
        }

        @rmdir($tempPath);
    }

    private function isValidFilename(string $filename): bool
    {
        return preg_match('/^[\w\.\-]+\.zip$/', $filename) === 1
            && ! str_contains($filename, '..')
            && ! str_contains($filename, '/');
    }
}
