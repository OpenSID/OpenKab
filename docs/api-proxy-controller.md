# ApiProxyController Documentation

## Overview

`ApiProxyController` is a Laravel controller that acts as a generic proxy for forwarding HTTP requests to external APIs defined in the configuration. It supports GET and POST requests with optional caching and timeout parameters.

## Routes

The controller is typically accessed via routes defined in `routes/api.php` or `routes/web.php`. Example route definitions:

```php
Route::get('/proxy', [ApiProxyController::class, 'get'])->name('api.proxy.get');
Route::post('/proxy', [ApiProxyController::class, 'post'])->name('api.proxy.post');
Route::delete('/proxy/cache', [ApiProxyController::class, 'clearCache'])->name('api.proxy.clearCache');
```

## Methods

### GET Request

Handles GET requests to proxy external APIs.

**Endpoint:** `GET /proxy`

**Query Parameters:**

| Parameter | Type    | Required | Description                                                                 |
|-----------|---------|----------|-----------------------------------------------------------------------------|
| endpoint  | string  | Yes      | The key of the endpoint defined in `config/api_proxy.php` or the full URL. |
| cache     | boolean | No       | If set to `true`, enables caching of the response. Defaults to `false`.    |
| timeout   | integer | No       | Request timeout in seconds. Defaults to the value configured in the service.|

**Example Request:**
```
GET /proxy?endpoint=desa&cache=true&timeout=10
```

**Response:**
- Success: JSON response from the proxied API.
- Error:
  - `400 Bad Request` if `endpoint` is empty.
  - `500 Internal Server Error` if the API request fails.

### POST Request

Handles POST requests to proxy external APIs.

**Endpoint:** `POST /proxy`

**Body Parameters (JSON or form-data):**

| Parameter | Type    | Required | Description                                                                 |
|-----------|---------|----------|-----------------------------------------------------------------------------|
| endpoint  | string  | Yes      | The key of the endpoint defined in `config/api_proxy.php` or the full URL. |
| cache     | boolean | No       | If set to `true`, enables caching of the response. Defaults to `false`.    |
| timeout   | integer | No       | Request timeout in seconds. Defaults to the value configured in the service.|
| [other]   | mixed   | No       | Additional parameters that will be forwarded to the target API.            |

**Example Request:**
```json
POST /proxy
{
  "endpoint": "desa",
  "cache": true,
  "timeout": 10,
  "provinsi": "Jawa Barat",
  "kabupaten": "Bandung"
}
```

**Response:**
- Success: JSON response from the proxied API.
- Error:
  - `400 Bad Request` if `endpoint` is empty.
  - `500 Internal Server Error` if the API request fails.

### Clear Cache

Clears cached responses for a specific endpoint or all endpoints.

**Endpoint:** `DELETE /proxy/cache` (or GET depending on route definition)

**Query Parameters:**

| Parameter | Type    | Required | Description                                                                 |
|-----------|---------|----------|-----------------------------------------------------------------------------|
| endpoint  | string  | No       | The key of the endpoint to clear cache for. If omitted, clears all cache.  |

**Example Request:**
```
DELETE /proxy/cache?endpoint=desa
```

**Response:**
- `200 OK` with JSON `{ "message": "Cache cleared" }`

## Configuration

External API endpoints are defined in `config/api_proxy.php`. Example:

```php
return [
    'endpoints' => [
        'desa' => 'https://api.example.com/v1/desa',
        // ... other endpoints
    ],
];
```

If the `endpoint` parameter matches a key in this array, the corresponding URL is used. Otherwise, the parameter value is used directly as the URL (allowing dynamic URLs).

## Service Layer

The controller delegates the actual HTTP requests to `App\Services\ApiProxyService`, which handles:
- Making GET/POST requests with Guzzle
- Caching responses (if enabled)
- Timeout handling
- Error handling

## Error Handling

- Missing or empty `endpoint` parameter returns a `400` error with message `"Endpoint tidak boleh kosong"`.
- If the proxied API request fails (returns null or throws an exception), a `500` error is returned with message `"Gagal mengambil data dari API"` (for GET) or `"Gagal mengirim data ke API"` (for POST).

## Usage Example in JavaScript (fetch)

```javascript
// GET request
fetch('/proxy?endpoint=desa&cache=true')
  .then(response => response.json())
  .then(data => console.log(data))
  .catch(error => console.error('Error:', error));

// POST request
fetch('/proxy', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
  },
  body: JSON.stringify({
    endpoint: 'desa',
    cache: true,
    provinsi: 'Jawa Barat',
  })
})
.then(response => response.json())
.then(data => console.log(data))
.catch(error => console.error('Error:', error));
```

## Testing

Unit tests for this controller can be found in `tests/Feature/Http/Controllers/ApiProxyControllerTest.php`.
