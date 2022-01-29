<?php

/**
 * @var \MODX\Revolution\modX $modx
 * @var array $namespace
 */

use ExtraBuilder\ExtraBuilder;
use xPDO\xPDO;

// Add the service
try {
    // Add the package and model classes
    $modx->addPackage('ExtraBuilder\Model', $namespace['path'] . 'src/', null, 'ExtraBuilder\\');

    if (class_exists('ExtraBuilder\\ExtraBuilder')) {
        $modx->services->add('ExtraBuilder', function($c) use ($modx) {
            return new ExtraBuilder($modx);
        });
    }
    else {
        $modx->log(xPDO::LOG_LEVEL_ERROR, "Class ExtraBuilder\\ExtraBuilder does not exist.".print_r($loader->getPrefixesPsr4(), true));
    }
}
catch (\Exception $e) {
    $modx->log(xPDO::LOG_LEVEL_ERROR, $e->getMessage());
}