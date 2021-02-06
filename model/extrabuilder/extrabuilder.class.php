<?php
class Extrabuilder
{
	public $modx;
	public $config = array();
	public function __construct(modX &$modx, array $config = array())
	{
		$this->modx = &$modx;

		// Default paths
		$corePath = $this->modx->getOption('core_path');
		$assetsPath = $this->modx->getOption('assets_url');

		// Check for overrides
		$basePath = $this->modx->getOption('extrabuilder.core_path', $config, $corePath . 'components/extrabuilder/');
		$assetsUrl = $this->modx->getOption('extrabuilder.assets_url', $config, $assetsPath . 'components/extrabuilder/');
		$this->config = array_merge(array(
			'basePath' => $basePath,
			'corePath' => $basePath,
			'modelPath' => $basePath . 'model/',
			'processorsPath' => $basePath . 'processors/',
			'templatesPath' => $basePath . 'templates/',
			'jsUrl' => $assetsUrl . 'js/',
			'cssUrl' => $assetsUrl . 'css/',
			'assetsUrl' => $assetsUrl,
			'connectorUrl' => $assetsUrl . 'connector.php',
		), $config);

		// Add the package
		$this->modx->addPackage('extrabuilder', $this->config['modelPath']);
	}

	/**
	 * Test log for class exists
	 */
	public function logExists()
	{
		$this->modx->log(xPDO::LOG_LEVEL_ERROR, "I exiest.");
	}

	/**
	 * Utility function to replace placeholders in a URL path
	 * with any global values.
	 * @param string $path Path with placeholders {core_path}
	 * @return string The resulting real path
	 */
	public function replaceCorePaths($path, $packageKey)
	{
		// Replace the possibilities
		$path = str_replace('{core_path}', MODX_CORE_PATH, $path);
		$path = str_replace('{base_path}', MODX_BASE_PATH, $path);
		$path = str_replace('{assets_path}', MODX_ASSETS_PATH, $path);
		$path = str_replace('{package_key}', $packageKey, $path);

		// Return the final path
		return $path;
	}

	/**
	 * Replace placeholders with values
	 * @param array $objectArray The array object
	 * @param string $stringTpl String with placeholders {fieldname}
	 * @return string Template value with placeholders replaced
	 */
	public function replaceValues($objectArray, $stringTpl)
	{
		$string = $stringTpl;
		foreach ($objectArray as $key => $value) {
			$string = str_replace('{' . $key . '}', $value, $string);
		}

		// Return the new string
		return $string;
	}

	/**
	 * Delete directory recursively
	 * 
	 */
	public function rrmdir($src)
	{
		$dir = opendir($src);
		if ($dir) {
			while (false !== ($file = readdir($dir))) {
				if (($file != '.') && ($file != '..')) {
					$full = $src . '/' . $file;
					if (is_dir($full)) {
						$this->rrmdir($full);
					} else {
						unlink($full);
					}
				}
			}

			closedir($dir);
			rmdir($src);
		}
	}

	/**
	 * Utility funciton to check startswith
	 * @param string $haystack The String to check against
	 * @param string $needle The value to check for
	 * @return boolean If it starts with the passed $needle
	 */
	public function startsWith($haystack, $needle)
	{
		// Check substring
		$first = substr($haystack, 0, 1);
		return $first === $needle;
	}

	/**
	 * Recursive copy function used during the build
	 * process to copy all core folders/files into
	 * the _dist/<package_key> directory
	 */
	public function copyCore($src, $dst)
	{
		// Open the source directory
		$dir = opendir($src);

		// Make the destination directory if not exist 
		if (!is_dir($dst)) {
			mkdir($dst, 0775, true);
		}

		// Loop through
		while (false !== ($file = readdir($dir))) {
			$exclude = [
				$file === 'assets',
				$this->startsWith($file, '_'),
				$this->startsWith($file, '.'),
				$file === '.',
				$file === '..'
			];

			// If none of the checks in the array resulted in true
			if (!in_array(true, $exclude)) {
				if (is_dir($src . '/' . $file)) {
					$this->copyCore($src . '/' . $file, $dst . '/' . $file);
				} else {
					copy($src . '/' . $file, $dst . '/' . $file);
				}
			}
		}
		closedir($dir);
	}

	/**
	 * Copy directory recursively
	 */
	public function copydir($src, $dst)
	{
		// open the source directory 
		$dir = opendir($src);

		// Make the destination directory if not exist 
		if (!is_dir($dst)) {
			mkdir($dst, 0775, true);
		}

		// Loop through the files in source directory 
		while (false !== ($file = readdir($dir))) {
			if (($file != '.') && ($file != '..')) {
				if (is_dir($src . '/' . $file)) {
					// Recursively calling custom copy function 
					// for sub directory  
					$this->copydir($src . '/' . $file, $dst . '/' . $file);
				} else {
					copy($src . '/' . $file, $dst . '/' . $file);
				}
			}
		}
		closedir($dir);
	}
}
