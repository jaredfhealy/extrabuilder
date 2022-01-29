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
			header: _("{$cmpNamespace}.{$gridClass|lower}.object"),
			dataIndex: "object",
			sortable: true,
			hidden: true
		},
		{
			header: _("{$cmpNamespace}.{$gridClass|lower}.alias_short"),
			dataIndex: "alias",
			sortable: true,
			editor: {
				xtype: "textfield"
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
			header: _("{$cmpNamespace}.{$gridClass|lower}.local_short"),
			dataIndex: "local",
			sortable: true,
			editor: {
				xtype: "textfield"
			}
		},
		{
			header: _("{$cmpNamespace}.{$gridClass|lower}.foreign_short"),
			dataIndex: "foreign",
			sortable: true,
			editor: {
				xtype: "textfield"
			}
		},
		{
			header: _("{$cmpNamespace}.{$gridClass|lower}.cardinality"),
			dataIndex: "cardinality",
			sortable: true,
			editor: {
				xtype: "textfield"
			}
		},
		{
			header: _("{$cmpNamespace}.{$gridClass|lower}.owner"),
			dataIndex: "owner",
			sortable: true,
			editor: {
				xtype: "textfield"
			}
		},
		{
			header: _("{$cmpNamespace}.{$gridClass|lower}.sortorder"),
			dataIndex: "sortorder",
			sortable: true,
			editor: {
				xtype: "textfield"
			}
		}
	]
	
});