// Model object and details
EB.config = {
	config: JSON.parse("{json_encode($config)|escape:'javascript'}")
};
EB.model = JSON.parse("{json_encode($model)|escape:'javascript'}");

// Placeholder for panel config
EB.panel = {
	config: {},
	tabs: [],
	overrides: {}
};

// Placeholder for window config
EB.window = {
	config: {},
	create: {
		overrides: {}
	},
	overrides: {}
}

// After load functions
EB.load = {
	extOnReady: [],
	asyncAfterReady: []
}


/**
 * Wrapper function to simplify creation of components
 * 
 * This function dynamically takes the 3 steps needed to register and add
 * each component.
 *  1. Create constructor class and register the superclass constructor call
 *  2. Extend our component from the type we want to use
 *  3. Register our component by xtype
 * {literal}
 * @param {object} subclassObj The object to add our constructor to
 * @param {string} subclassKey The key to use for our constructor
 * @param {string} xtypeString String to use as the xtype id
 * @param {object} sourceConfig Configuration object defining the component
 * @param {object} superClass The class we're extending
 * @param {object} overrides Any shared functions or functionality{/literal}
 */
EB.constructExtendRegister = function (subclassObj, subclassKey, xtypeString, sourceConfig, superClass, overrides) {
	// Defaults if missing parameters
	overrides = overrides || {};

	// Create the constructor function with passed in sourceConfig
	subclassObj[subclassKey] = function (config) {
		config = config || {};
		Ext.applyIf(config, sourceConfig);
		subclassObj[subclassKey].superclass.constructor.call(this, config);
	}

	// Extend the component which triggers the constructor call with any overrides
	Ext.extend(subclassObj[subclassKey], superClass, overrides);

	// Register the xtype to take advantage of lazy loading
	Ext.reg(xtypeString, subclassObj[subclassKey]);
}

// Global functions
// TODO: Move to root component instead?
EB.fn = {};
EB.fn.editLinkRender = function (value, metaData, record, rowIndex, colIndex, store) {
	value = "<i style='text-align:center; cursor:pointer;' class='icon icon-external-link icon-lg'></i>";
	return value;
}

// Function to get the selected parent
EB.fn.getParentId = function(nsLower, parentClassLower) {
	// Get the selection model
	console.log("Getting parentId function");
	var parentGrid = Ext.ComponentMgr.get(nsLower + '-grid-' + parentClassLower);
	if (!parentGrid) {
		return;
	}
	console.log(parentGrid);
	var sm = parentGrid.getSelectionModel();
	if (!sm.hasSelection()) {
		console.log("No selection");
		return 0;
	}
	
	// Return the selected row id
	var result = sm.getSelected().id;
	return result;
}