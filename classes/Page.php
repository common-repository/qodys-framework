<?php
class QodyPage extends QodyOwnable
{
	var $m_page_parent		= '';
	var $m_page_title 		= '';
	var $m_menu_title 		= '';
	var $m_capability 		= 1;
	var $m_menu_slug 		= '';
	var $m_icon_url 		= '';
	var $m_menu_position	= null;
	var $m_page_hook		= '';
	
	function __construct()
	{
		$fields = array();
		
		// Standard WP
		$fields['admin_init'] = 'AdminInit,WhenOnPage';
		
		add_action( 'init', array( $this, 'Init' ), $this->m_priority );
		
		$this->LoadActionHooks( $fields );
		
		// has to take priority so posttypes can latch to it
		add_action( 'admin_menu', array( $this, 'CreatePage' ), $this->m_priority );
		
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
		add_action( 'admin_head-'.$this->m_page_hook, array( $this, 'LoadMetaboxes' ) );
	}
	
	// Run when we are on this page in the admin area
	function WhenOnPage()
	{
		// if we are on any admin screen
		if( !is_admin() )
			return false;
		
		// if it's not the right post type
		if( $_GET['page'] != $this->m_menu_slug )
			return false;
		
		$this->EnqueueStyle( $this->m_menu_slug.'_post_edit' );
		$this->EnqueueScript( $this->m_menu_slug.'_post_edit' );
		
		add_action( 'admin_print_styles', array( $this, 'LoadAdminBootstrapLast' ), 10000 );
		
		return true;
	}
	
	function LoadAdminBootstrapLast()
	{
		$this->EnqueueStyle( 'admin-bootstrap' );
	}
	
	function CreatePage()
	{
		global $submenu;
		
		
			
		if( $this->m_page_parent )
			$this->add_submenu_page();
		else
			$this->add_menu_page();
		
		
	}
	
	function RegisterStyles()
	{
		$styles = $this->GetAssets('css');
		
		if( !$styles )
			return;
		
		foreach( $styles as $key => $value )
		{
			$this->RegisterStyle( $this->m_menu_slug.'_'.$value['file_slug'], $value['container_link'].'/'.$value['file_name'] );
		}
	}
	
	function RegisterScripts()
	{
		$scripts = $this->GetAssets('js');
		
		if( !$scripts )
			return;
		
		foreach( $scripts as $key => $value )
		{
			$this->RegisterScript( $this->m_menu_slug.'_'.$value['file_slug'], $value['container_link'].'/'.$value['file_name'] );
		}
	}
	
	function get_option( $slug )
	{
		return $this->Owner()->get_option( $slug );
	}
	
	function SetSlug( $slug )
	{
		$this->m_menu_slug = $this->Owner()->m_pre.'-'.$slug.'.php';
	}
	function SetParent( $slug )
	{
		$this->m_page_parent = $this->Owner()->m_pre.'-'.$slug.'.php';
	}
	
	function SetTitle( $title )
	{
		$this->m_page_title = $title;
		$this->m_menu_title = $title;
	}
	
	function ContentFunction()
	{
		global $qodys_framework;
		
		$content_file = $this->m_asset_folder.'/content.php';
		
		if( file_exists( $content_file ) )	
			include( $content_file );
		else
			echo 'content file not found';
	}
	
	function AddMetabox( $file_slug, $title, $position = 'normal', $type_slug = '', $priority = 'low', $output_directly = false )
	{
		$type_slug = $this->m_page_hook;
		
		parent::AddMetabox( $file_slug, $title, $position , $type_slug, $priority, $output_directly );
	}
	
	function do_meta_boxes( $context )
	{
		do_meta_boxes( $this->m_page_hook, $context, $this );
	}
	
	function add_menu_page()
	{
		$this->m_page_hook = add_menu_page
		(
			$this->m_page_title,
			$this->m_menu_title,
			1,
			$this->m_menu_slug,
			array( $this, 'ContentFunction' ),
			$this->m_icon_url,
			$this->m_menu_position
		);
		
		//$this->ItemDebug( $this->m_page_hook );
	}
	
	function add_submenu_page()
	{
		$this->m_page_hook = add_submenu_page
		(
			$this->m_page_parent,
			$this->m_page_title,
			$this->m_menu_title,
			1,
			$this->m_menu_slug,
			array( $this, 'ContentFunction' )
		);
	}
	

}
?>