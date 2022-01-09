<?php

namespace ExtraBuilder;

use ExtraBuilder\Model\ebPackage;
use ExtraBuilder\Model\ebObject;
use ExtraBuilder\Model\ebField;
use ExtraBuilder\Model\ebRel;
use ExtraBuilder\Model\ebTransport;
use MODX\Revolution\modX;

/**
 * MODX 3.x ExtraBuilder class
 *
 * Main script file for utilities and functions.
 *
 */
class ExtraBuilder
{

    /** @var \modX $modx A reference to the modX object. */
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

    public function __construct(modX &$modx, $config = [])
    {
        /** The MODX object. */
        $this->modx = &$modx;

        // Get our core and asset paths
        // These properties are set if we're developing outside of "core"
        $basePath = $this->modx->getOption('extrabuilder.core_path', $config, $this->modx->getOption('core_path') . 'components/ExtraBuilder/');
        $assetsUrl = $this->modx->getOption('extrabuilder.assets_url', $config, $this->modx->getOption('assets_url') . 'components/ExtraBuilder/');
        $assetsPath = $this->modx->getOption('extrabuilder.assets_path', $config, $this->modx->getOption('assets_path'));

        // As part of 3.x structure, we'll store all Processors, Elements, Templates, etc in our 'src/' directory
        $srcPath = $basePath . 'src/';

        // Merge/combine all paths into our config
        $this->config = array_merge([
            // Set our core and asset paths
            'corePath' => $basePath,
            'assetsPath' => $assetsPath . 'ExtraBuilder/',
            'assetsUrl' => $assetsUrl,

            // Set our asset and public paths
            'jsUrl' => $assetsUrl . 'js/',
            'cssUrl' => $assetsUrl . 'css/',

            // Add our srce and model paths
            'srcPath' => $srcPath,
            'modelPath' => $srcPath . 'Model/',

            // Set our controllors(connectors), processor, and template paths
            'connectorUrl' => $assetsUrl . 'connector.php',
            'connector_url' => $assetsUrl . 'connector.php',
            'processorsPath' => $srcPath . 'Processors/',
            'templatesPath' => $srcPath . 'Templates/',

			// Define a lexicon key
			'lexiconKey' => 'extrabuilder',

			// Define a service key to access modx->serviceKey
			'serviceKey' => 'eb'
        ], $config);

		// Populate the data model
		$this->populateModel();
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
			'ebRel' => $this->getRelModel()
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
		return array_merge([
			'class' => $className,
			'parentClass' => "",
			'childClass' => 'ebObject',
			'fieldDefaults' => $this->modx->getFields($className),
			'fieldMeta' => $this->modx->getFieldMeta($className),
			'gridfields' => ['id', 'display', 'package_key', 'version', 'sortorder'],
			'searchFields' => ['display', 'package_key', 'version'],
			'rowActionDescription' => "Manage Objects",
			'tabDisplayField' => 'package_key'
		], $this->cmpDefault);
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
			'parentField' => 'package',
			'childClass' => 'ebField',
			'fieldDefaults' => $this->modx->getFields($className),
			'fieldMeta' => $this->modx->getFieldMeta($className),
			'gridfields' => ['id', 'class', 'table_name', 'sortorder'],
			'searchFields' => ['class', 'table_name'],
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
			'parentField' => 'object',
			'childClass' => '',
			'fieldDefaults' => $this->modx->getFields($className),
			'fieldMeta' => $this->modx->getFieldMeta($className),
			'gridfields' => ['id', 'column_name', 'dbtype', 'phptype', 'default', 'sortorder'],
			'searchFields' => ['column_name', 'dbtype', 'phptype', 'default'],
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
			'parentField' => 'object',
			'childClass' => '',
			'fieldDefaults' => $this->modx->getFields($className),
			'fieldMeta' => $this->modx->getFieldMeta($className),
			'gridfields' => ['id', 'column_name', 'dbtype', 'phptype', 'default', 'sortorder'],
			'searchFields' => ['column_name', 'dbtype', 'phptype', 'default'],
			'rowActionDescription' => "Manage Rels",
			'tabDisplayField' => 'column_name'
		], $this->cmpDefault);
	}

	/**
	 * Get the class to use for things like getObject/newObject
	 * 
	 * Based on system version return the correct class format.
	 */
	public function getClass($classShort)
	{
		// Use our model to return the correct class
		return $this->model[$classShort]['class'] ?: "" ;
	}

    /**
     * Testing function to validate our service
     *
     * @param string $person The person to greet
     *
     * @return string The concatentated message
     */
    public function testGreeting($person)
    {
        return "I greet you: $person";
    }
}
