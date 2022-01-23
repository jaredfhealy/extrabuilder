<?php
/** @var xPDOTransport $transport */
/** @var array $options */
/** @var modX $modx */
if ($transport->xpdo) {
	$modx =& $transport->xpdo;

	switch ($options[xPDOTransport::PACKAGE_ACTION]) {
		case xPDOTransport::ACTION_UNINSTALL:
			// Store the core path and version
			$packageKey = '{package_key}';
			$keyLower = strtolower($packageKey);
			$corePath = MODX_CORE_PATH . "components/{$keyLower}/";
			$isV3 = $modx->getVersionData()['version'] >= 3;

			// If this is 2.x
			if (!$isV3) {
				$modx->addPackage("$keyLower.v2.model", MODX_CORE_PATH.'components/');
			}
			else {
				// Check for the bootstrap file and include it
				if (file_exists($corePath.'bootstrap.php')) {
					require $corePath.'bootstrap.php';
				}
			}

			// Get the manager
			$manager = $modx->getManager();

			// Classes array is dynamically replaced on Transport build
			$classes = $classesPlaceholder;
			
			// Only proceed if the json has been replaced
			foreach ($classes as $index => $class) {
				// If this is not v3 check the classes
				if(!$isV3) {
					// If they are namespaced, remove them
					if (strpos($class, '\\\\') !== false) {
						$class = end(explode('\\\\', $class));
					}
				}
				
				// Remove the class/table
				$manager->removeObjectContainer($class);
			}
			
		break;
    }
}

return true;