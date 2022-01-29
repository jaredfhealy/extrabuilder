// Get the context menu and pop off the last item which is delete
var tpContext = EB.model.ebTransport.grid.config.menuItems;

// Define the build option
var tpBuild = {
	text: _("{$cmpNamespace}.{$gridClass|lower}.build_transport"),
	handler: function(_this) {
		// Get the record
		var grid = Ext.getCmp("{$cmpNamespace}-grid-{$gridClass|lower}");
		if (grid) {
			grid.buildTransport(_this.ownerCt.record.id);
		}
	}
};

// Add resolver option
var tpAddResolver = {
	text: _("{$cmpNamespace}.{$gridClass|lower}.add_resolver_menu"),
	handler: function(_this) {
		// Get the record
		var grid = Ext.getCmp("{$cmpNamespace}-grid-{$gridClass|lower}");
		if (grid) {
			grid.addResolver(_this.ownerCt.record.id);
		}
	}
};

// Add resolver option
var tpAddTablesResolver = {
	text: _("{$cmpNamespace}.{$gridClass|lower}.add_tables_resolver_menu"),
	handler: function(_this) {
		// Get the record
		var grid = Ext.getCmp("{$cmpNamespace}-grid-{$gridClass|lower}");
		if (grid) {
			grid.transportAction(_this.ownerCt.record.id, 'addTablesResolver');
		}
	}
};

// Add resolver option
var tpAddRemoveTablesResolver = {
	text: _("{$cmpNamespace}.{$gridClass|lower}.add_remove_tables_resolver_menu"),
	handler: function(_this) {
		// Get the record
		var grid = Ext.getCmp("{$cmpNamespace}-grid-{$gridClass|lower}");
		if (grid) {
			grid.transportAction(_this.ownerCt.record.id, 'addRemoveTablesResolver');
		}
	}
};

// Add resolver option
var tpBackupElements = {
	text: _("{$cmpNamespace}.{$gridClass|lower}.backup_elements"),
	handler: function(_this) {
		// Get the record
		var grid = Ext.getCmp("{$cmpNamespace}-grid-{$gridClass|lower}");
		if (grid) {
			grid.backupElements(_this.ownerCt.record.id);
		}
	}
};

EB.model.ebTransport.grid.config.menuItems.splice(1, 0, '-', '-', 
	tpBuild, 
	tpAddResolver, 
	tpAddTablesResolver, 
	tpAddRemoveTablesResolver,
	tpBackupElements
);

// Apply overrides to add additional functions to the panel
Ext.apply(EB.panel.overrides, {});