<?php

namespace App\Console\Commands;

use App\Models\PasswordHistory;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AuditWeakPasswords extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'password:audit-weak {--force : Force flag without confirmation} {--dry-run : Show what would be done without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Audit existing users for weak passwords and force password reset';

    /**
     * Minimum password length for strong password.
     */
    protected int $minLength = 12;

    /**
     * Common passwords list.
     */
    protected array $commonPasswords = [
        'password',
        'password123',
        '123456',
        '123456789',
        'qwerty',
        'abc123',
        'monkey',
        'master',
        'dragon',
        'letmein',
        'login',
        'admin',
        'welcome',
        'admin123',
        'root',
        'toor',
        'pass',
        'test',
        'guest',
        'guest123',
    ];

    /**
     * Weak patterns to check.
     */
    protected array $weakPatterns = [
        '/^(.)\1{5,}$/', // Same character repeated 6+ times
        '/^(012|123|234|345|456|567|678|789|890)/', // Sequential numbers
        '/^(abc|bcd|cde|def|efg|fgh|ghi|hij|ijk|jkl|klm|lmn|mno|nop|opq|pqr|qrs|rst|stu|tuv|uvw|vwx|wxy|xyz)/i', // Sequential letters
    ];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('=== Audit Password Lemah ===');
        $this->newLine();

        $dryRun = $this->option('dry-run');
        $force = $this->option('force');

        if ($dryRun) {
            $this->warn('MODE DRY-RUN: Tidak ada perubahan yang akan dilakukan.');
            $this->newLine();
        }

        // Get all users
        $users = User::all();
        $totalUsers = $users->count();
        $weakPasswordCount = 0;
        $affectedUsers = [];

        $this->info("Memeriksa {$totalUsers} user...");
        $this->newLine();

        $bar = $this->output->createProgressBar($totalUsers);
        $bar->start();

        foreach ($users as $user) {
            $bar->advance();

            // Skip users without password (e.g., OAuth users)
            if (!$user->password) {
                continue;
            }

            if ($this->isPasswordWeak($user->password)) {
                $weakPasswordCount++;
                $affectedUsers[] = [
                    'id' => $user->id,
                    'email' => $user->email,
                    'name' => $user->name,
                    'reason' => $this->getWeakReason($user->password),
                ];

                if (!$dryRun) {
                    $this->flagUserForPasswordReset($user);
                }
            }
        }

        $bar->finish();
        $this->newLine(2);

        $this->table(
            ['ID', 'Email', 'Name', 'Alasan'],
            $affectedUsers
        );

        $this->newLine();
        $this->info("Ditemukan {$weakPasswordCount} user dengan password lemah dari {$totalUsers} total user.");

        if ($dryRun) {
            $this->warn("Dry-run selesai. Jalankan tanpa --dry-run untuk menerapkan perubahan.");
            return 0;
        }

        if ($weakPasswordCount > 0 && !$force) {
            if (!$this->confirm("Apakah Anda yakin ingin memaksa {$weakPasswordCount} user untuk reset password?")) {
                $this->info('Operasi dibatalkan.');
                return 1;
            }

            // Re-flag all users since confirmation was given
            foreach ($affectedUsers as $userData) {
                $user = User::find($userData['id']);
                $this->flagUserForPasswordReset($user);
            }
        }

        if ($weakPasswordCount > 0) {
            $this->info("Berhasil menandai {$weakPasswordCount} user untuk reset password wajib.");
            $this->info('User tersebut akan diminta reset password saat login berikutnya.');
        } else {
            $this->info('Tidak ada user dengan password lemah yang terdeteksi.');
        }

        return 0;
    }

    /**
     * Check if a password hash is weak.
     *
     * Note: Since we can't know the original password from the hash,
     * we check for common patterns by testing common passwords.
     */
    protected function isPasswordWeak(string $passwordHash): bool
    {
        // Check if password matches any common password
        foreach ($this->commonPasswords as $common) {
            if (Hash::check($common, $passwordHash)) {
                return true;
            }
        }

        // Check for short passwords (less than 8 characters)
        // We can't directly check length from hash, but we can check
        // if any short common password matches
        for ($i = 1; $i <= 7; $i++) {
            // Generate test patterns
            $patterns = [
                str_repeat('a', $i),
                str_repeat('1', $i),
                implode('', range('a', chr(ord('a') + $i - 1))),
                implode('', range('1', (string) $i)),
            ];

            foreach ($patterns as $pattern) {
                if (Hash::check($pattern, $passwordHash)) {
                    return true;
                }
            }
        }

        // Check if password doesn't meet complexity requirements
        // by testing common variations
        $weakVariations = [
            'Password1!',
            'Password123!',
            'Welcome1!',
            'Welcome123!',
            'Admin123!',
            'admin123!',
            'password123!',
            'Qwerty123!',
            'qwerty123!',
        ];

        foreach ($weakVariations as $variation) {
            if (Hash::check($variation, $passwordHash)) {
                return true;
            }
        }

        // Additional heuristic: check for users who never changed password
        // and have old-style hashes (not using bcrypt)
        // Old MD5/SHA1 hashes are 32/40 chars, bcrypt is 60 chars
        if (strlen($passwordHash) < 60) {
            return true;
        }

        return false;
    }

    /**
     * Get the reason why password is weak.
     */
    protected function getWeakReason(string $passwordHash): string
    {
        // Check common passwords
        foreach ($this->commonPasswords as $common) {
            if (Hash::check($common, $passwordHash)) {
                return 'Password umum/terkenal';
            }
        }

        // Check hash length (old hash algorithm)
        if (strlen($passwordHash) < 60) {
            return 'Menggunakan algoritma hash lama';
        }

        // Check weak variations
        $weakVariations = [
            'Password1!',
            'Password123!',
            'Welcome1!',
            'Welcome123!',
            'Admin123!',
            'admin123!',
            'password123!',
            'Qwerty123!',
            'qwerty123!',
        ];

        foreach ($weakVariations as $variation) {
            if (Hash::check($variation, $passwordHash)) {
                return 'Password mudah ditebak';
            }
        }

        return 'Tidak memenuhi standar password kuat';
    }

    /**
     * Flag a user for password reset.
     */
    protected function flagUserForPasswordReset(User $user): void
    {
        // Only flag if not already flagged
        if (!$user->force_password_reset) {
            $user->force_password_reset = true;
            $user->save();

            // Record in password history
            PasswordHistory::create([
                'user_id' => $user->id,
                'password' => $user->password,
                'reason' => 'security_audit',
            ]);

            $this->info("  → User {$user->email} ditandai untuk reset password");
        }
    }
}
