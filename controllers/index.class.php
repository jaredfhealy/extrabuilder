<?php
class ExtrabuilderIndexManagerController extends modExtraManagerController {
    public function process(array $scriptProperties = array()) {
		// Define package name and rootDir
		$rootDir = dirname(__FILE__, 2);
		$package = basename($rootDir);

		// Get the template contents
		$output = file_get_contents("{$rootDir}/templates/index.html");

		// Check for the loadapp parameter
		$loadApp = isset($_GET['loadapp']) ? $_GET['loadapp'] : '';

		// Add on the script template with the vuejs contents
		$output .= '<script type="text/x-template" id="appjs">'.file_get_contents("{$rootDir}/templates/{$loadApp}.js").'</script>';

		// Replace URL placeholders with URLs based on globals
		$output = str_replace('[[+iframe_src_url]]', MODX_ASSETS_URL."components/{$package}/{$loadApp}.html", $output);
		$output = str_replace('[[+connector_url]]', MODX_ASSETS_URL."components/{$package}/connector.php", $output);

		// Return the final html output
        return $output;
    }
    
    /**
     * The pagetitle to put in the <title> attribute.
     * @return null|string
     */
    public function getPageTitle() {
		return "Extrabuilder";
    }
}