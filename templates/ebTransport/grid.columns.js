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
			header: _("{$cmpNamespace}.{$gridClass|lower}.package_short"),
			dataIndex: "package",
			sortable: false,
			editor: {
				xtype: '{$gridClass|lower}-combo-package',
				renderer: true
			}
		},
		{
			header: _("{$cmpNamespace}.{$gridClass|lower}.category_short"),
			dataIndex: "category",
			sortable: false,
			editor: {
				xtype: '{$gridClass|lower}-combo-category',
				renderer: true
			}
		},
		{
			header: _("{$cmpNamespace}.{$gridClass|lower}.major_short"),
			dataIndex: "major",
			sortable: false,
		},
		{
			header: _("{$cmpNamespace}.{$gridClass|lower}.minor_short"),
			dataIndex: "minor",
			sortable: false,
		},
		{
			header: _("{$cmpNamespace}.{$gridClass|lower}.patch_short"),
			dataIndex: "patch",
			sortable: false,
		},
		{
			header: _("{$cmpNamespace}.{$gridClass|lower}.release_short"),
			dataIndex: "release",
			sortable: false,
			editor: {
				xtype: '{$cmpNamespace}-combo-release',
				renderer: true
			}
		},
		{
			header: _("{$cmpNamespace}.{$gridClass|lower}.release_index_short"),
			dataIndex: "release_index",
			sortable: false,
		}
	]

});

Ext.apply(EB.model['{$gridClass}'].grid.overrides, {

	// Function to handle context build
	buildTransport: function (transportId) {
		// Make the ajax request
		Ext.Ajax.request({
			url: {$phpNamespace}.config.connectorUrl,
			params: { 
				action: '{if $isV3}{$phpNamespace}\\Processors\\ebTransport\\{else}ebtransport/{/if}TpBuild',
				id: transportId
			},
			success: function (response) {
				var msg = "Unknown error...";
				try {
					var data = JSON.parse(response.responseText);
					if (data.success == true) {
						// Show a success message
						if (data.message) {
							msg = data.message
						}
						Ext.Msg.alert('Build Transport Package', msg);
					}
					else {
						msg = data.message ? data.message : JSON.stringify(data, null, 2);
						Ext.Msg.alert('Failure', msg);
					}
				}
				catch(e) {}
			}
		});
	},

	// Add resolver files
	addResolver: function (transportId) {
		// First prompt for a file name
		var _this = this;
		Ext.MessageBox.prompt(
			_("extrabuilder.ebtransport.add_resolver_title"),
			_("{$cmpNamespace}.{$gridClass|lower}.add_resolver_prompt"),
			function(btn, text) {
				// If btn is ok, proceed
				if (btn === 'ok') {
					// Make the ajax request
					Ext.Ajax.request({
						url: {$phpNamespace}.config.connectorUrl,
						params: { 
							action: '{if $isV3}{$phpNamespace}\\Processors\\ebTransport\\{else}ebtransport/{/if}TpActions',
							id: transportId,
							subAction: 'addResolver',
							filename: text
						},
						success: function (response) {
							var msg = "Unknown error...";
							try {
								var data = JSON.parse(response.responseText);
								if (data.success == true) {
									// Show a success message
									if (data.message) {
										msg = data.message
									}
									Ext.Msg.alert('Add Resolver', msg);
								}
								else {
									msg = data.message ? data.message : JSON.stringify(data, null, 2);
									Ext.Msg.alert('Failure', msg);
								}
							}
							catch(e) {}
						}
					});
				}
			},
			_this
		);
	},

	// Add standard 'tables' resolver
	transportAction: function (transportId, subAction) {
		// Make the ajax request
		Ext.Ajax.request({
			url: {$phpNamespace}.config.connectorUrl,
			params: { 
				action: '{if $isV3}{$phpNamespace}\\Processors\\ebTransport\\{else}ebtransport/{/if}TpActions',
				id: transportId,
				subAction: subAction
			},
			success: function (response) {
				var msg = "Unknown error...";
				try {
					var data = JSON.parse(response.responseText);
					if (data.success == true) {
						// Show a success message
						if (data.message) {
							msg = data.message
						}
						Ext.Msg.alert('Add Resolver', msg);
					}
					else {
						msg = data.message ? data.message : JSON.stringify(data, null, 2);
						Ext.Msg.alert('Failure', msg);
					}
				}
				catch(e) {}
			}
		});
	},

	// Backup all elements
	backupElements: function (transportId) {
		// Make the ajax request
		Ext.Ajax.request({
			url: {$phpNamespace}.config.connectorUrl,
			params: { 
				action: '{if $isV3}{$phpNamespace}\\Processors\\ebTransport\\{else}ebtransport/{/if}TpBuild',
				id: transportId,
				backup_only: 'true'
			},
			success: function (response) {
				var msg = "Unknown error...";
				try {
					var data = JSON.parse(response.responseText);
					if (data.success == true) {
						// Show a success message
						if (data.message) {
							msg = data.message
						}
						Ext.Msg.alert('Back Up Elements', msg);
					}
					else {
						msg = data.message ? data.message : JSON.stringify(data, null, 2);
						Ext.Msg.alert('Failure', msg);
					}
				}
				catch(e) {}
			}
		});
	}

});