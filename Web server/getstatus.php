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

if(isset($_GET)){
	ob_start(); //Redirect output to internal buffer
    require_once 'bmvcfg.php';
	ob_end_clean(); 
	
		
			switch ($_GET["q"]) {
				case 'years': //Flexnet device id
					send_year();
				break;
				case 'months'://FM/MX device id
					send_month_totals();
				break;
				case 'month_days'://FM/MX device id
					send_month_days();
				break;
				case 'day'://FX device id
					send_day();
				break;
				
				default:
					break;
			}
		
		
		
	
}
				
function send_day(){
	
	$connection = db_connection();
	$query = "SELECT * FROM bmv where date(date) = DATE('".$_GET["date"]."') ORDER BY date";
	$result = mysql_query($query, $connection);

	$query_summary = "SELECT date, max_soc, min_soc, max_v,min_v,max_i as max_amps, min_i as min_amps FROM bmv_hist where date(date) = DATE('".$_GET["date"]."') ORDER BY date";
	$result_summary = mysql_query($query_summary, $connection);
	
	$allday_data = array();



    $i=0;
		while($row = mysql_fetch_assoc($result)){
			$allday_data['day'][$i] = $row;
			//echo $row;
			$i++;
		}

	

	while($row= mysql_fetch_assoc($result_summary)){
	$allday_data['summary'] = $row;
		
	}
		
$json_day= json_encode($allday_data);
echo $json_day;
	

}
function send_month_totals(){
	
	$connection = db_connection();
	$query = "SELECT date as fecha, (min(H6)-(select max(H6) FROM bmv_hist where month(date)=(month(fecha)) group by month(date))) AS ah_out FROM bmv_hist where year(date) = year('".$_GET["date"]."') Group by month(fecha)";
	$query2 = "SELECT date as fecha, min(max_ce) as ah_out from bmv_hist where year(date) = year('".$_GET["date"]."') Group by month(fecha)";
	$query_result = mysql_query($query, $connection);
	$query_result2 = mysql_query($query2, $connection);
	$result=NULL;
	$result2=NULL;
	$i=0;
	while($row2 = mysql_fetch_assoc($query_result2)){
		 
			$result2[$i] = $row2;
			
			$i++;
	}
	$i=0;

	while($row = mysql_fetch_assoc($query_result)){
		 
			$result[$i] = $row;
			if($row["ah_out"]==null){
				$result[$i]=$result2[$i];
				
			}
			$i++;
	}
	
	$json_months = json_encode($result);
	echo $json_months;
	

}

function send_month_days(){
	
	$connection = db_connection();
	#$query = "SELECT date as fecha, (min(H6)-(select max(H6) FROM bmv_hist where day(date)=(day(fecha)) group by day(date))) AS ah_out FROM bmv_hist where year(date) = year('".$_GET["date"]."') and month(date)= month('".$_GET["date"]."') Group by day(date)";
	$query = "SELECT date as fecha, (min(H6)-(select max(H6) FROM bmv_hist where day(date)=day(fecha)-1 and month(date)=month(fecha) and year(date) = year(fecha) group by day(date))) AS ah_out FROM bmv_hist where year(date) = year('".$_GET["date"]."') and month(date)= month('".$_GET["date"]."') Group by day(date)";
	$query2 = "SELECT date as fecha, max_ce as ah_out from bmv_hist where year(date) = year('".$_GET["date"]."') AND month(date) = month('".$_GET["date"]."') Group by day(date)";
	$query_result2 = mysql_query($query2, $connection);
	$query_result = mysql_query($query, $connection);
	$result=NULL;
	$result2=NULL;
	$i=0;

	
	while($row2 = mysql_fetch_assoc($query_result2)){
		 
			$result2[$i] = $row2;
			
			$i++;
	}


	$i=0;

	if($query_result2 != NULL){

	while($row = mysql_fetch_assoc($query_result)){
		 
			$result[$i] = $row;
			if($row["ah_out"]==null){
				$result[$i]=$result2[$i];
				
			}
			$i++;
	}
	}else{
		$query3 = "SELECT date as fecha, H6 AS ah_out FROM bmv_hist where year(date) = year('".$_GET["date"]."') and month(date)= month('".$_GET["date"]."') Group by day(date)";
		$query_result3 = mysql_query($query3);
		$resç[$i] = mysql_fetch_assoc($query_result3);
	}

	
	$json_month_days = json_encode($result);
	echo $json_month_days;
	

}

function send_year(){
	
	$connection = db_connection();
	$query = " SELECT a.date as fecha, SUM( b.H6 - a.H6 ) as ah_out
				FROM bmv_hist a
				JOIN bmv_hist b ON b.date = DATE_SUB( DATE( a.date ) , INTERVAL 1 
				DAY ) 
			  GROUP BY YEAR(a.date) ";

	$query_result = mysql_query($query, $connection);
	$result=NULL;

   $i=0;
		while($row = mysql_fetch_assoc($query_result)){
		 
			$result[$i] = $row;
			
			$i++;
	}
			
	
	
	$json_years = json_encode($result);
	
	echo $json_years;
	

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

