<?php

namespace App\Models\Scopes;

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
        if (! session()->has('desa')) {
            return;
        }

        if (! Schema::hasColumn($model->getTable(), 'config_id')) {
            return;
        }

        return $builder->where("{$model->getTable()}.config_id", session('desa.id'));
    }

    /**
     * Extend the query builder with the needed functions.
     *
     * @return void
     */
    public function extend(Builder $builder)
    {
        $builder->macro('withConfigId', static function (Builder $builder, $alias = null) {
            if (! Schema::hasColumn($builder->getModel()->getTable(), 'config_id')) {
                return $builder;
            }

            if ($alias) {
                return $builder->where("{$alias}.config_id", session('desa.id'));
            }

            return $builder->where('config_id', session('desa.id'));
        });
    }
}