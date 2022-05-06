<?php

$isValidRefType = function (&$ref, &$validType) : bool {
  $refType = gettype($ref);

  if ($refType !== 'object') {
    return $refType === $validType;
  }

  return get_class($ref) === $validType;
};

return function (&$ref, $type) use (&$isValidRefType) {

  $typeType = gettype($type);

  if ($typeType !== 'string' && $typeType !== 'array') {
    throw new Exception('should only provide a string or array of valid types for parameter 2');
  }

  if (!is_array($type)) {
    if (!$isValidRefType($ref, $type)) {
      throw new Exception('only accepts type or class instance of '. $type);
    }
    return;
  }

  foreach ($type as &$t) {
    $typeType = gettype($t);

    if ($typeType !== 'string') {
      throw new Exception('error type list supplied a non-string item');
    }

  }

  foreach ($type as $index => &$t) {
    $typeType = gettype($t);

    if ($isValidRefType($ref, $t)) {
      return;
    }

    if ($index + 1 >= count($type)) {
      throw new Exception('only accepts type or class instance of "' . implode(', ', $type) . '"');
    }
  }

};