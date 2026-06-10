<?php

/**
 * Mock API server for smoke tests.
 *
 * Uses PHP's built-in web server.
 * Run with: php -S 127.0.0.1:8001 tests/Browser/mock-server.php
 * Or start via SessionState::startMockServer() in Pest.php
 */

$fixturesDir = __DIR__ . '/fixtures';
$uri = $_SERVER['REQUEST_URI'];
$path = parse_url($uri, PHP_URL_PATH);
$query = parse_url($uri, PHP_URL_QUERY);
$params = [];
if ($query) {
    parse_str($query, $params);
}
// Also check $_GET for filter[id] (handles bracket encoding)
$slug = $params['filter']['id'] ?? $_GET['filter']['id'] ?? null;

// CORS headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json; charset=utf-8');

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    return true;
}

// Route matching
$fixture = null;

if ($path === '/api/v1/statistik-web/get-list-kabupaten') {
    $fixture = 'kabupaten.json';
} elseif (preg_match('#^/api/v1/statistik-web/get-list-kecamatan/([\d.]+)$#', $path, $m)) {
    $fixture = "kecamatan-{$m[1]}.json";
} elseif (preg_match('#^/api/v1/statistik-web/get-list-desa/([\d.]+)$#', $path, $m)) {
    $fixture = "desa-{$m[1]}.json";
} elseif ($path === '/api/v1/data-website') {
    $fixture = 'data-website.json';
} elseif ($path === '/api/v1/statistik-web/get-list-coordinate') {
    $fixture = 'coordinates.json';
} elseif ($path === '/api/v1/wilayah/penduduk') {
    $fixture = 'penduduk.json';
} elseif ($path === '/api/v1/dasbor') {
    $fixture = 'dasbor.json';
} elseif ($path === '/api/v1/statistik/penduduk') {
    if ($slug) {
        $fixture = "statistik-penduduk-{$slug}.json";
    }
}

if ($fixture === null) {
    http_response_code(404);
    echo json_encode(['error' => 'Not Found', 'path' => $path]);
    return true;
}

$fixturePath = $fixturesDir . '/' . $fixture;

if (!file_exists($fixturePath)) {
    http_response_code(404);
    echo json_encode(['error' => 'Fixture not found', 'fixture' => $fixture]);
    return true;
}

echo file_get_contents($fixturePath);
return true;
