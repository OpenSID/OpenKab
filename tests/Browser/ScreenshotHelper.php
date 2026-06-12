<?php

declare(strict_types=1);

namespace Tests\Browser;

final class ScreenshotHelper
{
    private const STORAGE_PATH = __DIR__ . '/Screenshots';

    public static function enabled(): bool
    {
        return filter_var(
            env('SCREENSHOT_ON_SUCCESS', false),
            FILTER_VALIDATE_BOOLEAN
        );
    }

    public static function saveIfEnabled(\Pest\Browser\Api\PendingAwaitablePage|\Pest\Browser\Api\AwaitableWebpage $page, string $name): void
    {
        if (! self::enabled()) {
            return;
        }

        if (! is_dir(self::STORAGE_PATH)) {
            mkdir(self::STORAGE_PATH, 0755, true);
        }

        $timestamp = date('Y-m-d_H-i-s');
        $filename = "{$name}_{$timestamp}";

        $page->screenshot(true, $filename);
    }

    public static function getPath(): string
    {
        return self::STORAGE_PATH;
    }
}
