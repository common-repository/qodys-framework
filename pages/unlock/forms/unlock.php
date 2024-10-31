<?php
require_once( dirname(dirname(dirname(dirname(dirname(dirname(dirname(__FILE__))))))).'/wp-load.php' );

$response = array();

if( $_POST['plugin_pre'] )
{
	$api_key = $_POST['api_key'];
	$pre = $_POST['plugin_pre'];
	$action = $_POST['action'];
	
	if( $action == 'clock_out' )
	{
		delete_option( $pre.'api_key' );
		$response['results'][] = 'That O.I.N has been removed';
	}
	else if( !$api_key )
	{
		$response['errors'][] = 'You must enter an O.I.N to begin';
	}
	else
	{
		$response = $qodys_framework->ConnectToUnlocker( $api_key, $pre );
		
		// die();
		if( !$response['errors'] )
		{
			update_option( $pre.'api_key', $api_key );
			// update_option( 'prefix', $pre );
			//update_option( 'prefix' ,$pre );
			// echo "api: ".$qodys_framework->get_option('api_key');
			// echo "<br>test: ".$qodys_framework->get_option('prefixes');
			// die();
		}
		
		//$response['errors'][] = "We couldn't verify that O.I.N. Please try again or contact support at support@qody.co";
	}
}
else
{
	$response['errors'][] = 'Any unexpected error occured; please try again';
}

$qodys_framework->GetClass('postman')->SetMessage( $response );

$url = $qodys_framework->GetClass('tools')->GetPreviousPage();

header( "Location: ".$url );
exit;
?>