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
			header: _("{$cmpNamespace}.{$gridClass|lower}.column_name"),
			dataIndex: "column_name",
			sortable: true,
			editor: {
				xtype: "textfield"
			}
		},
		{
			header: _("{$cmpNamespace}.{$gridClass|lower}.dbtype"),
			dataIndex: "dbtype",
			sortable: true,
			editor: {
				xtype: "textfield"
			}
		},
		{
			header: _("{$cmpNamespace}.{$gridClass|lower}.precision"),
			dataIndex: "precision",
			sortable: true,
			editor: {
				xtype: "textfield"
			}
		},
		{
			header: _("{$cmpNamespace}.{$gridClass|lower}.phptype"),
			dataIndex: "phptype",
			sortable: true,
			editor: {
				xtype: "textfield"
			}
		},
		{
			header: _("{$cmpNamespace}.{$gridClass|lower}.index"),
			dataIndex: "index",
			sortable: true
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