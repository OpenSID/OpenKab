<?php

namespace App\Models;

use App\Models\Traits\ConfigIdTrait;
use Illuminate\Database\Eloquent\Model;

class ClusterDesa extends Model
{
    use ConfigIdTrait;

    /** {@inheritdoc} */
    protected $connection = 'openkab';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tweb_wil_clusterdesa';

    /**
     * Scope query dusun.
     *
     * @param \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     */
    public function scopeDusun($query)
    {
        return $query->where('rt', '0')->where('rw', '0');
    }

    /**
     * Scope query rw.
     *
     * @param \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     */
    public function scopeRW($query, string|null $dusun = null)
    {
        return $query
            ->when($dusun, function ($query, $dusun) {
                $query->where('dusun', $dusun);
            })
            ->where('rt', '0')->where('rw', '<>', '0');
    }

    /**
     * Scope query rt.
     *
     * @param \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     */
    public function scopeRT($query, string|null $dusun = null, string|null $rw = null)
    {
        return $query
            ->when($dusun && $rw, function ($query) use ($dusun, $rw) {
                $query->where('dusun', $dusun)->where('rw', $rw);
            })
            ->where('rt', '<>', '0');
    }
}
