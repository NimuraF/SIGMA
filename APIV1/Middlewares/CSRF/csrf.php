<?php

class csrfMiddleware extends Middleware {

    public function handle(Request $request, callable $next): Response
    {
        
        if (isset($request->params['csrf-token'])) {

        }

        return $next($request);
    }

}