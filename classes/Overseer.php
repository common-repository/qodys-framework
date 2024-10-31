<?php
class QodyOverseer extends QodyOwnable
{
	function __construct()
	{
		$fields = array();
		
		// Standard WP
		$fields['init'] = 'Init';
		$fields['admin_init'] = 'AdminInit';
		
		$this->LoadActionHooks( $fields );
		
		parent::__construct();
	}
	
	function Init()
	{
		// Register the styles included with this page
		//$this->RegisterStyles();
		//$this->RegisterScripts();
	}
	
	function AdminInit()
	{
		$this->LoadMetaboxes();
	}
}
?>