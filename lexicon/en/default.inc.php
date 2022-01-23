<?php

// Start language keys
$ns = "ExtraBuilder";
$nsl = strtolower($ns);
$_lang["extrabuilder"] = $ns;
$_lang[$ns] = $ns;

// Menu
$_lang["$nsl.menu.package"] = "Package Builder";
$_lang["$nsl.menu.transport"] = "Transport Builder";

// Home
$_lang["$nsl.home_title"] = $ns.": Build applications on MODx";
$_lang["$nsl.home_tab_title"] = "Packages";
$_lang["$nsl.home_tab_desc"] = "Create new packages to define your data model including objects (custom tables), fields, and relationships.";
$_lang["$nsl.transport_home_title"] = $ns.": Package Your Application with Transport";

// Schema import
$_lang["$nsl.import_button"] = "Import Schema";
$_lang["$nsl.schema_import"] = "Import a Schema (File or Text)";
$_lang["$nsl.schema_file_path"] = "Schema File Path";
$_lang["$nsl.schema_file_path_desc"] = "(From MODX Base, ex: mything/model/schema/mything.mysql.schema.xml)";
$_lang["$nsl.schema_xml"] = "Paste an XML Schema";

// Schema preview
$_lang["$nsl.schema_actions"] = "Schema Actions";
$_lang["$nsl.schema_preview"] = "Preview Schema";
$_lang["$nsl.save_schema"] = "Save/Write Schema to File";
$_lang["$nsl.preview_assets_path"] = "Assets Path";
$_lang["$nsl.preview_core_path"] = "Core Path";
$_lang["$nsl.schema_xml_out"] = "XML Schema";
$_lang["$nsl.schema_log"] = "Log Messages";

// Build options
$_lang["$nsl.schema_build_actions"] = "Build Actions";
$_lang["$nsl.schema_build_one"] = "Option 1: Tables (Create/update), Schema (Overwrite), Class Files (Skip existing)";
$_lang["$nsl.schema_build_two"] = "Option 2: Tables (Create/update), Schema (Overwrite), Class Files (Overwrite)";
$_lang["$nsl.schema_build_three"] = "Option 3: Tables (DROP ALL, Create), Schema (Overwrite), Class Files (Overwrite)";

// Grid shared
$_lang["$nsl.search_general"] = "Search...";
$_lang["$nsl.context_menu"] = "Menu";
$_lang["$nsl.sort_order"] = "Sort Order";
$_lang["$nsl.clear_search"] = "Clear Search";
$_lang["$nsl.create_button"] = "Create New ";
$_lang["$nsl.delete_record_menu"] = "Delete Record and all Child Records";
$_lang["$nsl.delete_record_title"] = "Delete Record and all Child Records";
$_lang["$nsl.delete_record_warning"] = "WARNING: This action will delete the selected record and all child related records.";
$_lang["$nsl.create_window_title"] = "Create New Record";
$_lang["$nsl.update_window_title"] = "Update Existing Record";
$_lang["$nsl.window_missing_fields"] = "Warning: This Class is Missing Defined Fields";
$_lang["$nsl.window_missing_fields_html"] = "Define a field array in your grid model to return.
		<br />------------------<br/>Or check that your index is properly including it.";
$_lang["$nsl.general_edit_msg"] = "Use the create button to add, double-click to update values inline, or right-click for context options or to delete.";

// Packages: General
$_lang["$nsl.ebpackage.tab_title"] = "Packages";
$_lang["$nsl.ebpackage.html_desc"] = "<p style='margin-top: 15px;'><span style='font-weight: bold;'>Create Your Package</span>: A package is defined by an XML Schema which contains all the tables needed by your application, indexes, and relationships between those tables.</p>";

// Packages: Field Labels
$_lang["$nsl.ebpackage.display"] = "Display Value (Not used by Modx)";
$_lang["$nsl.ebpackage.display_short"] = "Display Value";
$_lang["$nsl.ebpackage.package_key"] = "Package Key (Caps are allowed, ex: ExtraBuilder)";
$_lang["$nsl.ebpackage.package_key_short"] = "Package Key";
$_lang["$nsl.ebpackage.package_desc"] = "In MODX3 this ";
$_lang["$nsl.ebpackage.base_class"] = "Base Class";
$_lang["$nsl.ebpackage.platform"] = "DB Platform";
$_lang["$nsl.ebpackage.default_engine"] = "Default Engine";
$_lang["$nsl.ebpackage.phpdoc_package"] = "PHPDoc Package (Optional: Documentation only)";
$_lang["$nsl.ebpackage.phpdoc_package_short"] = "PHPDoc Package";
$_lang["$nsl.ebpackage.phpdoc_subpackage"] = "PHPDoc SubPackage (Optional: Documentation only)";
$_lang["$nsl.ebpackage.phpdoc_subpackage_short"] = "PHPDoc SubPackage";
$_lang["$nsl.ebpackage.sortorder"] = "Sort Order";
$_lang["$nsl.ebpackage.version"] = "Schema Version (Default for MODX-3: 3.0)";
$_lang["$nsl.ebpackage.version_short"] = "Schema Version";
$_lang["$nsl.ebpackage.core_path"] = "Core Path Override";
$_lang["$nsl.ebpackage.assets_path"] = "Assets Path Override";
$_lang["$nsl.ebpackage.lexicon"] = "Option: Generate Starting Lexicon File";

// Objects: General
$_lang["$nsl.ebobject.tab_title"] = "Manage Objects";
$_lang["$nsl.ebobject.html_desc"] = "<p style='margin-top: 15px;'><span style='font-weight: bold;'>Create an Object</span>: 
An Object is a representation of a database table. The objects are accessed using the Class Name. For MODx 3, that is the fully namespaced class (ex: ExtraBuilder\\Model\\ebObject).</p>";

// Objects: Field Labels
$_lang["$nsl.ebobject.class"] = "Class Name";
$_lang["$nsl.ebobject.class_short"] = "Class Name";
$_lang["$nsl.ebobject.table_name"] = "Table Name";
$_lang["$nsl.ebobject.extends"] = "Extends Object (Default xPDOSimpleObject)";
$_lang["$nsl.ebobject.extends_short"] = "Extends Object";
$_lang["$nsl.ebobject.sortorder"] = "Sort Order";
$_lang["$nsl.ebobject.package"] = "Package";
$_lang["$nsl.ebobject.raw_xml"] = "Raw XML (Define additional rules or schema values for edge cases)";


// Fields: General
$_lang["$nsl.ebfield.tab_title"] = "Manage Fields";
$_lang["$nsl.ebfield.quick_select"] = "Quick Select (Populate common combinations)";
$_lang["$nsl.ebfield.quick_select_placeholder"] = "Select an option to populate the values...";
$_lang["$nsl.ebfield.less_common"] = "Less common options below:</h3>";

// Fields: Field Labels
$_lang["$nsl.ebfield.column_name"] = "Column Name";
$_lang["$nsl.ebfield.dbtype"] = "DB Type";
$_lang["$nsl.ebfield.precision"] = "Precision";
$_lang["$nsl.ebfield.phptype"] = "PHP Type";
$_lang["$nsl.ebfield.allownull"] = "Allow Null";
$_lang["$nsl.ebfield.default"] = "Default Value";
$_lang["$nsl.ebfield.object"] = "Object";
$_lang["$nsl.ebfield.index"] = "Index Type";
$_lang["$nsl.ebfield.attributes"] = "Additional Attributes (ex: unsigned)";
$_lang["$nsl.ebfield.generated"] = "Generated";
$_lang["$nsl.ebfield.extra"] = "Extra (MySQL \"Extra\": See MySQL Docs)";

// Relationships: General
$_lang["$nsl.ebrel.tab_title"] = "Manage Rels";
$_lang["$nsl.ebrel.relation_type"] = "Relationship Type";
$_lang["$nsl.ebrel.relation_type_empty"] = "Select a relationship type...";

// Relationships: Field labels
$_lang["$nsl.ebrel.alias"] = "Relationship Alias (Ex: getMany('Users'), getOne('User'))";
$_lang["$nsl.ebrel.alias_short"] = "Alias";
$_lang["$nsl.ebrel.class"] = "Class (Ex: modUser)";
$_lang["$nsl.ebrel.class_short"] = "Class";
$_lang["$nsl.ebrel.local"] = "Local Field (Lowercase field name: )";
$_lang["$nsl.ebrel.local_short"] = "Local Field";
$_lang["$nsl.ebrel.foreign"] = "Foreign (Field storing the foreign key)";
$_lang["$nsl.ebrel.foreign_short"] = "Foreign";
$_lang["$nsl.ebrel.cardinality"] = "Cardinality";
$_lang["$nsl.ebrel.owner"] = "Owner";
$_lang["$nsl.ebrel.sortorder"] = "Sort Order";


// Transports: General
$_lang["$nsl.ebtransport.tab_title"] = "Transport";
$_lang["$nsl.ebtransport.html_desc"] = "<p style='margin-top: 15px;'><span style='font-weight: bold;'>Define Your Transport</span>: Use Transport to create a deployable package that can be moved between MODX instances or published to the Extras Directory.</p>";
$_lang["$nsl.ebtransport.category_desc"] = "<span style='font-weight:bold;'>NOTE:</span> If you do not already have a main Category for this package, go create one first.<br/>
This feature does NOT currently support nested categories.";

// Transports: Field Labels
$_lang["$nsl.ebtransport.package"] = "Pick a Defined Namespace (Package)";
$_lang["$nsl.ebtransport.package_short"] = "Namespace (Package)";
$_lang["$nsl.ebtransport.category"] = "Select a Main Category";
$_lang["$nsl.ebtransport.category_short"] = "Category";
$_lang["$nsl.ebtransport.version_section_label"] = "Version and Release";
$_lang["$nsl.ebtransport.major_desc"] = "(1.*.*)";
$_lang["$nsl.ebtransport.major_short"] = "Major";
$_lang["$nsl.ebtransport.minor_desc"] = "(*.1.*)";
$_lang["$nsl.ebtransport.minor_short"] = "Minor";
$_lang["$nsl.ebtransport.patch_desc"] = "(*.*.1)";
$_lang["$nsl.ebtransport.patch_short"] = "Patch";
$_lang["$nsl.ebtransport.release_desc"] = "(alpha, beta, rc, pl, dev)";
$_lang["$nsl.ebtransport.release_short"] = "Release";
$_lang["$nsl.ebtransport.release_index"] = "Release Index";
$_lang["$nsl.ebtransport.release_index_short"] = "Release Index";

// Transport: Context menu items
$_lang["$nsl.ebtransport.build_transport"] = "Build the Transport Package";
$_lang["$nsl.ebtransport.add_resolver_menu"] = "Create New Resolver (Run a srcript on Install/Update/Uninstall)";
$_lang["$nsl.ebtransport.add_resolver_title"] = "Add a Resolver";
$_lang["$nsl.ebtransport.add_resolver_prompt"] = "Filename (Excluding '.php')";
$_lang["$nsl.ebtransport.add_tables_resolver_menu"] = "Add \"Create Tables\" Resolver (Creates/Updates Tables on Install/Update)";
$_lang["$nsl.ebtransport.add_remove_tables_resolver_menu"] = "Add \"Remove Tables\" Resolver (Drop Tables on Uninstall)";
$_lang["$nsl.ebtransport.backup_elements"] = "Backup All Elements (Create a backup in your _build/ for source control)";