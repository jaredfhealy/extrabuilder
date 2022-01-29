<?php

//v3 only
namespace ExtraBuilder;

use ExtraBuilder\Model\ebPackage;
use ExtraBuilder\Model\ebObject;
use ExtraBuilder\Model\ebField;
use ExtraBuilder\Model\ebRel;
use ExtraBuilder\Model\ebTransport;
use MODX\Revolution\modX;
use xPDO;
//v3 only

/**
 * ExtraBuilder main service class
 *
 * Main script file for utilities and functions.
 *
 */
class ExtraBuilder
{

    /** @var MODX\Revolution\modX $modx A reference to the modX object. */
    public $modx = null;

    /** @var array Configuration details */
    public $config = [];

	/** @var array Data model and config */
	public $model = [];

	/** @var array Default object structure for javascript components */
	public $cmpDefault = [
		'grid' => [
			'config' => ['temp' => ''],
			'overrides' => ['temp' => '']
		],
		'tab' => [
			'config' => ['temp' => '']
		],
		'window' => [
			'create' => ['temp' => ''],
			'upcate' => ['temp' => '']
		],
		'data' => ['selectedId' => 0]
	];

	/** @var boolean If this is MODX 3 */
	public $isV3 = false;

	/**
	 * Major version number of MODX
	 * 
	 * @var int $version
	 */
	public $version;

    public function __construct(modX &$modx, $config = [])
    {
        /** The MODX object. */
        $this->modx = &$modx;

        // Get our core and asset paths
        // These properties are set if we're developing outside of "core"
        $basePath = $this->modx->getOption('extrabuilder.core_path', $config, $this->modx->getOption('core_path') . 'components/extrabuilder/');
        $assetsUrl = $this->modx->getOption('extrabuilder.assets_url', $config, $this->modx->getOption('assets_url') . 'components/extrabuilder/');
        $assetsPath = $this->modx->getOption('extrabuilder.assets_path', $config, $this->modx->getOption('assets_path'));

        // As part of 3.x structure, we'll store all Processors, Elements, Templates, etc in our 'src/' directory
        $srcPath = $basePath . 'src/';

        // Merge/combine all paths into our config
        $this->config = array_merge([
            // Set our core and asset paths
            'corePath' => $basePath,
            'assetsPath' => $assetsPath . 'extrabuilder/',
            'assetsUrl' => $assetsUrl,

            // Set our asset and public paths
            'jsUrl' => $assetsUrl . 'js/',
            'cssUrl' => $assetsUrl . 'css/',

            // Add our srce and model paths
            'srcPath' => $srcPath,
            'modelPath3' => $srcPath . 'Model/',
			'modelPath2' => $basePath . 'v2/model/',

            // Set our controllors(connectors), processor, and template paths
            'connectorUrl' => $assetsUrl . 'connector.php',
            'connector_url' => $assetsUrl . 'connector.php',
            'processorsPath3' => $srcPath . 'Processors/',
			'processorsPath2' => $basePath . 'v2/processors/',
            'templatesPath' => $basePath . 'templates/',

			// Build paths
			'buildPath' => $basePath . '_build/',
			'resolversPath' => $basePath . '_build/resolvers/',

			// Define a lexicon key
			'lexiconKey' => 'extrabuilder',

			// PHP Namespace for classes
			'phpNamespace' => 'ExtraBuilder', 

			// Define a service key to access modx->serviceKey
			'serviceKey' => 'eb',

			// Default install to false
			'install' => false
        ], $config);

		// Set our v3 check
		$v = $this->modx->getVersionData();
		$this->version = $v['version'];
		$this->isV3 = $v['version'] >= 3;

		// For v2, if this is not the initial install, call add package
		if (!$this->isV3 && $this->config['install'] === false) {
			// Call add package since there is no bootstrap feature in v2
			$result = $this->modx->addPackage("extrabuilder.v2.model", MODX_CORE_PATH.'components/');
		}
		
		if ($this->config['install'] === false) {
			// Populate the data model if this is not during installation
			$this->populateModel();
		}
    }

    /**
     * Get field list for a give object/table to display in our grid
     *
     * @param string $modelClass The model class to return
     * @return array The array of field names
     */
    private function populateModel()
    {
		// Define our fields to include
        $this->model = [
            'ebPackage' => $this->getPackageModel(),
			'ebObject' => $this->getObjectModel(),
			'ebField' => $this->getFieldModel(),
			'ebRel' => $this->getRelModel(),
			'ebTransport' => $this->getTransportModel()
        ];
    }

	/**
	 * Model and functions for ebPackage
	 * 
	 * When called, populates the model array with all the details
	 * specific to this class.
	 */
	public function getPackageModel()
	{
		// Store the classname
		$className = ebPackage::class;
		
		// Return the model
		$model = [
			'class' => $className,
			'parentClass' => "",
			'parentField' => "",
			'childClass' => 'ebObject',
			'fieldDefaults' => $this->modx->getFields($className),
			'fieldMeta' => $this->modx->getFieldMeta($className),
			'gridfields' => ['id', 'display', 'package_key', 'version', 'sortorder'],
			'searchFields' => ['display', 'package_key', 'version'],
			'rowActionDescription' => "Manage Objects",
			'tabDisplayField' => 'package_key'
		];

		// Set the version default for packages
		if ($this->isV3) {
			$model['fieldDefaults']['version'] = '3.0';
		}
		else {
			$model['fieldDefaults']['version'] = '1.1';
		}

		// Return the merged array
		return array_merge($model, $this->cmpDefault);
	}

	/**
	 * Model and functions for ebObject
	 * 
	 * When called, populates the model array with all the details
	 * specific to this class.
	 */
	public function getObjectModel()
	{
		// Store the classname
		$className = ebObject::class;
		
		// Return the model
		return array_merge([
			'class' => $className,
			'parentClass' => "ebPackage",
			'parentField' => 'Package',
			'childClass' => 'ebField',
			'fieldDefaults' => $this->modx->getFields($className),
			'fieldMeta' => $this->modx->getFieldMeta($className),
			'gridfields' => ['id', 'class', 'table_name', 'sortorder'],
			'searchFields' => ['class', 'table_name', 'raw_xml'],
			'rowActionDescription' => "Manage Fields",
			'tabDisplayField' => 'class'
		], $this->cmpDefault);
	}

	/**
	 * Model and functions for ebObject
	 * 
	 * When called, populates the model array with all the details
	 * specific to this class.
	 */
	public function getFieldModel()
	{
		// Store the classname
		$className = ebField::class;
		
		// Return the model
		return array_merge([
			'class' => $className,
			'parentClass' => "ebObject",
			'parentField' => 'Object',
			'childClass' => '',
			'fieldDefaults' => $this->modx->getFields($className),
			'fieldMeta' => $this->modx->getFieldMeta($className),
			'gridfields' => ['id', 'column_name', 'dbtype', 'phptype', 'index', 'default', 'sortorder'],
			'searchFields' => ['column_name', 'dbtype', 'phptype', 'index', 'default'],
			'rowActionDescription' => "Manage Rels",
			'tabDisplayField' => 'column_name'
		], $this->cmpDefault);
	}

	/**
	 * Model and functions for ebRel
	 * 
	 * When called, populates the model array with all the details
	 * specific to this class.
	 */
	public function getRelModel()
	{
		// Store the classname
		$className = ebRel::class;
		
		// Return the model
		return array_merge([
			'class' => $className,
			'parentClass' => "ebObject",
			'parentField' => 'Object',
			'childClass' => '',
			'fieldDefaults' => $this->modx->getFields($className),
			'fieldMeta' => $this->modx->getFieldMeta($className),
			'gridfields' => ['id', 'alias', 'class', 'local', 'foreign', 'cardinality', 'owner', 'sortorder'],
			'searchFields' => ['alias', 'class', 'cardinality', 'local', 'foreign', 'owner'],
			'rowActionDescription' => "Manage Rels",
			'tabDisplayField' => 'column_name'
		], $this->cmpDefault);
	}

	/**
	 * Model and functions for ebTransport
	 * 
	 * When called, populates the model array with all the details
	 * specific to this class.
	 */
	public function getTransportModel()
	{
		// Store the classname
		$className = ebTransport::class;
		
		// Return the model
		return array_merge([
			'class' => $className,
			'parentClass' => "",
			'parentField' => '',
			'childClass' => '',
			'fieldDefaults' => $this->modx->getFields($className),
			'fieldMeta' => $this->modx->getFieldMeta($className),
			'gridfields' => ['id', 'category', 'attributes', 'package', 'major', 'minor', 'release', 'release_index', 'patch', 'sortorder'],
			'searchFields' => ['category', 'attributes', 'package', 'default'],
			'rowActionDescription' => "Manage Transports",
			'tabDisplayField' => 'major'
		], $this->cmpDefault);
	}

	/**
	 * Get the class to use for things like getObject/newObject
	 * 
	 * Based on system version return the correct class format.
	 */
	public function getClass($classShort)
	{
		// If it's not v3 we don't need the namespaced class
		if (!$this->isV3) {
			return $classShort;
		}

		// Result class
		$result = '';

		// Check if this is an EB class
		if (array_key_exists($classShort, $this->model)) {
			// Use our model to return the correct class
			$result = $this->model[$classShort]['class'];
		}
		
		// Search the classMap that MODX generates for a match
		foreach ($this->modx->classMap as $classes) {
			foreach ($classes as $class) {
				if (strpos($class, $classShort)) {
					$result = $class;
				}
			}
		}

		// If it's still not found, check for edge cases
		if (empty($result)) {
			// Map edge cases
			$map = [
				'modPackageBuilder' => 'MODX\Revolution\Transport\modPackageBuilder',
				'xPDOTransport' => 'xPDO\Transport\xPDOTransport'
			];
			$result = isset($map[$classShort]) ? $map[$classShort] : '';
		}

		// Set the default return
		$result = $result ?: $classShort;

		// Debug log
		$this->logDebug("EB->getClass: $classShort, returned $result");

		// Return the result
		return $result;
	}

	/**
	 * Get config paths for this package
	 * 
	 * Calculates the paths based on standard or override
	 * if specified.
	 * 
	 * @param object $package The package object
	 * @return array Config array of paths
	 */
	public function getPackageConfig($package)
	{
		// Default paths
		$packageConfig = [
			'corePath' => '{core_path}components/{cmp_namespace}/',
			'sourcePath' => '{core_path}components/{cmp_namespace}/src/',
			'modelPath2' => '{core_path}components/{cmp_namespace}/v2/model/',
			'modelPath3' => '{core_path}components/{cmp_namespace}/src/Model/',
			'publicAssetsPath' => '{assets_path}components/{cmp_namespace}/',
			'coreAssetsPath' => '{core_path}components/{cmp_namespace}/assets/',
			'schemaPath2' => '{core_path}components/{cmp_namespace}/v2/schema/',
			'schemaPath3' => '{core_path}components/{cmp_namespace}/schema/',
			'schemaFileName' => '{cmp_namespace}.mysql.schema.xml',
			'schemaFilePath' => '',
			'classPrefix' => '',
			'cmpNamespace' => '',
			'phpNamespace' => '',
		];

        // Get package key and paths
        $packageKey = $package->get('package_key');

		// Set the core and assets paths if different
        if ($corePath = $package->get('core_path')) {
			$packageConfig['corePath'] = $corePath;
		}
        if ($assetsPath = $package->get('assets_path')) {
			$packageConfig['publicAssetsPath'] = $assetsPath;
		}

		// Set the build namespace for v3
		if ($this->isV3) {
			// Package key should be the PHP Namespace MyComp\Model
			$packageConfig['classPrefix'] = rtrim($packageKey, '\\') . '\\';
			$packageConfig['phpNamespace'] = explode('\\', $packageKey)[0];

			// Set the component (modx) namespace
			$packageConfig['cmpNamespace'] = strtolower($packageConfig['phpNamespace']);
		}
		else {
			$packageConfig['phpNamespace'] = explode('.', $packageKey)[0];
			$packageConfig['cmpNamespace'] = strtolower($packageConfig['phpNamespace']);
		}

		// Map replacement keys for all paths
		$mapKeys = [
			'core_path' => MODX_CORE_PATH,
			'base_path' => MODX_BASE_PATH,
			'assets_path' => MODX_ASSETS_PATH,
			'package_key' => $packageKey,
			'cmp_namespace' => $packageConfig['cmpNamespace'],
			'php_namespace' => $packageConfig['phpNamespace']
		];

		// Loop through the packageConfig and replace values for each
		foreach ($packageConfig as $key => $tpl) {
			$packageConfig[$key] = $this->replaceValues($mapKeys, $tpl);
		}
		$packageConfig['schemaFilePath'] = $packageConfig['schemaPath'.$this->version] . $packageConfig['schemaFileName'];

		// Return the final config
		$this->logDebug(print_r($packageConfig, true));
		return $packageConfig;
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
	 * Replace namespaces and designated v3 only blocks
	 * 
	 * Replace necessary namespaces so that processors will run
	 * on v2.
	 */
	public function replaceOnV2Install($dir)
	{
		// Iterate recursively through directory
		$dir_iterator = new RecursiveDirectoryIterator($dir);
		$iterator = new RecursiveIteratorIterator($dir_iterator, RecursiveIteratorIterator::SELF_FIRST);

		foreach ($iterator as $file) {
			if ($file->isFile()) {
				$this->replaceClassesForV2($file->getPathName());
			}
		}
	}

	/**
	 * Replace namespaces back to v2 compatible
	 *
	 * @param string $contents
	 * @return string Modified contents
	 */
	public function replaceClassesForV2($filePath)
	{
		// If the file is invalid
		if (!is_file($filePath)) {
			$this->modx->log(xPDO::LOG_LEVEL_INFO, "replaceOnV2Install: $dir");
			$this->logError("File is invalid: $file");
			return;
		}

		// Get the file contents
		$contents = file_get_contents($filePath);
		
		// Map new namespaced classes to the old classes
		$nsMap = [
			'MODX\\Revolution\\Processors\\Processor' => 'modProcessor',
			'MODX\\Revolution\\Processors\\ModelProcessor' => 'modObjectProcessor',
			'MODX\\Revolution\\Processors\\DriverSpecificProcessor' => 'modDriverSpecificProcessor',
			'MODX\\Revolution\\Processors\\Model\\CreateProcessor' => 'modObjectCreateProcessor',
			'MODX\\Revolution\\Processors\\Model\\DuplicateProcessor' => 'modObjectDuplicateProcessor',
			'MODX\\Revolution\\Processors\\Model\\ExportProcessor' => 'modObjectExportProcessor',
			'MODX\\Revolution\\Processors\\Model\\GetListProcessor' => 'modObjectGetListProcessor',
			'MODX\\Revolution\\Processors\\Model\\GetProcessor' => 'modObjectGetProcessor',
			'MODX\\Revolution\\Processors\\Model\\RemoveProcessor' => 'modObjectRemoveProcessor',
			'MODX\\Revolution\\Processors\\Model\\SoftRemoveProcessor' => 'modObjectSoftRemoveProcessor',
			'MODX\\Revolution\\Processors\\Model\\UpdateProcessor' => 'modObjectUpdateProcessor',
			'MODX\\Revolution\\Model\\' => '',
			'MODX\\Revolution\\' => '',
			'xPDO\\Transport\\' => '',
			'xPDO\\Om\\' => '',
			'ExtraBuilder\\Model\\' => '',
		];
		
		// Loop through the nsMap
		foreach ($nsMap as $new => $old) {
			// Replace namespaces in 'use' statements
			$contents = str_replace($new, $old, $contents);

			// Replace class extends
			if (strpos($new, 'Processors') !== false) {
				$classParts = explode('\\', $new);
				$newShort = end($classParts);
				$contents = str_replace("extends $newShort", "extends $old", $contents);
			}
		}

		// Also replace any blocks wrapped in //v3 only
		$key = '//v3 only';
		$start = strpos($contents, $key);
		if ($start !== false) {
			$end = strpos($contents, $key, $start + strlen($key));
			$contents = substr_replace($contents, '', $start, $end - $start + strlen($key));
		}

		// Write the file contents back
		file_put_contents($filePath, $contents);
	}

	/**
     * Log an info message
     *
     * @param string $msg The debug message
     */
    public function logInfo($msg)
    {
        $this->modx->log(xPDO::LOG_LEVEL_INFO, $msg);
    }

    /**
     * Log a debug message
     *
     * @param string $msg The debug message
     */
    public function logDebug($msg)
    {
        $this->modx->log(xPDO::LOG_LEVEL_DEBUG, $msg);
    }

	/**
     * Log an error message
     *
     * @param string $msg The debug message
     */
    public function logError($msg)
    {
        $this->modx->log(xPDO::LOG_LEVEL_ERROR, $msg);
    }
}
