<?php
require_once(ABSPATH . 'wp-admin/includes/plugin-install.php'); //for plugins_api..
require_once(ABSPATH . 'wp-admin/includes/file.php');
require_once(ABSPATH . 'wp-admin/includes/template.php');
require_once(ABSPATH . 'wp-admin/includes/misc.php');
require_once(ABSPATH . 'wp-admin/includes/class-wp-upgrader.php');

if( !class_exists('QodyCustomUpgrader') )
{
	class QodyCustomUpgrader extends Plugin_Upgrader
	{
		function upgrade($plugin_url)
		{
			$this->init();
			$this->upgrade_strings();
			
			add_filter('upgrader_pre_install', array(&$this, 'deactivate_plugin_before_upgrade'), 10, 2);
			add_filter('upgrader_clear_destination', array(&$this, 'delete_old_plugin'), 10, 4);
			//'source_selection' => array(&$this, 'source_selection'), //theres a track ticket to move up the directory for zip's which are made a bit differently, useful for non-.org plugins.
	
			$this->run(array(
				'package' => $plugin_url,
				'destination' => WP_PLUGIN_DIR,
				'clear_destination' => true,
				'clear_working' => true,
				'hook_extra' => array(
							'plugin' => $plugin
				)
			));
			// Cleanup our hooks, incase something else does a upgrade on this connection.
			remove_filter('upgrader_pre_install', array(&$this, 'deactivate_plugin_before_upgrade'));
			remove_filter('upgrader_clear_destination', array(&$this, 'delete_old_plugin'));
	
			if ( ! $this->result || is_wp_error($this->result) )
				return $this->result;
	
			// Force refresh of plugin update information
			delete_site_transient('update_plugins');
		}
	}
}

if( !class_exists('Blank_Skin') )
{
	class Blank_Skin extends Bulk_Plugin_Upgrader_Skin {
	
		function __construct($args = array()) {
			parent::__construct($args);
		}
		
		function header() {}
		function footer() {}
		function error($errors) {}
		function feedback($string) {}
		function before() {}
		function after() {}
		function bulk_header() {}
		function bulk_footer() {}
		function show_message() {}
		
		function flush_output() {
			ob_end_clean();
		}
	}
}
?>