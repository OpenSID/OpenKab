<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Support\Facades\Auth;

class EasyAuthorize
{
    /**
     * The gate instance.
     *
     * @var \Illuminate\Contracts\Auth\Access\Gate
     */
    protected $gate;

    /**
     * Create a new middleware instance.
     *
     * @return void
     */
    public function __construct(Gate $gate)
    {
        $this->gate = $gate;
    }

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param string                   $ability
     * @param array|null               ...$models
     *
     * @return mixed
     *
     * @throws \Illuminate\Auth\AuthenticationException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function handle($request, Closure $next, $permission)
    {
        $mapPermission = [
            'index' => 'read',
            'store' => 'write',
            'create' => 'write',
            'show' => 'read',
            'destroy' => 'delete',
            'update' => 'edit',
            'edit' => 'edit',
        ];
        $route = $request->route()->getName();
        $tmp = explode('.', $route);
        $arrLength = count($tmp);

        $ability = $permission.'-'.$mapPermission[$tmp[$arrLength - 1]] ?? $tmp[$arrLength - 1];
//        dd(Auth::user()->can($ability));
        $this->gate->authorize($ability);

        return $next($request);
    }
}
