<?php

require_once __DIR__ . '/x/modules/PathToRegexp.php';
require_once __DIR__ . '/x/Router.php';
require_once __DIR__ . '/x/components/Route.php';
require_once __DIR__ . '/x/components/Request.php';
require_once __DIR__ . '/x/components/Response.php';
require_once __DIR__ . '/x/core/Initialize.php';

Router\Core\Initialize::checkConfiguration();
Router\Core\Initialize::useConfiguration();