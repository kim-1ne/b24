<?php

use Kim1ne\B24\Application\Configuration;
use Kim1ne\B24\Service\UserField\UserFieldService;

/**
 * @var Configuration $this
 */

$this->setLazyService(UserFieldService::class, function () {
    return UserFieldService::getInstance();
});
