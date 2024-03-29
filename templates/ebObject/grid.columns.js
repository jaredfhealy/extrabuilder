Ext.apply(EB.model['{$gridClass}'].grid.config, {
	
	// Override any parameters from the grid.base.tpl
	columns: [
		{
			header: _('id'),
			dataIndex: "id",
			sortable: true,
			width: 40
		},
		{
			header: "Fields",
			id: "editLink",
			sortable: false,
			disabled: true,
			width: 60,
			align: "center",
			renderer: EB.fn.editLinkRender,
			listeners: {
				click: function(_this, grid, rowIndex, e) {
					// Call the function on our panel (2 levels up)
					var panel = grid.ownerCt.ownerCt.ownerCt;
					panel.openChildTab(grid, '{$cmpNamespace}', '{$gridClass}', 'ebField', '{$classDetail["tabDisplayField"]}');
					panel.openChildTab(grid, '{$cmpNamespace}', '{$gridClass}', 'ebRel', '{$classDetail["tabDisplayField"]}', false);
				}
			}
		},
		{
			header: _("{$cmpNamespace}.{$gridClass|lower}.class_short"),
			dataIndex: "class",
			sortable: true,
			editor: {
				xtype: "textfield"
			}
		},
		{
			header: _("{$cmpNamespace}.{$gridClass|lower}.table_name"),
			dataIndex: "table_name",
			sortable: true,
			editor: {
				xtype: "textfield"
			}
		},
		{
			header: _("{$cmpNamespace}.sort_order"),
			dataIndex: "sortorder",
			sortable: true,
			editor: {
				xtype: "textfield"
			}
		}
	]
	
});

// Override the grid panel buttons for ebPackage
// Add to the buttons for ebPackage only
var packageTbar = EB.model.ebObject.grid.config.tbar;
var createBtn = packageTbar.shift();
var schemaBtn = {
	text: _('{$cmpNamespace}.schema_actions'),
	id: '{$cmpNamespace}-schema-actions',
	// disabled: true,
	menu: [{
		text: _('{$cmpNamespace}.schema_preview'),
		handler: function() {
			// Make a request to the build processor
			Ext.Ajax.request({
				url: {$phpNamespace}.config.connectorUrl,
				params: { 
					action: '{if $isV3}{$phpNamespace}\\Processors\\ebPackage\\{else}ebpackage/{/if}Build',
					id: EB.model.ebPackage.data.selectedId
				},
				success: function (response) {
					var msg = "Unknown error...";
					try {
						var data = JSON.parse(response.responseText);
						if (data.success == true) {
							// Show the preview window and pass in the data object
							data.object;
							EB.window.schema.preview = MODx.load({
								xtype: '{$cmpNamespace}-window-schema',
								record: data.object
							});
							EB.window.schema.preview.show().center();
						}
						else {
							msg = data.message ? data.message : JSON.stringify(data, null, 2);
						}
					}
					catch(e) {}
				}
			});
		}
	},'-',{
		text: _('{$cmpNamespace}.save_schema'),
		handler: function() {
			Ext.Ajax.request({
				url: {$phpNamespace}.config.connectorUrl,
				params: { 
					action: '{if $isV3}{$phpNamespace}\\Processors\\ebPackage\\{else}ebpackage/{/if}Build',
					id: EB.model.ebPackage.data.selectedId,
					write_schema: 'true'
				},
				success: function (response) {
					var msg = "Unknown error...";
					try {
						var data = JSON.parse(response.responseText);
						if (data.success == true) {
							// Show the preview window and pass in the data object
							data.object;
							EB.window.schema.preview = MODx.load({
								xtype: '{$cmpNamespace}-window-schema',
								record: data.object
							});
							EB.window.schema.preview.show().center();
						}
						else {
							msg = data.message ? data.message : JSON.stringify(data, null, 2);
						}
					}
					catch(e) {}
				}
			});
		}
	}]
};

var buildBtn = {
	text: _('{$cmpNamespace}.schema_build_actions'),
	id: '{$cmpNamespace}-build-actions',
	// disabled: true,
	menu: [{
		text: _('{$cmpNamespace}.schema_build_one'),
		handler: function() {
			// Call the function with action
			var grid = Ext.getCmp('{$cmpNamespace}-grid-{$gridClass|lower}');
			grid.previewOrBuild('build_skip', 'true');
		}
	},'-',{
		text: _('{$cmpNamespace}.schema_build_two'),
		handler: function() {
			// Call the function with action
			var grid = Ext.getCmp('{$cmpNamespace}-grid-{$gridClass|lower}');
			grid.previewOrBuild('build_delete', 'true');
		}
	},'-',{
		text: _('{$cmpNamespace}.schema_build_three'),
		handler: function() {
			// Call the function with action
			var grid = Ext.getCmp('{$cmpNamespace}-grid-{$gridClass|lower}');
			grid.previewOrBuild('build_delete_drop', 'true');
		}
	}]
};

// Add the two buttons back to the front of the toolbar
EB.model.ebObject.grid.config.tbar.splice(0,0,createBtn,schemaBtn,buildBtn);

// Apply function overrides
Ext.apply(EB.model['{$gridClass}'].grid.overrides, {
	
	// Function to handle schema and build functions
	previewOrBuild: function (key, value) {
		// Set default params
		var params = {
			action: '{if $isV3}{$phpNamespace}\\Processors\\ebPackage\\{else}ebpackage/{/if}Build',
			id: EB.model.ebPackage.data.selectedId,
		};

		// Add passed parameter
		params[key] = value;

		// Make the ajax call
		Ext.Ajax.request({
			url: {$phpNamespace}.config.connectorUrl,
			params: params,
			success: function (response) {
				var msg = "Unknown error...";
				try {
					var data = JSON.parse(response.responseText);
					if (data.success == true) {
						// Show the preview window and pass in the data object
						EB.window.schema.preview = MODx.load({
							xtype: '{$cmpNamespace}-window-schema',
							record: data.object
						});
						EB.window.schema.preview.show().center();

						// If there is a message, display it
						if (data.message) {
							Ext.Msg.alert("Possible Error", data.message);
						}
					}
					else {
						msg = data.message ? data.message : JSON.stringify(data, null, 2);
					}
				}
				catch(e) {}
			}
		});
	}

});