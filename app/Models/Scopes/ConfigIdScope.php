<?php

namespace App\Models\Scopes;

use App\Models\Config;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Schema;

class ConfigIdScope implements Scope
{
    /**
     * {@inheritdoc}
     */
    public function apply(Builder $builder, Model $model)
    {
        if (! Schema::setConnection($model->getConnection())->hasColumn($model->getTable(), 'config_id')) {
            return;
        }

        // from web request
        if (session()->has('desa')) {
            return $builder->where("{$model->getTable()}.config_id", session('desa.id'));
        }

        // from api request
        if (request()->hasHeader('X-Desa') && ! empty(request()->header('X-Desa'))) {
            if ($config = Config::where('kode_desa', request()->header('X-Desa'))->first()) {
                return $builder->where("{$model->getTable()}.config_id", $config->id);
            }
        }
    }
}
