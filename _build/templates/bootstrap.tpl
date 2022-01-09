<?php

/**
 * @var \MODX\Revolution\modX $modx
 * @var array $namespace
 */

use {$namespace}\{$namespace};
use xPDO\xPDO;

// Add the service
try {
    // Load the classes
    $loader = $modx::getLoader();
    $loader->addPsr4('{$namespace}\\', $namespace['path'].'src/');

    if (class_exists('{$namespace}\\{$namespace}')) {
        $modx->services->add('{$namespace}', function($c) use ($modx) {
            return new {$namespace}($modx);
        });
    }
    else {
        $modx->log(xPDO::LOG_LEVEL_ERROR, "Class {$namespace}\\{$namespace} does not exist.");
    }
}
catch (\Exception $e) {
    $modx->log(xPDO::LOG_LEVEL_ERROR, $e->getMessage());
}