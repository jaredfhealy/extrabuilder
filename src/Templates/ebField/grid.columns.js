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
			header: _("{$namespace|lower}.{$gridClass|lower}.column_name"),
			dataIndex: "column_name",
			sortable: true,
			editor: {
				xtype: "textfield"
			}
		},
		{
			header: _("{$namespace|lower}.{$gridClass|lower}.dbtype"),
			dataIndex: "dbtype",
			sortable: true,
			editor: {
				xtype: "textfield"
			}
		},
		{
			header: _("{$namespace|lower}.{$gridClass|lower}.phptype"),
			dataIndex: "phptype",
			sortable: true,
			editor: {
				xtype: "textfield"
			}
		},
		{
			header: _("{$namespace|lower}.sort_order"),
			dataIndex: "sortorder",
			sortable: true,
			editor: {
				xtype: "textfield"
			}
		},
		{
			header: "Actions",
			dataIndex: 'actions',
			sortable: false
		}
	]
	
});