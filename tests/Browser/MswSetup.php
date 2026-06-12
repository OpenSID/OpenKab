<?php

declare(strict_types=1);

namespace Tests\Browser;

/**
 * MSW (Mock Service Worker) Setup for Browser tests.
 *
 * Generates JavaScript code that:
 * 1. Registers the MSW service worker
 * 2. Sets up request handlers from JSON fixtures
 * 3. Blocks external CDN resources
 *
 * This is injected as a Playwright init script (runs BEFORE page scripts).
 */
final class MswSetup
{
    private const FIXTURES_DIR = __DIR__ . '/fixtures';

    private const SW_PATH = '/mockServiceWorker.js';

    /**
     * Exact route patterns mapped to fixture files.
     */
    private const ROUTES = [
        '/api/v1/statistik-web/get-list-kabupaten' => 'kabupaten.json',
        '/api/v1/data-website' => 'data-website.json',
        '/api/v1/statistik-web/get-list-coordinate' => 'coordinates.json',
        '/api/v1/wilayah/penduduk' => 'penduduk.json',
        '/api/v1/dasbor' => 'dasbor.json',
        '/api/v1/penduduk' => 'data-penduduk.json',
        '/api/v1/data/kesehatan' => 'data-kesehatan.json',
        '/api/v1/pendidikan' => 'data-pendidikan.json',
        '/api/v1/ketenagakerjaan' => 'data-ketenagakerjaan.json',
        '/api/v1/bantuan' => 'data-bantuan.json',
        '/api/v1/lembaga' => 'data-lembaga.json',
    ];

    /**
     * Regex route patterns for dynamic URLs.
     * Keys are regex patterns, values are [fixture_dir_pattern, filename_regex].
     */
    private const REGEX_ROUTES = [
        '#/api/v1/statistik-web/get-list-kecamatan/([\d.]+)$#' => 'kecamatan-*.json',
        '#/api/v1/statistik-web/get-list-desa/([\d.]+)$#' => 'desa-*.json',
        '#/api/v1/statistik/penduduk\?.*filter(?:\[id\]|%5Bid%5D)=([^&]+)#' => 'statistik-penduduk-*.json',
    ];

    /**
     * Build the complete init script JS that registers MSW and sets up handlers.
     */
    public static function getInitScriptJs(): string
    {
        $fixturesJson = self::buildFixturesJson();
        $blockedDomains = json_encode([
            'fonts.googleapis.com',
            'fonts.gstatic.com',
        ]);
        $swPath = self::SW_PATH;

        return <<<JS
(function() {
    if (window.__mswSetupDone) return;
    window.__mswSetupDone = true;

    var FIXTURES = {$fixturesJson};
    var BLOCKED = {$blockedDomains};

    function isBlockedDomain(hostname) {
        return BLOCKED.indexOf(hostname) !== -1;
    }

    function matchFixture(url) {
        var urlObj;
        try { urlObj = new URL(url, window.location.origin); } catch(e) { return null; }
        var path = urlObj.pathname;
        var search = urlObj.search;
        var fullUrl = path + (search || '');

        for (var pattern in FIXTURES) {
            if (pattern.charAt(0) === '~') {
                var regex = new RegExp(pattern.substring(1));
                var m = fullUrl.match(regex);
                if (m && m[1]) {
                    var resolved = FIXTURES[pattern];
                    if (resolved && typeof resolved === 'object' && resolved[m[1]]) {
                        return resolved[m[1]];
                    }
                    return null;
                }
            } else if (path === pattern || path === pattern + '/') {
                return FIXTURES[pattern];
            }
        }
        return null;
    }

    // --- Service Worker Registration ---
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register('{$swPath}')
            .then(function(reg) {
                return navigator.serviceWorker.ready;
            })
            .then(function() {
                window.__mswReady = true;
                window.dispatchEvent(new Event('msw:ready'));
            })
            .catch(function(err) {
                console.warn('[MSW] Service worker registration failed:', err);
                window.__mswReady = true;
                window.dispatchEvent(new Event('msw:ready'));
            });
    } else {
        window.__mswReady = true;
        window.dispatchEvent(new Event('msw:ready'));
    }

    // --- Fetch Interception (fallback while SW activates) ---
    var origFetch = window.fetch;
    window.fetch = function(url, opts) {
        var urlStr = typeof url === 'string' ? url : (url && url.url ? url.url : '');
        if (isBlockedDomain(new URL(urlStr, location.origin).hostname)) {
            return Promise.resolve(new Response('', {status: 204}));
        }
        var f = matchFixture(urlStr);
        if (f) return Promise.resolve(new Response(JSON.stringify(f), {status: 200, headers: {'Content-Type': 'application/json'}}));
        return origFetch.apply(this, arguments);
    };

    var OrigXHR = window.XMLHttpRequest;
    function MockXHR() {
        var r = new OrigXHR(), self = this;
        self._r = r; self._f = null; self._blocked = false;
        self._rs = 0; self.status = 0; self.statusText = '';
        self.responseText = ''; self.response = ''; self.responseType = '';
        self.onreadystatechange = null; self.onload = null; self.onerror = null;
        self.ontimeout = null; self.onabort = null;
        self.withCredentials = false;
        self.timeout = 0;
        Object.defineProperty(self, 'readyState', {get:function(){return self._rs||0;},set:function(v){self._rs=v;}});
        var mockUpload = {addEventListener:function(){},removeEventListener:function(){},dispatchEvent:function(){return true;},onload:null,onerror:null,onabort:null,onprogress:null,ontimeout:null};
        Object.defineProperty(self, 'upload', {get:function(){return self._f||self._blocked?mockUpload:r.upload;}});
        r.onreadystatechange = function(){self._rs=r.readyState;self.status=r.status;self.statusText=r.statusText;self.responseText=r.responseText;self.response=r.response;if(self.onreadystatechange)self.onreadystatechange();};
        r.onload = function(){self._rs=4;self.status=r.status;self.responseText=r.responseText;self.response=r.response;if(self.onload)self.onload();};
        r.onerror = function(){if(self.onerror)self.onerror();};
        r.ontimeout = function(){if(self.ontimeout)self.ontimeout();};
        r.onabort = function(){if(self.onabort)self.onabort();};
    }
    MockXHR.prototype.open = function(m, u, a, user, pass) {
        this._m = m; this._u = u;
        try {
            var h = new URL(u, location.origin).hostname;
            if (isBlockedDomain(h)) { this._blocked = true; this._rs = 1; if(this.onreadystatechange)this.onreadystatechange(); return; }
        } catch(e) {}
        var f = matchFixture(u);
        if (f) { this._f = f; this._rs = 1; if(this.onreadystatechange)this.onreadystatechange(); }
        else { this._r.open.apply(this._r, arguments); }
    };
    MockXHR.prototype.setRequestHeader = function(){if(!this._f && !this._blocked) this._r.setRequestHeader.apply(this._r, arguments);};
    MockXHR.prototype.overrideMimeType = function(){if(!this._f && !this._blocked) this._r.overrideMimeType.apply(this._r, arguments);};
    MockXHR.prototype.send = function(b) {
        if (this._blocked) { var s=this; s._rs=4; s.status=204; setTimeout(function(){if(s.onreadystatechange)s.onreadystatechange();if(s.onload)s.onload();},0); return; }
        if (this._f) {
            var s=this;
            setTimeout(function(){
                s._rs=2;if(s.onreadystatechange)s.onreadystatechange();
                s._rs=3;if(s.onreadystatechange)s.onreadystatechange();
                s.status=200;s.statusText='OK';
                s.responseText=JSON.stringify(s._f);
                s.response=s.responseText;
                s._rs=4;
                if(s.onreadystatechange)s.onreadystatechange();
                if(s.onload)s.onload();
            },0);
        }
        else { this._r.send.apply(this._r, arguments); }
    };
    MockXHR.prototype.abort = function(){
        this._aborted=true;
        if(!this._f && !this._blocked) this._r.abort();
        else { this._rs=0; this.status=0; if(this.onabort)this.onabort(); }
    };
    MockXHR.prototype.getResponseHeader = function(n){if(this._f)return n.toLowerCase()==='content-type'?'application/json':null;if(this._blocked)return null;return this._r.getResponseHeader(n);};
    MockXHR.prototype.getAllResponseHeaders = function(){if(this._f)return 'content-type: application/json';if(this._blocked)return '';return this._r.getAllResponseHeaders();};
    MockXHR.prototype.addEventListener = function(type, fn) {
        var prop = 'on' + type;
        if (typeof this[prop] === 'function' && this[prop] !== null) {
            var prev = this[prop];
            this[prop] = function() { prev.apply(this, arguments); fn.apply(this, arguments); };
        } else {
            this[prop] = fn;
        }
    };
    MockXHR.prototype.removeEventListener = function(type, fn) {
        var prop = 'on' + type;
        if (this[prop] === fn) this[prop] = null;
    };
    MockXHR.prototype.dispatchEvent = function(){return true;};
    window.XMLHttpRequest = MockXHR;
})();
JS;
    }

    /**
     * Build JSON object mapping route patterns to fixture data.
     */
    private static function buildFixturesJson(): string
    {
        $fixtures = [];
        $fixturesDir = self::FIXTURES_DIR;

        // Load exact route fixtures
        foreach (self::ROUTES as $pattern => $file) {
            $path = $fixturesDir . '/' . $file;
            if (file_exists($path)) {
                $content = json_decode(file_get_contents($path), true);
                $fixtures[$pattern] = $content;
            }
        }

        // Load and resolve regex route fixtures
        $allFiles = glob($fixturesDir . '/*.json');
        foreach (self::REGEX_ROUTES as $pattern => $globPattern) {
            $resolved = [];
            // Convert glob pattern like "kecamatan-*.json" to regex
            $prefix = strtok($globPattern, '*');
            $suffix = substr($globPattern, strlen($prefix) + 1); // After the *
            $fileRegex = '/^' . preg_quote($prefix, '/') . '(.+)' . preg_quote($suffix, '/') . '$/';
            foreach ($allFiles as $file) {
                $basename = basename($file);
                if (preg_match($fileRegex, $basename, $m)) {
                    $key = $m[1];
                    $resolved[$key] = json_decode(file_get_contents($file), true);
                }
            }
            if (! empty($resolved)) {
                // Strip PHP regex delimiters (#...#) and prefix with ~ for JS detection
                $stripped = substr($pattern, 1, -1);
                $jsPattern = '~' . $stripped;
                $fixtures[$jsPattern] = $resolved;
            }
        }

        return json_encode($fixtures, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
    }
}
