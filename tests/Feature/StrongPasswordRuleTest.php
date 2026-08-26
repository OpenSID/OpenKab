<?php

namespace Tests\Feature;

use App\Models\User;
use App\Rules\StrongPassword;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class StrongPasswordRuleTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * Test password that meets all requirements.
     */
    public function test_strong_password_passes(): void
    {
        // Use unique passwords that won't be in HIBP database
        $rule = new StrongPassword(checkHibp: false);

        // Strong password: 12+ chars, upper, lower, number, special char
        $this->assertTrue($rule->passes('password', 'SecurePass123!'));
        $this->assertTrue($rule->passes('password', 'MyP@ssw0rd2024'));
        $this->assertTrue($rule->passes('password', 'Str0ng!Passw0rd'));
    }

    /**
     * Test password that is too short.
     */
    public function test_password_too_short_fails(): void
    {
        $rule = new StrongPassword();

        $this->assertFalse($rule->passes('password', 'Short1!'));
        $this->assertFalse($rule->passes('password', 'Test123!'));
        $this->assertFalse($rule->passes('password', 'Abc123!@#'));
    }

    /**
     * Test password without uppercase letter.
     */
    public function test_password_without_uppercase_fails(): void
    {
        $rule = new StrongPassword();

        $this->assertFalse($rule->passes('password', 'securepass123!'));
        $this->assertFalse($rule->passes('password', 'mysecurepassword1!'));
    }

    /**
     * Test password without lowercase letter.
     */
    public function test_password_without_lowercase_fails(): void
    {
        $rule = new StrongPassword();

        $this->assertFalse($rule->passes('password', 'SECUREPASS123!'));
        $this->assertFalse($rule->passes('password', 'MYSECUREPASSWORD1!'));
    }

    /**
     * Test password without number.
     */
    public function test_password_without_number_fails(): void
    {
        $rule = new StrongPassword();

        $this->assertFalse($rule->passes('password', 'SecurePassword!'));
        $this->assertFalse($rule->passes('password', 'MySecurePassword@'));
    }

    /**
     * Test password without special character.
     */
    public function test_password_without_special_char_fails(): void
    {
        $rule = new StrongPassword();

        $this->assertFalse($rule->passes('password', 'SecurePass123'));
        $this->assertFalse($rule->passes('password', 'MySecurePass456'));
    }

    /**
     * Test common passwords are rejected.
     */
    public function test_common_passwords_fail(): void
    {
        $rule = new StrongPassword();

        $commonPasswords = [
            'password',
            'password123',
            '123456',
            '123456789',
            'qwerty',
            'abc123',
            'admin',
            'admin123',
            'welcome',
            'letmein',
        ];

        foreach ($commonPasswords as $password) {
            $this->assertFalse(
                $rule->passes('password', $password),
                "Common password '{$password}' should fail"
            );
        }
    }

    /**
     * Test weak patterns are rejected.
     */
    public function test_weak_patterns_fail(): void
    {
        $rule = new StrongPassword(checkHibp: false);

        // Sequential numbers (pattern checks for 123, 234, etc at start)
        $this->assertFalse($rule->passes('password', '123abc!ABCdef'));
        $this->assertFalse($rule->passes('password', '234abc!ABCdef'));

        // Sequential letters (pattern checks for abc, bcd, etc at start)
        $this->assertFalse($rule->passes('password', 'abcdef1!ABCdef'));
        $this->assertFalse($rule->passes('password', 'xyzabc1!ABCdef'));
    }

    /**
     * Test validation with custom minimum length.
     */
    public function test_custom_min_length(): void
    {
        $rule = new StrongPassword(minLength: 16, checkHibp: false);

        // 12 chars - should fail with 16 min
        $this->assertFalse($rule->passes('password', 'SecurePass123!'));

        // 16+ chars - should pass (avoid weak patterns)
        $this->assertTrue($rule->passes('password', 'MyStr0ng!P@ssw0rd'));
    }

    /**
     * Test validation error messages.
     */
    public function test_error_messages(): void
    {
        $rule = new StrongPassword();

        $this->assertFalse($rule->passes('password', 'short1A@'));
        $this->assertStringContainsString('minimal 12', $rule->message());

        $this->assertFalse($rule->passes('password', 'nouppercase1!@#'));
        $this->assertStringContainsString('huruf kapital', $rule->message());

        $this->assertFalse($rule->passes('password', 'NOLOWERCASE1!@#'));
        $this->assertStringContainsString('huruf kecil', $rule->message());

        $this->assertFalse($rule->passes('password', 'NoNumber!@#abc'));
        $this->assertStringContainsString('angka', $rule->message());

        $this->assertFalse($rule->passes('password', 'NoSpecialChar1abc'));
        $this->assertStringContainsString('karakter spesial', $rule->message());

        $generic = (new StrongPassword())->message();
        $this->assertIsString($generic);
    }

    /**
     * Test validator integration.
     */
    public function test_validator_integration(): void
    {
        $validator = Validator::make(
            ['password' => 'SecurePass123!'],
            ['password' => ['required', new StrongPassword(checkHibp: false)]]
        );

        $this->assertFalse($validator->fails());

        $validator = Validator::make(
            ['password' => 'weak'],
            ['password' => ['required', new StrongPassword(checkHibp: false)]]
        );

        $this->assertTrue($validator->fails());
    }

    /**
     * Test password with all special characters.
     */
    public function test_various_special_characters(): void
    {
        $rule = new StrongPassword(checkHibp: false);

        $specialChars = ['!', '@', '#', '$', '%', '^', '&', '*', '(', ')', '-', '_', '=', '+'];

        foreach ($specialChars as $char) {
            // Use unique password base to avoid HIBP/common password issues
            $password = "Test{$char}Pass1234";
            $this->assertTrue(
                $rule->passes('password', $password),
                "Password with special char '{$char}' should pass"
            );
        }
    }

    /**
     * Test password history check (when logged in).
     */
    public function test_password_history(): void
    {
        // Create a test user
        $user = User::factory()->create([
            'password' => \Illuminate\Support\Facades\Hash::make('OldSecurePass123!'),
        ]);

        // Create password history
        $user->passwordHistory()->create([
            'password' => \Illuminate\Support\Facades\Hash::make('OldSecurePass123!'),
            'reason' => 'password_change',
        ]);

        // Login as this user
        $this->actingAs($user);

        $rule = new StrongPassword(checkHibp: false);

        // Old password should fail (in history)
        $this->assertFalse($rule->passes('password', 'OldSecurePass123!'));

        // New password should pass
        $this->assertTrue($rule->passes('password', 'NewSecurePass456!'));
    }

    /**
     * Test password history check with $user parameter (unauthenticated).
     */
    public function test_password_history_with_user_parameter(): void
    {
        $user = User::factory()->create();
        $user->passwordHistory()->create([
            'password' => \Illuminate\Support\Facades\Hash::make('OldSecurePass123!'),
            'reason' => 'password_change',
        ]);

        // Not logged in — history check should use the $user parameter
        $rule = new StrongPassword(checkHibp: false, user: $user);

        $this->assertFalse($rule->passes('password', 'OldSecurePass123!'));
        $this->assertTrue($rule->passes('password', 'NewSecurePass456!'));
    }

    /**
     * Test password history check skips when no user context.
     */
    public function test_password_history_skips_when_no_user(): void
    {
        // No user passed, not logged in — history check should allow
        $rule = new StrongPassword(checkHibp: false);

        $this->assertTrue($rule->passes('password', 'AnyPassword123!'));
    }

    /**
     * Test password check against configured history count (10).
     */
    public function test_password_history_checks_against_all_entries(): void
    {
        $user = User::factory()->create();
        $historyCount = config('password.history_count', 10);

        for ($i = 0; $i < $historyCount; $i++) {
            $user->passwordHistory()->create([
                'password' => \Illuminate\Support\Facades\Hash::make("UsedP@ssw0rd{$i}!"),
                'reason' => 'password_change',
            ]);
        }

        $rule = new StrongPassword(checkHibp: false, user: $user);

        for ($i = 0; $i < $historyCount; $i++) {
            $this->assertFalse(
                $rule->passes('password', "UsedP@ssw0rd{$i}!"),
                "Password 'UsedP@ssw0rd{$i}!' should be rejected"
            );
        }

        $this->assertTrue($rule->passes('password', 'BrandN3wP@ss!'));
    }

    /**
     * Test history check respects custom historySize parameter.
     */
    public function test_password_history_respects_custom_size(): void
    {
        $user = User::factory()->create();

        // Create 10 history entries
        for ($i = 0; $i < 10; $i++) {
            $user->passwordHistory()->create([
                'password' => \Illuminate\Support\Facades\Hash::make("UsedP@ssw0rd{$i}!"),
                'reason' => 'password_change',
            ]);
        }

        // Only check last 3
        $rule = new StrongPassword(checkHibp: false, historySize: 3, user: $user);

        // The 10th entry (most recent, index 9) should be blocked
        $this->assertFalse($rule->passes('password', 'UsedP@ssw0rd9!'));

        // The 1st entry (oldest, index 0) should be allowed (outside the 3)
        $this->assertTrue($rule->passes('password', 'UsedP@ssw0rd0!'));
    }
}
