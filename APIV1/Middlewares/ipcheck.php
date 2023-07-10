<?php

class ipcheckMiddleware extends Middleware {

    /* Данный middleware определяет ip-адрес клиента */
    public function handle(Request $request, callable $next) : Response {
        
        $request->ipadr = $_SERVER['REMOTE_ADDR'];

        return $next($request);
    }

}