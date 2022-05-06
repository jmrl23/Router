<?php

namespace Router\Component;

use Router\Router;

class Request {

  public $method, $protocol, $url, $get, $query, $post, $body, $files, $cookie, $session;
  public $baseURL, $uri;
  public $headers;

  public function __construct() {
    $this->setBasic();
    $this->setBaseURL();
    $this->setHeaders();
  }

  private function setBasic() {
    $this->protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $this->method = strtoupper($_SERVER['REQUEST_METHOD']);
    $this->url = $this->protocol. '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    $this->get = $_GET;
    $this->query = $_SERVER['QUERY_STRING'];
    $this->post = $_POST;
    $this->body = file_get_contents('php://input');
    $this->files = $_FILES;
    $this->cookie = $_COOKIE;
    $this->session  = session_status() !== PHP_SESSION_NONE ? $_SESSION : null;
  }

  private function setBaseURL() {    
    if (ROUTER_CONFIG_WEB_ROOT) {
      $this->baseURL = $this->protocol . '://' . $_SERVER['HTTP_HOST'] . '/';
      $this->uri = strtok('/' . str_replace($this->baseURL, '', $this->url), '?');
      return;
    }

    $dir = explode(DIRECTORY_SEPARATOR, dirname(__DIR__, 3));
    $base = end($dir);
    $uri = explode('/', $_SERVER['REQUEST_URI']);
    $baseIndex = array_search($base, $uri);
    array_splice($uri, $baseIndex + 1);
    $uri = implode('/', $uri);
    $this->baseURL = $this->protocol . '://' . $_SERVER['HTTP_HOST'] . $uri . '/';
    $this->uri = strtok('/' . str_replace($this->baseURL, '', $this->url), '?');
  }

  function setHeaders() {
    if (function_exists('getallheaders')) {
      $this->headers = [];
      foreach (getallheaders() as $name => &$value) {
        $this->headers[str_replace(' ', '-', ucwords(strtolower(str_replace('-', ' ', $name))))] = $value;
      }

    } else {
      $this->headers = [];
      foreach ($_SERVER as $name => &$value) {
        if (substr($name, 0, 5) == 'HTTP_') {
          $this->headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
        } else if ($name === 'CONTENT_TYPE') {
          $this->headers['Content-Type'] = $value;
        } else if ($name === 'CONTENT_LENGTH') {
          $this->headers['Content-Length'] = $value;
        }

      }
      
    }
  }

}