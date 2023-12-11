Ext.apply(EB.panel.overrides, {
	/**
	 * Open/show the child tab for the selected row
	 * 
	 * If it's the first time, add the component to the grid
	 * which triggers it to render and the API call to query
	 * for data. Any time after that, just update the store
	 * parameters to the new parentId and refresh
	 * {literal}
	 * @param {object} grid The grid component passed by the "click" listener
	 * @param {string} nsLower The namespace in lowercase
	 * @param {string} className The parent class clicked from
	 * @param {string} childClassName The child class type to open
	 * @param {string} tabDisplayField The field to add to the tab title
	 * @param {boolean} setActive Override to false to NOT set the new tab as active{/literal}
	 * 
	 */
	 openChildTab: function (grid, nsLower, className, childClassName, tabDisplayField, setActive) {
		// Get the selected row
		var sm = grid.getSelectionModel();
		if (!sm.hasSelection()) {
			console.log("No selection model");
			return;
		}

		// Defaults
		setActive = typeof setActive !== 'undefined' ? setActive : true;

		// Store a reference to our grid
		var parentPanel = grid.findParentByType('modx-tabs');
		var childClassLower = childClassName.toLowerCase();

		// Selected row
		var selected = sm.getSelected();

		// Set the selected ID on the class data object
		if (className) {
			EB.model[className].data.selectedId = selected.id;
		}

		// Try getting the component to see if it exists
		var tabCmp = Ext.ComponentMgr.get(nsLower + '-grid-' + childClassLower + '-tab');
		if (tabCmp) {
			// Reset the title
			tabCmp.setTitle(_(nsLower + '.' + childClassLower + '.tab_title') + 
			" (" + selected.data[tabDisplayField] + ")");

			// Get the store
			var store = Ext.StoreMgr.get(nsLower + '-store-' + childClassLower);
			if (store) {
				// Update the parameters and reload
				store.removeAll();
				store.baseParams.parentId = selected.id;
				store.lastOptions.params.parentId = selected.id;
				store.lastOptions.params.start = 0;
				store.lastOptions.params.search = "";
				store.reload();

				// Clear the search input
				Ext.getCmp(nsLower + '-' + childClassLower + '-search-input').setValue("");
			}

			// Unhide the panel if its an existing hidden panel
			parentPanel.unhideTabStripItem(tabCmp);
			tabCmp.tabEl.classList.remove('x-hide-display');

			// Set the active tab
			if (setActive) {
				parentPanel.setActiveTab(tabCmp);
			}
		}
	},

	showHelpModal: function(title, content) {
		Ext.Msg.show({
			title: title,
			msg: content,
			buttons: Ext.Msg.OK,
			icon: Ext.MessageBox.QUESTION,
			minWidth: 700,
			maxWidth: 1000
		});
	}
});