<?php

abstract class Middleware {

    /* Обязываем потомков реализовывать метод handle */
    abstract public function handle(Request $request, callable $next) : Response;

}