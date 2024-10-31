<?php
class QodySystemLinker extends QodyPlugin
{
	var $m_api_key;
	var $m_prefix;
	
	function __construct()
	{
		// echo "construct: ".$api_key;
		// die();
		// if( $api_key )
			// $this->m_api_key = $api_key;
		// else
			// $this->m_api_key = $this->get_option('api_key');
		
		// if( $prefix )
			// $this->m_prefix = $prefix;
		
	}
	
	function ProcessUnlock( $api_key = '', $prefix = '', $product_id = '' )
	{
		// echo "PU: ".$api_key;
		// die();
		$fields = array();
		$fields['action'] = 'unlock';
		$fields['api'] = $api_key;
		$fields['prefix'] = $prefix;
		
		if( $product_id )
			$fields['product_id'] = $product_id;
		
		$result = $this->SendCommand( $fields );
		
		$result = $this->DecodeResponse( $result );
		
		if( !$result )
			$result['errors'][] = 'An unexpected error occured; please try again';
		
		return $result;
	}
	
	function VerifyOwnershipByAssociation( $caller, $product_id )
	{
		$fields = array();
		$fields['action'] = 'ownership_by_association';
		$fields['api'] = $caller->get_option('api_key');
		$fields['product_id'] = $product_id;
		
		$result = $this->SendCommand( $fields );
		
		$result = $this->DecodeResponse( $result );
		
		if( !$result )
			$result['errors'][] = 'An unexpected error occured; please try again';
		
		return $result;
	}
	
	function VerifyApiKey( $api_key )
	{
		if( !$api_key )
			return false;
			
		$result = $this->ProcessUnlock( $api_key, $this->m_prefix );
		
		if( !$response['errors'] )
			return true;
		
		return false;
	}
	
	function GetOutsideLinkContent( $url )
	{
		$data = $this->DoCurl( $url );
		
		if( !$data )
			$data = file_get_contents( $url );
			
		return $data;
	}
	
	function DecodeResponse( $data )
	{
		return $this->GetClass('tools')->ObjectToArray( json_decode($data) );
	}
	
	function PackArray( $data )
	{
		return base64_encode( serialize( $data ) );
	}
	
	function UnpackArray( $data )
	{
		return base64_decode( unserialize( $data ) );
	}
	
	function GetProductID( $prefix )
	{
		// switch( $this->m_prefix )
		switch( $prefix )
		{
			case 'qfm': return 336;
			case 'qrd': return 382;
			case 'qst': return 384;
			case 'qaf': return 687;
			case 'qoi': return 774;
			case 'qsh': return 9182;
			case 'qpt': return 9949;
			case 'qbt': return 10148;
			case 'qrt': return 10345;
			case 'qmp': return 10460;
			case 'qpn': return 11443;
			
			default: return -1;
		}
	}
	
	function DoCurl( $url )
	{
		global $post;
		
		if( !$url )
			return;
		
		 // is cURL installed yet?
		if( !function_exists('curl_init') )
			return;
		
		if( is_object( $post ) )
			$ref_url = get_permalink( $post->ID );
		else
			$ref_url = get_bloginfo('url');
		
		$ch = curl_init();
		
		curl_setopt($ch, CURLOPT_URL, $url);
		
		// Set a referer
		curl_setopt( $ch, CURLOPT_REFERER, $ref_url );
		
		// User agent
		curl_setopt($ch, CURLOPT_USERAGENT, "MozillaXYZ/1.0");
		
		// Include header in result? (0 = yes, 1 = no)
		curl_setopt($ch, CURLOPT_HEADER, 0);
		
		// Should cURL return or print out the data? (true = return, false = print)
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		
		// Timeout in seconds
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);
		
		// Download the given URL, and return output
		$output = curl_exec($ch);
		
		// Close the cURL resource, and free system resources
		curl_close($ch);
		
		return $output;
	}
	
	function SendCommand( $fields )
	{
		// echo "key: ".$this->get_option('api_key');
		// ItemDebug($fields);
		// die();
		if( !$fields['api'] )
			$fields['api'] = $this->get_option( 'api_key' );
		
		if( !$fields['domain'] )
			$fields['domain'] = get_bloginfo('url');
			
		if( !$fields['product_id'] )
			$fields['product_id'] = $this->GetProductID( $fields['prefix'] );
		
		$hash = urlencode( base64_encode( serialize( $fields ) ) );
		
		$url = "http://plugins.qody.co/connector/?hash=".$hash;
		//$this->ItemDebug( $url );
		$response = $this->GetOutsideLinkContent( $url );
		
		if( !$response )
			$response = file_get_contents( $url );
		
		return $response;
	}
}
?>