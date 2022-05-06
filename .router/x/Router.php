<?php

namespace Router;

use \Exception,
    Router\Component\Route,
    Router\Component\Request,
    Router\Component\Response;

class Router {

  public $STACK = [];
  public $VIEWS;
  public $REQUEST;

  private $RESPONSE;

  public function __construct() {
    $this->REQUEST = new Request();
    $this->RESPONSE = new Response($this);
  }

  public function activate() {
    $index = 0;
    ob_start();
    $this->next($index);
    
    if (ob_get_level()) {
      ob_end_clean();
    }

    $this->RESPONSE->status(408)->html('<!docytpe html><html><head><title>Error</title></head><body><pre>Request Timeout</pre></body></html>');
  }

  public function apply() {
    $args = func_get_args();

    if (count($args) < 1) {
      return $this;
    }

    if (count($args) === 1 && gettype($args[0]) === 'string') {
      throw new Exception('callback is missing');
    }

    if (count($args) > 1 && gettype($args[0]) === 'string') {
      $expression = array_splice($args, 0, 1);
      $this->all($expression[0], $args);
      return $this;
    }

    $shouldValidType = require __DIR__ . '/utils/shouldValidType.php';

    foreach ($args as &$arg) {
      $shouldValidType($arg, ['Router\\Router', 'Router\\Component\\Route', 'Closure', 'array']);

      $type = is_array($arg) ? 'array' : get_class($arg);

      if ($type === 'Router\\Router') {
        foreach($arg->STACK as &$instance) {
          $this->apply($instance);
        }

        continue;
      }

      if ($type === 'Router\\Component\\Route') {
        array_push($this->STACK, $arg);
        continue;
      }

      if ($type === 'array') {
        foreach($arg as &$instance) {
          $this->apply($instance);
        }

        continue;
      }

      $route = new Route($arg);
      $this->apply($route);
    }

    return $this;
  }

  public function all(string $path) {
    $args = func_get_args();
    array_splice($args, 0, 1);
    $this->registerRoute('*', $path, $args);
    return $this;
  }

  public function get(string $path) {
    $args = func_get_args();
    array_splice($args, 0, 1);
    $this->registerRoute('get', $path, $args);
    return $this;
  }

  public function post(string $path) {
    $args = func_get_args();
    array_splice($args, 0, 1);
    $this->registerRoute('post', $path, $args);
    return $this;
  }

  public function put(string $path) {
    $args = func_get_args();
    array_splice($args, 0, 1);
    $this->registerRoute('put', $path, $args);
    return $this;
  }

  public function patch(string $path) {
    $args = func_get_args();
    array_splice($args, 0, 1);
    $this->registerRoute('patch', $path, $args);
    return $this;
  }

  public function delete(string $path) {
    $args = func_get_args();
    array_splice($args, 0, 1);
    $this->registerRoute('delete', $path, $args);
    return $this;
  }

  public function views(string $path) {
    $fixPath = require __DIR__ . '/utils/fixPath.php';
    $path = $fixPath($path);

    if (!is_dir($path)) {
      throw new Exception('directory not exists "' . $path . '"');
    }

    $this->VIEWS = $path;
    return $this;
  }

  public function serve(string $path, string $prefix = '/') {
    $fixPath = require __DIR__ . '/utils/fixPath.php';
    $path = $fixPath($path);

    if (!is_dir($path)) {
      throw new Exception('directory not exists "' . $path . '"');
    }

    $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path));
    $blacklist = ['php', 'htaccess'];

    $prefix = trim($prefix, '/');
    $prefix = '/' . $prefix;

    if ($prefix !== '/') {
      $prefix .= '/';
    }

    foreach ($files as $file) {
      if (is_dir($file)) {
        continue;
      }

      $extension = strtolower($file->getExtension());

      if (in_array($extension, $blacklist)) {
        continue;
      }

      $filePath = $file->getPathName();
      $filePathExp = str_replace(DIRECTORY_SEPARATOR, '/', $file->getPathName());
      $_path = str_replace(DIRECTORY_SEPARATOR, '\/', $filePath);
      $_path = preg_replace('/^' . str_replace(DIRECTORY_SEPARATOR, '\/', $path) . '/', '', $filePathExp);
      $_path = str_replace(DIRECTORY_SEPARATOR, '/', $_path);
      $_path = trim($_path, '/');
      $_path = $prefix . $_path;

      $this->get($_path, function ($_, $response) use ($filePath) {
        
        if (!ROUTER_CONFIG_CACHING_ENABLED) {
          $response
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache');
        } else {
          $response->header('Cache-Control', 'max-age=' . ROUTER_CONFIG_CACHING_MAX_AGE);
        }

        $response->file($filePath);
      });
    }

    return $this;
  }

  private function registerRoute($method, &$path, &$callbacks) {
    foreach($callbacks as &$callback) {
      if (is_array($callback)) {
        $this->registerRoute($method, $path, $callback);
        continue;
      }

      $route = new Route($callback, $method, $path);
      array_push($this->STACK, $route);
    }
  }

  private function next (&$index) {
    if (ob_get_level()) {
      ob_clean();
    }

    if ($index + 1 > count($this->STACK)) {
      $this->RESPONSE->status(404)->html('<!docytpe html><html><head><title>Error</title></head><body><pre>Cannot '. $this->REQUEST->method . ' ' . $this->REQUEST->uri . '</pre></body></html>');
      return;
    }

    $target = $this->STACK[$index];
    $index++;
    $next = function () use (&$index) { $this->next($index); };

    if ($target->method === null && $target->path === null) {
      $target->activate($this->REQUEST, $this->RESPONSE, $next);
      return;
    }

    if ($target->matched($this->REQUEST->method, $this->REQUEST->uri)) {
      $target->activate($this->REQUEST, $this->RESPONSE, $next);
      return;
    }

    $next();

  }

  public static function getConfig(string $key) {
    $key = strtoupper(trim($key));
    $fixPath = require __DIR__ . '/utils/fixPath.php';
    $configPath = $fixPath(__DIR__ . '/../router.ini');

    if (!file_exists($configPath)) {
      throw new Exception('missing router configuration file');
    }

    $config = parse_ini_file($configPath);
    return array_key_exists($key, $config) ? $config[$key] : null;
  }

}