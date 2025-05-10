<? 
	function validateDate($date, $format = 'Y-m-d H:i:s'){
		$d = DateTime::createFromFormat($format, $date);
		return $d && $d->format($format) == $date;
	}
	function getDefultDateNow(){
		if($GLOBALS['__LANGUAGE']=="th")
			return date("d/m")."/".(date("Y")+543);
		else if($GLOBALS['__LANGUAGE']=="en")
			return date("d/m")."/".(date("Y")-$prev);
	}
	function getDefultDate($prev=0){
		if($GLOBALS['__LANGUAGE']=="th")
			return date("d/m")."/".(date("Y")+(543-$prev));
		else if($GLOBALS['__LANGUAGE']=="en")
			return date("d/m")."/".(date("Y")-$prev);
	}
	function getDefultStartDateInMonth($prev=0){
		if($GLOBALS['__LANGUAGE']=="th")
			return date("01/m")."/".(date("Y")+(543-$prev));
		else if($GLOBALS['__LANGUAGE']=="en")
			return date("01/m")."/".(date("Y")-$prev);
	}  
	function getDefultEndDateInMonth($prev=0){
		if($GLOBALS['__LANGUAGE']=="th")
			return date("t/m")."/".(date("Y")+(543-$prev));
		else if($GLOBALS['__LANGUAGE']=="en")
			return date("t/m")."/".(date("Y")-$prev);
	}  
	function getDefultStartDateInYear($prev=0){
		if($GLOBALS['__LANGUAGE']=="th")
			return "01/01/".(date("Y")+(543-$prev));
		else if($GLOBALS['__LANGUAGE']=="en")
			return "01/12/".(date("Y")-$prev);
	}  
	function getDefultEndDateInYear($prev=0){
		if($GLOBALS['__LANGUAGE']=="th")
			return "31/12/".(date("Y")+(543-$prev));
		else if($GLOBALS['__LANGUAGE']=="en")
			return "31/12/".(date("Y")-$prev);
	}  


	function date_filter($mydate){
		global $configs;
		return date("j F Y", strtotime($mydate));
	}
	function dateDiff($str_dt,$end_dt){
			if(empty(strtotime($str_dt)) || strtotime($str_dt) == 0){
				return 0;
			}
			$daysDiff = strtotime($str_dt)-strtotime($end_dt);
			return round(abs($daysDiff)/(24*60*60))-1; 
	}
	function monthDiff($str_mt,$end_mt){
		//Case 1 
			// $startYear = intval(date('Y',strtotime($str_mt)));
			// $startMonth = intval(date('m',strtotime($str_mt)));
			// $endYear = intval(date('Y',strtotime($end_mt)));
			// $endMonth = intval(date('m',strtotime($end_mt)));

			// if($startYear==$endYear){
			// 	$count = abs($endMonth-$startMonth)+1;
			// }else{
			// 	if($startYear<$endYear){
			// 		$count = (12-$startMonth)+1;
			// 		$count += $endMonth;
			// 	}else{
			// 		$count = (12-$endMonth)+1;
			// 		$count += $startMonth;
			// 	}

			// }

		//Case 2
			// $start = date_create($str_mt."-01");
			// $end = date_create(date('Y-m-d',strtotime(date('Y-m-t',strtotime($end_mt."-01"))." +1 day")));
			// $diff =  date_diff($start, $end);
			// echo "diff: ".json_encode($diff)."<br>";

			// return (($diff->y * 12) + ($diff->m) + ($diff->d>0 ? 1 : 0)); 

		//Case 3
			// $date1 = mktime(0,0,0,substr($str_mt,5,2),0,substr($str_mt,0,4)); // m d y, use 0 for day
			// $date2 = mktime(0,0,0,substr($end_mt,5,2),0,substr($end_mt,0,4)); // m d y, use 0 for day

			// return round(($date2-$date1) / 60 / 60 / 24 / 30)+1;

		//Case 4
			// echo "start : ".$str_mt."-01 00:00:00<BR>";
			// echo "end : ".date('Y-m-t',strtotime($end_mt.'-01'))." 23:59:59<BR>";
			$start = new DateTime($str_mt.'-01 00:00:00');
			$end = new DateTime(date('Y-m-t',strtotime($end_mt.'-01')).' 23:59:59');
			$diff = $start->diff($end);
			// print_r($diff);
			// echo "<BR>";
			$yearsInMonths = $diff->format('%r%y') * 12;
			$months = $diff->format('%r%m');
			$totalMonths = $yearsInMonths + $months;
			if($diff->format('%r%d')>3){
				$totalMonths += 1;
			}

			return $totalMonths; // 14
	}
	function dateCount($number_of_day){
		global $date_unit;
			$yearCount = $number_of_day/365;
			$yy = explode('.',$yearCount);
			$year = $yy[0];
			
			$monthCount = ($yearCount-$year)*12;
			$mm = explode('.',$monthCount);
			$month = $mm[0];
			
			$day = ceil(($monthCount-$month)*30);

			$result = '';
			if($year>0){
				$result .= $year." {$date_unit['year']} ";	
			}
			if($month>0){
				$result .= $month." {$date_unit['month']} ";	
			}
			if($day>0){
				$result .= $day." {$date_unit['day']}";	
			}
			
			return $result;
	}

	function dateDisplay($startDate, $endDate, $self = 0){
		global $date_unit;
		$date1 = new DateTime($startDate);
		$date2 = new DateTime($endDate);

		if ($date1 > $date2) {
			return 0 . " " . $date_unit['day'];
		}

		if ($date1 == $date2) {
			return 1 . " " . $date_unit['day'];
		}

    	// ถ้า $self เป็น 1 ให้นับวันแรกด้วย
		if ($self == 1) {
			$date2->modify('+1 day');
		}

		$diff = $date1->diff($date2);

		$year = $diff->y;
		$month = $diff->m;
		$day = $diff->d;

		$result = '';
		if ($year > 0) {
			$result .= $year . " " . $date_unit['year'] . " ";
		}
		if ($month > 0) {
			$result .= $month . " " . $date_unit['month'] . " ";
		}
		if ($day > 0) {
			$result .= $day . " " . $date_unit['day'];
		}

		return $result;
	}

	/**
	 * Function dateCountDiffByType
	 * 
	 * คำนวณความแตกต่างระหว่างวันที่ตามประเภทที่กำหนด (ปี, เดือน, วัน) รวม ปีอธิกสุรทิน
	 * 
	 * @param string $startDate วันที่เริ่มต้น (รูปแบบ 'YYYY-MM-DD')
	 * @param string $endDate วันที่สิ้นสุด (รูปแบบ 'YYYY-MM-DD')
	 * @param string $type ประเภทที่ต้องการคำนวณ ('y' = ปี, 'm' = เดือน, 'd' = วัน)
	 * @param int $self ใช้ในการปรับค่าตัวแปร $endDate ถ้าค่าคือ 1 จะรวมวันแรกด้วย (default: 1)
	 * 
	 * @return int คืนค่าความแตกต่างระหว่าง $startDate กับ $endDate ตามประเภทที่เลือก ('y', 'm', 'd')
	 */
	function dateCountDiffByType($startDate, $endDate, $type = 'd', $self = 1){
		$date1 = new DateTime($startDate);
		$date2 = new DateTime($endDate);

		if ($date1 > $date2) {
			return 0;
		}

		if ($date1 == $date2) {
			if($type == 'd') {
				return 1;
			}
			return 0;
		}
	
		if ($self == 1) {
			$date2->modify('+1 day'); // รวมวันแรก
		}
	
		$diff = $date1->diff($date2);
	
		switch ($type) {
			case 'y':
				return $diff->y;
			case 'm':
				return ($diff->y * 12) + $diff->m;
			case 'd':
			default:
				return $diff->days;
		}
	}

	function birthDate($birthDate) {
		global $date_unit;
	
		if (empty($birthDate) || $birthDate == '0000-00-00' || strtotime($birthDate) === false) {
			return '';
		}
	
		$birthDateTime = new DateTime($birthDate);
		$currentDateTime = new DateTime();
	
		$interval = $birthDateTime->diff($currentDateTime);
	
		$result = '';
	
		if ($interval->y > 0) {
			$result .= $interval->y . " {$date_unit['year']} ";
		}
		if ($interval->m > 0) {
			$result .= $interval->m . " {$date_unit['month']} ";
		}
		if ($interval->d >= 0) {
			$result .= $interval->d . " {$date_unit['day']}";
		}
	
		return trim($result);
	}
	
	function dateShortCount($number_of_day){
		global $date_unit;
		
		$yearCount = $number_of_day/365; 

		$result = round($yearCount,2)." {$date_unit['year']}";	
		
		return $result;
	}
	function monthCount($number_of_day, $from_dt, $to_dt){
		$from_year = date('Y', strtotime($from_dt));
		$to_month = date('m', strtotime($to_dt));
		$to_year = date('Y', strtotime($to_dt));
		$year_count = $to_year - $from_year + ($to_month > 2 ? 1 : 0);
		$day_over = 0;
		for($year_idx = 0; $year_idx < $year_count; $year_idx++){
			if((($from_year + $year_idx) % 4) == 0){
				$day_over++;
			}
		}
		if($number_of_day == 0 || $number_of_day == -1){
			$day_over = 0;
		}
		$yearCount = (abs($number_of_day) - $day_over)/365;
		$yy = explode('.',$yearCount);
		$year = $yy[0];

		$result = ($year*12)+(($yearCount-$year)*12);

		
		return $result;
	}
	function selectMysqlDate($str_dt){    
			if(strlen($str_dt)!=10)
				return NULL;
			$str_dt = explode("-", $str_dt);   
			if($GLOBALS['__LANGUAGE']=="th")
				$str_dt[0] = $str_dt[0]+543;

			if(!checkDate($str_dt[1], $str_dt[2], $str_dt[0])){
				//print_r($str_dt);
				return "now()";
			}else{	     
				return implode("/", array_reverse($str_dt));
			}           
			return;		 
	} 
	function sys_cal_date($str_dt){      
			if(strlen($str_dt)!=10)
				return "";
			$str_dt = explode("/", $str_dt);
			if($GLOBALS['__LANGUAGE']=="th")
				$str_dt[2] = $str_dt[2]-543;

			if($str_dt=="now()")
				return $str_dt;
			
			$firstDay = $str_dt[0];
			$firstMonth = $str_dt[1];
			$firstYear = $str_dt[2];

			if(!checkDate($firstMonth,$firstDay,$firstYear)){
				return "now()";
			}else{
				if(strlen($firstMonth)==1) $firstMonth="0".$firstMonth;
				if(strlen($firstDay)==1) $firstDay="0".$firstDay;                      
				return "$firstYear/$firstMonth/$firstDay";	 
			}           
			return ""; 
	}         
	function InsertMysqlDate($str_dt){       

			//echo "__LANGUAGE >>input($str_dt)size[".strlen($str_dt)."]".$GLOBALS['__LANGUAGE']."<BR>";
			
			if(strlen($str_dt)!=10)
				return "NULL";
			
			//echo "__LANGUAGE >>input($str_dt)".$GLOBALS['__LANGUAGE']."<BR>";

			$str_dt = explode("/", $str_dt);
			if($GLOBALS['__LANGUAGE']=="th")
				$str_dt[2] = $str_dt[2]-543;

			if($str_dt=="now()")
				return $str_dt;
			
			$firstDay = $str_dt[0];
			$firstMonth = $str_dt[1];
			$firstYear = $str_dt[2];

			if(!checkDate($firstMonth,$firstDay,$firstYear)){
				return "now()";
			}else{
				if(strlen($firstMonth)==1) $firstMonth="0".$firstMonth;
				if(strlen($firstDay)==1) $firstDay="0".$firstDay;                      
				return "'$firstYear-$firstMonth-$firstDay'";	 
			}            
			return "NULL";
	}   
	function InsertMysqlDateTime($str_dt, $_hour="00", $_minute="00"){       
			
			if(empty($_hour) || $_hour=='')
				$_hour = "00";
			if(empty($_minute) || $_minute=='')
				$_minute = "00";

			if(strlen($str_dt)!=10)
				return "NULL";
			//echo "__LANGUAGE >>".$GLOBALS['__LANGUAGE']."<BR>";

			$str_dt = explode("/", $str_dt);
			if($GLOBALS['__LANGUAGE']=="th")
				$str_dt[2] = $str_dt[2]-543;

			if($str_dt=="now()")
				return $str_dt;
			
			$firstDay = $str_dt[0];
			$firstMonth = $str_dt[1];
			$firstYear = $str_dt[2];

			if(!checkDate($firstMonth,$firstDay,$firstYear)){
				return "now()";
			}else{
				if(strlen($firstMonth)==1) $firstMonth="0".$firstMonth;
				if(strlen($firstDay)==1) $firstDay="0".$firstDay;                      
				return "'$firstYear-$firstMonth-$firstDay $_hour:$_minute:00'";	 
			}           
			return "NULL";
	}      
	function InsertMysqlShotDate($str_dt){       
			
			if(empty($_hour) || $_hour=='')
				$_hour = "00";
			if(empty($_minute) || $_minute=='')
				$_minute = "00";

			if(strlen($str_dt)!=10)
				return "NULL";
			
			$str_dt = explode("/", $str_dt);
			if($GLOBALS['__LANGUAGE']=="th")
				$str_dt[2] = $str_dt[2]-543;

			if($str_dt=="now()")
				return $str_dt;
			
			$firstDay = $str_dt[0];
			$firstMonth = $str_dt[1];
			$firstYear = $str_dt[2];

			if(!checkDate($firstMonth,$firstDay,$firstYear)){
				return "now()";
			}else{
				if(strlen($firstMonth)==1) $firstMonth="0".$firstMonth;
				if(strlen($firstDay)==1) $firstDay="0".$firstDay;                      
				return "'$firstYear-$firstMonth-$firstDay'";	 
			}           
			return "NULL";
	}           
	function mysql2Date($str_dt){                 
  
			$firstDay = substr($str_dt,8,2);
			$firstMonth = substr($str_dt,5,2);      
			$firstYear = substr($str_dt,0,4);            
			
			if($GLOBALS['__LANGUAGE']=="th")
				$firstYear = $firstYear+543;
			
			//echo "input date is [$str_dt]<BR>";               
			//echo "[$firstMonth] [$firstDay] [$firstYear]<BR>";               
                                            
			if(!checkDate($firstMonth,$firstDay,$firstYear)){
				//echo "Invalid Input Date."; 
				//exit;
				return "-"; 
			} 
			$n_dt = JDToGregorian(GregorianToJD($firstMonth,$firstDay,$firstYear));
			$ret_tmp_dt = explode("/",$n_dt);                                      
			if(strlen($ret_tmp_dt[1])==1) $ret_tmp_dt[1]="0".$ret_tmp_dt[1];
			if(strlen($ret_tmp_dt[0])==1) $ret_tmp_dt[0]="0".$ret_tmp_dt[0];              
			//return $ret_tmp_dt[1]."/".$ret_tmp_dt[0]."/".($ret_tmp_dt[2]+543);
			return $ret_tmp_dt[1]."/".$ret_tmp_dt[0]."/".($ret_tmp_dt[2]);
	} 
	function display_MySQL_Date($str_dt){            
			if($str_dt==NULL || $str_dt=="")
				return "";
						
			$firstDay = substr($str_dt,8,2);
			$firstMonth = substr($str_dt,5,2);      
			$firstYear = substr($str_dt,0,4);                               
						                                      
			if(!checkDate($firstMonth,$firstDay,$firstYear)){
				return "";
			} 

			$n_dt = JDToGregorian(GregorianToJD($firstMonth,$firstDay,$firstYear));
			$ret_tmp_dt = explode("/",$n_dt);                                      
			if(strlen($ret_tmp_dt[1])==1) $ret_tmp_dt[1]="0".$ret_tmp_dt[1];
			if(strlen($ret_tmp_dt[0])==1) $ret_tmp_dt[0]="0".$ret_tmp_dt[0];              
			//return $ret_tmp_dt[1]."/".$ret_tmp_dt[0]."/".($ret_tmp_dt[2]+543);
			//echo "__LANGUAGE >>[".strtolower($GLOBALS['__LANGUAGE'])."]<br>";
			if(strtolower($GLOBALS['__LANGUAGE']) == 'th'){
				//echo "1++";
				return $ret_tmp_dt[1]."/".$ret_tmp_dt[0]."/".($ret_tmp_dt[2]+543);
			}else{  
				//echo "2++";
				return $ret_tmp_dt[1]."/".$ret_tmp_dt[0]."/".($ret_tmp_dt[2]);
			}
	}
	/*
	function displayDate($GLOBALS['__LANGUAGE'],$str_dt){            
			if($str_dt==NULL || $str_dt=="")
				return "";
			
			$firstDay = substr($str_dt,8,2);
			$firstMonth = substr($str_dt,5,2);      
			$firstYear = substr($str_dt,0,4);                               
			                                      
			if(!checkDate($firstMonth,$firstDay,$firstYear)){
				echo "[$str_dt] - [$firstMonth][$firstDay][$firstYear] Invalid Input Date."; 
				exit;
			} 
			$n_dt = JDToGregorian(GregorianToJD($firstMonth,$firstDay,$firstYear));
			$ret_tmp_dt = explode("/",$n_dt);                                      
			if(strlen($ret_tmp_dt[1])==1) $ret_tmp_dt[1]="0".$ret_tmp_dt[1];
			if(strlen($ret_tmp_dt[0])==1) $ret_tmp_dt[0]="0".$ret_tmp_dt[0];              
			//return $ret_tmp_dt[1]."/".$ret_tmp_dt[0]."/".($ret_tmp_dt[2]+543);
			if($GLOBALS['__LANGUAGE']=='th')
				return $ret_tmp_dt[1]."/".$ret_tmp_dt[0]."/".($ret_tmp_dt[2]+543);
			else  
				return $ret_tmp_dt[1]."/".$ret_tmp_dt[0]."/".($ret_tmp_dt[2]);
	}
	*/

	function dateNow(){ 
		if(strtolower($GLOBALS['__LANGUAGE'])=="th"){
			$date = date('d/m/Y');
			$_dates = explode("/", $date);
			$_dates[2] = $_dates[2]+543;
			return implode("/", $_dates);
		}else if(strtolower($GLOBALS['__LANGUAGE'])=="en"){
			return date('d/m/Y');
		}
	}
	function dateNowInterval($_intv){ 
		if(strtolower($GLOBALS['__LANGUAGE'])=="th"){
			$date = date('d/m/Y');
			$_dates = explode("/", $date);
			$_dates[2] = $_dates[2]+543+$_intv;
			return implode("/", $_dates);
		}else if(strtolower($GLOBALS['__LANGUAGE'])=="en"){
			return date('d/m/Y');
		}
	}
	

	$thai_day_arr=array("อาทิตย์","จันทร์","อังคาร","พุธ","พฤหัสบดี","ศุกร์","เสาร์");   
	$short_thai_day_arr=array("อา.","จ.","อ.","พ.","พฤ.","ศ.","ส.");   
	$thai_month_arr=array(   
													"0"=>"",   
													"1"=>"มกราคม",   
													"2"=>"กุมภาพันธ์",   
													"3"=>"มีนาคม",   
													"4"=>"เมษายน",   
													"5"=>"พฤษภาคม",   
													"6"=>"มิถุนายน",    
													"7"=>"กรกฎาคม",   
													"8"=>"สิงหาคม",   
													"9"=>"กันยายน",
													"10"=>"ตุลาคม",   
													"11"=>"พฤศจิกายน",   
													"12"=>"ธันวาคม"                     
												);   
	 $short_thai_month_arr = Array("","ม.ค.","ก.พ.","มี.ค.","เม.ย.","พ.ค.","มิ.ย.","ก.ค.","ส.ค.","ก.ย.","ต.ค.","พ.ย.","ธ.ค."); 

	$english_day_arr=array("Sun","Mon","Tue","Wed","Thu","Fri","Sat");   
	$short_english_day_arr=array("S","M","T","W","Th","F","Sa");   
	$english_month_arr=array(   
													"0"=>"",   
													"1"=>"January",   
													"2"=>"February",   
													"3"=>"March",   
													"4"=>"April",   
													"5"=>"May",   
													"6"=>"June",    
													"7"=>"July",   
													"8"=>"August",   
													"9"=>"September",
													"10"=>"October",   
													"11"=>"November",   
													"12"=>"December"                     
												);   
	 $short_english_month_arr = Array("","Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec");

	 $date_unit=array(   
		"year"=>$_lang == "TH" ? "ปี" : "years",   
		"month"=>$_lang == "TH" ? "เดือน" : "months",   
		"day"=>$_lang == "TH" ? "วัน" : "days",   
		"hour"=>$_lang == "TH" ? "ชั่วโมง" : "hours",   
		"min"=>$_lang == "TH" ? "นาที" : "minutes",   
		"sec"=>$_lang == "TH" ? "วินาที" : "seconds"               
	 );   
function month_year($Year_month){
	if($GLOBALS['_lang']=='TH'){
		return thai_month_year($Year_month);
	}else{
		return english_month_year($Year_month);
	}   
} 

function thai_gov_date($time){   
	//echo "thai_gov_date($time)<br>";
	$thai_date_return = "";
	$dates = explode("/", $time);
	global $thai_day_arr,$thai_month_arr;    
	$thai_date_return.=  ($dates[0]*1)."&nbsp;".$thai_month_arr[($dates[1]*1)]."&nbsp;พ.ศ.&nbsp;".($dates[2]*1);   
	//$thai_date_return.= "  ".date("H:i:s", strtotime($time))." น.";   
	return $thai_date_return;
}

function thai_gov_date_no_year_label($time){   
	//echo "thai_gov_date($time)<br>";
	$thai_date_return = "";
	$dates = explode("/", $time);
	global $thai_day_arr,$thai_month_arr;    
	$thai_date_return.=  ($dates[0]*1)."&nbsp;".$thai_month_arr[($dates[1]*1)]."&nbsp;".($dates[2]*1);   
	//$thai_date_return.= "  ".date("H:i:s", strtotime($time))." น.";   
	return $thai_date_return;
}

function pdf_thai_gov_date($time){   
	//echo "pdf_thai_gov_date($time)<br>";
	$thai_date_return = "";
	$dates = explode("/", $time);
	global $thai_day_arr,$thai_month_arr;    
	$thai_date_return.=  "วันที่&nbsp;".($dates[0]*1)."&nbsp;เดือน".$thai_month_arr[($dates[1]*1)]."&nbsp;พ.ศ.&nbsp;".($dates[2]*1);   
	//$thai_date_return.= "  ".date("H:i:s", strtotime($time))." น.";   
	return $thai_date_return;
}

function thai_gov_date_xls($time){   
	//echo "thai_gov_date($time)<br>";
	$thai_date_return = "";
	$dates = explode("/", $time);
	global $thai_day_arr,$thai_month_arr;    
	$thai_date_return.=  ($dates[0]*1)." ".$thai_month_arr[($dates[1]*1)]." พ.ศ. ".($dates[2]*1);   
	//$thai_date_return.= "  ".date("H:i:s", strtotime($time))." น.";   
	return $thai_date_return;
}
function thai_month_year($Year_month){   
	//echo "thai_gov_date($time)<br>";
	$thai_month_year = "";
	$dates = explode("-", $Year_month);
	global $thai_day_arr,$thai_month_arr;    
	$thai_month_year.=  $thai_month_arr[($dates[1]*1)]." ".($dates[0]);   
	//$thai_date_return.= "  ".date("H:i:s", strtotime($time))." น.";   
	return $thai_month_year;
}
function thai_short_date($time){   
	//echo "thai_short_date($time)<br>";
	$thai_date_return = "";
	global $thai_day_arr,$thai_month_arr;    
	$thai_date_return.= date("d/m", strtotime($time))."/".(date("Y", strtotime($time))+543);   
	$thai_date_return.= "  ".date("H:i:s", strtotime($time))." น.";   
	return $thai_date_return;
}
function thai_date($time){   
	global $thai_day_arr,$thai_month_arr;   
	$thai_date_return="วัน".$thai_day_arr[date("w",$time)];   
	$thai_date_return.= "ที่ ".date("j",$time);   
	$thai_date_return.=" เดือน".$thai_month_arr[date("n",$time)];   
	$thai_date_return.= " พ.ศ.".(date("Yํ",$time)+543);   
	$thai_date_return.= "  ".date("H:i",$time)." น.";   
	return $thai_date_return;   
}    
function english_month_year($Year_month){   
	//echo "thai_gov_date($time)<br>";
	$english_month_year = "";
	$dates = explode("-", $Year_month);
	global $english_day_arr,$english_month_arr;    
	$english_month_year.=  $english_month_arr[($dates[1]*1)]." ".($dates[0]);   
	//$english_date_return.= "  ".date("H:i:s", strtotime($time))." น.";   
	return $english_month_year;
}
function DateThai($strDate){
	$strYear = date("Y",strtotime($strDate))+543;
	$strMonth= date("n",strtotime($strDate)); 
	$strDay= date("j",strtotime($strDate)); 
	$strHour= date("H",strtotime($strDate)); 
	$strMinute= date("i",strtotime($strDate));
	$strSeconds= date("s",strtotime($strDate)); 
	$strMonthCut = Array("","ม.ค.","ก.พ.","มี.ค.","เม.ย.","พ.ค.","มิ.ย.","ก.ค.","ส.ค.","ก.ย.","ต.ค.","พ.ย.","ธ.ค."); 
	$strMonthThai=$strMonthCut[$strMonth];  
	return "$strDay $strMonthThai $strYear<BR>เวลา $strHour:$strMinute น.";
}

function ShotDateThai($strDate){
	$strYear = date("Y",strtotime($strDate))+543;
	$strMonth= date("n",strtotime($strDate)); 
	$strDay= date("j",strtotime($strDate)); 
	$strHour= date("H",strtotime($strDate)); 
	$strMinute= date("i",strtotime($strDate)); 
	$strSeconds= date("s",strtotime($strDate)); 
	$strMonthCut = Array("","ม.ค.","ก.พ.","มี.ค.","เม.ย.","พ.ค.","มิ.ย.","ก.ค.","ส.ค.","ก.ย.","ต.ค.","พ.ย.","ธ.ค."); 
	$strMonthThai=$strMonthCut[$strMonth];  
	return "$strDay $strMonthThai $strYear $strHour:$strMinute น.";
}


function thai_date_disp2($dd){
	$Time = strtotime($dd);
	$nDD = date("d/m/", $Time).(date("Y", $Time)+543)." ".date("H:i:s", $Time);
	return $nDD;   
}   

function fixDigits($_num, $_digit){
	return str_pad($_num, $_digit, "0", STR_PAD_LEFT);
}

//$strDate = "2008-08-14 13:42:44";  
//echo "ThaiCreate.Com Time now : ".DateThai($strDate); 

//$eng_date=time(); // แสดงวันที่ปัจจุบัน   
//echo thai_date($eng_date); 
function previous_month($Year_month){   
					$ex = explode("-",$Year_month);
					$y = intval($ex[0]);
					$m = intval($ex[1]);
					
					$previousMonth =$m-1;
					if($previousMonth==0){
					$month_previous=12;
					$year_previous = $y-1;	
					}else{
					$month_previous=$previousMonth;
					$year_previous = $y;	
					}
					$previousMonth = $year_previous."-".str_pad($month_previous, 2, '0', STR_PAD_LEFT);
	return $previousMonth;
}
function next_month($Year_month){   
					$ex = explode("-",$Year_month);
					$y = $ex[0];
					$m = $ex[1];
					
					$nextMonth =$m+1;
					if($nextMonth==13){
					$month_next=1;
					$year_next = $y+1;	
					}else{
					$month_next=$nextMonth;
					$year_next = $y;	
					}
					$nextMonth = $year_next."-".str_pad($month_next, 2, '0', STR_PAD_LEFT);
	return $nextMonth;
}
		function hour2sec($hour){
			$seperate = explode(":",$hour);
			$h = $seperate[0]*60*60;
			$m = $seperate[1]*60;
			$s = $seperate[2];
			$secound = $h + $m + $s;
			return $secound;	///return in second format
		}
		
		function hour2min($hour){
			$seperate = explode(":",$hour);
			$h = $seperate[0]*60*60;
			$m = $seperate[1]*60;
			$s = $seperate[2];
			$secound = $h + $m + $s;
			$minute = floor($secound/60);
			return $minute;	///return in second format
		}
		function hour2hour($hour,$ceilfloor=''){
			$seperate = explode(":",$hour);
			$h = $seperate[0]*60*60;
			$m = $seperate[1]*60;
			$s = $seperate[2];
			$secound = $h + $m + $s;
			if($ceilfloor=='ceil'){
				$time = ceil($secound/(60*60));
			}else if($ceilfloor=='floor'){
				$time = floor($secound/(60*60));
			}else if(strpos($ceilfloor,'round')!==false){
				$ex = explode("-",$ceilfloor);
				if(sizeof($ex) > 1){
					$time = round($secound/(60*60), $ex[1]);
				}else{
					$time = round($secound/(60*60), 4);
				}
				// echo round($secound/(60*60), empty($ex[1]) ? 2 : $ex[1])."<br>";
				// $time = round($secound/(60*60),$ex[1] ? $ex[1] : 2);
			}else{
				$time = round($secound/(60*60),4);
			}
			return $time;	///return in hour number
		}
		function hour2hourmin($hour){
			$seperate = explode(":",$hour);
			return $seperate[0].":".$seperate[1];	///return in second format
		}
		function number2hour($number,$round='min'){
			if(empty($number))	return;
			$number = number_format(round($number,2), 2, '.', '');
			$ex = explode(".",$number);
			$hour = $ex[0];
	
			if(strlen($ex[1])==2){
				// if(strpos($ex[1],"0") == 0){
					$ex[1] = $ex[1]/10;
				// }
			}else{
				$ex[1] = $ex[1]*10;
			}
			
			$mm = ($ex[1]*60)/10;
			$min = floor($mm);
			$ex = explode(".",$mm);
			$ss = ($ex[1]*60)/10;
			$sec = floor($ss);
			if($round=='min') $sec = 0;
			$time = str_pad($hour, 2, '0', STR_PAD_LEFT).":".str_pad($min, 2, '0', STR_PAD_LEFT).":".str_pad($sec, 2, '0', STR_PAD_LEFT);
			return $time;	///return in hour format
		}
		function number2hourmin($number){
			if(empty($number))	return;
			$number = number_format(round($number,2), 2, '.', '');
			$ex = explode(".",$number);
			$hour = $ex[0];

			if(strlen($ex[1])==2){
				if(strpos($ex[1],"0") == 0){
					$ex[1] = $ex[1]/10;
				}
			}else{
				$ex[1] = $ex[1]*10;
			}
			$mm = ($ex[1]*60)/100;
			$min = round($mm);
			$time = str_pad($hour, 2, '0', STR_PAD_LEFT).":".str_pad($min, 2, '0', STR_PAD_LEFT);
			return $time;	///return in hour format
		}
		function sec2hour($sec){
		$hh =  explode(".",(($sec)/(60*60)));
		$h = $hh[0];
		if(strlen($hh[1])==1){
			$hhh=$hh[1]*10;
		}else{
			$hhh=$hh[1];
		}
		$mm =  explode(".",(floatval("0.".$hhh)*60));
		$m = $mm[0];
		if(strlen($mm[1])==1){
			$mmm=$mm[1]*10;
		}else{
			$mmm=$mm[1];
		}
		$s =  round((floatval("0.".$mmm)*60),0);
		if($s==60){$m += 1;$s=0;}
		if($m==60){$h =+ 1;$m=0;}
		
		$worktime = str_pad($h, 2, '0', STR_PAD_LEFT).":".str_pad($m, 2, '0', STR_PAD_LEFT).":".str_pad($s, 2, '0', STR_PAD_LEFT);
		return $worktime;////return in string format
		}
		function sec2hour2($sec){
			$sec = 5520;
			$hh =  explode(".",(($sec)/(60*60)));
			$h = $hh[0];
			if(strlen($hh[1])==1){
				$hhh=$hh[1]*10;
			}else{
				$hhh=$hh[1];
			}
			$hhh = 54;

			$mm =  explode(".",(floatval("0.".$hhh)*60));
			$m = $mm[0];
			if(strlen($mm[1])==1){
				$mmm=$mm[1]*10;
			}else{
				$mmm=$mm[1];
			}
			$s =  round((floatval("0.".$mmm)*60),0);
			if($s==60){$m += 1;$s=0;}
			if($m==60){$h =+ 1;$m=0;}
			
			$worktime = str_pad($h, 2, '0', STR_PAD_LEFT).":".str_pad($m, 2, '0', STR_PAD_LEFT).":".str_pad($s, 2, '0', STR_PAD_LEFT);
			return $worktime;////return in string format
			}
		
	
	function thai_2Eng($_date){
		if(strlen($_date)!=10)
			return "";
		$_date = explode("/", $_date);
		$_date[2] = $_date[2]-543;
		return implode("/", $_date);
	}
	function thaifulldate_2mySQL($_datetime){
		$ex =explode (" ",$_datetime);
		$_date = $ex[0];
		$_time = $ex[2];
		
		$exDate = explode("/",$_date);
		$d = str_pad($exDate[0], 2, '0', STR_PAD_LEFT);
		$m = str_pad($exDate[1], 2, '0', STR_PAD_LEFT);
		$y = $exDate[2]-543;
		
		
		$mySQL_datetime = $y."-".$m."-".$d." ".$_time;
		return $mySQL_datetime;
	}
	function thaiDate2mySQL($_datetime){
		$ex =explode (" ",$_datetime);
		$_date = $ex[0];
		$_time = $ex[2];
		
		$exDate = explode("/",$_date);
		$d = str_pad($exDate[0], 2, '0', STR_PAD_LEFT);
		$m = str_pad($exDate[1], 2, '0', STR_PAD_LEFT);
		$y = $exDate[2]-543;
		
		$mySQL_datetime = $y."-".$m."-".$d." ".$_time;
		return $mySQL_datetime;
	}
	function thai_2Eng2($_date){
		if(strlen($_date)!=10)
			return "";
		$_date = explode("/", $_date);
		$_date[2] = $_date[2]-543;
		return $_date[2]."-".$_date[1]."-".$_date[0];
	}
	function mySQL_2Thai($_date){
		if(strlen($_date)!=10)
			return "";
		$_date = explode("-", $_date);
		$_date[0] = $_date[0]+ 543;
		return implode("/", array_reverse($_date));
	}
	
	function mySQL_2ThaiTime($_datetime){
		$ex =explode (" ",$_datetime);
		$_date = $ex[0];
		$_time = $ex[1];
		
		$exTime = explode(":",$_time);
		$h = str_pad($exTime[0], 2, '0', STR_PAD_LEFT);
		$m = str_pad($exTime[1], 2, '0', STR_PAD_LEFT);
		
		
		$mySQL_thaitime = $h.".".$m." น.";
		return $mySQL_thaitime;
	}
	
	function mySQL_2mySQLTime($_datetime){
		$ex =explode (" ",$_datetime);
		$_date = $ex[0];
		$_time = $ex[1];		

		return $_time;
	}
function anyDate($format,$value,$lang="EN")  {
		global $thai_day_arr,$thai_month_arr,$short_thai_month_arr,$short_thai_day_arr,$english_month_arr,$short_english_month_arr;   
   		if (!$value||$value=='0000-00-00'||$value=='0000-00-00 00:00:00') {
        	return "";
   		}	

			$ex = explode(" ",$value);

				$exDate = explode("-",$ex[0]);
				if(count($exDate)==1){
					$exDate = explode("/",$ex[0]);
					if(count($exDate)==3){
						if(strlen($exDate[2])==4){
							$define = intval($exDate[2]);
							if($define>2300){
								$year = $exDate[2]-543;
							}else{
								$year = $exDate[2];
							}
							$month = str_pad($exDate[1], 2, '0', STR_PAD_LEFT);
							$date = str_pad($exDate[0], 2, '0', STR_PAD_LEFT);
						}else{
							$define = intval($exDate[0]);
							if($define>2300){
								$year = $exDate[0]-543;
							}else{
								$year = $exDate[0];
							}
							$month = str_pad($exDate[1], 2, '0', STR_PAD_LEFT);
							$date = str_pad($exDate[2], 2, '0', STR_PAD_LEFT);
						}
					}
				}else if(count($exDate)==3){
					if(strlen($exDate[2])==4){
							$define = intval($exDate[2]);
							if($define>2300){
								$year = $exDate[2]-543;
							}else{
								$year = $exDate[2];
							}
							$month = str_pad($exDate[1], 2, '0', STR_PAD_LEFT);
							$date = str_pad($exDate[0], 2, '0', STR_PAD_LEFT);
					}else{
							$define = intval($exDate[0]);
							if($define>2300){
								$year = $exDate[0]-543;
							}else{
								$year = $exDate[0];
							}
							$month = str_pad($exDate[1], 2, '0', STR_PAD_LEFT);
							$date = str_pad($exDate[2], 2, '0', STR_PAD_LEFT);
					}
				}

			if(count($ex)==1){	
				$time = $ex[0];
			}else if(count($ex)==2){
				$time =  $ex[1];
			}else if(count($ex)==3){
				if($ex[2]=="น."){
					$time =  $ex[1];
				}else{
					$time =  $ex[2];
				}
			}else if(count($ex)==4){
				if($ex[3]=="น."){
					$time =  $ex[2];
				}else{
					$time =  $ex[3];
				}
			}
			
			$time = str_replace(".",":",$time);
			if(strlen($time)==7||strlen($time)==8){
				$time= date('H:i:s',strtotime($time));
			}else if(strlen($time)==4||strlen($time)==5){
				$time= date('H:i:s',strtotime($time.':00'));
			}else if(strlen($time)>8){
				$time= date('H:i:s',strtotime(substr($time,0,8)));
			}else{
				$time = "00:00:00";	
			}

		$_datetime = $year."-".$month."-".$date." ".$time;	
		//echo $format." = ".$_datetime."<BR>";

		if($lang=="TH"||$lang=="th"){
			$year = $year+543;
			if($format=="Y-m-d H:i:s"){
				return $year."-".$month."-".$date." ".$time;	
			}else if($format=="Y-m-d H:i"){
				return $year."-".$month."-".$date." ".date('H:i',strtotime($time));	
			}else if($format=="Y-m-d"){
				return $year."-".$month."-".$date;	
			}else if($format=="Y-m-t"){
				return $year."-".$month."-".date("t",strtotime(($year+543)."-".$month."-".$date));	
			}else if($format=="Y-m"){
				return $year."-".$month;	
			}else if($format=="m"){
				return $month;	
			}else if($format=="mm"){
				return $short_thai_month_arr[intval($month)*1];	
			}else if($format=="mmmm"){
				return $thai_month_arr[intval($month)*1];	
			}else if($format=="Y"){
				return $year;	
			}else if($format=="d"){
				return $date;
			}else if($format=="dd"){
				return $short_thai_day_arr[date("w",strtotime($year."-".$month."-".$date))];	
			}else if($format=="dddd"){
				return $thai_day_arr[date("w",strtotime($year."-".$month."-".$date))];	
			}else if($format=="d mm Y H.i"){
				return $date." ".$short_thai_month_arr[intval($month)*1]." ".$year." ".date("H.i",strtotime($time));	
			}else if($format=="d mmmm Y H.i"){
				return $date." ".$thai_month_arr[intval($month)*1]." ".$year." ".date("H.i",strtotime($time));	
			}else if($format=="d mm Y H:i"){
				return $date." ".$short_thai_month_arr[intval($month)*1]." ".$year." ".date("H:i",strtotime($time));	
			}else if($format=="d mmmm Y H:i"){
				return $date." ".$thai_month_arr[intval($month)*1]." ".$year." ".date("H:i",strtotime($time));	
			}else if($format=="d mm Y H:i:s"){
				return $date." ".$short_thai_month_arr[intval($month)*1]." ".$year." ".date("H:i:s",strtotime($time));	
			}else if($format=="d mmmm Y H:i:s"){
				return $date." ".$thai_month_arr[intval($month)*1]." ".$year." ".date("H:i:s",strtotime($time));	
			}else if($format=="d mm Y"){
				return $date." ".$short_thai_month_arr[intval($month)*1]." ".$year;	
			}else if($format=="d mmmm Y"){
				return $date." ".$thai_month_arr[intval($month)*1]." ".$year;	
			}else if($format=="mmmm Y"){
				return $thai_month_arr[intval($month)*1]." ".$year;	
			}else if($format=="d/m/Y H:i:s"){
				return $date."/".$month."/".$year." ".$time;	
			}else if($format=="d/m/Y H:i"){
				return $date."/".$month."/".$year." ".date("H:i",strtotime($time));	
			}else if($format=="d/m/Y"){
				return $date."/".$month."/".$year;
			}else if($format=="dmY"){
				return $date.$month.$year;
			}else if($format=="dm"){
				return $date.$month;
			}else if($format=="H:i:s"){
				return $time;
			}else if($format=="H:i"){
				return date("H:i",strtotime($time));	
			}else if($format=="H.i"){
				return date("H.i",strtotime($time));	
			}else if($format=="H"){
				return date("H",strtotime($time));	
			}else if($format=="i"){
				return date("i",strtotime($time));	
			}else if($format=="s"){
				return date("s",strtotime($time));	
			}
		}else{
			$year = $year;
			if($format=="Y-m-d H:i:s"){
				return $year."-".$month."-".$date." ".$time;	
			}else if($format=="Y-m-d H:i"){
				return $year."-".$month."-".$date." ".date('H:i',strtotime($time));	
			}else if($format=="Y-m-d"){
				return $year."-".$month."-".$date;	
			}else if($format=="Y-m-t"){
				return $year."-".$month."-".date("t",strtotime($year."-".$month."-".$date));	
			}else if($format=="Y-m"){
				return $year."-".$month;	
			}else if($format=="m"){
				return $month;	
			}else if($format=="mm"){
				return $short_english_month_arr[intval($month)*1];	
			}else if($format=="mmmm"){
				return $english_month_arr[intval($month)*1];	
			}else if($format=="Y"){
				return $year;	
			}else if($format=="d"){
				return $date;
			}else if($format=="dd"){
				return date("D",strtotime($year."-".$month."-".$date));	
			}else if($format=="dddd"){
				return date("l",strtotime($year."-".$month."-".$date));	
			}else if($format=="d mm Y H.i"){
				return $date." ".date("M",strtotime($year."-".$month."-".$date))." ".$year." ".date("H.i",strtotime($time));	
			}else if($format=="d mmmm Y H.i"){
				return $date." ".date("F",strtotime($year."-".$month."-".$date))." ".$year." ".date("H.i",strtotime($time));	
			}else if($format=="d mm Y H:i"){
				return $date." ".date("M",strtotime($year."-".$month."-".$date))." ".$year." ".date("H:i",strtotime($time));	
			}else if($format=="d mmmm Y H:i"){
				return $date." ".date("F",strtotime($year."-".$month."-".$date))." ".$year." ".date("H:i",strtotime($time));	
			}else if($format=="d mm Y H:i:s"){
				return $date." ".date("M",strtotime($year."-".$month."-".$date))." ".$year." ".date("H:i:s",strtotime($time));	
			}else if($format=="d mmmm Y H:i:s"){
				return $date." ".date("F",strtotime($year."-".$month."-".$date))." ".$year." ".date("H:i:s",strtotime($time));	
			}else if($format=="d mm Y"){
				return $date." ".date("M",strtotime($year."-".$month."-".$date))." ".$year;	
			}else if($format=="d mmmm Y"){
				return $date." ".date("F",strtotime($year."-".$month."-".$date))." ".$year;	
			}else if($format=="mmmm Y"){
				return $thai_month_arr[intval($month)*1]." ".$year;	
			}else if($format=="d/m/Y H:i:s"){
				return $date."/".$month."/".$year." ".$time;	
			}else if($format=="d/m/Y H:i"){
				return $date."/".$month."/".$year." ".date("H:i",strtotime($time));	
			}else if($format=="d/m/Y"){
				return $date."/".$month."/".$year;
			}else if($format=="dmY"){
				return $date.$month.$year;
			}else if($format=="dm"){
				return $date.$month;
			}else if($format=="H:i:s"){
				return $time;
			}else if($format=="H:i"){
				return date("H:i",strtotime($time));	
			}else if($format=="H.i"){
				return date("H.i",strtotime($time));	
			}else if($format=="H"){
				return date("H",strtotime($time));	
			}else if($format=="i"){
				return date("i",strtotime($time));	
			}else if($format=="s"){
				return date("s",strtotime($time));	
			}
		}
}

function anyDateForEnglish($format,$value,$lang="EN")  {
	global $thai_day_arr,$thai_month_arr,$short_thai_month_arr,$short_thai_day_arr,$english_month_arr;   
	   if (!$value||$value=='0000-00-00'||$value=='0000-00-00 00:00:00') {
		return "";
	   }	

		$ex = explode(" ",$value);

			$exDate = explode("-",$ex[0]);
			if(count($exDate)==1){
				$exDate = explode("/",$ex[0]);
				if(count($exDate)==3){
					if(strlen($exDate[2])==4){
						$define = intval($exDate[2]);
						if($define>2300){
							$year = $exDate[2]-543;
						}else{
							$year = $exDate[2];
						}
						$month = str_pad($exDate[1], 2, '0', STR_PAD_LEFT);
						$date = str_pad($exDate[0], 2, '0', STR_PAD_LEFT);
					}else{
						$define = intval($exDate[0]);
						if($define>2300){
							$year = $exDate[0]-543;
						}else{
							$year = $exDate[0];
						}
						$month = str_pad($exDate[1], 2, '0', STR_PAD_LEFT);
						$date = str_pad($exDate[2], 2, '0', STR_PAD_LEFT);
					}
				}
			}else if(count($exDate)==3){
				if(strlen($exDate[2])==4){
						$define = intval($exDate[2]);
						if($define>2300){
							$year = $exDate[2]-543;
						}else{
							$year = $exDate[2];
						}
						$month = str_pad($exDate[1], 2, '0', STR_PAD_LEFT);
						$date = str_pad($exDate[0], 2, '0', STR_PAD_LEFT);
				}else{
						$define = intval($exDate[0]);
						if($define>2300){
							$year = $exDate[0]-543;
						}else{
							$year = $exDate[0];
						}
						$month = str_pad($exDate[1], 2, '0', STR_PAD_LEFT);
						$date = str_pad($exDate[2], 2, '0', STR_PAD_LEFT);
				}
			}

		if(count($ex)==1){	
			$time = $ex[0];
		}else if(count($ex)==2){
			$time =  $ex[1];
		}else if(count($ex)==3){
			if($ex[2]=="น."){
				$time =  $ex[1];
			}else{
				$time =  $ex[2];
			}
		}else if(count($ex)==4){
			if($ex[3]=="น."){
				$time =  $ex[2];
			}else{
				$time =  $ex[3];
			}
		}
		
		$time = str_replace(".",":",$time);
		if(strlen($time)==7||strlen($time)==8){
			$time= date('H:i:s',strtotime($time));
		}else if(strlen($time)==4||strlen($time)==5){
			$time= date('H:i:s',strtotime($time.':00'));
		}else if(strlen($time)>8){
			$time= date('H:i:s',strtotime(substr($time,0,8)));
		}else{
			$time = "00:00:00";	
		}

	$_datetime = $year."-".$month."-".$date." ".$time;	
	//echo $format." = ".$_datetime."<BR>";

	if($lang=="EN"||$lang=="en"){
		if($format=="Y-m-d H:i:s"){
			return $year."-".$month."-".$date." ".$time;	
		}else if($format=="mmmm"){
			return $english_month_arr[intval($month)*1];
		}	
	}
}

function weekOfMonth($date) {
    //Get the first day of the month.
    $firstOfMonth = strtotime(date("Y-m-01", strtotime($date)));
    //Apply above formula.
	//return date("W", strtotime($date));
	
	if(date("D", $firstOfMonth)=='Mon'){
		$week = intval(date("W", strtotime($date))) - intval(date("W", $firstOfMonth)) + 1;
	}else{
		$weekThisMonth = intval(date("W", strtotime($date))) - intval(date("W", $firstOfMonth)) + 1;
		if($weekThisMonth==1&&date("D", strtotime(date("Y-m-01", strtotime($date." -1 month"))))=='Mon'){
			$week = intval(date("W",strtotime(date("Y-m-t", strtotime($date." -1 month"))))) - intval(date("W", strtotime(date("Y-m-01", strtotime($date." -1 month")))))+1;
		}else{
			if($weekThisMonth==1&&date("D", strtotime(date("Y-m-01", strtotime($date." -1 month"))))!='Mon'){
				$week = intval(date("W",strtotime(date("Y-m-t", strtotime($date." -1 month")))))-intval(date("W",strtotime(date("Y-m-t", strtotime($date." -2 month")))));
				if($week<0){
					$week = 52 + intval(date("W", strtotime($date))) - intval(date("W", strtotime(date("Y-m-01", strtotime($date." -1 month")))));	
				}
			}else{
				$week = intval(date("W", strtotime($date))) - intval(date("W", $firstOfMonth));
				if($week<0){
					$week = 52 + intval(date("W", strtotime($date))) - intval(date("W", $firstOfMonth));	
				}
			}
		}
	}
    return $week;
}

// Convert a string to an array with multibyte string
function getMBStrSplit($string, $split_length = 1){
	mb_internal_encoding('UTF-8');
	mb_regex_encoding('UTF-8'); 
	
	$split_length = ($split_length <= 0) ? 1 : $split_length;
	$mb_strlen = mb_strlen($string, 'utf-8');
	$array = array();
	$i = 0; 
	
	while($i < $mb_strlen)
	{
		$array[] = mb_substr($string, $i, $split_length);
		$i = $i+$split_length;
	}
	
	return $array;
}

// Get string length for Character Thai
function getStrLenTH($string)
{
	$array = getMBStrSplit($string);
	$count = 0;
	
	foreach($array as $value)
	{
		$ascii = ord(iconv("UTF-8", "TIS-620", $value ));
		
		if( !( $ascii == 209 ||  ($ascii >= 212 && $ascii <= 218 ) || ($ascii >= 231 && $ascii <= 238 )) )
		{
			$count += 1;
		}
	}
	return $count;
}

function getDuration($startDate, $endDate, $_lang='EN') {
	$date_unit = getDateUnit($_lang);

	// สร้าง DateTime จากวันที่เริ่มต้นและสิ้นสุด
    $start = new DateTime($startDate);
    $end = new DateTime($endDate);

    // หาความต่างระหว่างสองวันที่
    $interval = $start->diff($end);

    // สร้างผลลัพธ์ที่ต้องการ
    $result = $interval->y . ' '.$date_unit['year'].' ' . $interval->m . ' '.$date_unit['month'].' ' . $interval->d . ' '.$date_unit['day'].'';
    
    return $result;

}

function dateCountLang($number_of_day, $_lang){
	global $date_unit, $_lang;
	$date_unit = getDateUnit($_lang);
	$yearCount = $number_of_day/365;
	$yy = explode('.',$yearCount);
	$year = $yy[0];
	
	$monthCount = ($yearCount-$year)*12;
	$mm = explode('.',$monthCount);
	$month = $mm[0];
	
	$day = ceil(($monthCount-$month)*30);

	$result = '';
	if($year>0){
		$result .= $year." {$date_unit['year']} ";	
	}
	if($month>0){
		$result .= $month." {$date_unit['month']} ";	
	}
	if($day>0){
		$result .= $day." {$date_unit['day']}";	
	}
	return $result;
}

function getDateUnit($lang) {
	return array(
        "year" => $lang == "TH" ? "ปี" : "years",   
        "month" => $lang == "TH" ? "เดือน" : "months",   
        "day" => $lang == "TH" ? "วัน" : "days",   
        "hour" => $lang == "TH" ? "ชั่วโมง" : "hours",   
        "min" => $lang == "TH" ? "นาที" : "minutes",   
        "sec" => $lang == "TH" ? "วินาที" : "seconds"
    );
}

// ฟังก์ชั่นคำนวณวันที่สิ้นสุดจากวันที่เริ่มต้นและจำนวนวัน
function addDaysToDate($startDate, $days) {
    // ใช้ strtotime เพื่อเพิ่มหรือลดจำนวนวัน
    $endDate = date('Y-m-d', strtotime("$days days", strtotime($startDate)));
    return $endDate;
}

// ถ้า error จะคืนค่าปีปัจจุบัน
function getBuddhistYear (string $year) {
	 $buddhistYear = 543;
    // ถ้าไม่มีค่าปีที่ส่งมา หรือค่าไม่ถูกต้อง ให้ใช้ปีปัจจุบัน
    if (empty($year) || $year === 'null' || $year === 'undefined') {
        return (int)date('Y') + $buddhistYear;
    }
    $yearInt = (int)$year;
    return $yearInt + $buddhistYear;
}

function getThaiMonthLabel(string $month) {
    $thaiMonths = [
		'01' => 'มกราคม',
		'02' => 'กุมภาพันธ์',
		'03' => 'มีนาคม',
		'04' => 'เมษายน',
		'05' => 'พฤษภาคม',
		'06' => 'มิถุนายน',
		'07' => 'กรกฎาคม',
		'08' => 'สิงหาคม',
		'09' => 'กันยายน',
		'10' => 'ตุลาคม',
		'11' => 'พฤศจิกายน',
		'12' => 'ธันวาคม'
    ];
    return $thaiMonths[$month];
}

function hour2number($time) {
    if ($time === null || !preg_match('/^\d{2}:\d{2}:\d{2}$/', $time)) {
        return null;
    }

    list($h, $m, $s) = explode(':', $time);

    return $h + ($m / 60) + ($s / 3600);
}

?>