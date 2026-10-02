<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Dipakai untuk kegagalan konfigurasi SSO (secret belum di-set, URL tidak valid).
 */
class SsoConfigurationException extends RuntimeException {}
