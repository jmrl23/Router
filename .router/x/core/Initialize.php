<?php

namespace Router\Core;

use \Exception,
    Router\Router;

class Initialize {

  public static function checkConfiguration() {

    function booleanValue($value) : bool {
      if ($value === null) {
        throw new Exception('cannot get boolean value');
      }

      $value = strtolower($value);
      $t = ['1'];
      $f = ['0'];

      if (in_array($value, $t)) {
        return true;
      }

      if (in_array($value, $f)) {
        return false;
      }

      throw new Exception('cannot get boolean value');
    }

    function intValue($value) : int {
      if (!ctype_digit($value)) {
        throw new Exception('cannot get int value');
      }

      return intval($value);
    }

    function enumValue($value, $list = []) : string {
      foreach ($list as &$valid) {
        if ($value === $valid) {
          return $value;
        }
      }

      throw new Exception('invalid value');
    }

    define('ROUTER_CONFIG_WEB_ROOT',        booleanValue(Router::getConfig('WEB_ROOT')));
    define('ROUTER_CONFIG_CACHING_ENABLED', booleanValue(Router::getConfig('CACHING_ENABLED')));
    define('ROUTER_CONFIG_CACHING_MAX_AGE', intValue(Router::getConfig('CACHING_MAX_AGE')));
    define('ROUTER_CONFIG_CONTENT_TYPE',    Router::getConfig('CONTENT_TYPE'));
  }

  public static function useConfiguration() {

    if (!ROUTER_CONFIG_CONTENT_TYPE) {
      header('Content-Type: text/html; charset=UTF-8');
    } else {
      header('Content-Type: ' . ROUTER_CONFIG_CONTENT_TYPE);
    }
    
  }

}