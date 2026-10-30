<?php

namespace App\Support;

/**
 * Batas koordinat wilayah Indonesia.
 *
 * Latitude  : -11.0 (selatan) s.d. 6.0 (utara)
 * Longitude : 95.0 (barat) s.d. 141.0 (timur)
 */
class Coordinate
{
    public const MIN_LAT = -11.0;

    public const MAX_LAT = 6.0;

    public const MIN_LNG = 95.0;

    public const MAX_LNG = 141.0;
}
