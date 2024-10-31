<?php
require_once( dirname(dirname(dirname(dirname(dirname(dirname(dirname(__FILE__))))))).'/wp-load.php' );

$response = array();

if( $_POST['plugin_global'] )
{
	$plugin_variable = ${$_POST['plugin_global']};
	
	$plugin_variable->GetClass('db')->ClearTable( 'logs', $plugin_variable );
	
	$response['results'][] = 'Logs cleared';
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