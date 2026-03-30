<?php

namespace App\Rules;

use App\Models\PasswordHistory;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Hash;

class StrongPassword implements Rule
{
    /**
     * Minimum password length.
     */
    protected int $minLength;

    /**
     * Password history size to check.
     */
    protected int $historySize;

    /**
     * Enable HIBP (Have I Been Pwned) check.
     */
    protected bool $checkHibp;

    /**
     * Additional weak patterns to check.
     */
    protected array $weakPatterns;

    /**
     * Common passwords list.
     */
    protected array $commonPasswords;

    /**
     * Create a new rule instance.
     */
    public function __construct(
        ?int $minLength = null,
        ?bool $checkHibp = null,
        ?int $historySize = null
    ) {
        $this->minLength = $minLength ?? config('password.min_length', 12);
        $this->checkHibp = $checkHibp ?? config('password.check_hibp', true);
        $this->historySize = $historySize ?? config('password.history_count', 5);
        $this->weakPatterns = config('password.weak_patterns', []);
        $this->commonPasswords = config('password.common_passwords', []);
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param mixed  $value
     *
     * @return bool
     */
    public function passes($attribute, $value)
    {
        // Check minimum length
        if (strlen($value) < $this->minLength) {
            return false;
        }

        // Check for at least one uppercase letter
        if (!preg_match('/[A-Z]/', $value)) {
            return false;
        }

        // Check for at least one lowercase letter
        if (!preg_match('/[a-z]/', $value)) {
            return false;
        }

        // Check for at least one number
        if (!preg_match('/[0-9]/', $value)) {
            return false;
        }

        // Check for at least one special character
        if (!preg_match('/[!@#$%^&*(),.?":{}|<>_\-+=\[\]\\;\'\/`~]/', $value)) {
            return false;
        }

        // Check for weak patterns
        if ($this->matchesWeakPattern($value)) {
            return false;
        }

        // Check for common passwords
        if ($this->isCommonPassword($value)) {
            return false;
        }

        // Check HIBP database
        if ($this->checkHibp && !$this->isNotPwned($value)) {
            return false;
        }

        // Check password history
        if (!$this->isNotInHistory($value)) {
            return false;
        }

        return true;
    }

    /**
     * Check if password matches any weak pattern.
     */
    protected function matchesWeakPattern(string $password): bool
    {
        foreach ($this->weakPatterns as $pattern) {
            if (preg_match($pattern, $password)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if password is in the common passwords list.
     */
    protected function isCommonPassword(string $password): bool
    {
        return in_array(strtolower($password), array_map('strtolower', $this->commonPasswords));
    }

    /**
     * Check if password has been pwned using HIBP API.
     * Uses k-anonymity model (only send first 5 chars of SHA1 hash).
     */
    protected function isNotPwned(string $password): bool
    {
        $sha1 = strtoupper(sha1($password));
        $prefix = substr($sha1, 0, 5);
        $suffix = substr($sha1, 5);

        try {
            $response = Http::withHeaders([
                'User-Agent' => 'OpenKab-Password-Policy/1.0',
                'Add-Padding' => 'true',
            ])->get("https://api.pwnedpasswords.com/range/{$prefix}");

            if ($response->successful()) {
                $lines = explode("\n", $response->body());
                foreach ($lines as $line) {
                    [$hashSuffix, ] = explode(':', $line);
                    if (strtoupper(trim($hashSuffix)) === $suffix) {
                        return false; // Password is pwned
                    }
                }
            }
        } catch (\Exception $e) {
            // If HIBP API fails, we allow the password but log the issue
            \Log::warning('HIBP API check failed: '.$e->getMessage());
        }

        return true; // Password is not pwned or API unavailable
    }

    /**
     * Check if password was used recently (in password history).
     */
    protected function isNotInHistory(string $password): bool
    {
        if (!auth()->check()) {
            return true; // No logged in user to check history for
        }

        $user = auth()->user();
        $passwordHistory = PasswordHistory::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit($this->historySize)
            ->get();

        foreach ($passwordHistory as $history) {
            if (Hash::check($password, $history->password)) {
                return false; // Password is in history
            }
        }

        return true; // Password is not in history
    }

    /**
     * Get the validation error message.
     *
     * @return string|array
     */
    public function message()
    {
        return [
            'length' => 'Password harus memiliki minimal :min karakter.',
            'complexity' => 'Password harus mengandung huruf kapital, huruf kecil, angka, dan karakter spesial (!@#$%^&*...).',
            'hibp' => 'Password ini telah bocor di database password yang pernah diretas. Silakan gunakan password lain yang lebih unik.',
            'history' => 'Password ini telah digunakan sebelumnya. Silakan gunakan password baru.',
            'weak_pattern' => 'Password terlalu lemah atau mudah ditebak. Hindari pola berulang atau berurutan.',
            'common' => 'Password ini terlalu umum dan mudah ditebak. Silakan gunakan password yang lebih unik.',
        ];
    }

    /**
     * Get the validation error message with proper formatting.
     */
    public function failedMessage(?string $failedRule = null): string
    {
        $messages = $this->message();
        $value = $this->getValue() ?? '';

        if (strlen($value) < $this->minLength) {
            return str_replace(':min', (string) $this->minLength, $messages['length']);
        }

        if (!preg_match('/[A-Z]/', $value) ||
            !preg_match('/[a-z]/', $value) ||
            !preg_match('/[0-9]/', $value) ||
            !preg_match('/[!@#$%^&*(),.?":{}|<>_\-+=\[\]\\;\'\/`~]/', $value)) {
            return $messages['complexity'];
        }

        if ($this->matchesWeakPattern($value)) {
            return $messages['weak_pattern'];
        }

        if ($this->isCommonPassword($value)) {
            return $messages['common'];
        }

        if ($this->checkHibp && !$this->isNotPwned($value)) {
            return $messages['hibp'];
        }

        if (!$this->isNotInHistory($value)) {
            return $messages['history'];
        }

        return 'Password tidak memenuhi persyaratan keamanan.';
    }

    /**
     * Store the value being validated for error messaging.
     */
    protected ?string $value = null;

    /**
     * Get the value being validated.
     */
    protected function getValue(): ?string
    {
        return $this->value;
    }

    /**
     * Set the value being validated.
     */
    public function setValue(string $value): self
    {
        $this->value = $value;

        return $this;
    }
}
