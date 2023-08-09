<?php

use Configuration\Configuration;

class csrfMiddleware extends Middleware {

    public function handle(Request $request, callable $next): Response
    {

        if (!isset($request->params['csrf-token']) || !isset($request->cookies['csrf-token'])) {
            return new Response(new class {
                public bool $access = false;
                public string $errorm = "CSRF token was not sent";
            });
        }

        $redis = new Redis();
        
        if 
        (
            !$redis->connect(Configuration::REDIS_HOST, Configuration::REDIS_PORT) 
            || $request->params['csrf-token'] != $request->cookies['csrf-token']
            || !$redis->get($request->params['csrf-token'])
        ) 
        {
            setcookie('csrf-token', '', [
                'path' => '/',
                'domain' => 'gamedata.ru',
            ]);
            return new Response(new class {
                public bool $access = false;
                public string $errorm = "CSRF token validation error";
            });
        }

        $redis->set($request->params['csrf-token'], 'csrf', 2400);

        $redis->close();
        

        return $next($request);
    }

}