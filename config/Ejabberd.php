<?php
use App\Http\Ejabberd;

$ejabberd  = new Ejabberd([
    'server'   =>  env('EJABBERD_SERVER_PATH', "http://test:5281"),
    'host'     =>  env('EJABBERD_SERVER_HOST', "test.com"), 
    'debug'    =>  false,
]);

return $ejabberd;