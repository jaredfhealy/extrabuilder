<?php
/** @var xPDOTransport $transport */
/** @var array $options */
/** @var modX $modx */
if ($transport->xpdo) {
    $modx =& $transport->xpdo;

    switch ($options[xPDOTransport::PACKAGE_ACTION]) {
		case xPDOTransport::ACTION_INSTALL:
		case xPDOTransport::ACTION_UPGRADE:
			// Commonly similar actions for install and upgrade
			break;
		case xPDOTransport::ACTION_UNINSTALL:
			// Any uninstall cleanup needed?
			break;
	}
}

return true;