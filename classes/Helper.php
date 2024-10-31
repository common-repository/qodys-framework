<?php
class QodyHelper extends QodyOwnable
{
	
	function __construct()
	{
		parent::__construct();
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
}
?>