<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Password Policy Configuration
    |--------------------------------------------------------------------------
    |
    | Configure the password policy settings for the application.
    | These settings control password strength requirements, expiry, and history.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Minimum Password Length
    |--------------------------------------------------------------------------
    |
    | The minimum number of characters required for all passwords.
    | Recommended: 12 or more for strong security.
    |
    */
    'min_length' => 12,

    /*
    |--------------------------------------------------------------------------
    | Password Expiry
    |--------------------------------------------------------------------------
    |
    | Number of days before a password expires. Set to null to disable expiry.
    | When enabled, users will be forced to change their password after expiry.
    |
    */
    'expiry_days' => 90,

    /*
    |--------------------------------------------------------------------------
    | Password History
    |--------------------------------------------------------------------------
    |
    | Number of previous passwords to remember and prevent reuse.
    | Set to 0 to disable password history check.
    |
    */
    'history_count' => 10,

    /*
    |--------------------------------------------------------------------------
    | HIBP (Have I Been Pwned) Check
    |--------------------------------------------------------------------------
    |
    | Enable checking passwords against the HIBP database of breached passwords.
    | Uses k-anonymity model for privacy.
    |
    */
    'check_hibp' => true,

    /*
    |--------------------------------------------------------------------------
    | Force Reset for Existing Users
    |--------------------------------------------------------------------------
    |
    | When enabled, existing users with weak passwords will be flagged for
    | forced password reset on next login.
    |
    */
    'force_reset_weak_passwords' => true,

    /*
    |--------------------------------------------------------------------------
    | Weak Password Patterns
    |--------------------------------------------------------------------------
    |
    | Additional patterns that indicate a weak password.
    | These are checked in addition to the standard requirements.
    |
    */
    'weak_patterns' => [
        '/^(.)\1+$/', // Same character repeated (e.g., aaaaaa)
        '/^(012|123|234|345|456|567|678|789|890)/', // Sequential numbers
        '/^(abc|bcd|cde|def|efg|fgh|ghi|hij|ijk|jkl|klm|lmn|mno|nop|opq|pqr|qrs|rst|stu|tuv|uvw|vwx|wxy|xyz)/i', // Sequential letters
    ],

    /*
    |--------------------------------------------------------------------------
    | Common Passwords List
    |--------------------------------------------------------------------------
    |
    | List of common passwords to reject (in addition to HIBP check).
    |
    */
    'common_passwords' => [
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
    ],
];
