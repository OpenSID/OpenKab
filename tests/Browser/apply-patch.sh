#!/bin/bash
#
# Patches for vendor packages used by Browser tests.
# Run this after composer install or composer update.
#
# Usage: bash tests/Browser/apply-patch.sh
#

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_DIR="$(dirname "$(dirname "$SCRIPT_DIR")")"

# ===================================================================
# Patch 1: Pest Browser InitScript - inject MSW (Mock Service Worker)
# ===================================================================
VENDOR_INIT="$PROJECT_DIR/vendor/pestphp/pest-plugin-browser/src/Playwright/InitScript.php"

if [ ! -f "$VENDOR_INIT" ]; then
    echo "SKIP: Vendor InitScript.php not found at: $VENDOR_INIT"
elif grep -q "MswSetup" "$VENDOR_INIT" 2>/dev/null; then
    echo "OK: InitScript.php already patched with MSW setup"
else
    # Backup original
    cp "$VENDOR_INIT" "$VENDOR_INIT.bak"

    # Generate the MSW init script JS via PHP
    MSW_JS=$(cd "$PROJECT_DIR" && php -r "
require 'vendor/autoload.php';
use Tests\Browser\MswSetup;
echo MswSetup::getInitScriptJs();
")

    if [ -z "$MSW_JS" ]; then
        echo "ERROR: Failed to generate MSW init script"
        exit 1
    fi

    # Create the patched InitScript.php
    cat > "$VENDOR_INIT" << 'PATCHPHP'
<?php

declare(strict_types=1);

namespace Pest\Browser\Playwright;

/**
 * @internal
 *
 * Patched: includes MSW (Mock Service Worker) setup for Browser tests.
 * Original backup: InitScript.php.bak
 * Source: tests/Browser/MswSetup.php + tests/Browser/apply-patch.sh
 * Revert: cp InitScript.php.bak InitScript.php
 */
final class InitScript
{
    public static function get(): string
    {
        $axe = (string) file_get_contents(
            dirname(__DIR__, 2).'/resources/js/axe.min.js'
        );

PATCHPHP

    # Append the original get() method body
    cat >> "$VENDOR_INIT" << 'ORIGMETHOD'
        $initScriptJs = <<<JS
            $axe

            window.__pestBrowser = {
                jsErrors: [],
                consoleLogs: []
            };

            const originalConsoleLog = console.log;
            console.log = function(...args) {
                window.__pestBrowser.consoleLogs.push({
                    timestamp: new Date().getTime(),
                    message: args.map(arg => String(arg)).join(' ')
                });
                originalConsoleLog.apply(console, args);
            };

            window.addEventListener('error', (e) => {
                window.__pestBrowser.jsErrors.push({
                    message: e.message,
                    filename: e.filename,
                    lineno: e.lineno,
                    colno: e.colno
                });
            });
            JS;

ORIGMETHOD

    # Append the MSW setup call
    cat >> "$VENDOR_INIT" << MSWCALL
        \$mswSetupJs = \Tests\Browser\MswSetup::getInitScriptJs();

        return \$initScriptJs . "\n" . \$mswSetupJs;
    }
}
MSWCALL

    if [ $? -eq 0 ]; then
        echo "OK: Patched InitScript.php with MSW (Mock Service Worker) support"
    else
        echo "ERROR: Failed to patch InitScript.php"
        cp "$VENDOR_INIT.bak" "$VENDOR_INIT"
        exit 1
    fi
fi

# ===================================================================
# Patch 2: Fix akaunting/laravel-apexcharts asset path bug
# ===================================================================
CHART_PHP="$PROJECT_DIR/vendor/akaunting/laravel-apexcharts/src/Chart.php"

if [ ! -f "$CHART_PHP" ]; then
    echo "SKIP: Chart.php not found at: $CHART_PHP"
elif grep -q "asset('vendor/apexcharts/apexcharts.js')" "$CHART_PHP" 2>/dev/null; then
    echo "OK: Chart.php already patched (asset path fixed)"
else
    # Backup original
    cp "$CHART_PHP" "$CHART_PHP.bak"

    # Fix: asset('public/vendor/...') -> asset('vendor/...')
    sed -i "s|asset('public/vendor/apexcharts/apexcharts.js')|asset('vendor/apexcharts/apexcharts.js')|g" "$CHART_PHP"

    if grep -q "asset('vendor/apexcharts/apexcharts.js')" "$CHART_PHP" 2>/dev/null; then
        echo "OK: Patched Chart.php (fixed apexcharts asset path)"
    else
        echo "ERROR: Failed to patch Chart.php"
        cp "$CHART_PHP.bak" "$CHART_PHP"
    fi
fi
