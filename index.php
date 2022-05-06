<?php

/**
 * This is an example file.
 * This file shows how you can start
 * your project using the router module.
 * 
 * I call this file a `base file` because
 * all the request to the server is being handled
 * by this.
 * 
 * check .htaccess' RewriteRule
 */

# Require the router into your base file
require_once __DIR__ . '/.router/mod.php';

# Make a router\Router instance
$router = new Router\Router();

/**
 * serve a static directory
 * 
 * @param string $path
 * @param string $prefix = '/'
 * 
 * $router->serve(__DIR__ . '/public', '/static');
 */

# chaining is available
$router

  # homepage
  ->get('/', function ($request, $response) {
    $response->html(
      '<h1>Router is running!</h1>' .
      '<br>' .
      '<a href="' .$request->baseURL. 'request/param_value?data1=value1&data2=value2">view request object</a>'
    );
  })

  # middleware
  ->apply(function ($request, $response, $next) {
    $request->customProperty = 'some value';
    $response->removeHeader('X-Powered-By');
    $next(); # <- this is required to proceed, unless it will response a timeout
  })

  # request route
  ->get('/request/:example', function ($request, $response) {
    var_dump($request);
    exit;
  })

  # activate $router
  ->activate();