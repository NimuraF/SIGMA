<?php

class csrfMiddleware extends Middleware {

    public function handle(Request $request, callable $next): Response
    {
        if (!isset($request->params['csrf-token'])) {
            return new Response(new class {
                public bool $access = false;
                public string $errorm = "CSRF token validation error";
            });
        }

        return $next($request);
    }

}