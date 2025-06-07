<?php

$routes = [
    ['method' => 'POST', 'path' => '/usuarios', 'handler' => 'UserController@store', 'auth' => true],
    ['method' => 'GET',  'path' => '/usuarios/(\d+)', 'handler' => 'UserController@show', 'auth' => false],
    [
        'method' => 'GET',
        'path' => '/api/produtos',
        'handler' => 'ProdutoController@index',
        'auth' => true,
        'roles' => ['admin', 'fornecedor']
    ],
    [
        'method' => 'POST',
        'path' => '/api/login',
        'handler' => 'AuthController@login',
        'auth' => false
    ],
];
