<?php

namespace Router\Component;

use Router\Module\PathToRegexp,
    \Exception;

class Route {

  public $path;
  public $regex;
  public $method;

  private $callback;
  private $keys = [];
  private $params = [];

  public function __construct(&$callback, &$method = null, &$path = null) {
    if (!is_callable($callback)) {
      throw new Exception('invalid callback');
    }

    $this->callback = $callback;
    $this->method = $method !== null ? strtoupper($method) : null;
    $this->path = $path === '*' ? '' : ($path === '' ? '/' : $path);
    $this->regex = PathToRegexp::convert($this->path, $this->keys);
  }

  public function matched(&$method, &$uri) {
    if ($method !== $this->method && $this->method !== '*') {
      return false;
    }

    if (!$this->path) {
      return true;
    }

    $matches = PathToRegexp::match($this->regex, $uri);

    if ($matches) {
      foreach ($this->keys as $index => &$entry) {
        $name = $entry['name'];
        $this->params[$name] = urldecode($matches[$index + 1]);
      }
    }

    return (bool) $matches;
  }

  public function handler(&$request, &$response, &$next) {
    ($this->callback)($request, $response, $next);
  }

  public function activate(&$request, &$response, &$next) {
    $shouldValidType = require __DIR__ . '/../utils/shouldValidType.php';
    $shouldValidType($request, 'Router\\Component\\Request');
    $shouldValidType($response, 'Router\\Component\\Response');
    $shouldValidType($next, 'Closure');
    $request->params = $this->params;
    $this->handler($request, $response, $next);
  }

}