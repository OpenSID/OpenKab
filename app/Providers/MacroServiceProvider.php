<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class MacroServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        Builder::macro('toBoundSql', function () {
            /* @var Builder $this */
            $bindings = array_map(
                fn ($parameter) => is_string($parameter) ? "'$parameter'" : $parameter,
                $this->getBindings()
            );

            return Str::replaceArray(
                '?',
                $bindings,
                $this->toSql()
            );
        });

        EloquentBuilder::macro('toBoundSql', function () {
            return $this->toBase()->toBoundSql();
        });
    }
}
