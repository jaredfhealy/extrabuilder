<?php
/** @var xPDOTransport $transport */
/** @var array $options */
/** @var modX $modx */
if ($transport->xpdo) {
    $modx = &$transport->xpdo;

    switch ($options[xPDOTransport::PACKAGE_ACTION]) {
        case xPDOTransport::ACTION_INSTALL:
        case xPDOTransport::ACTION_UPGRADE:
			$packageKey = '{package_key}';
			$keyLower = strtolower($packageKey);
            $corePath = MODX_CORE_PATH . "components/$keyLower/";
            $eb; /* Service class */
            $isV3 = $modx->getVersionData()['version'] >= 3;
            if (!$isV3) {
                $modx->addPackage("$keyLower.v2.model", MODX_CORE_PATH.'components/');
            }
            else {
                // Check for the bootstrap file and include it
                if (file_exists($corePath.'bootstrap.php')) {
					// For v3, the bootstrap file needs a namespace object, which doesn't exist yet
					// Create an object so it can reference it
					$namespace = [
						'name' => $keyLower,
						'path' => $corePath,
						'assets_path' => MODX_ASSETS_PATH . "components/$keyLower/"
					];
					require $corePath.'bootstrap.php';
                }
				else {
					$modx->log(xPDO::LOG_LEVEL_ERROR, "Bootstrap file not valid: ".$corePath.'bootstrap.php');
				}
            }

            $manager = $modx->getManager();
            $objects = [];
			$classPrefix = "";
            $schemaFile = MODX_CORE_PATH . "components/{$keyLower}/schema/{$keyLower}.mysql.schema.xml";
            if (is_file($schemaFile)) {
                $schema = new SimpleXMLElement($schemaFile, 0, true);
                if (isset($schema->object)) {
                    foreach ($schema->object as $obj) {
                        $objects[] = (string) $obj['class'];
                    }
                }

				// Store the package value
				$classPrefix = $schema[0]['package'];
                unset($schema);
            }

            foreach ($objects as $class) {
				// Get the full class if v3
				if ($isV3) {
					$class = $classPrefix.$class;
				}

                // Get the table for this class
                $table = $modx->getTableName($class);
				if ($table) {
					$sql = "SHOW TABLES LIKE '" . trim($table, '`') . "'";
					$stmt = $modx->prepare($sql);
					$newTable = true;
					if ($stmt->execute() && $stmt->fetchAll()) {
						$newTable = false;
					}
				}
				else {
					// Log an error and skip this table
					$modx->log(xPDO::LOG_LEVEL_ERROR, "Unable to determine table name for class: $class");
					continue;
				}
                
                // Create the new table as it doesn't exist
                if ($newTable) {
                    $manager->createObjectContainer($class);
                } 
				else {
                    /**
					 * If the table exists, check and update columns and indexes.
					 */
                    $tableFields = [];
                    $c = $modx->prepare("SHOW COLUMNS IN {$modx->getTableName($class)}");
                    $c->execute();
                    while ($cl = $c->fetch(PDO::FETCH_ASSOC)) 
					{
                        $tableFields[$cl['Field']] = $cl['Field'];
                    }

					// Loop through the fields defined in the MODX class file
                    foreach ($modx->getFields($class) as $field => $v) 
					{
						// If the field exists in the database
                        if (in_array($field, $tableFields)) {
							// Alter the existing field
                            unset($tableFields[$field]);
                            $manager->alterField($class, $field);
                        } 
						else {
							// Add the new field
                            $manager->addField($class, $field);
                        }
                    }

					/**
					 * If there are any database fields that weren't "unset" above,
					 * it means they no longer exist in the schema, remove them
					 */
                    foreach ($tableFields as $field) {
                        $manager->removeField($class, $field);
                    }
                    
					// Get any indexes and add to an array
                    $indexes = [];
                    $c = $modx->prepare("SHOW INDEX FROM {$modx->getTableName($class)}");
                    $c->execute();
                    while ($row = $c->fetch(PDO::FETCH_ASSOC)) {
                        $name = $row['Key_name'];
                        if (!isset($indexes[$name])) {
                            $indexes[$name] = [$row['Column_name']];
                        } else {
                            $indexes[$name][] = $row['Column_name'];
                        }
                    }

					// Loop through the index array
                    foreach ($indexes as $name => $values) {
                        sort($values);
                        $indexes[$name] = implode(':', $values);
                    }

					// Get the defined indexes based on the schema
                    $map = $modx->getIndexMeta($class);

                    // Remove old indexes
					foreach ($indexes as $key => $index) {
						// If the index is not in the map
						if (!isset($map[$key])) {
							// Remove the old index
							if ($manager->removeIndex($class, $key)) {
								$modx->log(modX::LOG_LEVEL_INFO, "Removed index \"{$key}\" of the table \"{$class}\"");
                            }
						}
					}

                    // Add or alter existing indexes
                    foreach ($map as $key => $index) {
                        ksort($index['columns']);
                        $index = implode(':', array_keys($index['columns']));
                        if (!isset($indexes[$key])) {
                            if ($manager->addIndex($class, $key)) {
                                $modx->log(modX::LOG_LEVEL_INFO, "Added index \"{$key}\" in the table \"{$class}\"");
                            }
                        } else {
                            if ($index != $indexes[$key]) {
                                if ($manager->removeIndex($class, $key) && $manager->addIndex($class, $key)) {
                                    $modx->log(modX::LOG_LEVEL_INFO,
                                        "Updated index \"{$key}\" of the table \"{$class}\""
                                    );
                                }
                            }
                        }
                    }
                }
            }
		break;
    }
}

return true;
