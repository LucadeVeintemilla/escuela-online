<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $roles = ''): Response
    {
        $user = $request->user();

        if (!$user) {
            abort(403, 'No autenticado');
        }

        // Roles pueden venir separados por coma: role:admin,docente
        $allowed = array_filter(array_map('trim', explode(',', $roles)));

        // Si no se especifica roles, permitir por defecto
        if (empty($allowed)) {
            return $next($request);
        }

        // Se asume que el usuario tiene campo role_id y relación role->rol
        $userRole = method_exists($user, 'role') && $user->role ? $user->role->rol : null;

        if ($userRole && in_array($userRole, $allowed, true)) {
            return $next($request);
        }

        abort(403, 'No autorizado');
    }
}
