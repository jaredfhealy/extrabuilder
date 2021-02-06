<?php

/**
 * Import schema
 *
 * @package extrabuilder
 * @subpackage processors
 */
class ExtrabuilderAddTablesProcessor extends modObjectProcessor
{
	public $classKey = 'ebPackage';
    public $languageTopics = array('extrabuilder:default');
	public $objectType = 'extrabuilder.package';

	/**
	 * Override the process function
	 *
	 */
	public function process()
	{
		// Get the package object
		$primaryKey = $this->getProperty($this->primaryKeyField,false);
		$this->object = $this->modx->getObject($this->classKey, $primaryKey);
		
		// Get the passed tables parameter
		$tables = $this->getProperty('tables');
		
		// Call the main processing function
		$this->readFromTables($tables);

		// Set the response
		return $this->success('', []);
	}

	public function getIndex($index) {
        switch ($index) {
            case 'PRI':
                $index= 'pk';
                break;

            case 'UNI':
                $index= 'unique';
                break;

            case 'MUL':
                $index= 'index';
                break;

            default:
                break;
        }
        if (!empty ($index)) {
            $index= ' index="' . $index . '"';
        }
        return $index;
    }

	/**
	 * Function modified from xpdogenerator writeSchema
	 * /core/xpdo/om/mysql/xpdogenerator.class.php writeSchema
	 * 
	 * Instead of writing to an XML file, modified to write to
	 * the database tables within ExtraBuilder (ebObject) to 
	 * define the table, fields, and indexes.
	 * 
	 * NOTE: Currently only handles simple single field indexes
	 * 
	 * @param array $tables Simple array of table names
	 */
	public function readFromTables($tables)
	{
		// Get the manager and generator
		$manager = $this->modx->getManager();
		$generator = $manager->getGenerator();

		// Get parameters
		
		// Loop through the tables
		foreach ($tables as $tableIndex => $table) {
			// Remove the root modx table prefix
			$tableNoPrefix = str_replace($manager->xpdo->config[xPDO::OPT_TABLE_PREFIX], '', $table);

			// Determine class and baseClass
            $class = $generator->getClassName($tableNoPrefix);
			
			// Start an object record
			$object = $this->modx->getObject('ebObject', ['class' => $class, 'table_name' => $tableNoPrefix]);
			if (!$object) {
				$object = $this->modx->newObject('ebObject');
				$object->set('class', $class);
				$object->set('table_name', $tableNoPrefix);
				$object->set('extends', 'xPDOObject');
				$object->set('sortorder', $tableIndex);
				$object->set('package', $this->object->get('id'));
			}

			// Get all the fields/columns
			$childFields = [];
            $fieldsStmt= $manager->xpdo->query('SHOW COLUMNS FROM ' . $manager->xpdo->escape($table));
            if ($fieldsStmt) {
                $fields= $fieldsStmt->fetchAll(PDO::FETCH_ASSOC);
                if ($manager->xpdo->getDebug() === true) $manager->xpdo->log(xPDO::LOG_LEVEL_DEBUG, print_r($fields, true));
                if (!empty($fields)) {
                    foreach ($fields as $field) {
						// Setup defaults
                        $Field= '';
                        $Type= '';
                        $Null= '';
                        $Key= '';
						$Default= '';
						
						// Extract and overwrite
						extract($field, EXTR_OVERWRITE);
						
						// Parse values, add xml attribute strings
                        $Type= xPDO :: escSplit(' ', $Type, "'", 2);
                        $precisionPos= strpos($Type[0], '(');
                        $dbType= $precisionPos? substr($Type[0], 0, $precisionPos): $Type[0];
                        $dbType= strtolower($dbType);
                        $Precision= $precisionPos? substr($Type[0], $precisionPos + 1, strrpos($Type[0], ')') - ($precisionPos + 1)): '';
                        if (!empty ($Precision)) {
							$Precision = trim($Precision);
                        }
                        $attributes= '';
                        if (isset ($Type[1]) && !empty ($Type[1])) {
							$attributes = trim($Type[1]);
                        }
						$PhpType= $manager->xpdo->driver->getPhpType($dbType);
						$Null = (($Null === 'NO') ? 'false' : 'true');
						$Key= $this->getIndex($Key);

						// Handle the existence of an auto_increment field
						if (!empty($Extra)) {
                            if ($Extra === 'auto_increment') {
                                if ($object->get('extends') === 'xPDOObject' && $Field === 'id') {
									$object->set('extends', 'xPDOSimpleObject');
                                    continue;
                                } else {
									$object->set('generated', 'native');
                                }
                            } else {
								$object->set('extra', strtolower($Extra));
                            }
                        }
						
						// Create a new child field object or update existing
						$updateField = true;
						$fieldObj = $this->modx->getObject('ebField', ['object' => $object->get('id'), 'column_name' => $Field]);
						if (!$fieldObj) {
							$fieldObj = $this->modx->newObject('ebField');
							$fieldObj->set('column_name', $Field);
							$updateField = false;
						}
						
						$fieldObj->set('dbtype', $dbType);
						$fieldObj->set('precision', $Precision);
						$fieldObj->set('phptype', $PhpType);
						$fieldObj->set('allownull', $Null);
						$fieldObj->set('default', $Default);
						$fieldObj->set('attributes', $attributes);
						
						if ($updateField) {
							// Save the field
							$fieldObj->save();
						}
						else {
							$childFields[] = $fieldObj;
						}
					}
					
					// If we have childFields
					if (count($childFields) > 0) {
						// Add many fields to the object
						$object->addMany($childFields);
						unset($childFields); // save memory

						// Save the object and fields
						$object->save();
					}
                } 
            }
			
			// Now handle indexes
            $whereClause= ($extends === 'xPDOSimpleObject' ? " WHERE `Key_name` != 'PRIMARY'" : '');
            $indexesStmt= $manager->xpdo->query('SHOW INDEXES FROM ' . $manager->xpdo->escape($table) . $whereClause);
            if ($indexesStmt) {
                $indexes= $indexesStmt->fetchAll(PDO::FETCH_ASSOC);
                if ($manager->xpdo->getDebug() === true) $manager->xpdo->log(xPDO::LOG_LEVEL_DEBUG, "Indices for table {$table[0]}: " . print_r($indexes, true));
				
				// If we have indexes
				if (!empty($indexes)) {
					// Loop through and simplify xpdo statement to an array
                    $indices = array();
                    foreach ($indexes as $index) {
                        if (!array_key_exists($index['Key_name'], $indices)) $indices[$index['Key_name']] = array();
                        $indices[$index['Key_name']][$index['Seq_in_index']] = $index;
					}
					unset($indexes);

					// Get the table object that was created or updated above
					$object = $this->modx->getObject('ebObject', ['class' => $class, 'table_name' => $tableNoPrefix]);
					if ($object) {
						// Loop through the index array
						foreach ($indices as $index) {
							// Index and columns
							if ($manager->xpdo->getDebug() === true) $manager->xpdo->log(xPDO::LOG_LEVEL_DEBUG, "Details of index: " . print_r($index, true));
							foreach ($index as $columnSeq => $column) {
								if ($columnSeq == 1) {
									$keyName = $column['Key_name'];
									$primary = $keyName == 'PRIMARY' ? 'true' : 'false';
									$unique = empty($column['Non_unique']) ? 'true' : 'false';
									$packed = empty($column['Packed']) ? 'false' : 'true';
									$type = $column['Index_type'];
								}
								$null = $column['Null'] == 'YES' ? 'true' : 'false';

								// Get the field for this index
								$childFields = $object->getMany('Fields', ['column_name' => $keyName]);
								if ($childFields) {
									foreach ($childFields as $fieldObj) {
										// Set the index value
										$fieldObj->set('index', $type);
										$fieldObj->save();
									}
								}
							}
						}
					}
                }
			}
        }
	}
}
return 'ExtrabuilderAddTablesProcessor';