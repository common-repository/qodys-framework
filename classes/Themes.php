<?php
class QodyThemes extends QodyPlugin
{
	var $m_theme_folder_slug = 'themes';
	
	function __construct()
	{
		parent::__construct();
	}
	
	function GetAvailableThemes( $plugin_dir )
	{
		$folder_contents = $this->OrganizeDirectoryIntoArray( $plugin_dir.'/'.$this->m_theme_folder_slug.'/' );
		
		if( !$folder_contents )
			return;
		
		$fields = array();
		
		foreach( $folder_contents as $key => $value )
		{
			$fields[] = $key;
		}
		
		return $fields;
	}
	
	function LoadThemeContent( $plugin_dir, $theme, $file_path )
	{
		$full_path = $plugin_dir.'/'.$this->m_theme_folder_slug.'/'.$theme.'/'.$file_path;
		
		if( !file_exists( $full_path ) )
			return '<em>theme file not found</em>';
			
		$stream = fopen( $full_path, "r" );
		
		$data = stream_get_contents( $stream );
		
		fclose( $stream );
		
		return $data;
	}
}
?>