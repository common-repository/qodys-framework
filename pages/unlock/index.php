<?php
class qodyPage_FrameworkUnlock extends QodyPage
{
	function __construct()
	{
		$this->SetOwner( func_get_args() );
		
		$this->m_raw_file = __FILE__;
		
		// if we've entered the OIN, show it as the OIN management page
		if( $this->PassApiCheck() )
		{
			$this->SetParent( 'home' );
			$this->SetTitle( 'O.I.N' );
			$this->SetPriority( 100 );
		}
		else // otherwise make it the homepage of the plugin
		{
			$this->SetSlug( 'home' );
			$this->SetTitle( $this->Owner()->m_plugin_name );
			$this->m_icon_url = $this->GetIcon();
			$this->SetPriority( 1, false );
		}
		
		parent::__construct();
	}
	
	function LoadMetaboxes()
	{
		$this->AddMetabox( 'unlock', 'O.I.N Required' );
	}
}
?>