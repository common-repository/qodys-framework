<?php
class QodyTools extends QodyPlugin
{
	function __construct()
	{
		parent::__construct();
	}
	
	function CookieSet( $cookieName, $cookieValue, $cookieDuration = '2592000', $cookiePath = '/' )
	{
		$domain = get_bloginfo('url');
		$domain = str_replace( 'www.', '', $domain );
		$domain = str_replace( 'http://', '.', $domain );
		$domain = rtrim( $domain, '/' );
		
		$result = setcookie( $cookieName, $cookieValue, time() + $cookieDuration, $cookiePath, $domain);
		
		return $result;
	}
	
	function CookieGet( $cookieName )
	{
		return $_COOKIE[ $cookieName ];
	}
	
	function DecodeArrayKeys( $data )
	{
		if( !$data )
			return $data;
		
		$fields = array();
		
		foreach( $data as $key => $value )
		{
			$new_key = html_entity_decode( $key );
			$new_key = urldecode( $new_key );
			
			$fields[ $new_key ] = $value;
		}
		
		return $fields;
	}
	
	function remove_server_limits()
	{
		if( !ini_get('safe_mode') )
		{
			@set_time_limit(0);
			@ini_set('memory_limit', '256M');
			@ini_set('upload_max_filesize', '128M');
			@ini_set('post_max_size', '256M');
			@ini_set('max_execution_time', 0);
			return true;
		}

		return false;
	}
	
	function HelpLink( $link )
	{
		$image_src = $this->GetUrl().'/images/help-icon.png';
		
		$data = '<a target="_blank" href="'.$link.'" title="Need help? Click to open in new tab"><img style="display:inline-block; width:12px;" src="'.$image_src.'"></a>';
		
		return $data;
	}
	
	// this currently fails real badly
	function CalculateColorLuminance( $hex, $lum )
	{
		// validate hex string
		$hex = str_replace( '#', '', $hex );
		
		if( strlen( $hex ) < 6)
		{
			$hex = $hex[0] + $hex[0] + $hex[1] + $hex[1] + $hex[2] + $hex[2];
		}
		
		if( !$lum )
			$lum = 0;
			
		// convert to decimal and change luminosity
		$rgb = "#";
		
		for( $i = 0; $i < 3; $i++ )
		{
			// c = parseInt(hex.substr(i*2,2), 16);
			$c = base_convert( substr( $hex, i*2,2), 10, 16 );
			$c = base_convert( round( min( max( 0, $c + ($c * $lum)), 255) ), 10, 16 );
			
			$rgb .= $c;
		}
		
		return $rgb;
	}
	
	function GetInputsOfForm( $form_html, $show_hidden = false )
	{
		// enable user error handling
		libxml_use_internal_errors( true );

		$dom = new DOMDocument();
		
		if( !$dom->loadHTML( $form_html ) )
		{
			foreach( libxml_get_errors() as $error )
			{
				//$this->ItemDebug( $error );
				// handle errors here
			}
		}
		
		$xpath = new DOMXPath($dom);
		
		$data = array();
		
		$inputs = $xpath->query('//input');
		foreach ($inputs as $input) {
			if ($name = $input->getAttribute('name'))
			{
				if( !$show_hidden && $input->getAttribute('type') == 'hidden' )
					continue;
				
				if( $input->getAttribute('type') == 'submit' )
					continue;
					
				$data[$name] = $input->getAttribute('value');
			}
		}
		
		$textareas = $xpath->query('//textarea');
		foreach ($textareas as $textarea) {
			if ($name = $textarea->getAttribute('name')) {
				$data[$name] = $textarea->nodeValue;
			}
		}
		
		$options = $xpath->query('//select/option[@selected="selected"]');
		foreach ($options as $option) {
			if ($name = $option->parentNode->getAttribute('name')) {
				$data[$name] = $option->getAttribute('value');
			}
		}
		
		return $data;
	}
	
	function Thumbnail( $src, $width = '', $height = '')
	{
		global $blog_id;
		
		$fields = array();
		$fields['src'] = $src;
		$fields['w'] = $width;
		$fields['h'] = $height;
		$fields['blog_id'] = $blog_id;
		
		$path = $this->GetUrl().'/includes/timthumb/timthumb.php?'.http_build_query( $fields );
		
		return $path;
	}
	
	function GetArrayOfBorderStyles()
	{
		$fields = array();
		$fields[] = 'dashed';
		$fields[] = 'dotted';
		$fields[] = 'double';
		$fields[] = 'groove';
		$fields[] = 'inset';
		$fields[] = 'outset';
		$fields[] = 'ridge';
		$fields[] = 'solid';		
		
		return $fields;
	}
	
	function base64_url_decode($input)
	{
		return base64_decode(strtr($input, '-_', '+/'));
	}
	
	function JavascriptForceIntoSingleLine( $input )
	{
	   $input = preg_replace("/'/", "\'", $input); // Escape slashes
	   $lines = preg_split("/[\r\n]+/si", $input);    // Separate into each line
	   $lines = implode("", $lines); // Turn back into a string
	
	   return $lines;
	}

	function close_dangling_tags($html)
	{
		#put all opened tags into an array
		preg_match_all("#<([a-z]+)( .*)?(?!/)>#iU",$html,$result);
		$openedtags=$result[1];
		
		#put all closed tags into an array
		preg_match_all("#</([a-z]+)>#iU",$html,$result);
		$closedtags=$result[1];
		$len_opened = count($openedtags);
		# all tags are closed
		if(count($closedtags) == $len_opened){
			return $html;
		}
		
		$openedtags = array_reverse($openedtags);
		# close tags
		for($i=0;$i < $len_opened;$i++) {
			if (!in_array($openedtags[$i],$closedtags)){
				$html .= '</'.$openedtags[$i].'>';
			} else {
				unset($closedtags[array_search($openedtags[$i],$closedtags)]);
			}
		}
		return $html;
	}
	
	function SafeSubstr($text, $length = 180)
	{ 
		if((mb_strlen($text) > $length)) { 
			$whitespaceposition = mb_strpos($text, ' ', $length) - 1; 
			if($whitespaceposition > 0) { 
				$chars = count_chars(mb_substr($text, 0, ($whitespaceposition + 1)), 1); 
				if ($chars[ord('<')] > $chars[ord('>')]) { 
					$whitespaceposition = mb_strpos($text, ">", $whitespaceposition) - 1; 
				} 
				$text = mb_substr($text, 0, ($whitespaceposition + 1)); 
			} 
			$text = str_replace( '<br / ', '<br>', $text ); 
			$text .= ' ...';
			
			$text = $this->close_dangling_tags( $text );
		}
		
		return $text; 
	}
	
	function special_chars_decode( $data )
	{
		$old_data = $data;
		
		if( !is_array( $data ) )
		{
			$data = array( $data );
		}
		
		foreach( $data as $key => $value )
		{
			if( is_array( $value ) )
				continue;
				
			$data[ $key ] = str_replace(array("&lt;", "&gt;", '&amp;', '&#039;', '&quot;','&lt;', '&gt;'), array("<", ">",'&','\'','"','<','>'), htmlspecialchars_decode($value, ENT_NOQUOTES)); 
		}
		
		if( is_array( $old_data ) )
			return $data;
		
		return $data[0];
	}
	
	function Clean( $theString, $strip_slashes = false )
	{
		$clean_string = str_replace( "\\", "", html_entity_decode($theString) );
		
		if( $strip_slashes )
		{
			$clean_string = str_replace( "\'", "'", $clean_string );
			$clean_string = str_replace( '\"', '"', $clean_string );
		}
		
		return $clean_string;
	}
	
	function CleanForInput( $thing )
	{
		// the nextra variables help force foreign characters to not show up mangled
		$clean_thing = htmlentities( $thing, ENT_COMPAT, 'UTF-8');
		
		return $clean_thing;
	}
	
	function filter( $str )
	{
		if( is_array( $str ) )
			return;

		$str = addslashes( $str );
		$str = htmlentities( $str );
		$str = trim( $str );
		
		return $str;
	}
	
	function GetPreviousPage()
	{
		$url = $_SERVER['HTTP_REFERER'];
		
		if( !$url )
			$url = $this->m_url;
		
		return $url;
	}
	
	function StorePostedData()
	{
		if( $_POST )
		{
			$post_copy = $_POST;
			
			$this->ClearPostedData();
			
			foreach( $post_copy as $key => $value )
			{
				$_SESSION['post_data'][ $key ] = $value;
			}
		}
	}
	
	function ClearPostedData()
	{
		if( isset( $_SESSION['post_data'] ) )
			unset( $_SESSION['post_data'] );
	}
	
	function GetPostedData()
	{
		return $_SESSION['post_data'];
	}
	
	function ItemDebug( $data )
	{
		echo "<br>---------------- Start Debug ----------------<br>";
		echo "<pre>".print_r( $data, true )."</pre>";
		echo "----------------  End Debug  ----------------<br>";
	}
	
	function Encrypt( $data, $type = 'base64' )
	{
		switch( $type )
		{
			case 'base64':
				$result = base64_encode( $data );
				break;
			
			case 'rsa':
				$keys = $this->GetClass('rsa')->generate_keys( '3754241', '3782059' ); 

				$result = $this->GetClass('rsa')->encrypt( $data, $keys[1], $keys[0], 5 );
				break;
			
			case 'rsa_base64':
				$result = $this->Encrypt( $this->Encrypt( $data, 'rsa' ), 'base64' );
				break;
			
			case 'mcrypt':
				$key = "kitty";
				$iv_size =  mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);
				$iv = mcrypt_create_iv( $iv_size, MCRYPT_RAND );
				
				$string = trim($data);
				
				$result = mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $key, $string, MCRYPT_MODE_ECB, $iv);
				break;
			
			case 'mcrypt_base64':
				$result = $this->Encrypt( $this->Encrypt( $data, 'mcrypt' ), 'base64' );
				break;
		}
		
		return $result;
	}
	
	function Decrypt( $data, $type = 'base64' )
	{
		switch( $type )
		{
			case 'base64':
				$result = base64_decode( $data );
				break;
			
			case 'rsa':
				$keys = $this->GetClass('rsa')->generate_keys( '3754241', '3782059' );

				$result = $this->GetClass('rsa')->decrypt( $data, $keys[2], $keys[0] );
				break;
			
			case 'rsa_base64':
				$result = $this->Decrypt( $this->Decrypt( $data, 'base64' ), 'rsa' );
				break;
			
			case 'mcrypt':
				$key = "kitty";
				$key = trim($key);
				$iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);
				$iv = mcrypt_create_iv( $iv_size, MCRYPT_RAND );
			
				$result = trim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256,$key,$data,MCRYPT_MODE_ECB,$iv));
				break;
			
			case 'mcrypt_base64':
				$result = $this->Decrypt( $this->Decrypt( $data, 'base64' ), 'mcrypt' );
				break;
		}
		
		return $result;
	}
	
	function AddThisBlogToSiteTracker()
	{
		// Notify Qody of your main site
		$keyword = strtolower( $this->get_option( 'keyword_to_track') );
		
		$this->ConnectWithSiteTracker( $keyword, 'enable' );
	}
	
	function Encode( $stuff )
	{
		$encoded = serialize( $stuff );
		$encoded = $this->filter( $encoded );
		
		return $encoded;
	}
	
	function Decode( $stuff )
	{
		$stuff = $this->Clean( $stuff );
		$decoded = unserialize( html_entity_decode($stuff) );		
		
		return $decoded;
	}
	
	function DecodeResponse( $response )
	{
		return $this->ObjectToArray( json_decode($response) );
	}
	
	function EncodeResponse( $response )
	{
		return json_encode($response);
	}
	
	function ObjectToArray( $object )
	{
		if( !is_object( $object ) && !is_array( $object ) )
		{
			return $object;
		}
		if( is_object( $object ) )
		{
			$object = get_object_vars( $object );
		}
		
		return array_map( array($this, 'ObjectToArray'), $object );
	}
	
	function MakeSlug( $slug )
	{
		$slug = str_replace( ' ', '_', $slug );
		$slug = strtolower( $slug );
		
		return $slug;
	}
	
	function GetFromSlug( $slug )
	{
		$slug = str_replace( '_', ' ', $slug );
		$slug = ucwords( $slug );
		
		return $slug;
	}
	
	function xmlstr_to_array($xmlstr) {
		$doc = new DOMDocument();
		$doc->loadXML($xmlstr);
		
		return $this->domnode_to_array($doc->documentElement);
	}
	
	function domnode_to_array($node)
	{
		$output = array();
		switch ($node->nodeType)
		{
			case XML_CDATA_SECTION_NODE:
			case XML_TEXT_NODE:
				$output = trim($node->textContent);
				break;
			
			case XML_ELEMENT_NODE:
			for ($i=0, $m=$node->childNodes->length; $i<$m; $i++)
			{
				$child = $node->childNodes->item($i);
				$v = Qody::domnode_to_array($child);
				
				if(isset($child->tagName))
				{
					$t = $child->tagName;
					if(!isset($output[$t]))
					{
						$output[$t] = array();
					}
					$output[$t][] = $v;
				}
				elseif($v)
				{
					$output = (string) $v;
				}
			}
			if(is_array($output))
			{
				if($node->attributes->length)
				{
					$a = array();
					foreach($node->attributes as $attrName => $attrNode)
					{
						$a[$attrName] = (string) $attrNode->value;
					}
					$output['@attributes'] = $a;
				}
				foreach ($output as $t => $v)
				{
					if(is_array($v) && count($v)==1 && $t!='@attributes')
					{
						$output[$t] = $v[0];
					}
				}
			}
			break;
		}
		return $output;
	}
	
	function NumberTimeToStringTime( $theTime, $styled = 'strong', $spotsWanted = 1 )
	{
		$oneSecond = 1;
		$oneMinute = $oneSecond * 60;
		$oneHour = $oneMinute * 60;
		$oneDay = $oneHour * 24;
		
		if( $styled && $styled != 'none' )
		{
			$start = '<'.$styled.'>';
			$end = '</'.$styled.'>';
		}
		
		$timeLeft = $theTime;
		$runningTotal = "";
		$daysLeft = (int)($timeLeft / $oneDay);
		$timeLeft -= $daysLeft * $oneDay;
		$hoursLeft = (int)($timeLeft / $oneHour);
		$timeLeft -= $hoursLeft * $oneHour;
		$minutesLeft = (int)($timeLeft / $oneMinute);
		$timeLeft -= $minutesLeft * $oneMinute;
		$secondsLeft = (int)($timeLeft / $oneSecond);
		
		$spotsTaken = 0;
		
		if( $daysLeft > 0 )
		{
			$spotsTaken++;
			$runningTotal .= ' ';
			
			$runningTotal .= $start.$daysLeft.$end." day";
			if( $daysLeft > 1 )
				$runningTotal .= "s";
		}
		
		if( $hoursLeft > 0 && $spotsTaken < $spotsWanted )
		{
			$spotsTaken++;
			$runningTotal .= ' ';
			
			$runningTotal .= $start.$hoursLeft.$end." hour";
			if( $hoursLeft > 1 )
				$runningTotal .= "s";
		}
		
		if( $minutesLeft > 0 && $spotsTaken < $spotsWanted )
		{
			$spotsTaken++;
			$runningTotal .= ' ';
			
			$runningTotal .= $start.$minutesLeft.$end." minute";
			if( $minutesLeft > 1 )
				$runningTotal .= "s";
		}
		
		if( $secondsLeft > 0 && $spotsTaken < $spotsWanted )
		{
			$spotsTaken++;
			$runningTotal .= ' ';
			
			$runningTotal .= $start.$secondsLeft.$end." second";
			if( $secondsLeft > 1 )
				$runningTotal .= "s";
		}
		
		if( $spotsTaken == 0 )
		{
			$runningTotal = "no time";
		}
		
		return trim( $runningTotal );
	}
	
	function ListFilesInDirectory( $start_dir = '.' )
	{
		$files = array();
		
		if( is_dir($start_dir) )
		{
			$fh = opendir($start_dir);
			
			while( ($file = readdir($fh) ) !== false )
			{
				# loop through the files, skipping . and .., and recursing if necessary
				if( strcmp($file, '.') == 0 || strcmp($file, '..') == 0 )
					continue;
					
				$filepath = $start_dir . '/' . $file;
				
				if( is_dir($filepath) )
					$files = array_merge( $files, $this->ListFilesInDirectory($filepath) );
				else
					array_push( $files, $filepath );
			}
			
			closedir($fh);
		}
		else
		{
			# false if the function was called with an invalid non-directory argument
			$files = false;
		}
		
		return $files;
	}
	
	function OrganizeDirectoryIntoArray($dir = ".") 
    { 
        $listDir = array();
		
		if( !is_dir( $dir ) )
			return $listDir;
			
        if($handler = opendir($dir)) { 
            while (($sub = readdir($handler)) !== FALSE) { 
                if ($sub != "." && $sub != ".." && $sub != "Thumb.db" && $sub != "Thumbs.db") { 
                    if(is_file($dir."/".$sub)) { 
                        $listDir[] = $sub; 
                    }elseif(is_dir($dir."/".$sub)){ 
                        $listDir[$sub] = $this->OrganizeDirectoryIntoArray($dir."/".$sub); 
                    } 
                } 
            } 
            closedir($handler); 
        } 
        return $listDir; 
    }
	
	function file_get_php_classes( $filepath )
	{
		$php_code = file_get_contents( $filepath );
		
		$classes = $this->get_php_classes( $php_code );
		
		return $classes;
	}
	
	function get_php_classes($php_code)
	{
		$classes = array();
		
		$tokens = token_get_all($php_code);
		
		$count = count($tokens);
		
		for( $i = 2; $i < $count; $i++ )
		{
			if( $tokens[$i - 2][0] == T_CLASS && $tokens[$i - 1][0] == T_WHITESPACE && $tokens[$i][0] == T_STRING )
			{
				$class_name = $tokens[$i][1];
				$classes[] = $class_name;
			}
		}
		
		return $classes;
	}
	
	function getRealIP()
	{
		if( $_SERVER['HTTP_X_FORWARDED_FOR'] != '' )
		{
			$client_ip =
			( !empty($_SERVER['REMOTE_ADDR']) ) ?
			$_SERVER['REMOTE_ADDR']
			:
			( ( !empty($_ENV['REMOTE_ADDR']) ) ?
			$_ENV['REMOTE_ADDR']
			:
			"unknown" );
		
			// Proxies are added at the end of this header
			// Ip addresses that are "hiding". To locate the actual IP
			// User begins to look for the beginning to find
			// Ip address range that is not private. If not
			// Found none is taken as the value REMOTE_ADDR

			$entries = split('[, ]', $_SERVER['HTTP_X_FORWARDED_FOR']);

			reset($entries);
			while (list(, $entry) = each($entries))
			{
				$entry = trim($entry);
				if ( preg_match("/^([0-9]+\.[0-9]+\.[0-9]+\.[0-9]+)/", $entry, $ip_list) )
				{
					// http://www.faqs.org/rfcs/rfc1918.html
					$private_ip = array(
					'/^0\./',
					'/^127\.0\.0\.1/',
					'/^192\.168\..*/',
					'/^172\.((1[6-9])|(2[0-9])|(3[0-1]))\..*/',
					'/^10\..*/');

					$found_ip = preg_replace($private_ip, $client_ip, $ip_list[1]);

					if ($client_ip != $found_ip)
					{
						$client_ip = $found_ip;
						break;
					}
				}
			}
		}
		else
		{
			$client_ip =
			( !empty($_SERVER['REMOTE_ADDR']) ) ?
			$_SERVER['REMOTE_ADDR']
			:
			( ( !empty($_ENV['REMOTE_ADDR']) ) ?
			$_ENV['REMOTE_ADDR']
			:
			"unknown" );
		}

		return $client_ip;
	}
}
?>