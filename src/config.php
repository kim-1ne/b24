<?php

use B24\Devtools\Application\Configuration;
use B24\Devtools\Service\UserField\UserFieldService;

/**
 * @var Configuration $this
 */

$this->setLazyService(UserFieldService::class, function () {
    return UserFieldService::getInstance();
});
