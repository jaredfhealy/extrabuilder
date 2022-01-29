<?php

namespace ExtraBuilder\Processors\ebTransport;

use MODX\Revolution\Processors\Processor;
use MODX\Revolution\modX;

/**
 * Handle all build options
 *
 */
class Actions extends Processor
{
    public $languageTopics = array('extrabuilder:default');
    public $objectType = 'extrabuilder.transport';
    public $logMessages = [];
    
	/** @var ExtraBuilder\ExtraBuilder $eb */
	public $eb;

	/** @var object Transport record */
	public $transport;
	
	/** @var object Package record */
	public $package;

	/** @var string Resolvers directory */
	public $resolversDir;

	/**
	 * Config for the current package being built
	 * 
	 * @var array $packageConfig
	 */
	public $packageConfig = [];

    /**
     * Override the process function
     *
     */
    public function process()
    {
        // Return error if we don't have our service class
		if (!$this->modx->eb) {
			return $this->failure('Service Class is not defined. Validate connector.php is correct.');
		}
		else {
			// Store a reference to our service class that was loaded in 'connector.php'
			$this->eb =& $this->modx->eb;
		}

		// Get the passed in Transport object or return failure
		$id = $this->getProperty('id');
        if (!$this->transport = $this->modx->getObject($this->eb->getClass('ebTransport'), $id)) {
            return $this->failure('Unable to get the Transport object record');
        }

        // Get the related Package or return failure
        $this->package = $this->modx->getObject($this->eb->getClass('ebPackage'), $this->transport->get('package'));
        if (!$this->package) {
            return $this->failure('Unable to get the related Package object record.');
        }

		// Get the configuration for this package
        $this->packageConfig = $this->eb->getPackageConfig($this->package);
		if ($this->packageConfig === false) {
			return $this->failure("Failed to get package configuration paths.");
		}

		// Determine the resolvers directory and paths
        $this->resolversDir = $this->packageConfig['corePath'] . '_build/resolvers/';

        // Check if the directory exists
        if (!is_dir($this->resolversDir)) {
            mkdir($this->resolversDir, 0775, true);
        }

        // Determine the action
		$action = $this->getProperty('subAction');
		if ($action) {
			switch ($action) {
				case 'addResolver':
					return $this->addResolver();
				break;

				case 'addTablesResolver':
					return $this->addTablesResolver();
				break;

				case 'addRemoveTablesResolver':
					return $this->addRemoveTablesResolver();
				break;

				default:
					return $this->failure("Unable to determine the action needed.");
				break;
			}
		}
	}

	/**
	 * Add a resolver to the build directory
	 */
	public function addResolver ()
	{
		// Get the passed in filename
        $filename = $this->getProperty('filename', 'example');
        $filename = $this->resolversDir . "{$filename}.php";

		// If the file doesn't exist yet
        if (!is_file($filename)) {
			// Determine the source file and that it exists
			$source = $this->eb->config['resolversPath'] . '_example.php';
            if (is_file($source)) {
				// Copy the source to the new file
				copy($source, $filename);
                return $this->success("Resolver created successfully: $filename <br/><u>Update the file directly to add your resolver code.</u>");
            } 
			else {
                return $this->failure("Unable to create file: $filename <br/><u>Please validate permissions in the directory</u>.");
            }
        } 
		else {
            return $this->failure("That file already exists.<br/>$filename<br/>To avoid unintentional data loss, please remove manually.");
        }
	}

	/**
	 * Add the standard "tables" resolver
	 * 
	 * This resolver is responsible for creating or updating tables,
	 * fields, and indexes when the package is installed/updated.
	 */
	public function addTablesResolver () 
	{
		// Determine the tables resolver source path
        $tablesResolver = $this->eb->config['resolversPath'] . 'tables.php';
        if (is_file($tablesResolver)) {
			// Define the destination file path
            $destinationFile = $this->resolversDir . 'tables.php';

			// If the file doesn't exist yet
            if (!is_file($destinationFile)) {
                if (copy($tablesResolver, $destinationFile)) {
                    return $this->success("Resolver created successfully:<br/>$destinationFile<br/><u>Update the file directly to add your resolver code.</u>");
                } 
				else {
                    return $this->failure("Unable to create file:<br/>$destinationFile<br/><u>Please validate permissions in the directory</u>.");
                }
            } 
			else {
                return $this->failure("That file already exists.<br/>$destinationFile<br/>To avoid unintentional data loss, please remove manually.");
            }
        } 
		else {
            return $this->failure("The source file appears to be missing:<br/>$tablesResolver");
        }
	}

	/**
	 * Add the standard "remove tables" resolver
	 * 
	 * This resolver is responsible for dropping all tables,
	 * fields, and indexes when the package is uninstalled.
	 */
	public function addRemoveTablesResolver () 
	{
		// Determine the remove tables resolver source path
        $removeTablesResolver = $this->eb->config['resolversPath'] . 'uninstall/remove_tables.php';
        if (is_file($removeTablesResolver)) {
			// Define the destination file path
            $destinationFile = $this->resolversDir . 'remove_tables.php';

			// If the file doesn't exist yet
            if (!is_file($destinationFile)) {
                if (copy($removeTablesResolver, $destinationFile)) {
                    return $this->success("Resolver created successfully:<br/>$destinationFile<br/><u>Update the file directly to add your resolver code.</u>");
                } 
				else {
                    return $this->failure("Unable to create file:<br/>$destinationFile<br/><u>Please validate permissions in the directory</u>.");
                }
            } 
			else {
                return $this->failure("That file already exists.<br/>$destinationFile<br/>To avoid unintentional data loss, please remove manually.");
            }
        } 
		else {
            return $this->failure("The source file appears to be missing:<br/>$removeTablesResolver");
        }
	}
}