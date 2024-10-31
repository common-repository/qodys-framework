<?php
class QodyDatabase extends QodyPlugin
{
	function __construct()
	{
		parent::__construct();
	}
	
	function TableExists( $table_name, $caller = null )
	{
		global $wpdb;
		
		$table_name = $this->FixTableName( $table_name, $caller );
		
		if( $wpdb->get_results( "SHOW TABLES LIKE '". $table_name ."'" ) )
			return true;
		
		return false;
	}
	
	function FixTableName( $table_name, $caller = null )
	{
		global $wpdb;
		
		if( $caller )
		{
			$table_name = $caller->Owner() ? $caller->Owner()->GetPre().'_'.$table_name : $caller->GetPre().'_'.$table_name;
		}
		
		$table_name = $wpdb->prefix.$table_name;
		
		return $table_name;
	}
	
	function CreateTable( $table_name, $fields, $append_config = '', $caller = null )
	{
		global $wpdb;
		
		$table_name = $this->FixTableName( $table_name, $caller );
		
		$sql = "CREATE TABLE IF NOT EXISTS $table_name (".implode( ',', $fields ).") ";
		
		if( $append_config )
			$sql .= $append_config;
		
		$sql .= ';';
		
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta($sql);
	}
	
	function Select( $table_name, $query = '', $caller = null, $postfix = '' )
	{
		global $wpdb;
		
		$table_name = $this->FixTableName( $table_name, $caller );
		
		$data = $wpdb->get_results( "SELECT * FROM ".$table_name.($query ? " WHERE ".$query : '')." ".$postfix, ARRAY_A );
		
		return $data;
	}

	function GetFromDatabase( $table_name, $field = '', $value = '', $single = false, $caller = null )
	{
		global $wpdb;
		
		$table_name = $this->FixTableName( $table_name, $caller );
		
		$value = $wpdb->escape( $value );
		
		$query = "SELECT * FROM ".$table_name;
		
		if( $field && $value )
			$query .= " WHERE {$field} = '{$value}'";
		
		$results = $wpdb->get_results( $query, ARRAY_A);
		
		if( $single )
			$results = $results[0];
			
		return $results;
	}
	
	function ClearTable( $table_name, $caller = null )
	{
		global $wpdb;
		
		$table_name = $this->FixTableName( $table_name, $caller );
		
		$wpdb->query( "DELETE FROM ".$table_name );
	}
	
	function DeleteFromDatabase( $table, $field, $value )
	{
		global $wpdb;
		
		$value = $wpdb->escape( $value );
		
		$wpdb->query( "DELETE FROM ".$wpdb->pre.$table." WHERE {$field} = '{$value}'" );
	}
	
	function UpdateDatabase( $fields, $table_name, $id, $option = 'id', $caller = null )
	{
		global $wpdb;
		
		$table_name = $this->FixTableName( $table_name, $caller );
		
		$first = '';
		$looper = 0;
		
		foreach( $fields as $key => $value )
		{
			$looper++;
			
			if( $looper == 1 )
				$first .= $wpdb->escape( $key )." = '".$wpdb->escape( $value )."' ";
			else
				$first .= ",".$wpdb->escape( $key )." = '".$wpdb->escape( $value )."' ";
		}
		
		$wpdb->query( "UPDATE ".$table_name." SET ".$first." WHERE {$option} = '".$wpdb->escape( $id )."'" );
	}
	
	function InsertToDatabase( $fields, $table, $caller = null, $use_prefix = true )
	{
		global $wpdb;
		
		if( !$fields )
			return;

		$first = '';
		$second = '';
		$looper = 0;
		
		if( $caller )
		{
			$table = $caller->Owner() ? $caller->Owner()->GetPre().'_'.$table : $caller->GetPre().'_'.$table;
		}
		
		if( $use_prefix )
			$table = $wpdb->prefix.$table;
		
		$bits = explode( '.', $table );
		
		if( count($bits) > 1 )
		{
			$table = '`'.$bits[0].'` . `'.$bits[1].'`';
		}
		else
		{
			$table = '`'.$table.'`';
		}
			
		foreach( $fields as $key => $value )
		{
			$looper++;
			
			if( $looper == 1 )
				$first .= "`".$wpdb->escape( $key )."`";
			else
				$first .= ",`".$wpdb->escape( $key )."`";
				
			if( $looper == 1 )
				$second .= "'".$wpdb->escape( $value )."'";
			else
				$second .= ",'".$wpdb->escape( $value )."'";			
		}

		$wpdb->query( "INSERT INTO ".$wpdb->pre.$table." (".$first.") VALUES (".$second.")" );
	}

}
?>