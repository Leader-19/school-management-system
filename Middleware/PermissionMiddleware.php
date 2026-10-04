<?php

/**
 * PermissionMiddleware
 * --------------------
 * Requires one or more RBAC permissions before the route runs.
 *
 * Usage in the route table:
 *     ['GET', '/student', 'StudentController', 'index', [PermissionMiddleware::class, ['manage_students']]],
 *
 * When several permissions are given, holding any one of them is enough.
 * This is the declarative form of Controller::checkPermission().
 */
class PermissionMiddleware extends Middleware {
    /** @var string[] */
    private $permissions;

    public function __construct($permissions = []) {
        $this->permissions = (array) $permissions;
    }

    public function handle(Request $request, callable $next) {
        if (empty($this->permissions)) {
            return $next($request);
        }

        foreach ($this->permissions as $permission) {
            if (RBAC::checkAccess($permission)) {
                return $next($request);
            }
        }

        if ($request->wantsJson()) {
            return Response::json(['error' => 'You do not have permission to access this resource.'], 403);
        }

        Session::setFlash('error', 'You do not have permission to access this page.');
        return Response::redirect('dashboard');
    }
}
