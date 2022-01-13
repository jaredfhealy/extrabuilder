<?php
/** @var xPDOTransport $transport */
/** @var array $options */
/** @var modX $modx */
if ($transport->xpdo) {
	$modx =& $transport->xpdo;

	switch ($options[xPDOTransport::PACKAGE_ACTION]) {
		case xPDOTransport::ACTION_UNINSTALL:
			// Store the core path
			$corePath = MODX_CORE_PATH . 'components/{package_key}/';

            // Check for the bootstrap file and include it
			if (file_exists($corePath.'bootstrap.php')) {
				require $corePath.'bootstrap.php';
			}

			// Get the manager
			$manager = $modx->getManager();

			// Classes array is dynamically replaced on Transport build
			$classes = $classesPlaceholder;
			
			// Only proceed if the json has been replaced
			foreach ($classes as $index => $class) {
				$manager->removeObjectContainer($class);
			}
			
		break;
    }
}

return true;