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
			header: "Objects",
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
					panel.openChildTab(grid, '{$cmpNamespace}', '{$gridClass}', '{$classDetail["childClass"]}', '{$classDetail["tabDisplayField"]}');
				}
			}
		},
		{
			header: _("{$cmpNamespace}.{$gridClass|lower}.display_short"),
			dataIndex: "display",
			sortable: true,
			editor: {
				xtype: "textfield"
			}
		},
		{
			header: _("{$cmpNamespace}.{$gridClass|lower}.package_key_short"),
			dataIndex: "package_key",
			sortable: true,
			editor: {
				xtype: "textfield"
			}
		},
		{
			header: _("{$cmpNamespace}.{$gridClass|lower}.base_class"),
			dataIndex: "base_class",
			sortable: true,
			hidden: true
		},
		{
			header: _("{$cmpNamespace}.{$gridClass|lower}.platform"),
			dataIndex: "platform",
			sortable: true,
			hidden: true
		},
		{
			header: _("{$cmpNamespace}.{$gridClass|lower}.default_engine"),
			dataIndex: "default_engine",
			sortable: true,
			hidden: true
		},
		{
			header: _("{$cmpNamespace}.{$gridClass|lower}.phpdoc_package_short"),
			dataIndex: "phpdoc_package",
			sortable: true,
			hidden: true
		},
		{
			header: _("{$cmpNamespace}.{$gridClass|lower}.phpdoc_subpackage_short"),
			dataIndex: "phpdoc_subpackage",
			sortable: true,
			hidden: true
		},
		{
			header: _("{$cmpNamespace}.{$gridClass|lower}.version_short"),
			dataIndex: "version",
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
		},
		{
			header: _("{$cmpNamespace}.{$gridClass|lower}.core_path"),
			dataIndex: "core_path",
			sortable: true,
			hidden: true
		},
		{
			header: _("{$cmpNamespace}.{$gridClass|lower}.assets_path"),
			dataIndex: "assets_path",
			sortable: true,
			hidden: true
		}
	]

});