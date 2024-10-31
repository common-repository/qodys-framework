<?php
class QodyTime extends QodyPlugin
{
	function __construct()
	{
		parent::__construct();
	}
	
	function SecondsPerDay()
	{
		$seconds_in_day = 60 * 60 * 24;
		
		return $seconds_in_day;
	}
	
	function CalculateDay( $date )
	{
		$seconds_in_day = $this->SecondsPerDay();
		
		$the_day = (int)(strtotime($date) / $seconds_in_day) * $seconds_in_day;
		
		return $the_day;
	}
	
	function GetToday()
	{
		$seconds_in_day = $this->SecondsPerDay();
		
		$today = (int)(time() / $seconds_in_day) * $seconds_in_day;
		
		return $today;
	}
	
	function GetYesterday()
	{
		$seconds_in_day = $this->SecondsPerDay();
		$today = $this->GetToday();
		
		$yesterday = $today - ($seconds_in_day * 1);
		
		return $yesterday;
	}
}
?>