<?php

return function (string &$path) {

    return $path[0] === DIRECTORY_SEPARATOR || 
            preg_match('~\A[A-Z]:(?![^/\\\\])~i',$path) > 0;

};