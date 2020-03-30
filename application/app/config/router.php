<?php

$router = $di->getRouter();

// Define your routes here

$router->add(
    '/users',
    [
        'controller' => 'users',
        'action'     => 'index',
    ]
);

$router->add(
    '/users/login',
    [
        'controller' => 'users',
        'action'     => 'login',
    ]
);

$router->add(
    '/users/register',
    [
        'controller' => 'users',
        'action'     => 'register',
    ]
);

$router->add(
    '/users/update',
    [
        'controller' => 'users',
        'action'     => 'update',
    ]
);

$router->add(
    '/users/activate',
    [
        'controller' => 'users',
        'action'     => 'activate',
    ]
);

$router->add(
    '/users/cron',
    [
        'controller' => 'users',
        'action'     => 'cron',
    ]
);




$router->handle($_SERVER['REQUEST_URI']);
