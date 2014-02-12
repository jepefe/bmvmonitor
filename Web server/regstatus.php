<?php
/*
Copyright (C) 2012 Jesus Perez <jepefe@gmail.com>
This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
(at your option) any later version.
 
This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License at <http://www.gnu.org/licenses/>
for more details.
*/

if(isset($_POST)){
	ob_start(); //Redirect output to internal buffer
    require_once 'bmvcfg.php';
	ob_end_clean(); 
	date_default_timezone_set($timezone);

	if($_POST["token"] == $token) {
	
		if(!(date('i',time())%$reg_interval)){
			
		
			if(isset($_POST["datetime"])){
			$date_time = date('Y-m-d G:i',$_POST["datetime"]); //Date from remote host 
		}else{
			$date_time = date('Y-m-d G:i',time()); //Date from localhost
		}
		
	
			
		$device_array = json_decode($_POST["device"]);
		$i=0;
		while($element = current($device_array)) {

    		$device_array_keys[$i] = key($device_array);
    		$i++;
   			next($device_array);
		}
		global $sqlkeystring;
		global $sqlvalstring;

		//while($element = current($device_array)){

		//for ($i=0; $i<count($device_array_keys);$i++){
		$sqlkeystring = "date";
		$sqlvalstring = "'".$date_time."'";
		$hsqlkeystring = "date";
		$hsqlvalstring = "'".$date_time."'";	

		foreach ($device_array as $key => $value) {
			
			if ((($key !="BMV") && ($key !="CHECKSM")) && ($key !="FW")) {

				if ($key[0]=="H"){
					$hsqlkeystring = $hsqlkeystring.','.$key;
					$hsqlvalstring = $hsqlvalstring.','."'".$value."'";

				}else{
					 $sqlkeystring = $sqlkeystring.','.$key;
					 $sqlvalstring = $sqlvalstring.','."'".$value."'";
					 	
			}
		}
		


		}
		
		register_data($date_time, $sqlkeystring,$sqlvalstring);
		register_hist($date_time, $hsqlkeystring,$hsqlvalstring);

		$file = "bmvlog";
		$data = $_POST["device"];
		file_put_contents($file, $data);
		
	}else {
		$file = "bmvlog";
		$data = $_POST["device"];
		file_put_contents($file, $data);
		}
}
}
				
	
		
	function register_data($date_time, $sqlkeys, $sqlvals){
		
	
	 $query =	"INSERT INTO bmv (".$sqlkeys.") VALUES (".$sqlvals.")";
	echo $query;
	
	$connection = db_connection();
	mysql_query($query,$connection);
}


	function register_hist($date_time, $hsqlkeys, $hsqlvals){
	$date_time = strtotime($date_time);
	$yearq = date("Y", $date_time);
    $monthq  = date("m", $date_time);
    $dayq = date("d", $date_time);
    


	$connection = db_connection();
	$max_v = mysql_query("select max(V) from bmv where day(date) ='".$dayq."' AND month(date) =  '".$monthq."' AND year(date) = '".$yearq."'",$connection);
	$max_v = mysql_fetch_row($max_v);

	$historial["max_v"] = $max_v[0];

	$min_v = mysql_query("select min(V) from bmv where day(date) ='".$dayq."' AND month(date) =  '".$monthq."' AND year(date) = '".$yearq."'",$connection);
	$min_v = mysql_fetch_row($min_v);
	$historial["min_v"] = $min_v[0];

	$max_i = mysql_query("select max(I) from bmv where day(date) ='".$dayq."' AND month(date) =  '".$monthq."' AND year(date) = '".$yearq."'",$connection);
	$max_i = mysql_fetch_row($max_i);
	$historial["max_i"] = $max_i[0];

	$min_i = mysql_query("select min(I) from bmv where day(date) ='".$dayq."' AND month(date) =  '".$monthq."' AND year(date) = '".$yearq."'",$connection);
	$min_i = mysql_fetch_row($min_i);
	$historial["min_i"] = $min_i[0];

	$max_ce = mysql_query("select min(CE) from bmv where day(date) ='".$dayq."' AND month(date) =  '".$monthq."' AND year(date) = '".$yearq."'",$connection);
	$max_ce = mysql_fetch_row($max_ce);
	$historial["max_ce"] = $max_ce[0];

	$max_soc = mysql_query("select max(SOC) from bmv where day(date) ='".$dayq."' AND month(date) =  '".$monthq."' AND year(date) = '".$yearq."'",$connection);
	$max_soc = mysql_fetch_row($max_soc);
	$historial["max_soc"] = $max_soc[0];

	$min_soc = mysql_query("select min(SOC) from bmv where day(date) ='".$dayq."' AND month(date) =  '".$monthq."' AND year(date) = '".$yearq."'",$connection);
	$min_soc = mysql_fetch_row($min_soc);
	$historial["min_soc"] = $min_soc[0];


	$query = "REPLACE INTO bmv_hist(".$hsqlkeys.",max_v,min_v,max_i,min_i,max_ce,max_soc,min_soc) VALUES ("
		.$hsqlvals.",'".$historial['max_v']."','".$historial['min_v']."','"
		.$historial['max_i']."','".$historial['min_i']."','".$historial['max_ce']."','".$historial['max_soc']."','".$historial['min_soc']."')"; 
	
	echo $query;
	
	mysql_query($query,$connection);
		
}




function db_connection(){
	global $dbpass;
	global $dbuser;
	global $dbname;
	global $dbhost;
	$connection = mysql_connect($dbhost, $dbuser, $dbpass);
            	mysql_select_db($dbname, $connection);
    return $connection;
}

?>

