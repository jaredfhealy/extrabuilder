{* Output the correct template for the application 
{include file="{$loadApp}/home.tpl"}*}
<script>
// Global object to use with configuration placeholders
window.{$jsPrefix} = window.{$jsPrefix} || {};

// Load the rest from ExtraBuilder.js using EB.<prop>
{include file="Global/$namespace.js"}

Ext.onReady(function() {
	/**
	 * Define a configuration function for our component/class.
	 * 
	 * Further details of the superclass constructor call can
	 * be found here.
	 * 
	 * Ext.extend documentation
	 * https://docs.sencha.com/extjs/3.4.0/#!/api/Ext-method-extend
	 *
	 */
	var {$namespace}Class = function (config) {
		config = config || {};
		{$namespace}Class.superclass.constructor.call(this, config);
	};

	{literal}/**
	 * Use ExtJS to extend this from Ext.Component
	 * Per docs referenced above, we're using the 3-argument format
	 * 
	 * overrides: A literal with members which are copied into the subclass's 
	 *            prototype, and are therefore shared among all instances of 
	 *            the new class.
	 *
	 * @param {function} subclass The subclass constructor
	 * @param {function} superclass The constructor of the class being extended
	 * @param {object} overrides Shared data object for class instances
	 */{/literal}
	Ext.extend({$namespace}Class, Ext.Component, {
		page:{}, window:{}, grid:{}, tree:{}, panel:{}, combo:{}, config:{}, renderer:{}
	});

	// Register our main component constructor class
	Ext.reg('{$namespace|lower}-{$a|lower}-component', {$namespace}Class);

	// Create an instance of our component class and pass our config object
	{$namespace} = new {$namespace}Class({$jsPrefix}.config);

	// Load panels and widgets
	{foreach $model as $gridClass => $classDetail}

		{* If loadApp is not TransportBuilder, include all classes except transport *}
		{if $loadApp === 'PackageBuilder' && $gridClass !== 'ebTransport'}

			// PackageBuilder: Include class grids and forms
			{include file='class.base.tpl'}

			{if $gridClass == 'ebPackage'}

				// Define and register the main {$loadApp} home panel
				{include file="{$loadApp}/home.panel.tpl"}
			{/if}
		{/if}

		{* If loadApp IS TransportBuilder, ONLY include transport *}
		{if $loadApp === 'TransportBuilder' && $gridClass === 'ebTransport'}

			// TransportBuilder: Include class grids and forms
			{include file='class.base.tpl'}

			{include file="{$loadApp}/home.panel.tpl"}
		{/if}

	{/foreach}

	{if $loadApp === 'PackageBuilder'}
		// Load the import schema window before registration so it can override buttons on ebPackage
		{include file='ebPackage/window.schema.import.tpl'}
	{/if}

	// Run extOnReady functions
	for (const extOnReady of EB.load.extOnReady) {
		// Trigger the function
		extOnReady();
	}

	// Load all other classes async
	setTimeout(function(){

		// Run asyncAfterReady functions
		for (const asyncAfterReady of EB.load.asyncAfterReady) {
			// Trigger the function
			asyncAfterReady();
		}

		// Add the remaining tabs to the panel, start the count at 1 instead of 0
		var ebPanel = Ext.ComponentMgr.get('extrabuilder-tabs');
		for (var i = 1; i < EB.panel.tabs.length; i++) {
			ebPanel.add(EB.panel.tabs[i]);
		}

		{if $loadApp === 'PackageBuilder'}
			// Include modal windows for PackageBuilder
			{include file="ebPackage/window.schema.preview.tpl"}
		{/if}

	}, 500);

	/**
	 * The page is the main layout container for our application
	 * functionality. Define the page and components contained.
	 * 
	 * ExtraBuilder.page.Index
	 * extrabuilder-page-index
	 */
	EB.constructExtendRegister(
		{$namespace}.page,
		'{$a}',
		'{$namespace|lower}-page-{$a|lower}',
		{
			components: [{
				xtype: '{$namespace|lower}-panel-{$a|lower}',
				renderTo: 'modx-panel-holder'
			}]
		},
		MODx.Component
	);

	/**
	 * Trigger MODX to load our page component which then 
	 * loads and renders the components within it
	 */
	MODx.load({ xtype: '{$namespace|lower}-page-{$a|lower}'});
});
</script>

{* Include global css *}
{include file='css/css.tpl'}

{* Load class specifc css *}
{foreach $model as $gridClass => $classDetail}

	{if $loadApp === 'PackageBuilder' && $gridClass !== 'ebTransport'}

		{include file='css/class.specific.css.tpl'}
	{/if}

	{if $loadApp === 'TransportBuilder' && $gridClass === 'ebTransport'}

		{include file='css/class.specific.css.tpl'}
	{/if}

{/foreach}