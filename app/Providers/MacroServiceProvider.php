<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;

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
