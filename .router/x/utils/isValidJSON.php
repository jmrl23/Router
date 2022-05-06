<?php

return function ($json) : bool {

  $jsonType = gettype($json);

  if ($jsonType === 'object' || $jsonType === 'array') {
    return true;
  }

  if ($jsonType !== 'string') {
    return false;
  }

  @json_encode($json);

  return json_last_error() === JSON_ERROR_NONE;

};