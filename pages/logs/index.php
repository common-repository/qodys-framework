<?php
class qodyPage_FrameworkLogs extends QodyPage
{
	function __construct()
	{
		$this->SetOwner( func_get_args() );
		
		$this->m_raw_file = __FILE__;
		
		// if we've entered the OIN, show it as the OIN management page
		if( $this->PassApiCheck() )
		{
			$this->SetParent( 'home' );
			$this->SetTitle( 'logs' );
			$this->SetPriority( 99 );
		}
		
		parent::__construct();
	}
	
	function LoadMetaboxes()
	{
		$this->AddMetabox( 'logs', 'Logs' );
	}
	
	function WhenOnPage()
	{
		if( !parent::WhenOnPage() )
			return;
		
		$this->EnqueueStyle( 'admin-bootstrap' );
		
		$this->EnqueueScript( 'jquery-ui' );		
	}
	
	function GetLogs()
	{
		$data = $this->GetClass('db')->Select( 'logs', '', $this, ' ORDER BY date DESC, id DESC' );
		
		return $data;
	}
}
?>