<?php

/**
 * @var \MODX\Revolution\modX $modx
 * @var array $namespace
 */

use ExtraBuilder\ExtraBuilder;
use xPDO\xPDO;

// Add the service
try {
    // Load the classes
    $loader = $modx::getLoader();
    $loader->addPsr4('ExtraBuilder\\', $namespace['path'].'src/');

    if (class_exists('ExtraBuilder\\ExtraBuilder')) {
        $modx->services->add('ExtraBuilder', function($c) use ($modx) {
            return new Extrabuilder($modx);
        });
    }
    else {
        $modx->log(xPDO::LOG_LEVEL_ERROR, "Class ExtraBuilder\\ExtraBuilder does not exist.");
    }
}
catch (\Exception $e) {
    $modx->log(xPDO::LOG_LEVEL_ERROR, $e->getMessage());
}