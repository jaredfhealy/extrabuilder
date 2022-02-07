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
			$isV3 = $modx->getVersionData()['version'] >= 3;

			// MODX 3 uses the bootstrap.php file which already called addPackage for us
			// If this is 2.x, we need to determine the path and call addPackage
			if (!$isV3) {
				$keyLower = strtolower($packageKey);
				$keyPath = "";
				$componentsDir = MODX_CORE_PATH."components/";
				
				// If this doesn't have "dots"
				if (strpos($packageKey, '.') === false) {
					// Determine the directory path, try build from 3 cross-compatible path
					$keyPath = "$keyLower/v2/model/";
					$dir = $componentsDir.$keyPath;
					
					// If that's not valid
					if (!is_dir($dir)) {
						// Try the traditional path
						$keyPath = "$keyLower/model/$keyLower";
						$dir = $componentsDir.$keyPath;
						if (!is_dir($dir)) {
							return false;
						}
					}
				}

				// If we determined a valid directory using the keyPath
				if ($keyPath) {
					// Convert keyPath to "dot" notation and overwrite
					$packageKey = str_replace('/', '.', $keyPath);
				}

				// Call addPackage with "dot" notation
				$modx->addPackage($packageKey, MODX_CORE_PATH.'components/');
			}

			// Get the manager
			$manager = $modx->getManager();

			// Classes array is dynamically replaced on Transport build
			$classes3 = $classesPlaceholder3;
			$classes2 = $classesPlaceholder2;
			$classes = $isV3 ? $classes3 : $classes2;
			
			// Only proceed if the json has been replaced
			foreach ($classes as $index => $class) {
				// Remove the class/table
				$manager->removeObjectContainer($class);
			}
			
		break;
    }
}

return true;