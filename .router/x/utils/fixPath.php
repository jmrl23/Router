<?php

return function (string $path) : string {

  $isAbsolutePath = require __DIR__ . '/isAbsolutePath.php'; 

  if (!$isAbsolutePath($path)) {
    throw new Exception('require an absolute path');
  }

  $path = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $path);
  $parts = array_filter(explode(DIRECTORY_SEPARATOR, $path), 'strlen');
  $absolutes = [];

  foreach ($parts as &$part) {

    if ('.' === $part) {
      continue;
    }

    if ('..' === $part) {
      array_pop($absolutes);
      continue;
    }

    array_push($absolutes, $part);

  }

  $result = implode(DIRECTORY_SEPARATOR, $absolutes);

  if (DIRECTORY_SEPARATOR === '/') {
    if (substr($result, 0, 1) !== '/') {
      $result = '/' . $result;
    }
  }

  return $result;
};
