<?php
class ExtrabuilderTransportManagerController extends modExtraManagerController {
    public function process(array $scriptProperties = array()) {
		// Get the template contents
		$output = file_get_contents(dirname(dirname(__FILE__)) . '/templates/transport.html');

		// Add on the script template with the vuejs contents
		$output .= '<script type="text/x-template" id="extrabuilderjs">'.file_get_contents(dirname(dirname(__FILE__)) . '/templates/transport-builder.js').'</script>';

		// Replace URL placeholders with URLs based on globals
		$output = str_replace('[[+iframe_src_url]]', MODX_ASSETS_URL.'components/extrabuilder/transport-builder.html', $output);
		$output = str_replace('[[+connector_url]]', MODX_ASSETS_URL.'components/extrabuilder/connector.php', $output);

		// Return the final html output
		return $output;
    }
    
    /**
     * The pagetitle to put in the <title> attribute.
     * @return null|string
     */
    public function getPageTitle() {
        return 'Packages and Custom Tables';
    }
}