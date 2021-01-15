<?php

/**
 * Build the Transport
 *
 * @package extrabuilder
 * @subpackage processors.transport
 */
class ExtrabuilderAddResolverTransportProcessor extends modObjectProcessor
{
	public $classKey = 'ebTransport';
	public $languageTopics = array('extrabuilder:default');
	public $objectType = 'extrabuilder.transport';

	/**
	 * Override the process function
	 *
	 */
	public function process()
	{
		// Get the passed in Transport object or return failure
		$primaryKey = $this->getProperty($this->primaryKeyField,false);
		if (!$this->object = $this->modx->getObject($this->classKey, $primaryKey)) {
			return $this->failure('Unable to get the Transport object record');
		}

		// Get the related Package or return failure
		$this->package = $this->modx->getObject('ebPackage', $this->object->get('package'));
		if (!$this->package) {
			return $this->failure('Unable to get the related Package object record.');
		}
		
		// Copy the _build/resolvers/_example.php to the destination
		$resolversDir = $this->modx->eb->replaceCorePaths($this->package->get('core_path'), $this->package->get('package_key'));
		$resolversDir = $resolversDir.'_build/resolvers/';

		// Check if the directory exists
		if (!is_dir($resolversDir)) {
			mkdir($resolversDir, 0775, true);
		}

		// Get the passed in filename
		$filename = $this->getProperty('filename', 'example');
		$filename = $resolversDir."{$filename}.php";

		if (!is_file($filename)) {
			if (copy(MODX_CORE_PATH.'/components/extrabuilder/_build/resolvers/_example.php', $filename)) {
				return $this->success("Resolver created successfully: $filename <br/><u>Update the file directly to add your resolver code.</u>");
			}
			else {
				return $this->failure("Unable to create file: $filename <br/><u>Please validate permissions in the directory</u>.");
			}
		}
		else {
			return $this->failure('This file already exists. To prevent loss, you must manually delete the file.');
		}
	}
}
return 'ExtrabuilderAddResolverTransportProcessor';