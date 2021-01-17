# MODX Package & Custom Table Builder UI
ExtraBuilder adds the ability to create tables directly in the MODX Manager interface. This can be useful to rapidly prototype custom tables, skipping some of the learning curve of setting up a schema XML file and the supporting directory structure and files. New users can simply create new Model (Package), Objects (tables), and associated Fields and Relationships all from within a standard UI.

## Functionality Added
Below are the key additions to MODX when you install:

1. Menu added "Extras > ExtraBuilder" with sub-menus:
    1. Package Builder
    2. Transport Builder
2. The main "/extrabuilder" directory is added to your "/core/components/" directory
3. A category "ExtraBuilder" is used as a transport mechanism, but no Elements will appear under it.
3. No other plugins, or elements are created at this time.

## Package Builder
Selecting this menu option will take you to the main package building area where you can do the following:

1. Define a new Package or use "Import Schema" to import an existing schema file.
2. Create Object (custom table) child entries under the top level "Package" which is your model.
3. Create Fields associated to the object (database columns)
4. Create Relationships between objects. (xPDO feature to describe the relationship for the ORM)
5. Preview the Schema
6. Build the package to create the database tables, and columns as well as automatically create the PHP Class files needed for xPDO.

## Transport Builder
This area allows you to create a transport package that has all your component files, schema, etc. It requires that you create a main Category to use as the transport mechanism. All child elements will be added to your package.

1. Create a Transport
2. Select the Package and corresponding Category
3. Provide version details
4. Transport Utilities
    1. Create New PHP Resolver (Run a script on Install/Update/Uninstall)
    2. Add "Create Tables Resolver" (Creates/Updates Tables on Install/Uninstall)
    3. Add "Remove Tables Resolver" (Drop Tables on Uninstall)
    4. Backup All Elements (Create a backup in your _build/ for source control)
5. Build Transport Package!
