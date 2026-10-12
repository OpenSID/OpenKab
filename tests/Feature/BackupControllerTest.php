<?php

namespace Tests\Feature;

use App\Http\Controllers\BackupController;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\BaseTestCase;

class BackupControllerTest extends BaseTestCase
{    

    private string $diskName = 'backups';

    public function setUp(): void
    {
        parent::setUp();

        Storage::fake($this->diskName); 
    }

    public function test_can_view_backup_index_page(): void
    {
        $response = $this->get(route('backup.index'));

        $response->assertStatus(Response::HTTP_OK);
        $response->assertViewIs('backup.index');
        $response->assertSee('Backup Database');
    }

    public function test_can_run_backup_successfully(): void
    {
        Artisan::shouldReceive('call')
            ->once()
            ->with('backup:run', ['--disable-notifications' => true])
            ->andReturn(0);

        $response = $this->postJson(route('backup.store'));

        $response->assertStatus(Response::HTTP_OK);
        $response->assertJson([
            'success' => true,
            'message' => 'Backup berhasil dibuat.',
        ]);
    }

    public function test_run_backup_returns_error_on_failure(): void
    {
        Artisan::shouldReceive('call')
            ->once()
            ->with('backup:run', ['--disable-notifications' => true])
            ->andReturn(1);

        Artisan::shouldReceive('output')
            ->once()
            ->andReturn('Database dump failed');

        $response = $this->postJson(route('backup.store'));

        $response->assertStatus(Response::HTTP_INTERNAL_SERVER_ERROR);
        $response->assertJson([
            'success' => false,
        ]);
    }

    public function test_can_download_backup_file(): void
    {
        $filename = 'test-backup-2026-07-03-12-00-00.zip';

        Storage::disk($this->diskName)->put($filename, 'fake-backup-content');

        $response = $this->get(route('backup.show', $filename));

        $response->assertStatus(Response::HTTP_OK);
    }

    public function test_download_nonexistent_backup_returns_404(): void
    {
        $response = $this->get(route('backup.show', 'nonexistent.zip'));

        $response->assertStatus(Response::HTTP_NOT_FOUND);
    }

    public function test_download_with_invalid_filename_returns_400(): void
    {
        $response = $this->get(route('backup.show', 'test..backup.zip'));

        $response->assertStatus(Response::HTTP_BAD_REQUEST);
    }

    public function test_can_delete_backup_file(): void
    {
        $filename = 'test-backup-2026-07-03-12-00-00.zip';

        Storage::disk($this->diskName)->put($filename, 'fake-backup-content');

        $response = $this->deleteJson(route('backup.destroy', $filename));

        $response->assertStatus(Response::HTTP_OK);
        $response->assertJson([
            'success' => true,
            'message' => 'Backup berhasil dihapus.',
        ]);

        Storage::disk($this->diskName)->assertMissing($filename);
    }

    public function test_delete_nonexistent_backup_returns_404(): void
    {
        $response = $this->deleteJson(route('backup.destroy', 'nonexistent.zip'));

        $response->assertStatus(Response::HTTP_NOT_FOUND);
    }

    public function test_delete_with_invalid_filename_returns_400(): void
    {
        $response = $this->deleteJson(route('backup.destroy', 'test..backup.zip'));

        $response->assertStatus(Response::HTTP_BAD_REQUEST);
    }

    public function test_index_page_shows_backup_files(): void
    {
        Storage::disk($this->diskName)->put('backup-1.zip', 'content-1');
        Storage::disk($this->diskName)->put('backup-2.zip', 'content-2');

        $response = $this->get(route('backup.index'));

        $response->assertStatus(Response::HTTP_OK);
        $response->assertViewIs('backup.index');
        $response->assertSee('backup-1.zip');
        $response->assertSee('backup-2.zip');
    }

    public function test_index_page_ignores_non_zip_files(): void
    {
        Storage::disk($this->diskName)->put('backup-1.zip', 'content-1');
        Storage::disk($this->diskName)->put('notes.txt', 'not-a-backup');

        $response = $this->get(route('backup.index'));

        $response->assertStatus(Response::HTTP_OK);
        $response->assertSee('backup-1.zip');
        $response->assertDontSee('notes.txt');
    }

    public function test_backup_files_stored_on_correct_disk(): void
    {
        $filename = 'backup-2026-07-04-12-00-00.zip';

        Storage::disk($this->diskName)->put($filename, 'fake-backup-content');

        Storage::disk($this->diskName)->assertExists($filename);

        $backups = collect(Storage::disk($this->diskName)->files())
            ->filter(fn (string $file): bool => str_ends_with($file, '.zip'))
            ->values();

        $this->assertCount(1, $backups);
        $this->assertStringEndsWith('.zip', $backups[0]);
    }

    public function test_backup_index_reads_from_backups_disk(): void
    {
        Storage::disk($this->diskName)->put('disk-backup.zip', 'content');
        Storage::disk('local')->put('local-backup.zip', 'content');

        $response = $this->get(route('backup.index'));

        $response->assertSee('disk-backup.zip');
        $response->assertDontSee('local-backup.zip');
    }

    public function test_backup_index_returns_correct_file_metadata(): void
    {
        Storage::disk($this->diskName)->put('backup-test.zip', 'content');

        $response = $this->get(route('backup.index'));

        $response->assertStatus(Response::HTTP_OK);
        $response->assertViewHas('backups', function ($backups) {
            $this->assertCount(1, $backups);
            $this->assertEquals('backup-test.zip', $backups[0]['filename']);
            $this->assertArrayHasKey('size', $backups[0]);
            $this->assertArrayHasKey('last_modified', $backups[0]);

            return true;
        });
    }

    public function test_find_sql_file_with_flat_db_dumps_structure(): void
    {
        $tempPath = storage_path('app/backups/test_find_sql_' . uniqid());
        mkdir($tempPath . '/db-dumps', 0755, true);
        touch($tempPath . '/db-dumps/mysql-database.sql');

        $controller = App::make(BackupController::class);
        $reflection = new \ReflectionMethod($controller, 'findSqlFile');
        $result = $reflection->invoke($controller, $tempPath);

        $this->assertNotNull($result);
        $this->assertStringEndsWith('mysql-database.sql', $result);

        $this->cleanDir($tempPath);
    }

    public function test_find_sql_file_with_nested_db_dumps_structure(): void
    {
        $tempPath = storage_path('app/backups/test_find_sql_' . uniqid());
        mkdir($tempPath . '/backup-dir/db-dumps', 0755, true);
        touch($tempPath . '/backup-dir/db-dumps/mysql-database.sql');

        $controller = App::make(BackupController::class);
        $reflection = new \ReflectionMethod($controller, 'findSqlFile');
        $result = $reflection->invoke($controller, $tempPath);

        $this->assertNotNull($result);
        $this->assertStringEndsWith('mysql-database.sql', $result);

        $this->cleanDir($tempPath);
    }

    public function test_find_sql_file_returns_null_when_sql_missing(): void
    {
        $tempPath = storage_path('app/backups/test_find_sql_' . uniqid());
        mkdir($tempPath, 0755, true);

        $controller = App::make(BackupController::class);
        $reflection = new \ReflectionMethod($controller, 'findSqlFile');
        $result = $reflection->invoke($controller, $tempPath);

        $this->assertNull($result);

        rmdir($tempPath);
    }

    private function cleanDir(string $path): void
    {
        if (! is_dir($path)) {
            return;
        }

        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($path, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($files as $file) {
            $file->isDir() ? @rmdir($file->getRealPath()) : @unlink($file->getRealPath());
        }

        @rmdir($path);
    }
}
