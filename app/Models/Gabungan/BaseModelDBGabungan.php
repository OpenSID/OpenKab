<?php

namespace App\Models\Gabungan;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Model;
use App\Exceptions\DBGabunganInaccessible;

class BaseModelDBGabungan extends Model
{
    /**
     * The database connection that should be used by the model.
     * nama koneksi diatur di config/database.php
     * @var string
     */
    protected $connection = 'openkab';

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        self::isDBGabunganConnected();
    }

    static public function isDBGabunganConnected()
    {
        try {
            // gunakan cache untuk mengurangi permintaan koneksi seperti memanggil relasi dan lain-lain
            $pdo = Cache::remember('cek_koneksi_db_gabungan', $second = 3, function(){
                DB::connection($this->connection)->getPdo();
            });
            return true;

        } catch (\Throwable $th) {
            $msg = '';
            switch($th->getCode()){
                case 2002: $msg = 'DB Gabungan : Host tidak ditemukan';
                    break;
                case 1049: $msg = 'DB Gabungan : Nama Database tidak ditemukan';
                    break;
                case 1045: $msg = 'DB Gabungan : Akses ditolak untuk user';
                    break;
                default: $msg = 'DB Gabungan : ' . $th->getMessage();
                    break;
            }
            throw new DBGabunganInaccessible($msg);
        }
    }
}