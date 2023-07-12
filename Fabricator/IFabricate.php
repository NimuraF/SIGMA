<?php

interface IFabricate {

    function fabricate(callable $next);

}