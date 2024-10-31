<?php
/**
 * Plugin Name: Qody's Framework
 * Plugin URI: http://qody.co
 * Description: Framework required for all plugins managed by Qody's owls.
 * Version: 2.3.3
 * Author: Qody LLC
 * Author URI: http://qody.co
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 */

if( !function_exists('qody_framework_update_warning') )
{
	function qody_framework_update_warning()
	{
		$data = "
	<div class='updated fade'>
	<p><strong>Big framework change. Please <a class=\"thickbox\" href=\"".admin_url('plugin-install.php?tab=plugin-information&plugin=qodys-framework&TB_iframe=true' )."\">update the Qody's Framework plugin</a> for it and the rest of Qody's plugins to work again.</p>
	</div>";
		echo $data;
	}
	function qody_redirector_update_warning()
	{
		$data = "
	<div class='updated fade'>
	<p><strong>Big framework change. Please <a class=\"thickbox\" href=\"".admin_url('plugin-install.php?tab=plugin-information&plugin=qodys-redirector&TB_iframe=true' )."\">update the Qody's Redirector plugin</a> for it and the rest of Qody's plugins to work again.</p>
	</div>";
		echo $data;
	}
	function qody_optiner_update_warning()
	{
		$data = "
	<div class='updated fade'>
	<p><strong>Big framework change. Please <a class=\"thickbox\" href=\"".admin_url('plugin-install.php?tab=plugin-information&plugin=qodys-optiner&TB_iframe=true' )."\">update the Qody's Optiner plugin</a> for it and the rest of Qody's plugins to work again.</p>
	</div>";
		echo $data;
	}
	function qody_fbmeta_update_warning()
	{
		$data = "
	<div class='updated fade'>
	<p><strong>Big framework change. Please <a class=\"thickbox\" href=\"".admin_url('plugin-install.php?tab=plugin-information&plugin=qodys-fb-meta&TB_iframe=true' )."\">update the Qody's FB Meta plugin</a> for it and the rest of Qody's plugins to work again.</p>
	</div>";
		echo $data;
	}
}

require_once(ABSPATH . 'wp-admin/includes/plugin.php');

// which versions of the plugins are manditory
$fields = array();
$fields['qodys-framework'] = '1.9.1';
$fields['qodys-redirector'] = '2.0.4';
$fields['qodys-optiner'] = '3.1.4';
$fields['qodys-fb-meta'] = '2.0.0';

foreach( $fields as $key => $value )
{
	$installed_plugins = get_plugins( '/'.$key );
	//echo "<pre>".print_r( $installed_plugins, true )."</pre>";
	
	if( !$installed_plugins )
		continue;
	
	foreach( $installed_plugins as $key2 => $value2 )
	{
		if( version_compare($value2['Version'], $value, "<") )
		{
			$too_low = true;
			
			switch( $key )
			{
				case 'qodys-framework': add_action('admin_notices', 'qody_framework_update_warning', 0 ); break;
				case 'qodys-redirector': add_action('admin_notices', 'qody_redirector_update_warning', 1 ); break;
				case 'qodys-optiner': add_action('admin_notices', 'qody_optiner_update_warning', 2 ); break;
				case 'qodys-fb-meta': add_action('admin_notices', 'qody_fbmeta_update_warning', 3 ); break;
			}
		}
	}
}

if( $too_low )
{
	// these trick the plugins into working until the framework is updated
	if( !class_exists('QodyPlugin') )	{class QodyPlugin{function __construct(){}public function __call($name, $arguments){}}}
	if( !class_exists('QodyPostType') )	{class QodyPostType{function __construct(){}public function __call($name, $arguments){}}}
	if( !class_exists('QodyPage') )		{class QodyPage{function __construct(){}public function __call($name, $arguments){}}}
	if( !class_exists('QodyOverseer') )	{class QodyOverseer{function __construct(){}public function __call($name, $arguments){}}}
	
	return;
}

define( "QODYS_FRAMEWORK_PREFIX", 'frmwk' );
define( "QODYS_FRAMEWORK_GLOBALS_CONTAINER_STRING", 'QodyGlobals' );

if( !class_exists('QodyPlugin') )
{
	class QodyPlugin
	{
		// general plugin variables
		var $m_plugin_name = 'Qodys Framework';
		var $m_plugin_slug = 'qodys-framework';
		var $m_plugin_file;
		var $m_raw_file;
		
		var $m_plugin_folder;
		var $m_plugin_url;
		
		// owl variables
		var $m_owl_name = 'Qody';
		var $m_owl_gender = 'male';
		var $m_owl_image = 'http://plugins.qody.co/wp-content/uploads/2011/09/owl6a-320x320.png';
		var $m_owl_buy_url = 'http://plugins.qody.co/owls/';
		
		// current page-specific variables
		var $m_page_url;
		var $m_page_url_args;
		var $m_page_referer;
		
		var $m_pages = array();
		var $m_globals = array();
		
		// Plugin-wide variables
		var $m_pre = QODYS_FRAMEWORK_PREFIX;
		var $m_plugin_version;
		var $m_overseer = null;
		
		function __construct()
		{
			// Function to run when plugin is activated
			register_activation_hook( $this->m_plugin_file, array( &$this, 'LoadDefaultOptions' ) );
			
			// Set the general class variables
			$this->SetupPluginVariables();
	
			// Store the current url and any variables in it
			$this->SetupPageVariables();
		}
		
		function RegisterPlugin( $ignore_hooks = false )
		{
			// Handle the plugin activation functions, like setting default options
			$this->RegisterScripts();
			$this->EnqueueScripts();
			
			$this->RegisterStyles();
			$this->EnqueueStyles();
			
			$this->LoadClasses();
			
			if( !$ignore_hooks )
				$this->SetupHooks();
			
			$this->HideFromNonAdmins();
		}
		
		function HideFromNonAdmins()
		{
			if( is_admin() && !current_user_can('administrator') )
				add_action('admin_menu', array( $this, 'RemoveMyMenu' ) );
		}
		
		function RemoveMyMenu()
		{
			global $menu;
			
			if( !$menu )
				return;
				
			foreach( $menu as $key => $value )
			{
				if( $value[0] == $this->m_plugin_name )
					unset( $menu[ $key ] );
			}		
		}
		
		function GetOverseer()
		{
			return $this->m_overseer;
		}
		
		function GetName()
		{
			return $this->m_plugin_name;
		}
		
		function GetOwlName()
		{
			return $this->m_owl_name;
		}
		
		function GetPre()
		{
			return $this->m_pre;
		}
		function GetPluginFolder()
		{
			return $this->m_plugin_folder;
		}
		
		function GetOwlImage()
		{
			return $this->m_owl_image;
		}
		
		function SetupHooks()
		{
			//add_action( 'admin_menu', array( $this, 'LoadWordpressPages' ), 1 );
			add_action( 'save_post', array( $this, 'SavePostCustom' ) );
			
			//add_action( 'wp_print_scripts', 'enqueue_my_scripts' );
			//add_action( 'wp_print_styles', array( $this, 'LoadStyles' ) );
			//add_action( 'admin_print_scripts', 'enqueue_my_scripts' );
			//add_action( 'admin_print_styles', array( $this, 'LoadStyles' ) );
		}
		
		function GetPluginData()
		{
			$data = get_plugin_data( $this->m_raw_file );
			
			return $data;
		}
		
		function LoadOverseers()
		{
			$this->PerformDynamicLoad( 'overseer' );
		}
		function LoadPostTypes()
		{
			$this->PerformDynamicLoad( 'post_type' );
		}
		
		function LoadHelpers()
		{
			$this->PerformDynamicLoad( 'helper' );
		}
		
		function LoadAdminPages()
		{
			global $qodys_framework;
			
			$this->PerformDynamicLoad( 'page', $qodys_framework );
			
			if( $this->PassApiCheck() )
				$this->PerformDynamicLoad( 'page' );
		}
		
		function LoadContentControllers()
		{
			$this->PerformDynamicLoad( 'controller' );
		}
		
		function PerformDynamicLoad( $load_type, $next_object = null )
		{
			$the_target = $this;
			
			if( $next_object != null )
				$the_target = $next_object;
			
			switch( $load_type )
			{
				case 'page':
					$container_slug = 'pages';
					$class_prefix = 'page';
					break;
				case 'post_type':
					$container_slug = 'posttypes';
					$class_prefix = 'posttype';
					break;
				case 'controller':
					$container_slug = 'content-controllers';
					$class_prefix = 'controller';
					break;
				case 'overseer':
					$container_slug = 'overseers';
					$class_prefix = 'overseer';
					break;
				case 'helper':
					$container_slug = 'helpers';
					$class_prefix = 'helper';
					break;
			}
				
			$dir = $the_target->m_plugin_folder.'/'.$container_slug;
			$url = $the_target->m_plugin_url.'/'.$container_slug;
			
			$data = $this->OrganizeDirectoryIntoArray( $dir );
			
			if( !$data )
				return;
			
			// cycle through each folder
			foreach( $data as $slug => $folder_contents )
			{
				if( !$folder_contents )
					return;
				
				// ignore single files at this level
				if( !$folder_contents || !is_array( $folder_contents ) )
					continue;
				
				$brain_key = array_search( 'index.php', $folder_contents );
				
				if( $brain_key === false )
					continue;
				
				// the file holding the page class
				$file_dir = $dir.'/'.$slug.'/'.$folder_contents[ $brain_key ];
				
				// sanity check
				if( !file_exists( $file_dir ) )
					continue;
					
				require_once( $file_dir );
				
				// used for things we want to reference, not to create
				if( $slug == 'include_only' )
					continue;
					
				$classes = $this->file_get_php_classes( $file_dir );
				
				if( !$classes )
					continue;
				
				$bits = explode( '.', $class_file );
				$class_slug = $bits[0];
				
				$this->LoadClass( $class_prefix.'_'.$slug, $classes[0] );
				
				$storage_class = $this->GetClass( $class_prefix.'_'.$slug );
				
				if( $load_type == 'page' || $load_type == 'controller' )
					$storage_class->SetSlug( $slug );
					
				$storage_class->m_asset_folder = $dir.'/'.$slug;
				$storage_class->m_asset_url = $url.'/'.$slug;
				
				// remove it from asset detection
				unset( $folder_contents[ $brain_key ] );
					
				$this->GetClass('array_matrix')->data = $storage_class->m_assets;
				
				$this->RecursivelyFetchAssets( $folder_contents, $storage_class->m_asset_folder, $storage_class->m_asset_url );
				
				$storage_class->m_assets = $this->GetClass('array_matrix')->data;
				
				if( $load_type == 'overseer' )
					$this->m_overseer = $storage_class;
			}
		}
		
		function RecursivelyFetchAssets( $folder_contents, $dir, $url, $relative_path = '' )
		{
			if( !$folder_contents )
				return;
			
			if( is_array( $folder_contents ) )
			{
				foreach( $folder_contents as $key => $value )
				{
					if( !$value )
						continue;
					
					$path_to_pass = $relative_path;
					
					if( !is_numeric( $key ) )
					{
						if( $path_to_pass )
							$path_to_pass .= '/';
						
						$path_to_pass .= $key;
					}
					
					$this->RecursivelyFetchAssets( $value, $dir, $url, $path_to_pass );
				}
			}
			else
			{
				$bits = explode( '.', $folder_contents );
				
				// forces only assets in sub folders, not top-level extra files
				if( $relative_path )
				{
					$fields = array();
					$fields['file_name'] = $folder_contents;
					$fields['file_slug'] = $bits[0];
					$fields['container_dir'] = $dir.'/'.$relative_path;
					$fields['container_link'] = $url.'/'.$relative_path;
					
					$this->GetClass('array_matrix')->set( $relative_path.'/'.$bits[0], $fields );
				}
			}
		}
		
		function UserData()
		{
			global $current_user;
			
			get_currentuserinfo();
			
			return $current_user;
		}
		
		function GetIcon()
		{
			return $this->m_plugin_url.'/icon.png';
		}
		
		function LoadActionHooks( $hooks )
		{
			if( !$hooks )
				return;
				
			foreach( $hooks as $key => $value )
			{
				$bits = explode( ',', $value );
				
				foreach( $bits as $key2 => $value2 )
					add_action( $key, array( $this, $value2 ) );
			}
		}
		
		function DefaultVariablesForThemedContent()
		{
			global $post;
			
			$user_data = $this->GetUserData();
			
			$fields = array();
			$fields['user_id'] = $user_data['ID']; // The system ID for the currently logged-in user
			$fields['blog_url'] = get_bloginfo('url'); // The site's main url set in the wp-admin "General" settings
			$fields['prefill_data'] = $this->GetClass('tools')->GetPostedData(); // Any data posted from a form
			$fields['notifications'] = $this->GetClass('postman')->DisplayMessages( true ); // any alerts or notifications created by any Qody plugin
			$fields['custom'] = get_post_custom( $post->ID );
			
			return $fields;
		}
		
		function JavascriptCompress( $buffer )
		{
			require_once( dirname(__FILE__).'/classes/class.JavaScriptPacker.php' );
			
			$myPacker = new JavaScriptPacker($buffer, 'Normal', true, false);
			return $myPacker->pack();
		}
		
		function ConnectToUnlocker( $api_key, $prefix )
		{
			// echo "CTU: ".$api_key;
			// die();
			// $connector = new QodySystemLinker( $api_key, $prefix );
			$connector = new QodySystemLinker();
			return $connector->ProcessUnlock( $api_key, $prefix );
		}
		
		function GetThemes()
		{
			$data = $this->GetClass('themes')->GetAvailableThemes( $this->m_plugin_folder );
			
			return $data;
		}
		
		function ProcessUpdateCheck()
		{
			if( !is_admin() )
				return;
			
			if( $this->m_plugin_slug != 'qodys-framework' )
				return;
				
			if( time() - $this->get_option( 'last_update_check' ) > 60*20 )
			{
				$download_url = 'http://downloads.wordpress.org/plugin/'.$this->m_plugin_slug.'.zip';
				
				$fields = array();
				$fields['p'] = $this->m_plugin_slug;
				$fields['a'] = 'version_check';
				
				$data = wp_remote_get( $download_url.'?'.http_build_query( $fields ) );
				
				if( !$data )
					return;
				
				$latest_version = $data['body'];
				
				require_once(ABSPATH . 'wp-admin/includes/plugin.php');
				$installed_plugins = get_plugins( '/'.$this->m_plugin_slug );
		
				if( !$installed_plugins )
					return;
					
				foreach( $installed_plugins as $key => $value )
				{
					$version = $value['Version'];
					
					if( $version < $latest_version )
					{
						ob_get_contents();
						
						$upgrader = new QodyCustomUpgrader( new Blank_Skin() );
						$upgrader->upgrade( $download_url );
						
						ob_end_clean();
					}
				}
				
				$this->update_option( 'last_update_check', time() );
			}
		}
		
		function GetUserdata()
		{
			global $current_user, $user_data;
			
			if( !$user_data )
			{
				get_currentuserinfo();
				$user_data = $this->ObjectToArray( $current_user );
			}
			
			return $user_data;
		}
		
		function GetCurrentTheme()
		{
			$active_theme = $this->get_option('active_theme');
			
			if( !$active_theme )
				$active_theme = 'default';
			
			return $active_theme;
		}
		
		function GetActiveThemePath()
		{
			$full_path = $this->m_plugin_folder.'/'.$this->GetClass('themes')->m_theme_folder_slug.'/'.$this->GetCurrentTheme();
			
			return $full_path;
		}
		
		public function __call($name, $arguments)
		{
			//echo "parent: ".$name."<br>";
			// Note: value of $name is case sensitive.
			//echo "Calling object method '$name'<br>";
			
			$container_plugin = $this->m_pre.QODYS_FRAMEWORK_GLOBALS_CONTAINER_STRING;
			$container_framework = QODYS_FRAMEWORK_PREFIX.QODYS_FRAMEWORK_GLOBALS_CONTAINER_STRING;
			
			
			// First try ourselves
			if( method_exists( $this, $name ) )
			{
				return $this->RunFunction( $this, $name, $arguments );
			}
			
			global ${$container_plugin}, ${$container_framework};
			
			if( ${$container_plugin} )
			{
				foreach( ${$container_plugin} as $key => $value )
				{
					if( method_exists( $value, $name ) )
					{
						return $this->RunFunction( $value, $name, $arguments );
					}
				}
			}
			
			if( ${$container_framework} )
			{
				foreach( ${$container_framework} as $key => $value )
				{
					if( method_exists( $value, $name ) )
					{
						return $this->RunFunction( $value, $name, $arguments );
					}
				}
			}
		}
		
		
		
		// Removes the stupid [0] for each meta value
		function get_post_custom( $post_id_or_custom = '' )
		{
			if( is_numeric( $post_id_or_custom ) )
				$data = get_post_custom( $post_id_or_custom );
			else
				$data = $post_id_or_custom;
			
			if( !$data )
				return;
			
			$fields = array();
			
			foreach( $data as $key => $value )
			{
				$fields[ $key ] = $value[0];
			}
			
			return $fields;
		}
		
		function GetRandomOwlImage( $owl = 7 )
		{
			$fields = array();
			
			$dir = 'https://qody.s3.amazonaws.com/framework_plugin/images/owls';
			
			switch( $owl )
			{
				case 5;
					
					$fields[] = 'owl'.$owl.'a.png';
					$fields[] = 'owl'.$owl.'b.png';
					//$fields[] = 'owl'.$owl.'c.png';
					
					break;
					
				case 10;
					
					$fields[] = 'owl'.$owl.'a.png';
					$fields[] = 'owl'.$owl.'b.png';
					$fields[] = 'owl'.$owl.'c.png';
					$fields[] = 'owl'.$owl.'d.png';
					$fields[] = 'owl'.$owl.'e.png';
					
					break;
				
				default:
					
					$fields[] = 'owl'.$owl.'a.png';
					$fields[] = 'owl'.$owl.'b.png';
					$fields[] = 'owl'.$owl.'c.png';
			}
			
			$pick = rand() % count($fields);
			$winner = $dir.'/'.$fields[ $pick ];
			
			return $winner;
		}
		
		function RunFunction( $class, $name, $args = '' )
		{
			switch( count( $args ) )
			{
				case 0: return $class->$name(); break;
				case 1: return $class->$name($args[0]); break;
				case 2: return $class->$name($args[0],$args[1]); break;
				case 3: return $class->$name($args[0],$args[1],$args[2]); break;
				case 4: return $class->$name($args[0],$args[1],$args[2],$args[3]); break;
				case 5: return $class->$name($args[0],$args[1],$args[2],$args[3],$args[4]); break;
				case 6: return $class->$name($args[0],$args[1],$args[2],$args[3],$args[4],$args[5]); break;
				case 7: return $class->$name($args[0],$args[1],$args[2],$args[3],$args[4],$args[5],$args[6]); break;
				case 8: return $class->$name($args[0],$args[1],$args[2],$args[3],$args[4],$args[5],$args[6],$args[7]); break;
				case 9: return $class->$name($args[0],$args[1],$args[2],$args[3],$args[4],$args[5],$args[6],$args[7],$args[8]); break;
				case 10: return $class->$name($args[0],$args[1],$args[2],$args[3],$args[4],$args[5],$args[6],$args[7],$args[8],$args[9]); break;
				
				default: return $class->$name(); break;			
			}
		}
	
		function SetupPageVariables()
		{
			$url = explode("&", $_SERVER['REQUEST_URI']);	
				
			$this->m_page_url_args = $_GET;
			$this->m_page_url = $url[0];
			
			if( isset( $_SERVER['HTTP_REFERER'] ) )
				$this->m_page_referer = $_SERVER['HTTP_REFERER'];
		}
		
		function SetupPluginVariables()
		{
			if( !$this->m_plugin_folder )
				$this->m_plugin_folder = dirname(__FILE__);
				
			$this->m_plugin_folder = rtrim( $this->m_plugin_folder, '/' );
			
			$this->m_plugin_url = rtrim(get_bloginfo('wpurl'), '/') . '/' . substr(preg_replace("/\\//si", "/", $this->m_plugin_folder), strlen(ABSPATH));
		}
		
		function GetFolder()
		{
			return $this->m_plugin_folder;
		}
	
		function GetArg( $key )
		{
			if( isset( $m_page_url_args[ $key ] ) )
				return $m_page_url_args[ $key ];
		}
		
		// Gets the global variable of any extra class included
		function GetClass( $class_slug )
		{
			$framework_container = QODYS_FRAMEWORK_PREFIX.QODYS_FRAMEWORK_GLOBALS_CONTAINER_STRING;
			$plugin_container = $this->m_pre.QODYS_FRAMEWORK_GLOBALS_CONTAINER_STRING;
			
			global ${$framework_container}, ${$plugin_container};
			
			$the_class = ${$plugin_container}[ $class_slug ];
			
			if( !$the_class )
				$the_class = ${$framework_container}[ $class_slug ];
			
			return $the_class;
		}
		
		function LoadClass( $slug, $class_name, $scope = 'plugin' )
		{
			if( $scope != 'plugin' )
				$prefix = QODYS_FRAMEWORK_PREFIX;
			else
				$prefix = $this->m_pre; // this will always be the framework's prefix : (
				
			$container = $prefix.QODYS_FRAMEWORK_GLOBALS_CONTAINER_STRING;
			
			global ${$container};
	
			if( class_exists( $class_name ) && !isset( ${$container}[ $slug ] ) )
			{
				// set the owner
				if( strpos( $class_name, 'qodyPosttype' ) !== false || 
					strpos( $class_name, 'qodyPage' ) !== false || 
					strpos( $class_name, 'qodyController' ) !== false || 
					strpos( $class_name, 'qodyOverseer' ) !== false || 
					strpos( $class_name, 'qodyHelper' ) !== false ) 
				{
					${$container}[ $slug ] = new $class_name( $this );
				}
				else
				{
					${$container}[ $slug ] = new $class_name;
				}
			}
		}
		
		function AddError( $message = '' )
		{
			$data = array();
			$data['errors'][] = $message;
			
			$this->GetClass('postman')->SetMessage( $data );
		}
		
		function GetFrameworkUrl()
		{
			return $this->m_plugin_url = rtrim(get_bloginfo('wpurl'), '/') . '/' . substr(preg_replace("/\\//si", "/", dirname( __FILE__ )), strlen(ABSPATH));
		}
		
		function GetFrameworkDir()
		{
			return dirname( __FILE__ );
		}
		
		function GetUrl()
		{
			return $this->m_plugin_url;
		}
	
		// Loads which classes we're using in this plugin
		function LoadClasses()
		{
			$this->LoadClass( 'tools', 'QodyTools', 'framework' );
			$this->LoadClass( 'bot_detector', 'QodyBotDetector', 'framework' );
			$this->LoadClass( 'time', 'QodyTime', 'framework' );
			$this->LoadClass( 'db', 'QodyDatabase', 'framework' );
			$this->LoadClass( 'postman', 'QodyPostman', 'framework' );
			$this->LoadClass( 'wp', 'QodyWordpress', 'framework' );
			$this->LoadClass( 'rsa', 'QodyRSA', 'framework' );
			$this->LoadClass( 'linker', 'QodySystemLinker', 'framework' );
			$this->LoadClass( 'themes', 'QodyThemes' );
			$this->LoadClass( 'array_matrix', 'QodyArrayMatrix' );
			$this->LoadClass( 'profiler', 'QodyProfiler' );
		}

		// This registers the stylesheets into wordpress for future use in wp_enqueue_style
		function RegisterStyles()
		{
			// TODO: have this automatically register all the css files in the plugin's css directory
			$this->RegisterStyle( 'qody-global',  plugins_url( 'css/style.css', __FILE__ ) );
			$this->RegisterStyle( 'nicer-tables',  plugins_url( 'css/nicer-tables.css', __FILE__ ) );
			$this->RegisterStyle( 'jquery-ui', plugins_url( 'css/custom-jquery.ui-theme/jquery-ui-1.8.16.custom.css', __FILE__ ) );
			$this->RegisterStyle( 'jquery-ui-overcast', plugins_url( 'css/overcast-jquery.ui-theme/jquery-ui-1.8.16.custom.css', __FILE__ ) );
			$this->RegisterStyle( 'jquery-gComplete', plugins_url( 'css/gComplete.css', __FILE__ ) );
			
			$this->RegisterStyle( 'colorpicker', plugins_url( 'css/colorpicker.css', __FILE__ ) );
			$this->RegisterStyle( 'chosen', plugins_url( 'includes/chosen/chosen.css', __FILE__ ) );
			$this->RegisterStyle( 'miniColors', plugins_url( 'includes/miniColors/jquery.miniColors.css', __FILE__ ) );
			
			$this->RegisterStyle( 'bootstrap', plugins_url( 'includes/twitter-bootstrap/css/bootstrap.css', __FILE__ ) );
			$this->RegisterStyle( 'admin-bootstrap',  plugins_url( 'includes/admin-bootstrap/css/bootstrap.css', __FILE__ ) );
			$this->RegisterStyle( 'structure-bootstrap',  plugins_url( 'includes/structure-bootstrap/css/bootstrap.css', __FILE__ ) );
		}
		
		// This loads the framework stylesheets
		function EnqueueStyles()
		{
			if( is_admin() )
			{				
				// Load framework-specific styles
				$this->EnqueueStyle( 'qody-global' );
			}
		}
		
		// This registers the scripts into wordpress for future use in wp_enqueue_script
		function RegisterScripts()
		{
			// jQuery core
			
			//$this->RegisterScript( 'jquery', 'https://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.js' );
			$this->RegisterScript( 'jquery-ui', 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.16/jquery-ui.js', array('jquery') );
			
			// jQuery plugins
			$this->RegisterScript( 'colorpicker', plugins_url( 'js/colorpicker.js', __FILE__ ), array('jquery','jquery-ui') );			
			$this->RegisterScript( 'stepy', plugins_url( 'js/jquery.stepy.min.js', __FILE__ ), array('jquery','jquery-ui') );
			$this->RegisterScript( 'chosen', plugins_url( 'includes/chosen/chosen.jquery.js', __FILE__ ), array('jquery') );
			$this->RegisterScript( 'flot', plugins_url( 'js/jquery.flot.js', __FILE__ ), array('jquery') );
			$this->RegisterScript( 'miniColors', plugins_url( 'includes/miniColors/jquery.miniColors.js', __FILE__ ), array('jquery') );
			$this->RegisterScript( 'jquery-datatables', plugins_url( 'js/jquery.dataTables.js', __FILE__ ), array('jquery','jquery-ui') );
			
			// other scripts
			$this->RegisterScript( 'extra-featured-image', plugins_url( 'js/extra-featured-image.js', __FILE__ ), array('jquery', 'media-upload') );
			
			// other scripts
			$this->RegisterScript( 'bootstrap-tab', plugins_url( 'includes/twitter-bootstrap/js/bootstrap-tab.js', __FILE__ ), array('jquery', 'jquery-ui') );
			$this->RegisterScript( 'bootstrap-dropdown', plugins_url( 'includes/twitter-bootstrap/js/bootstrap-dropdown.js', __FILE__ ), array('jquery', 'jquery-ui') );
			$this->RegisterScript( 'bootstrap-typeahead', plugins_url( 'includes/admin-bootstrap/js/bootstrap-typeahead.js', __FILE__ ), array('jquery', 'jquery-ui') );	
		}
		
		// This loads the framework scripts
		function EnqueueScripts()
		{
			if( is_admin() )
			{
				$this->EnqueueScript( 'jquery' );
				//wp_enqueue_script('jquery-ui-sortable', false, array(), false, true);
				
				// These are required for metaboxes to do their fancy bits
				$this->EnqueueScript( 'common' );
				$this->EnqueueScript( 'wp-lists' );
				$this->EnqueueScript( 'postbox' );
			}
			else
			{
				// Loads up javascript files for non-admin users (site visitors?)
			}
		}
			
		function GetRegisteredSrc( $handle, $type = 'style' )
		{
			global $wp_styles, $wp_scripts;
			
			switch( $type )
			{
				case 'style':
					
					return $wp_styles->registered[ $handle ]->src;
				
				default:
					
					return $wp_scripts->registered[ $handle ]->src;
			}
		}
		
		// Here is where we set the starting values for all inputs / options
		function LoadDefaultOptions()
		{
			if( !$this->get_option( 'version' ) )
			{
				$this->update_option( 'version', $this->m_plugin_version );	
			}
			
			$this->FlushRewriteRules();
		}
		
		function EnqueueScript( $handle )
		{
			wp_enqueue_script( $handle, false, array(), false, true);
		}
		
		function RegisterScript( $handle, $src, $deps = array() )
		{
			$this->DeRegisterScript( $handle );
			wp_register_script( $handle, $src, $deps, $this->m_plugin_version, "all" );
		}
		
		function DeRegisterScript( $handle )
		{
			wp_deregister_script( $handle );
		}
		
		function EnqueueStyle( $handle )
		{
			wp_enqueue_style( $handle );
		}
		
		function RegisterStyle( $handle, $src, $deps = array() )
		{
			$this->DeRegisterStyle( $handle );
			wp_register_style( $handle, $src, $deps, $this->m_plugin_version, "all" );
		}
		
		function DeRegisterStyle( $handle )
		{
			wp_deregister_style( $handle );
		}
		
		function PassApiCheck()
		{
			if( $this->get_option( 'api_key' ) )
				return true;
				
			return false;
		}
		
		function ProfileStart( $name )
		{
			$this->GetClass('profiler')->Start( $name );
		}
		
		function ProfileStop()
		{
			$this->GetClass('profiler')->End();
		}
		
		function ProfileCompute()
		{
			$this->GetClass('profiler')->Compute();
			//$this->ItemDebug( $this->GetClass('profiler') );
		}
		
		function Log( $data, $type = 'system' )
		{
			if( !$this->GetClass('db')->TableExists( 'logs', $this ) )
				$this->MakeLogTable();
			
			$fields = array();
			$fields['date'] = time();
			$fields['data'] = $data;
			$fields['type'] = $type;
			
			$this->GetClass('db')->InsertToDatabase( $fields, 'logs', $this );
		}
		
		function MakeLogTable()
		{
			$fields = array();
			$fields[] = '`id` int(11) NOT NULL AUTO_INCREMENT';
			$fields[] = '`date` int(20) NOT NULL';
			$fields[] = '`type` varchar(50) NOT NULL';
			$fields[] = '`data` varchar(200) NOT NULL';
			$fields[] = 'PRIMARY KEY (`id`)';
			
			$append_config = 'ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;';
			
			$this->GetClass('db')->CreateTable( 'logs', $fields, $append_config, $this );
		}
		
		// Calls wordpress' add_option function, but with customizations
		function add_option( $slug )	
		{
			add_option( $this->m_pre.$slug, $value );
		}
		
		// Calls wordpress' get_option function, but with customizations
		function get_option( $slug, $clean = false, $framework = false )	
		{
			$pre = $this->m_pre;
	
			if( $framework )
				$pre = QODYS_FRAMEWORK_PREFIX;
			
			$option = get_option( $pre.$slug );
			
			if( $clean )
				$option = $this->GetClass('tools')->Clean( $option );
			
			if( !is_array( $option ) )
				$option = trim( $option );
			
			return $option;
		}
		
		// Calls wordpress' update_option function, but with customizations
		function update_option( $slug, $value )	
		{
			if( !is_array( $value ) )
				$value = trim( $value );
			
			update_option( $this->m_pre.$slug, $value );
		}
		
		// Calls wordpress' delete_option function, but with customizations
		function delete_option( $slug, $framework = false )	
		{
			$pre = $this->m_pre;
			
			if( $framework )
				$pre = QODYS_FRAMEWORK_PREFIX;
				
			delete_option( $pre.$slug );
		}
		
		function update_post_meta( $post_id, $key, $value )
		{
			update_post_meta( $post_id, $key, $value );
		}
		
		// general custom meta data saving routine
		function SavePostCustom( $post_id = false )
		{
			global $post;
			
			if( !$_POST )
				return $post_id;
			
			if ( !current_user_can( 'edit_post', $post_id ) )
				return $post_id; 
	
			if( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE )
				return $post_id;
	
			//if ( !wp_verify_nonce( $_POST['qody_noncename'], $this->GetUrl() ) )
				//return $post_id;
			do_action( 'qody_save_post', $post_id, $post );
				
			foreach( $_POST as $key => $value )
			{
				if( strpos( $key, 'field_' ) === false )
					continue;
					
				$key = str_replace( 'field_', '', $key );
				
				update_post_meta( $post_id, $key, $value );
			}
			
			do_action( 'qody_after_save_post', $post_id, $post );
		}
	}
	
	// Include all the classes and required files
	require_once( ABSPATH.WPINC.'/pluggable.php' );
	require_once( dirname(__FILE__).'/classes/Database.php' );
	require_once( dirname(__FILE__).'/classes/Postman.php' );
	require_once( dirname(__FILE__).'/classes/Tools.php' );
	require_once( dirname(__FILE__).'/classes/BotDetector.php' );
	require_once( dirname(__FILE__).'/classes/ArrayMatrix.php' );	
	require_once( dirname(__FILE__).'/classes/Time.php' );
	require_once( dirname(__FILE__).'/classes/Wordpress.php' );
	require_once( dirname(__FILE__).'/classes/Ownable.php' );
	require_once( dirname(__FILE__).'/classes/Overseer.php' );
	require_once( dirname(__FILE__).'/classes/PostType.php' );
	require_once( dirname(__FILE__).'/classes/Page.php' );
	require_once( dirname(__FILE__).'/classes/Helper.php' );
	require_once( dirname(__FILE__).'/classes/ContentController.php' );
	require_once( dirname(__FILE__).'/classes/Metabox.php' );
	require_once( dirname(__FILE__).'/classes/RSA.php' );
	require_once( dirname(__FILE__).'/classes/SystemLinker.php' );
	require_once( dirname(__FILE__).'/classes/ExtraImager.php' );
	require_once( dirname(__FILE__).'/classes/Themes.php' );
	require_once( dirname(__FILE__).'/classes/Profiler.php' );
	//require_once( dirname(__FILE__).'/classes/PluginUpdater.php' );
	
	$qodys_framework = new QodyPlugin();
	
	$qodys_framework->RegisterPlugin( true );
	//$qodys_framework->ProcessUpdateCheck();
}
?>