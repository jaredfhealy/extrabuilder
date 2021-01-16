<?php
/** @var xPDOTransport $transport */
/** @var array $options */
/** @var modX $modx */
if ($transport->xpdo) {
	$modx =& $transport->xpdo;

	switch ($options[xPDOTransport::PACKAGE_ACTION]) {
		case xPDOTransport::ACTION_UNINSTALL:
			// Get the package and manager
			$modx->addPackage('{package_key}', MODX_CORE_PATH . 'components/{package_key}/model/');
			$manager = $modx->getManager();

			// Classes array is dynamically populated on Transport build
			$classJson = '{classes_array}';
			
			// Only proceed if the json has been replaced
			if (!(strlen($classJson) === 15 && strpos($classJson, 'classes_array') !== false)) {
				$classes = json_decode($classJson, true);
				foreach ($classes as $class) {
					$manager->removeObjectContainer($class);
				}
			}
			
            break;
    }
}

return true;