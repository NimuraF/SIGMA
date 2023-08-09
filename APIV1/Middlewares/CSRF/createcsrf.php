<?php

use Configuration\Configuration;

class createcsrfMiddleware extends Middleware {

    public function handle(Request $request, callable $next) : Response
    {
        if (!isset($request->cookies['csrf-token'])) {
            $redis = new Redis();

            if ($redis->connect(Configuration::REDIS_HOST, Configuration::REDIS_PORT)) {

                $newCSRF = hash('sha256', $_SERVER['REMOTE_ADDR'].$_SERVER['REQUEST_TIME_FLOAT'].$_SERVER['HTTP_USER_AGENT']);

                if ($redis->set($newCSRF, 'csrf', 2400)) {
                    setcookie('csrf-token', $newCSRF, [
                        'path' => '/',
                        'domain' => 'gamedata.ru',
                    ]);

                    $redis->close();
                }
                
            }
        }
        
        return $next($request);
    }

}