<?php

namespace App\Http\Middleware;

/**
 * Если необходимо выполнить какой-то роут только с админским паролем.
 * @package App\Http\Middleware
 */
class WithSecretAdminPasswordMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     *
     * @return mixed
     * @throws \LogicException
     */
    public function handle($request, \Closure $next)
    {
        $param = config('values.secret_admin_password_query_param');
        $password = $request->input($param, 'bad_pass');
        if ($password !== config('values.secret_admin_password')) {
            return response(__('base.wrong_secret_admin_password', ['param_name' => $param]));
        }

        return $next($request);
    }
}
