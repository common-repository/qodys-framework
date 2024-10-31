<?php
/** 
 * Functions for examining and manipulating matrices (n-dimensional arrays) of data 
 * with string dot-separated paths. For example, you might do this with multidimensional 
 * array: 
 *   $var = $array['someKey']['cats']['dogs']['potato']; 
 * 
 * Accomplishing this can be a nightmare if you don't know the depth of the path or the array 
 * is of a variable dimension. 
 * 
 * You can accomplish the same by using $array as a Matrix: 
 *   $array = new Matrix($array); 
 *   $var = $array->get('someKey.cats.dogs.potato); 
 *   
 * @author Daniel Tomasiewicz <www.fourstaples.com> 
 */ 
class QodyArrayMatrix { 
	public $data;
	private $m_delimiter = '/';
	
	public function __construct(array $data = array()) { 
		$this->data = $data; 
	} 
	
	/** 
	 * Gets the value at the specified path. 
	 */ 
	public function get($path = null) { 
		if($path === null) { 
			return $this->data; 
		} 
		
		$segs = explode('.', $path); 
		
		$target =& $this->data; 
		for($i = 0; $i < count($segs)-1; $i++) { 
			if(isset($target[$segs[$i]]) && is_array($target[$segs[$i]])) { 
				$target =& $target[$segs[$i]]; 
			} else { 
				return null; 
			} 
		} 
		
		if(isset($target[$segs[count($segs)-1]])) { 
			return $target[$segs[count($segs)-1]]; 
		} else { 
			return null; 
		} 
	} 
	
	/** 
	 * Sets a value to a specified path. If the provided value is 
	 * null, the existing value at the path will be unset. 
	 */ 
	public function set($path, $value = null) { 
	
		//echo "-------------<pre>".print_r( $path, true )."</pre>-------------";
		//echo "-------------<pre>".print_r( $value, true )."</pre>-------------";
					
		if(is_array($path)) { 
			foreach($path as $p => $v) { 
				$this->set($p, $v); 
			} 
		} else { 
			$segs = explode($this->m_delimiter, $path); 
		
			$target =& $this->data; 
			for($i = 0; $i < count($segs)-1; $i++) { 
				if(!isset($target[$segs[$i]])) { 
					$target[$segs[$i]] = array(); 
				} 
				
				$target =& $target[$segs[$i]]; 
			} 
		
			if($segs[count($segs)-1] == '*') { 
				foreach($target as $key => $value) { 
					$target[$key]; 
				} 
			} elseif($value === null && isset($target[$segs[count($segs)-1]])) { 
				unset($target[$segs[count($segs)-1]]); 
			} else { 
				$target[$segs[count($segs)-1]] = $value; 
				//$target[$segs[count($segs)-1]][$value['file_name']] = $value; 
			} 
		} 
	} 
	
	/** 
	 * Returns a flattened version of the data (one-dimensional array 
	 * with dot-separated paths as its keys). 
	 */ 
	public function flatten($path = null) { 
		$data = $this->get($path); 
		
		if($path === null) { 
			$path = ''; 
		} else { 
			$path .= $this->m_delimiter; 
		} 
		
		$flat = array(); 
					
		foreach($data as $key => $value) { 
			if(is_array($value)) { 
				$flat += $this->flatten($path.$key); 
			} else { 
				$flat[$path.$key] = $value; 
			} 
		} 
		
		return $flat; 
	} 
	
	/** 
	 * Expands a flattened array to an n-dimensional matrix. 
	 */ 
	public static function expand($flat) { 
		$matrix = new Matrix(); 
		
		foreach($flat as $key => $value) { 
			$matrix->set($key, $value); 
		} 
		
		return $matrix; 
	} 
} 
?>