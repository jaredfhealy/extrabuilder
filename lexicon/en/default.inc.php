<?php

// Start language keys
// Menu
$_lang["extrabuilder.menu.main"] = "ExtraBuilder";
$_lang["extrabuilder.menu.main_desc"] = "Build custom tables, packages, and transports";
$_lang["extrabuilder.menu.package"] = "Package Builder";
$_lang["extrabuilder.menu.package_desc"] = "Build a Package with Custom Tables";
$_lang["extrabuilder.menu.transport"] = "Transport Builder";
$_lang["extrabuilder.menu.transport_desc"] = "Build a Transport Package for Publishing";

// Home
$_lang["extrabuilder.home_title"] = "ExtraBuilder: Build applications on MODx";
$_lang["extrabuilder.home_tab_title"] = "Packages";
$_lang["extrabuilder.home_tab_desc"] = "Create new packages to define your data model including objects (custom tables), fields, and relationships.";
$_lang["extrabuilder.transport_home_title"] = "ExtraBuilder : Package Your Application with Transport";

// Schema import
$_lang["extrabuilder.import_button"] = "Import Schema";
$_lang["extrabuilder.schema_import"] = "Import a Schema (File or Text)";
$_lang["extrabuilder.schema_file_path"] = "Schema File Path";
$_lang["extrabuilder.schema_file_path_desc"] = "Relative path from MODX base - example: core/model/schema/modx.mysql.schema.xml";
$_lang["extrabuilder.schema_xml"] = "Paste an XML Schema";

// Schema preview
$_lang["extrabuilder.schema_actions"] = "Schema Actions";
$_lang["extrabuilder.schema_preview"] = "Preview Schema";
$_lang["extrabuilder.save_schema"] = "Save/Write Schema to File";
$_lang["extrabuilder.preview_assets_path"] = "Assets Path";
$_lang["extrabuilder.preview_core_path"] = "Core Path";
$_lang["extrabuilder.schema_xml_out"] = "XML Schema";
$_lang["extrabuilder.schema_log"] = "Log Messages";

// Build options
$_lang["extrabuilder.schema_build_actions"] = "Build Actions";
$_lang["extrabuilder.schema_build_one"] = "Option 1: Tables (Create/update), Schema (Overwrite), Class Files (Skip existing)";
$_lang["extrabuilder.schema_build_two"] = "Option 2: Tables (Create/update), Schema (Overwrite), Class Files (Overwrite)";
$_lang["extrabuilder.schema_build_three"] = "Option 3: Tables (DROP ALL, Create), Schema (Overwrite), Class Files (Overwrite)";

// Form shared
$_lang["extrabuilder.form.less_common"] = "Less common options below:";

// Grid shared
$_lang["extrabuilder.search_general"] = "Search...";
$_lang["extrabuilder.context_menu"] = "Menu";
$_lang["extrabuilder.sort_order"] = "Sort Order";
$_lang["extrabuilder.clear_search"] = "Clear Search";
$_lang["extrabuilder.create_button"] = "Create New ";
$_lang["extrabuilder.delete_record_menu"] = "Delete Record and all Child Records";
$_lang["extrabuilder.delete_record_title"] = "Delete Record and all Child Records";
$_lang["extrabuilder.delete_record_warning"] = "WARNING: This action will delete the selected record and all child related records.";
$_lang["extrabuilder.create_window_title"] = "Create New Record";
$_lang["extrabuilder.update_window_title"] = "Update Existing Record";
$_lang["extrabuilder.window_missing_fields"] = "Warning: This Class is Missing Defined Fields";
$_lang["extrabuilder.window_missing_fields_html"] = "Define a field array in your grid model to return.
		<br />------------------<br/>Or check that your index is properly including it.";
$_lang["extrabuilder.general_edit_msg"] = "Use the create button to add, double-click to update values inline, or right-click for context options or to delete.";

// Packages: General
$_lang["extrabuilder.ebpackage.tab_title"] = "Packages";
$_lang["extrabuilder.ebpackage.html_desc"] = "<p style='margin-top: 15px;'><span style='font-weight: bold;'>Create Your Package</span>: A package is defined by an XML Schema which contains all the tables needed by your application, indexes, and relationships between those tables.</p>";

// Packages: Field Labels
$_lang["extrabuilder.ebpackage.display"] = "Display Value (Not used by Modx)";
$_lang["extrabuilder.ebpackage.display_short"] = "Display Value";
$_lang["extrabuilder.ebpackage.package_key_short"] = "Package Key";
$_lang["extrabuilder.ebpackage.package_desc3"] = "This is the PHP Namespace that will be generated at the top of your class files. It is also used to determine the output directory. Recommended format: <span style='font-weight:bold;'>MyComponent\Model</span>";
$_lang["extrabuilder.ebpackage.package_desc2"] = "To prepare for MODX 3, use lowercase: mycomponent.v2.model (This will build to mycomponent/v2/model/)";
$_lang["extrabuilder.ebpackage.base_class"] = "Base Class";
$_lang["extrabuilder.ebpackage.platform"] = "DB Platform";
$_lang["extrabuilder.ebpackage.default_engine"] = "Default Engine";
$_lang["extrabuilder.ebpackage.phpdoc_package"] = "PHPDoc Package (Optional: Documentation only)";
$_lang["extrabuilder.ebpackage.phpdoc_package_short"] = "PHPDoc Package";
$_lang["extrabuilder.ebpackage.phpdoc_subpackage"] = "PHPDoc SubPackage (Optional: Documentation only)";
$_lang["extrabuilder.ebpackage.phpdoc_subpackage_short"] = "PHPDoc SubPackage";
$_lang["extrabuilder.ebpackage.sortorder"] = "Sort Order";
$_lang["extrabuilder.ebpackage.version"] = "Schema Version (Default for MODX-3: 3.0)";
$_lang["extrabuilder.ebpackage.version_short"] = "Schema Version";
$_lang["extrabuilder.ebpackage.core_path"] = "Core Path Override";
$_lang["extrabuilder.ebpackage.core_path_desc"] = "Allowed placeholders: {base_path}, {core_path}, {assets_path}, {cmp_namespace}, {php_namespace}";
$_lang["extrabuilder.ebpackage.assets_path"] = "Assets Path Override";
$_lang["extrabuilder.ebpackage.assets_path_desc"] = "Allowed placeholders: {base_path}, {core_path}, {assets_path}, {cmp_namespace}, {php_namespace}";
$_lang["extrabuilder.ebpackage.lexicon"] = "Option: Generate Starting Lexicon File";

// Objects: General
$_lang["extrabuilder.ebobject.tab_title"] = "Manage Objects";
$_lang["extrabuilder.ebobject.html_desc"] = "<p style='margin-top: 15px;'><span style='font-weight: bold;'>Create an Object</span>: 
An Object is a representation of a database table. The objects are accessed using the Class Name.</p>";

// Objects: Field Labels
$_lang["extrabuilder.ebobject.class"] = "Class Name";
$_lang["extrabuilder.ebobject.class_short"] = "Class Name";
$_lang["extrabuilder.ebobject.table_name"] = "Table Name";
$_lang["extrabuilder.ebobject.extends"] = "Extends Object (Default xPDOSimpleObject)";
$_lang["extrabuilder.ebobject.extends_short"] = "Extends Object";
$_lang["extrabuilder.ebobject.sortorder"] = "Sort Order";
$_lang["extrabuilder.ebobject.package"] = "Package";
$_lang["extrabuilder.ebobject.raw_xml"] = "Raw XML (Define additional rules or schema values for edge cases)";


// Fields: General
$_lang["extrabuilder.ebfield.tab_title"] = "Manage Fields";
$_lang["extrabuilder.ebfield.quick_select"] = "Quick Select (Populate common combinations)";
$_lang["extrabuilder.ebfield.quick_select_placeholder"] = "Select an option to populate the values...";
$_lang["extrabuilder.ebfield.html_desc"] = "<p style='margin-top: 15px;'><span style='font-weight: bold;'>Create a Database Column (Field):</span> Define column names and data types for this table/object.";

// Fields: Field Labels
$_lang["extrabuilder.ebfield.column_name"] = "Column Name";
$_lang["extrabuilder.ebfield.dbtype"] = "DB Type";
$_lang["extrabuilder.ebfield.precision"] = "Precision";
$_lang["extrabuilder.ebfield.phptype"] = "PHP Type";
$_lang["extrabuilder.ebfield.allownull"] = "Allow Null";
$_lang["extrabuilder.ebfield.default"] = "Default Value";
$_lang["extrabuilder.ebfield.object"] = "Object";
$_lang["extrabuilder.ebfield.index"] = "Index Type";
$_lang["extrabuilder.ebfield.index_attributes"] = "Index Attributes (primary/unique)";
$_lang["extrabuilder.ebfield.attributes"] = "Additional Attributes (ex: unsigned)";
$_lang["extrabuilder.ebfield.generated"] = "Generated";
$_lang["extrabuilder.ebfield.extra"] = "Extra (MySQL \"Extra\": See MySQL Docs)";
$_lang["extrabuilder.ebfield.sortorder"] = "Sort Order";

// Relationships: General
$_lang["extrabuilder.ebrel.tab_title"] = "Manage Rels";
$_lang["extrabuilder.ebrel.relation_type"] = "Relationship Type";
$_lang["extrabuilder.ebrel.relation_type_empty"] = "Select a relationship type...";
$_lang["extrabuilder.ebrel.html_desc"] = "<p style='margin-top: 15px;'><span style='font-weight: bold;'>Create a Relationship:</span> Define relationships between your tables.";

// Relationships: Field labels
$_lang["extrabuilder.ebrel.alias"] = "Relationship Alias (Ex: getMany('Users'), getOne('User'))";
$_lang["extrabuilder.ebrel.alias_short"] = "Alias";
$_lang["extrabuilder.ebrel.class"] = "Class (Ex: modUser)";
$_lang["extrabuilder.ebrel.class_short"] = "Class";
$_lang["extrabuilder.ebrel.local"] = "Local Field (Lowercase field name: )";
$_lang["extrabuilder.ebrel.local_short"] = "Local Field";
$_lang["extrabuilder.ebrel.foreign"] = "Foreign (Field storing the foreign key)";
$_lang["extrabuilder.ebrel.foreign_short"] = "Foreign";
$_lang["extrabuilder.ebrel.cardinality"] = "Cardinality";
$_lang["extrabuilder.ebrel.owner"] = "Owner";
$_lang["extrabuilder.ebrel.sortorder"] = "Sort Order";


// Transports: General
$_lang["extrabuilder.ebtransport.tab_title"] = "Transport";
$_lang["extrabuilder.ebtransport.html_desc"] = "<p style='margin-top: 15px;'><span style='font-weight: bold;'>Define Your Transport</span>: Use Transport to create a deployable package that can be moved between MODX instances or published to the Extras Directory.</p>";
$_lang["extrabuilder.ebtransport.category_desc"] = "<span style='font-weight:bold;'>NOTE:</span> If you do not already have a main Category for this package, go create one first.<br/>
This feature does NOT currently support nested categories.";

// Transports: Field Labels
$_lang["extrabuilder.ebtransport.package"] = "Select a Package Definition";
$_lang["extrabuilder.ebtransport.package_short"] = "Namespace (Package)";
$_lang["extrabuilder.ebtransport.category"] = "Select a Main Category";
$_lang["extrabuilder.ebtransport.category_short"] = "Category";
$_lang["extrabuilder.ebtransport.version_section_label"] = "Version and Release";
$_lang["extrabuilder.ebtransport.major_desc"] = "(1.*.*)";
$_lang["extrabuilder.ebtransport.major_short"] = "Major";
$_lang["extrabuilder.ebtransport.minor_desc"] = "(*.1.*)";
$_lang["extrabuilder.ebtransport.minor_short"] = "Minor";
$_lang["extrabuilder.ebtransport.patch_desc"] = "(*.*.1)";
$_lang["extrabuilder.ebtransport.patch_short"] = "Patch";
$_lang["extrabuilder.ebtransport.release_desc"] = "(alpha, beta, rc, pl, dev)";
$_lang["extrabuilder.ebtransport.release_short"] = "Release";
$_lang["extrabuilder.ebtransport.release_index"] = "Release Index";
$_lang["extrabuilder.ebtransport.release_index_short"] = "Release Index";

// Transport: Context menu items
$_lang["extrabuilder.ebtransport.build_transport"] = "Build the Transport Package";
$_lang["extrabuilder.ebtransport.add_resolver_menu"] = "Create New Resolver (Run a srcript on Install/Update/Uninstall)";
$_lang["extrabuilder.ebtransport.add_resolver_title"] = "Add a Resolver";
$_lang["extrabuilder.ebtransport.add_resolver_prompt"] = "Filename (Excluding '.php')";
$_lang["extrabuilder.ebtransport.add_tables_resolver_menu"] = "Add \"Create Tables\" Resolver (Creates/Updates Tables on Install/Update)";
$_lang["extrabuilder.ebtransport.add_remove_tables_resolver_menu"] = "Add \"Remove Tables\" Resolver (Drop Tables on Uninstall)";
$_lang["extrabuilder.ebtransport.backup_elements"] = "Backup All Elements (Create a backup in your _build/ for source control)";