<?php
class QodyContentController extends QodyOwnable
{
	var $m_option_slug		= '';
	
	function __construct()
	{
		$fields = array();
		
		add_action( 'init', array( $this, 'Init' ) );
		add_action( 'admin_init', array( $this, 'AdminInit' ) );
		
		add_filter( 'template_redirect', array( $this, 'WhenOnPage' ) );
		//$this->ItemDebug( $this );
		
		parent::__construct();
	}
	
	function Init()
	{
		// Register the styles included with this page
		$this->RegisterStyles();
		$this->RegisterScripts();
	}
	
	function AdminInit()
	{
		
	}
	
	function RegisterStyles()
	{
		$styles = $this->GetAssets('css');
		
		if( !$styles )
			return;
		
		foreach( $styles as $key => $value )
		{
			$this->RegisterStyle( $this->m_option_slug.'_'.$value['file_slug'], $value['container_link'].'/'.$value['file_name'] );
		}
	}
	
	function RegisterScripts()
	{
		$scripts = $this->GetAssets('js');
		
		if( !$scripts )
			return;
		
		foreach( $scripts as $key => $value )
		{
			$this->RegisterScript( $this->m_option_slug.'_'.$value['file_slug'], $value['container_link'].'/'.$value['file_name'], $this->m_script_dependencies );
		}
	}
	
	function SetSlug( $slug )
	{
		$this->m_option_slug = $slug;
	}
	
	// Run when we are on this page on the public site
	function WhenOnPage( $enforce_post_type = true )
	{
		global $post;
		
		// if we are on any admin screen
		if( is_admin() )
			return false;
		
		$page_id = $this->GetPageID();
		
		if( $page_id && $page_id != $post->ID )
			return false;
		
		if( $enforce_post_type )
		{
			if( get_post_type( $post ) != 'page' )
				return false;
		}
		
		$this->AddContentFilter();
		
		$this->EnqueueStyle( $this->m_option_slug.'_post_showing' );
		$this->EnqueueScript( $this->m_option_slug.'_post_showing' );
		
		$this->EnqueueStyle( $this->m_option_slug.'_post_view' );
		$this->EnqueueScript( $this->m_option_slug.'_post_view' );
		
		return true;
	}
	
	function GetPageID()
	{
		$page_id = $this->Owner()->get_option( $this->m_option_slug.'_page' );
		
		if( !$page_id )
			$page_id = -1;
			
		return $page_id;
	}
	
	function GetFormattedContentText( $text )
	{
		$this->RemoveContentFilter();
		
		$text = apply_filters( 'the_content', $text );
		
		$this->AddContentFilter();
		
		return $text;
	}
	
	function AddContentFilter()
	{
		add_filter( 'the_content', array( $this, 'ContentFunction' ) );
	}
	
	function RemoveContentFilter()
	{
		remove_filter( 'the_content', array( $this, 'ContentFunction' ) );
	}
	
	function ManualQuickPrint( $calling_controller = null )
	{
		if( $calling_controller != null )
			$calling_controller->RemoveContentFilter();
			
		$this->AddContentFilter();
		the_content();
		$this->RemoveContentFilter();
		
		if( $calling_controller != null )
			$calling_controller->AddContentFilter();
	}
	
	function LoadThemedContent( $file_path, $echo = false, $plugin_folder = '', $theme = '' )
	{
		if( !$plugin_folder )
			$plugin_folder = $this->GetPluginFolder();
		
		if( !$theme )
			$theme = $this->GetCurrentTheme();
		
		$data = $this->GetClass('themes')->LoadThemeContent( $plugin_folder, $theme, $file_path );
		
		if( $echo )
			echo $data;
		else
			return $data;
	}
	
	function LoadPage( $old_content = '', $alternative_view = false )
	{
		global $post;
		
		$user_data = $this->GetUserData();
		
		$defaults = $this->DefaultVariablesForThemedContent();
		
		if( $defaults )
			extract( $defaults );
		
		// does the theme loading and server-side page processing
		include( $this->m_asset_folder.'/content.php' );
		
		return $content;
	}
	
	function ContentFunction( $old_content = '' )
	{
		return $this->LoadPage( $old_content );
	}
	

}
?>