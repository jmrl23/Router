<?php

namespace Router\Component;

use \Exception;

class Response {

  private $ref;

  public function __construct(&$ref) {
    
    $shouldValidType = require __DIR__ . '/../utils/shouldValidType.php';
    $shouldValidType($ref, 'Router\\Router');
    $this->ref = $ref;

  }

  public function status(int $code) {
    $validStatusCodes = ['100', '101', '200', '201', '202', '203', '204', '205', '206', '300', '301', '302', '303', '304', '305', '306', '307', '400', '401', '402', '403', '404', '405', '406', '407', '408', '409', '410', '411', '412', '413', '414', '415', '416', '417', '500', '501', '502', '503', '504', '505'];
    if (!in_array($code, $validStatusCodes)) {
      throw new Exception('invalid status code');
    }
    http_response_code($code);
    return $this;
  }

  public function header(string $key, string $value = '') {
    header($key . ': ' . $value);
    return $this;
  }

  public function removeHeader(string $key) {
    header_remove($key);
    return $this;
  }

  public function end(string $input = '', $contentType = '') {
    if ($contentType) {
      $this->header('Content-Type', $contentType);
    }
    exit($input);
  }

  public function text(string $text) {
    $this->end($text, 'text/plain;charset=UTF-8');
  }

  public function html(string $input) {
    $this->end($input, 'text/html;charset=UTF-8');
  }

  public function json($input) {
    $isValidJSON = require __DIR__ . '/../utils/isValidJSON.php';
    
    if (!$isValidJSON($input)) {
      throw new Exception('invalid json');
    }

    if (gettype($input) === 'array' || gettype($input) === 'object') {
      $this->end(json_encode($input, JSON_PRETTY_PRINT), 'application/json;charset=UTF-8');
    }

    $this->end($input, 'application/json;charset=UTF-8');
  }

  public function file(string $path) {
    $fixPath = require __DIR__ . '/../utils/fixPath.php';
    $getMimeType = require __DIR__ . '/../utils/getMimeType.php';
    $path = $fixPath($path);

    if (!file_exists($path)) {
      throw new Exception('file not found "' . $path . '"');
    }

    $this->end(file_get_contents($path), $getMimeType($path));
  }

  public function render(string $template, array $data = []) {
    if (!$this->ref->VIEWS) {
      throw new Exception('views directory not yet set');
    }

    $fixPath = require __DIR__ . '/../utils/fixPath.php';
    $_db74a4b7966f444eb87d3346be8e44d7 = json_decode(json_encode($this->ref->REQUEST), true);
    $_90e54aeed33743c5b473ebd563b08b4e = $fixPath($this->ref->VIEWS . '/' . $template);
    $_b132297860634183981adc07cf23e182 = $data;

    call_user_func(function () use (
      &$_db74a4b7966f444eb87d3346be8e44d7,
      &$_90e54aeed33743c5b473ebd563b08b4e,
      &$_b132297860634183981adc07cf23e182
    ) {
      header('Content-Type: text/html;charset=UTF-8');
      extract($_db74a4b7966f444eb87d3346be8e44d7);
      extract($_b132297860634183981adc07cf23e182);
      require $_90e54aeed33743c5b473ebd563b08b4e;
      exit;
    });
  }

  public function redirect(string $path) {
    $baseURL = $this->ref->REQUEST->baseURL;
    $this->header('location', $baseURL . ltrim($path, '/'));
    exit;
  }

}
