var app = new Vue({
	el: '#app',
	data: function () {
		return {
			siteId: '',
			title: 'Build a Transport Package',
			alerts: [],
			transport: {},
			categories: [],
			category: {
				category: 0
			},
			categoryOptions: {},
			categoryAction: '',
			packages: [],
			package: {},
			meta: {
				ebTransport: {
					listColumns: ['id','package.display', 'package.package_key', 'category', 'sortorder'],
					label_list: 'Transports',
					label_singular: 'Transport',
					fields: "",
					getlist: 'transport/getlist',
					create: 'transport/create',
					update: 'transport/update',
					remove: 'transport/remove',
					get: 'transport/get',
					parentField: 'package',
					dataKey: 'transport',
				}
			},
			model: 'ebTransport',
			view: 'table',
			action: 'list',
			dataLoaded: false,
			items: [],
			form: 'listTransport',
			formErrors: false,
			resolverName: '',
			resolverErrors: false
		}
	},
	computed: {
		transportTabLabel: function () {
			return this.transport.id ? this.meta['ebTransport'].label_singular : 'New Transport';
		},
		sortedItems: function () {
			return _.sortBy(this.items, 'sortorder');
		},
		transportSelected: function () {
			var c1 = this.transport.id !== null;
			var c2 = this.transport.id !== 0;
			var c3 = this.transport.id !== '';
			var c4 = typeof this.transport.id !== 'undefined';
			return (c1 && c2 !== 0 && c3 && c4);
		}
	},
	methods: {
		navigate: function(action, model, item) {
			// Update the item and action
			item = item ? item : '';
			if (item) {
				this[this.meta[this.model].dataKey] = item;
			}
			this.action = action;

			// If create or update show the form
			if (action === 'create') {
				this.getPackages();
				this.getCategories();

				// Set default values
				this[this.meta[this.model].dataKey] = JSON.parse(this.meta[this.model].fields);
			}
			else if (action === 'update' && item) {
				// Get the item
				this.getModel(item.id);
			}
			else if (action === 'list') {
				// Clear the object
				this[this.meta[this.model].dataKey] = {};
				this.items = [];
				this.loadListData();
			}

			// Update the form and view, replace the class prefix 'eb'
			// with the action, ex: ebPackage = updatePackage
			this.form = model.replace('eb', action);
			this.view = action === 'list' ? 'table' : 'form';
		},
		saveModel: function(backToList) {
			// Validate the form
			if (!this.$refs.transportForm.checkValidity()) {
				this.formErrors = true;
				return;
			}
			else {
				this.formErrors = false;
			}

			// Default to false
			backToList = backToList || false;

			// Save the data
			var _this = this;
			var data = this[this.meta[this.model].dataKey];
			data.action = this.meta[this.model][this.action];
			data.id = this.action == 'create' ? undefined : data.id;

			// Make the api calls
			$.ajax({
				type: 'POST',
				headers: { modAuth: this.siteId },
				url: '/assets/components/extrabuilder/connector.php',
				data: data,
				dataType: 'json'
			}).always(function (response) {
				if (response.success) {
					// Show the alert
					_this.addAlert('success', _this.meta[_this.model].label_singular + " saved successfully");
					if (backToList) {
						_this.navigate('list', _this.model);
					}
					else {
						if (_this.mode == 'create') {
							_this[_this.meta[_this.model].dataKey].id = response.object.id;
							_this.navigate('update', _this.model);
						}
					}
				}
			});
		},
		deleteModel: function() {
			// Delete the record
			var _this = this;
			$.ajax({
				type: 'POST',
				headers: { modAuth: this.siteId },
				url: '/assets/components/extrabuilder/connector.php',
				data: {
					'action': this.meta[this.model].remove,
					'id': this[this.meta[this.model].dataKey].id
				},
				dataType: 'json'
			})
				.always(function (response) {
					if (response.success) {
						// Show the alert
						_this.addAlert('success', _this.meta[_this.model].label_singular + " DELETED successfully");
						_this.navigate('list', _this.model);
					}
					else {
						_this.addAlert('danger', 'Unable to delete: ' + response.message || "No error returned.");
					}
				});
		},
		addAlert: function (type, message) {
			var alert = {
				type: type,
				message: message,
				id: Date.now()
			};
			this.alerts.push(alert);

			// If this is success, auto dismiss
			if (type == 'success') {
				var _this = this;
				setTimeout(function () {
					for (var index in _this.alerts) {
						if (_this.alerts[index].id === alert.id) {
							_this.removeAlert(index);
						}
					}
					//_this.removeAlert(alert.id);
				}, 1000);
			}
		},
		removeAlert: function (index) {
			this.$delete(this.alerts, index);
		},
		getModel: function (id) {
			// Get packages to select from
			var _this = this;
			$.ajax({
				type: 'POST',
				headers: { modAuth: this.siteId },
				url: '/assets/components/extrabuilder/connector.php',
				data: {
					action: this.meta[this.model].get,
					id: id
				},
				dataType: 'json'
			}).always(function (response) {
				// If we have results
				if (response.success) {
					// Make sure items is empty, and add new items
					_this[_this.meta[_this.model].dataKey] = response.object;
				}
			});
		},
		getPackages: function () {
			// Get packages to select from
			var _this = this;
			$.ajax({
				type: 'POST',
				headers: { modAuth: this.siteId },
				url: '/assets/components/extrabuilder/connector.php',
				data: {
					action: 'package/getlist'
				},
				dataType: 'json'
			}).always(function (response) {
				// If we have results
				if (response.results) {
					// Make sure items is empty, and add new items
					_this.packages = response.results;
				}
			});
		},
		getCategories: function () {
			// Get categories
			var _this = this;
			$.ajax({
				type: 'POST',
				url: '/assets/components/extrabuilder/connector.php?action=getcategories',
				headers: { modAuth: this.siteId },
				data: {
					query: JSON.stringify({
						parent: 0
					})
				},
				dataType: 'json'
			}).always(function (response) {
				if (response.success) {
					_this.categories = response.results;
				}
			});
		},
		loadListData: function() {
			// Get the transport entries
			var _this = this;
			$.ajax({
				type: 'POST',
				headers: { modAuth: this.siteId },
				url: '/assets/components/extrabuilder/connector.php',
				data: {
					action: this.meta[this.model].getlist
				},
				dataType: 'json'
			}).always(function (response) {
				// If we have results
				if (response.results) {
					// Update the items array
					_this.items = response.results;
					_this.dataLoaded = true;
				}
			});
		},
		getFieldDefaults: function() {
			// Get field value defaults
			var _this = this;
			$.ajax({
				type: 'POST',
				url: '/assets/components/extrabuilder/connector.php?action=getdefaults',
				headers: { modAuth: this.siteId },
				dataType: 'json'
			}).always(function (response) {
				if (response.success) {
					var data = response.object;
					_this.meta[_this.model].fields = data[_this.model];
				}
			});
		},
		buildTransport: function(backupOnly) {
			// Set data
			var data = {
				action: 'transport/build',
				id: this.transport.id
			};
			if (backupOnly) {
				data['backup_only'] = 'true';
			}
			// Call the build function
			var _this = this;
			$.ajax({
				type: 'POST',
				url: '/assets/components/extrabuilder/connector.php',
				headers: { modAuth: this.siteId },
				data: data,
				dataType: 'json'
			}).always(function (response) {
				if (response.success) {
					_this.addAlert('success', "Success: " + response.message);
				}
				else {
					_this.addAlert('danger', "Error: " + response.message); 
				}
			});
		},
		createResolver: function() {
			// Validate the form
			if (!this.$refs.resolverForm.checkValidity()) {
				this.resolverErrors = true;
				return;
			}
			else {
				this.resolverErrors = false;
			}

			// Call the build function
			var _this = this;
			$.ajax({
				type: 'POST',
				url: '/assets/components/extrabuilder/connector.php',
				headers: { modAuth: this.siteId },
				data: {
					action: 'transport/addresolver',
					id: this.transport.id,
					filename: this.resolverName.replace('.php', '')
				},
				dataType: 'json'
			}).always(function (response) {
				if (response.success) {
					_this.addAlert('warning', "Success: " + response.message);
				}
				else {
					_this.addAlert('danger', "Error: " + response.message); 
				}
			});
		},
		addTablesResolver: function() {
			// Call the build function
			var _this = this;
			$.ajax({
				type: 'POST',
				url: '/assets/components/extrabuilder/connector.php',
				headers: { modAuth: this.siteId },
				data: {
					action: 'transport/addtablesresolver',
					id: this.transport.id
				},
				dataType: 'json'
			}).always(function (response) {
				if (response.success) {
					_this.addAlert('warning', "Success: " + response.message);
				}
				else {
					_this.addAlert('danger', "Error: " + response.message); 
				}
			});
		}
	},
	mounted: function () {
		// Setup the siteId
		if (parent !== self) {
			this.siteId = parent.window.MODx.siteId + '';
		}
		else {
			// Value was passed with config as a local window variable
			this.siteId = siteId + '';
		}

		// Load the Transport list
		this.loadListData();

		// Get the supporting data
		this.$nextTick(function(){
			this.getFieldDefaults();
			this.getPackages();
			this.getCategories();
		});
	}
});