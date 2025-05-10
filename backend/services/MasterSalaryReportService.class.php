<? 
	    include("AbstractMasterSalaryReport.class.php");

	class MasterSalaryReportService extends AbstractMasterSalaryReport{

		function createNewMasterReport($year_month){  
				if($year_month==''){
					throw  new Exception("createNewMasterReport Parameter Required!!");
				}   

				$month = $this->getMasterReportByMonth($year_month);
				if($month['master_salary_report_id']==''){
					$config = $GLOBALS['configService']->getSpecConfig('round_month');
					$_start = $config['config_key_1'];
					$_end = $config['config_key_2'];
					if($_start!=''&&$_end!=''){
						if(strtoupper($_end)=="EOM"){
							$salary_report_start_dt = $year_month."-01 00:00:00";
							$salary_report_end_dt = date("Y-m-t", strtotime($year_month."-01"))." 23:59:59";
							$day_in_month = dateDiff($salary_report_start_dt,$salary_report_end_dt)+1;
						}else{
							$salary_report_start_dt = date('Y-m-d',strtotime($year_month."-".$_start." -1 month"))." 00:00:00";
							$salary_report_end_dt = $year_month."-".$_end." 23:59:59";
							$day_in_month = dateDiff($salary_report_start_dt,$salary_report_end_dt)+1;
						}
						$data= array();
						$data['master_salary_month'] = $year_month;
						$data['salary_report_start_dt'] = $salary_report_start_dt;
						$data['salary_report_end_dt'] = $salary_report_end_dt;
						$data['day_in_month'] = $day_in_month;
						//print_r($data);
						//echo "<HR>";
						$result = $this->trx_servicecreate($data);
						$month = $this->getMasterReportByMonth($year_month);
						
						return $month;
					}

					
				}else{
					return $month;	
				}

				
		}
				
		function getListMonth($_status,$_limit,$_order){     
			$_sql="	SELECT _report.master_salary_report_id,
			_report.master_salary_month,
			_report.salary_split_flag,
			_report.read_only_flag,
			_report.salary_report_start_dt,
			_report.salary_report_end_dt 
			FROM {$GLOBALS['instanceServer']['instance_server_dbn']}.payroll_master_salary_report _report 
			WHERE _report.server_id='{$_REQUEST['server_id']}' 
			AND _report.instance_server_id = '{$_REQUEST['instance_server_id']}' 
			AND _report.instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}' ";
			if($_status!=''){
				$_sql .= "AND _report.read_only_flag='{$_status}' ";
			}
			$_sql .= "ORDER BY _report.master_salary_month {$_order} ";
			if($_limit!=''){
				$_sql .= "LIMIT {$_limit}";
			}
			//echo "$_sql";
			$lists =  $this->_sqllists($_sql);     
			$retLists = array();
			for($i=0;$i<sizeof($lists);$i++){
				if($GLOBALS['__LANGUAGE']=="th")
					$lists[$i]['month_name'] = month_year($lists[$i]['master_salary_month']);
				else if($GLOBALS['__LANGUAGE']=="en")
					$lists[$i]['month_name'] = date('F Y',strtotime($lists[$i]['master_salary_month']));
				
				$retLists[$i] = $lists[$i];
			}
			return $retLists;
		}
		function getListMonthForSlip($_status,$_limit,$_order,$_year){     
			$_sql="	SELECT _report.master_salary_report_id,
			_report.master_salary_month,
			_report.salary_split_flag,
			_report.read_only_flag,
			_report.salary_report_start_dt,
			_report.salary_report_end_dt 
			FROM {$GLOBALS['instanceServer']['instance_server_dbn']}.payroll_master_salary_report _report 
			INNER JOIN {$GLOBALS['instanceServer']['instance_server_dbn']}.payroll_master_salary_slip _slip 
			WHERE _report.server_id='{$_REQUEST['server_id']}' 
			AND _report.instance_server_id = '{$_REQUEST['instance_server_id']}' 
			AND _report.instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}'
			AND  SUBSTRING(_report.master_salary_month,1,4)= '".$_year."'  ";
			if($_status!=''){
				$_sql .= "AND _report.read_only_flag='{$_status}' ";
			}
			$_sql .= "ORDER BY _report.master_salary_month {$_order} ";
			if($_limit!=''){
				$_sql .= "LIMIT {$_limit}";
			}
			//echo "$_sql";
			$lists =  $this->_sqllists($_sql);   
			$retLists = array();
			for($i=0;$i<sizeof($lists);$i++){
				if($GLOBALS['__LANGUAGE']=="th")
					$lists[$i]['month_name'] = month_year($lists[$i]['master_salary_month']);
				else if($GLOBALS['__LANGUAGE']=="en")
					$lists[$i]['month_name'] = date('F Y',strtotime($lists[$i]['master_salary_month']));
				
				$retLists[$i] = $lists[$i];
			}
			return $retLists;
		}
		
		
		function countEmployeeByReportID($_master_salary_report_id){     
			$_sql="	SELECT  _slip.master_salary_report_id, COUNT(*)  AS cnt_emp
			FROM {$GLOBALS['instanceServer']['instance_server_dbn']}.payroll_master_salary_slip _slip 
			WHERE _slip.master_salary_report_id='".$_master_salary_report_id."' 
			AND _slip.server_id='{$_REQUEST['server_id']}' 
			AND _slip.instance_server_id = '{$_REQUEST['instance_server_id']}' 
			AND _slip.instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}'  
			GROUP BY _slip.master_salary_report_id";
			//echo "$_sql";
			$lists =  $this->_sqlget($_sql);
			return $lists['cnt_emp'];
		}

		function getMasterReportByYear($_year){     
			$_sql="	SELECT * FROM {$GLOBALS['instanceServer']['instance_server_dbn']}.payroll_master_salary_report _report    
			WHERE SUBSTRING(_report.master_salary_month,1,4)= '".$_year."' 
			AND _report.server_id='{$_REQUEST['server_id']}' 
			AND _report.instance_server_id = '{$_REQUEST['instance_server_id']}' 
			AND _report.instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}'  
			ORDER BY _report.master_salary_month ASC";
			//echo "$_sql";
			$lists =  $this->_sqllists($_sql);
			return $lists;
		}

		function getMasterReportByMonthLists($_month_start,$_month_end){     
			$_sql="	SELECT * FROM {$GLOBALS['instanceServer']['instance_server_dbn']}.payroll_master_salary_report _report    
			WHERE _report.master_salary_month BETWEEN '".$_month_start."' AND '".$_month_end."'  
			AND _report.server_id='{$_REQUEST['server_id']}' 
			AND _report.instance_server_id = '{$_REQUEST['instance_server_id']}' 
			AND _report.instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}'  
			ORDER BY _report.master_salary_month ASC";
			//echo "$_sql";
			$lists =  $this->_sqllists($_sql);
			return $lists;
		}
		
		function getMasterReportByMonth($_month){     
			$_sql="	SELECT * FROM {$GLOBALS['instanceServer']['instance_server_dbn']}.payroll_master_salary_report _report  
			WHERE _report.master_salary_month= '{$_month}' 
			AND _report.server_id='{$_REQUEST['server_id']}' 
			AND _report.instance_server_id = '{$_REQUEST['instance_server_id']}' 
			AND _report.instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}' ";
			// echo "$_sql";
			$lists =  $this->_sqlget($_sql);
			return $lists;
		}

		/* function getListMasterReportByMonthFilter($_month, $_PARAM){     
			$_sql="	SELECT * FROM {$GLOBALS['instanceServer']['instance_server_dbn']}.payroll_master_salary_report _report  
			WHERE _report.master_salary_month= '{$_month}' 
			AND _report.server_id='{$_REQUEST['server_id']}' 
			AND _report.instance_server_id = '{$_REQUEST['instance_server_id']}' ";
			if(!empty($_PARAM['instance_server_channel_id'])){
				$_sql .= " AND _report.instance_server_channel_id = '{$_PARAM['instance_server_channel_id']}' ";
			}else if(is_array($_PARAM['channel_list']) && sizeof($_PARAM['channel_list']) > 0){
				$_sql .= " AND _report.instance_server_channel_id IN ('".implode("','", array_map('base64_decode', array_column($_PARAM['channel_list'], 'id')))."') ";
			}
			if(!empty($_PARAM['CURRENT_PAGE']) && !empty($_PARAM['RECORDS_PER_PAGE'])){
				$_OFFSET = ($_PARAM['CURRENT_PAGE']-1)*$_PARAM['RECORDS_PER_PAGE'];
				$_COUNT = $_PARAM['RECORDS_PER_PAGE'];
				$_sql .= " LIMIT {$_OFFSET}, {$_COUNT} ";
			}

			$lists =  $this->_sqllists($_sql);
			return $lists;
		} */

		function getMasterReportByMonthDomain($_month){     
			$_sql="	SELECT _report.*
			, _comp.company_id
			FROM {$GLOBALS['instanceServer']['instance_server_dbn']}.payroll_master_salary_report _report 
			LEFT JOIN hms_api.comp_company _comp ON (_report.instance_server_channel_id = _comp.instance_server_channel_id)
			WHERE _report.master_salary_month= '{$_month}' 
			AND _report.server_id='{$_REQUEST['server_id']}' 
			AND _report.instance_server_id = '{$_REQUEST['instance_server_id']}' ";
			if(!empty($_REQUEST['instance_server_channel_id'])){
				$_sql .= " AND _report.instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}' ";
			}
			$lists =  $this->_sqllists($_sql);
			return $lists;
		}

		function getMasterReportByMonthByCCS($_month){     
			$_sql="	SELECT * FROM {$GLOBALS['instanceServer']['instance_server_dbn']}.payroll_master_salary_report _report   
			WHERE _report.master_salary_month= '{$_month}' 
			AND _report.server_id='{$_REQUEST['server_id']}' 
			AND _report.instance_server_id = '{$_REQUEST['instance_server_id']}' ";
			
			// echo "$_sql";
			$lists =  $this->_sqllists($_sql);
			return $lists;
		}
		
		function getMasterReportByFutureMonth($_month){     
			$_sql = " SELECT * FROM {$GLOBALS['instanceServer']['instance_server_dbn']}.payroll_master_salary_report _report   
			WHERE _report.master_salary_month>='{$_month}' 
			AND _report.server_id = '{$_REQUEST['server_id']}' 
			AND _report.instance_server_id = '{$_REQUEST['instance_server_id']}'
			AND _report.instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}'
			ORDER BY _report.master_salary_month  ";
			//echo "$_sql";
			$lists =  $this->_sqllists($_sql);
			return $lists;
		}

		function getMasterReportByDate($_date){   
			// echo $_date."<br>";  
			$_datetime = date("Y-m-d",strtotime($_date))." 00:00:00";
			$_sql="	SELECT * FROM {$GLOBALS['instanceServer']['instance_server_dbn']}.payroll_master_salary_report _report  
			WHERE _report.salary_report_start_dt<='{$_datetime}' 
			AND _report.salary_report_end_dt>='{$_datetime}' 
			AND _report.server_id='{$_REQUEST['server_id']}' 
			AND _report.instance_server_id = '{$_REQUEST['instance_server_id']}' 
			AND _report.instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}'  ";
			// echo "$_sql";
			$lists =  $this->_sqlget($_sql);
			return $lists;
		}
		
		function getMasterReportByDatetime($_datetime){  
			//echo "DateIn : ".$_datetime."<BR>";
			$_datetime = date("Y-m-d H:i:s",strtotime($_datetime));
			//echo "DateTrue : ".$_datetime."<BR>";
			$_sql="	SELECT * FROM {$GLOBALS['instanceServer']['instance_server_dbn']}.payroll_master_salary_report _report  
			WHERE _report.salary_report_start_dt<='{$_datetime}' 
			AND _report.salary_report_end_dt>='{$_datetime}' 
			AND _report.server_id='{$_REQUEST['server_id']}' 
			AND _report.instance_server_id = '{$_REQUEST['instance_server_id']}' 
			AND _report.instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}'  ";
			//echo "$_sql";
			$lists =  $this->_sqlget($_sql);
			return $lists;
		}

		function getMasterReportByID($_master_salary_report_id){     
			$_sql="	SELECT * FROM {$GLOBALS['instanceServer']['instance_server_dbn']}.payroll_master_salary_report _report 
			WHERE _report.master_salary_report_id= '".$_master_salary_report_id."' 
			AND _report.server_id='{$_REQUEST['server_id']}' 
			AND _report.instance_server_id = '{$_REQUEST['instance_server_id']}' 
			AND _report.instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}' ";
			//echo "$_sql";
			$lists =  $this->_sqlget($_sql);
			return $lists;
		}
		
		function getMasterReportCurrent(){     
			$_sql="	SELECT * FROM {$GLOBALS['instanceServer']['instance_server_dbn']}.payroll_master_salary_report _report 
			WHERE _report.salary_report_start_dt < NOW() 
			AND _report.server_id='{$_REQUEST['server_id']}' 
			AND _report.instance_server_id = '{$_REQUEST['instance_server_id']}' 
			AND _report.instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}' 
			AND _report.read_only_flag='N' 
			ORDER BY _report.master_salary_month LIMIT 1 ";
			// echo "$_sql";
			$lists =  $this->_sqlget($_sql);     

			return $lists;
		}
		function getMasterReportCurrentDESC(){     
			$_sql="	SELECT * FROM {$GLOBALS['instanceServer']['instance_server_dbn']}.payroll_master_salary_report _report 
			WHERE _report.salary_report_start_dt < NOW() 
			AND _report.server_id='{$_REQUEST['server_id']}' 
			AND _report.instance_server_id = '{$_REQUEST['instance_server_id']}' 
			AND _report.instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}' 
			AND _report.read_only_flag='N' 
			ORDER BY _report.master_salary_month DESC LIMIT 1 ";
			// echo "$_sql";
			$lists =  $this->_sqlget($_sql);     

			return $lists;
		}

		function getMasterReportLastFinished(){     
			$_sql="	SELECT * FROM {$GLOBALS['instanceServer']['instance_server_dbn']}.payroll_master_salary_report _report 
			WHERE _report.server_id='{$_REQUEST['server_id']}' 
			AND _report.instance_server_id = '{$_REQUEST['instance_server_id']}' 
			AND _report.instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}' 
			AND _report.read_only_flag='Y' 
			ORDER BY _report.master_salary_month DESC LIMIT 1 ";
			//echo "$_sql";
			$lists =  $this->_sqlget($_sql);     

			return $lists;
		}
		
		function deleteMasterReport($_master_salary_report_id){     
			$_sql="	DELETE FROM {$GLOBALS['instanceServer']['instance_server_dbn']}.payroll_master_salary_report 
			WHERE master_salary_report_id= '{$_master_salary_report_id}' 
			AND server_id='{$_REQUEST['server_id']}' 
			AND instance_server_id = '{$_REQUEST['instance_server_id']}' 
			AND instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}'  ";
			//echo "$_sql";
			$this->Execute_Query($_sql);		

			$_sql = "DELETE FROM {$GLOBALS['instanceServer']['instance_server_dbn']}.payroll_master_salary_slip 
			WHERE master_salary_report_id= '{$_master_salary_report_id}' 
			AND server_id='{$_REQUEST['server_id']}' 
			AND instance_server_id = '{$_REQUEST['instance_server_id']}' 
			AND instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}'  ";
			//echo "$_sql";
			$this->Execute_Query($_sql);	

			$_sql = "DELETE FROM {$GLOBALS['instanceServer']['instance_server_dbn']}.payroll_master_salary_expenses 
			WHERE master_salary_report_id= '{$_master_salary_report_id}' 
			AND server_id='{$_REQUEST['server_id']}' 
			AND instance_server_id = '{$_REQUEST['instance_server_id']}' 
			AND instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}'  ";
			//echo "$_sql";
			$this->Execute_Query($_sql);

			$_sql = "DELETE FROM {$GLOBALS['instanceServer']['instance_server_dbn']}.payroll_master_salary_income 
			WHERE master_salary_report_id= '{$_master_salary_report_id}' 
			AND server_id='{$_REQUEST['server_id']}' 
			AND instance_server_id = '{$_REQUEST['instance_server_id']}' 
			AND instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}'  ";
			//echo "$_sql";
			$this->Execute_Query($_sql);

			$_sql = "DELETE FROM {$GLOBALS['instanceServer']['instance_server_dbn']}.payroll_master_salary_log 
			WHERE master_salary_report_id= '{$_master_salary_report_id}' 
			AND server_id='{$_REQUEST['server_id']}' 
			AND instance_server_id = '{$_REQUEST['instance_server_id']}' 
			AND instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}'  ";
			//echo "$_sql";
			$this->Execute_Query($_sql);

			$_sql = "DELETE FROM {$GLOBALS['instanceServer']['instance_server_dbn']}.payroll_time_attendance_group_transac 
			WHERE master_salary_report_id= '{$_master_salary_report_id}' 
			AND server_id='{$_REQUEST['server_id']}' 
			AND instance_server_id = '{$_REQUEST['instance_server_id']}' 
			AND instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}'  ";
			//echo "$_sql";
			$this->Execute_Query($_sql);		

			$_sql = "DELETE FROM {$GLOBALS['instanceServer']['instance_server_dbn']}.payroll_employee_auto 
			WHERE master_salary_report_id= '{$_master_salary_report_id}' 
			AND server_id='{$_REQUEST['server_id']}' 
			AND instance_server_id = '{$_REQUEST['instance_server_id']}' 
			AND instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}'  ";
			//echo "$_sql";
			$this->Execute_Query($_sql);

			// $_sql = "DELETE FROM {$GLOBALS['instanceServer']['instance_server_dbn']}.payroll_employee_withdraw 
			// WHERE master_salary_report_id= '{$_master_salary_report_id}' 
			// AND server_id='{$_REQUEST['server_id']}' 
			// AND instance_server_id = '{$_REQUEST['instance_server_id']}' 
			// AND instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}'  ";
			// //echo "$_sql";
			// $this->Execute_Query($_sql);

			// $_sql = "DELETE FROM {$GLOBALS['instanceServer']['instance_server_dbn']}.payroll_employee_withdraw_doc 
			// WHERE master_salary_report_id= '{$_master_salary_report_id}' 
			// AND server_id='{$_REQUEST['server_id']}' 
			// AND instance_server_id = '{$_REQUEST['instance_server_id']}' 
			// AND instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}'  ";
			// //echo "$_sql";
			// $this->Execute_Query($_sql);

			$_sql = "DELETE FROM {$GLOBALS['instanceServer']['instance_server_dbn']}.payroll_employee_expenses 
			WHERE master_salary_report_id= '{$_master_salary_report_id}' 
			AND server_id='{$_REQUEST['server_id']}' 
			AND instance_server_id = '{$_REQUEST['instance_server_id']}' 
			AND instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}'  ";
			//echo "$_sql";
			$this->Execute_Query($_sql);		

			$_sql = "DELETE FROM {$GLOBALS['instanceServer']['instance_server_dbn']}.payroll_employee_income 
			WHERE master_salary_report_id= '{$_master_salary_report_id}' 
			AND server_id='{$_REQUEST['server_id']}' 
			AND instance_server_id = '{$_REQUEST['instance_server_id']}' 
			AND instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}'  ";
			//echo "$_sql";
			$this->Execute_Query($_sql);	

			$_sql = "DELETE FROM {$GLOBALS['instanceServer']['instance_server_dbn']}.payroll_fund_company_log 
			WHERE master_salary_report_id= '{$_master_salary_report_id}' 
			AND server_id='{$_REQUEST['server_id']}' 
			AND instance_server_id = '{$_REQUEST['instance_server_id']}' 
			AND instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}'  ";
			//echo "$_sql";
			$this->Execute_Query($_sql);

			$_sql = "DELETE FROM {$GLOBALS['instanceServer']['instance_server_dbn']}.payroll_fund_branch_log 
			WHERE master_salary_report_id= '{$_master_salary_report_id}' 
			AND server_id='{$_REQUEST['server_id']}' 
			AND instance_server_id = '{$_REQUEST['instance_server_id']}' 
			AND instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}'  ";
			//echo "$_sql";
			$this->Execute_Query($_sql);

			$_sql = "DELETE FROM {$GLOBALS['instanceServer']['instance_server_dbn']}.payroll_fund_employee_log 
			WHERE master_salary_report_id= '{$_master_salary_report_id}' 
			AND server_id='{$_REQUEST['server_id']}' 
			AND instance_server_id = '{$_REQUEST['instance_server_id']}' 
			AND instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}'  ";
			//echo "$_sql";
			$this->Execute_Query($_sql);

			$_sql = "DELETE FROM {$GLOBALS['instanceServer']['instance_server_dbn']}.payroll_fund_log 
			WHERE master_salary_report_id= '{$_master_salary_report_id}' 
			AND server_id='{$_REQUEST['server_id']}' 
			AND instance_server_id = '{$_REQUEST['instance_server_id']}' 
			AND instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}'  ";
			//echo "$_sql";
			$this->Execute_Query($_sql);	
			
			$_sql = "DELETE FROM {$GLOBALS['instanceServer']['instance_server_dbn']}.payroll_master_salary_split_expenses 
			WHERE master_salary_report_id= '{$_master_salary_report_id}' 
			AND server_id='{$_REQUEST['server_id']}' 
			AND instance_server_id = '{$_REQUEST['instance_server_id']}' 
			AND instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}'  ";
			//echo "$_sql";
			$this->Execute_Query($_sql);	

			$_sql = "DELETE FROM {$GLOBALS['instanceServer']['instance_server_dbn']}.payroll_master_salary_split_income 
			WHERE master_salary_report_id= '{$_master_salary_report_id}' 
			AND server_id='{$_REQUEST['server_id']}' 
			AND instance_server_id = '{$_REQUEST['instance_server_id']}' 
			AND instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}'  ";
			//echo "$_sql";
			$this->Execute_Query($_sql);	

			$_sql = "DELETE FROM {$GLOBALS['instanceServer']['instance_server_dbn']}.payroll_master_salary_split_inex 
			WHERE master_salary_report_id= '{$_master_salary_report_id}' 
			AND server_id='{$_REQUEST['server_id']}' 
			AND instance_server_id = '{$_REQUEST['instance_server_id']}' 
			AND instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}'  ";
			//echo "$_sql";
			$this->Execute_Query($_sql);	

			$_sql = "DELETE FROM {$GLOBALS['instanceServer']['instance_server_dbn']}.payroll_master_salary_split_report 
			WHERE master_salary_report_id= '{$_master_salary_report_id}' 
			AND server_id='{$_REQUEST['server_id']}' 
			AND instance_server_id = '{$_REQUEST['instance_server_id']}' 
			AND instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}'  ";
			//echo "$_sql";
			$this->Execute_Query($_sql);	

			$_sql = "DELETE FROM {$GLOBALS['instanceServer']['instance_server_dbn']}.payroll_master_salary_split_report_inex 
			WHERE master_salary_report_id= '{$_master_salary_report_id}' 
			AND server_id='{$_REQUEST['server_id']}' 
			AND instance_server_id = '{$_REQUEST['instance_server_id']}' 
			AND instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}'  ";
			//echo "$_sql";
			$this->Execute_Query($_sql);	

			$_sql = "DELETE FROM {$GLOBALS['instanceServer']['instance_server_dbn']}.payroll_master_salary_split_slip 
			WHERE master_salary_report_id= '{$_master_salary_report_id}' 
			AND server_id='{$_REQUEST['server_id']}' 
			AND instance_server_id = '{$_REQUEST['instance_server_id']}' 
			AND instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}'  ";
			//echo "$_sql";
			$this->Execute_Query($_sql);	

			$_sql = "DELETE FROM {$GLOBALS['instanceServer']['instance_server_dbn']}.payroll_master_salary_split_temp_expenses 
			WHERE master_salary_report_id= '{$_master_salary_report_id}' 
			AND server_id='{$_REQUEST['server_id']}' 
			AND instance_server_id = '{$_REQUEST['instance_server_id']}' 
			AND instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}'  ";
			//echo "$_sql";
			$this->Execute_Query($_sql);	

			$_sql = "DELETE FROM {$GLOBALS['instanceServer']['instance_server_dbn']}.payroll_master_salary_split_temp_income 
			WHERE master_salary_report_id= '{$_master_salary_report_id}' 
			AND server_id='{$_REQUEST['server_id']}' 
			AND instance_server_id = '{$_REQUEST['instance_server_id']}' 
			AND instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}'  ";
			//echo "$_sql";
			$this->Execute_Query($_sql);	

			$_sql = "DELETE FROM {$GLOBALS['instanceServer']['instance_server_dbn']}.payroll_master_salary_type 
			WHERE master_salary_report_id= '{$_master_salary_report_id}' 
			AND server_id='{$_REQUEST['server_id']}' 
			AND instance_server_id = '{$_REQUEST['instance_server_id']}' 
			AND instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}'  ";
			//echo "$_sql";
			$this->Execute_Query($_sql);	

			$_sql = "DELETE FROM {$GLOBALS['instanceServer']['instance_server_dbn']}.payroll_master_salary_xtra_expenses 
			WHERE master_salary_report_id= '{$_master_salary_report_id}' 
			AND server_id='{$_REQUEST['server_id']}' 
			AND instance_server_id = '{$_REQUEST['instance_server_id']}' 
			AND instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}'  ";
			//echo "$_sql";
			$this->Execute_Query($_sql);	

			$_sql = "DELETE FROM {$GLOBALS['instanceServer']['instance_server_dbn']}.payroll_master_salary_xtra_income 
			WHERE master_salary_report_id= '{$_master_salary_report_id}' 
			AND server_id='{$_REQUEST['server_id']}' 
			AND instance_server_id = '{$_REQUEST['instance_server_id']}' 
			AND instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}'  ";
			//echo "$_sql";
			$this->Execute_Query($_sql);	

			$_sql = "DELETE FROM {$GLOBALS['instanceServer']['instance_server_dbn']}.payroll_master_salary_xtra_report 
			WHERE master_salary_report_id= '{$_master_salary_report_id}' 
			AND server_id='{$_REQUEST['server_id']}' 
			AND instance_server_id = '{$_REQUEST['instance_server_id']}' 
			AND instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}'  ";
			//echo "$_sql";
			$this->Execute_Query($_sql);	

			$_sql = "DELETE FROM {$GLOBALS['instanceServer']['instance_server_dbn']}.payroll_master_salary_xtra_slip 
			WHERE master_salary_report_id= '{$_master_salary_report_id}' 
			AND server_id='{$_REQUEST['server_id']}' 
			AND instance_server_id = '{$_REQUEST['instance_server_id']}' 
			AND instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}'  ";
			//echo "$_sql";
			$this->Execute_Query($_sql);	
			
			$_sql = "DELETE FROM {$GLOBALS['instanceServer']['instance_server_dbn']}.payroll_work_insurance_log 
			WHERE master_salary_report_id='{$_master_salary_report_id}' 
			AND server_id = '{$_REQUEST['server_id']}' 
			AND instance_server_id = '{$_REQUEST['instance_server_id']}' 
			AND instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}' ";
			//echo "$_sql";
			$this->Execute_Query($_sql);

			$_sql = "DELETE FROM {$GLOBALS['instanceServer']['instance_server_dbn']}.payroll_time_leave_log 
			WHERE master_salary_report_id='{$_master_salary_report_id}' 
			AND time_leave_source IN ('Calculation','Simulate') 
			AND server_id = '{$_REQUEST['server_id']}' 
			AND instance_server_id = '{$_REQUEST['instance_server_id']}' 
			AND instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}' ";
			//echo "$_sql";
			$this->Execute_Query($_sql);

			$_sql = "DELETE FROM {$GLOBALS['instanceServer']['instance_server_dbn']}.payroll_master_salary_ot_report  
			WHERE master_salary_report_id= '{$_master_salary_report_id}' 
			AND server_id='{$_REQUEST['server_id']}' 
			AND instance_server_id = '{$_REQUEST['instance_server_id']}' 
			AND instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}'  ";
			//echo "$_sql";
			$this->Execute_Query($_sql);	

			$_sql = "DELETE FROM {$GLOBALS['instanceServer']['instance_server_dbn']}.payroll_master_salary_ot_slip  
			WHERE master_salary_report_id= '{$_master_salary_report_id}' 
			AND server_id='{$_REQUEST['server_id']}' 
			AND instance_server_id = '{$_REQUEST['instance_server_id']}' 
			AND instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}'  ";
			//echo "$_sql";
			$this->Execute_Query($_sql);

			$_sql = "DELETE FROM {$GLOBALS['instanceServer']['instance_server_dbn']}.payroll_master_salary_worktime_report  
			WHERE master_salary_report_id= '{$_master_salary_report_id}' 
			AND server_id='{$_REQUEST['server_id']}' 
			AND instance_server_id = '{$_REQUEST['instance_server_id']}' 
			AND instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}'  ";
			//echo "$_sql";
			$this->Execute_Query($_sql);	

			$_sql = "DELETE FROM {$GLOBALS['instanceServer']['instance_server_dbn']}.payroll_master_salary_worktime_slip  
			WHERE master_salary_report_id= '{$_master_salary_report_id}' 
			AND server_id='{$_REQUEST['server_id']}' 
			AND instance_server_id = '{$_REQUEST['instance_server_id']}' 
			AND instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}'  ";
			//echo "$_sql";
			$this->Execute_Query($_sql);

			// Config Month
			$_sql = "DELETE FROM {$GLOBALS['instanceServer']['instance_server_dbn']}.payroll_master_company_config  
			WHERE master_salary_report_id= '{$_master_salary_report_id}' 
			AND server_id='{$_REQUEST['server_id']}' 
			AND instance_server_id = '{$_REQUEST['instance_server_id']}' 
			AND instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}'  ";
			//echo "$_sql";
			$this->Execute_Query($_sql);

			$_sql = "DELETE FROM {$GLOBALS['instanceServer']['instance_server_dbn']}.payroll_master_employee_config  
			WHERE master_salary_report_id= '{$_master_salary_report_id}' 
			AND server_id='{$_REQUEST['server_id']}' 
			AND instance_server_id = '{$_REQUEST['instance_server_id']}' 
			AND instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}'  ";
			//echo "$_sql";
			$this->Execute_Query($_sql);

			$_sql = "DELETE FROM {$GLOBALS['instanceServer']['instance_server_dbn']}.payroll_master_time_leave_flag  
			WHERE master_salary_report_id= '{$_master_salary_report_id}' 
			AND server_id='{$_REQUEST['server_id']}' 
			AND instance_server_id = '{$_REQUEST['instance_server_id']}' 
			AND instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}'  ";
			//echo "$_sql";
			$this->Execute_Query($_sql);

			$_sql = "DELETE FROM {$GLOBALS['instanceServer']['instance_server_dbn']}.payroll_master_config  
			WHERE master_salary_report_id= '{$_master_salary_report_id}' 
			AND server_id='{$_REQUEST['server_id']}' 
			AND instance_server_id = '{$_REQUEST['instance_server_id']}' 
			AND instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}'  ";
			//echo "$_sql";
			$this->Execute_Query($_sql);

			$_sql = "DELETE FROM {$GLOBALS['instanceServer']['instance_server_dbn']}.payroll_master_employee   
			WHERE master_salary_report_id= '{$_master_salary_report_id}' 
			AND server_id='{$_REQUEST['server_id']}' 
			AND instance_server_id = '{$_REQUEST['instance_server_id']}' 
			AND instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}'  ";
			//echo "$_sql";
			$this->Execute_Query($_sql);

			// * SSSALARYADJUST
			// * ลบการปรับปรุงรอบเดือน
			$_sql = "DELETE _sadjust, _sadjust_list FROM {$GLOBALS['instanceServer']['instance_server_dbn']}.payroll_salary_adjust _sadjust
			INNER JOIN {$GLOBALS['instanceServer']['instance_server_dbn']}.payroll_salary_adjust_list _sadjust_list ON (
				_sadjust.salary_adjust_id = _sadjust_list.salary_adjust_id
			) 
			WHERE _sadjust.master_salary_report_id = '{$_master_salary_report_id}'
			AND _sadjust.server_id='{$_REQUEST['server_id']}' 
			AND _sadjust.instance_server_id = '{$_REQUEST['instance_server_id']}' 
			AND _sadjust.instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}'
			";

			$this->Execute_Query($_sql);

			// * SSTYPEADJUST
			// * ลบการปรับประเภทพนักงาน
			$_sql = "DELETE _etjust, _etjust_list FROM {$GLOBALS['instanceServer']['instance_server_dbn']}.payroll_employee_type_adjust _etjust
			INNER JOIN {$GLOBALS['instanceServer']['instance_server_dbn']}.payroll_employee_type_adjust_list _etjust_list ON (
				_etjust.employee_type_adjust_id = _etjust_list.employee_type_adjust_id
			) 
			WHERE _etjust.master_salary_report_id = '{$_master_salary_report_id}'
			AND _etjust.server_id='{$_REQUEST['server_id']}' 
			AND _etjust.instance_server_id = '{$_REQUEST['instance_server_id']}' 
			AND _etjust.instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}'
			";

			$this->Execute_Query($_sql);
			
			$_sql = "DELETE FROM {$GLOBALS['instanceServer']['instance_server_dbn']}.payroll_master_branch_sso_config   
			WHERE master_salary_report_id= '{$_master_salary_report_id}' 
			AND server_id='{$_REQUEST['server_id']}' 
			AND instance_server_id = '{$_REQUEST['instance_server_id']}' 
			AND instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}'  ";
			//echo "$_sql";
			$this->Execute_Query($_sql);
		
		}

		function deleteFinish($_after_date){     
			//Delete Log Notify
			$_sql="	DELETE FROM {$GLOBALS['instanceServer']['instance_server_dbn']}.payroll_notify 
			WHERE created<='".date('Y-m-d H:i:s',strtotime($_after_date." -2 month"))."' 
			AND server_id='{$_REQUEST['server_id']}' 
			AND instance_server_id = '{$_REQUEST['instance_server_id']}' 
			AND instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}' ";
			//echo "$_sql<BR>";
			$this->Execute_Query($_sql);

			$_sql="	DELETE FROM {$GLOBALS['instanceServer']['instance_server_dbn']}.payroll_notify_user 
			WHERE created<='".date('Y-m-d H:i:s',strtotime($_after_date." -2 month"))."' 
			AND server_id='{$_REQUEST['server_id']}' 
			AND instance_server_id = '{$_REQUEST['instance_server_id']}' 
			AND instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}' ";
			//echo "$_sql<BR>";
			$this->Execute_Query($_sql);	
	
			//Delete Truck
			$_sql="	DELETE FROM {$GLOBALS['instanceServer']['instance_server_dbn']}.payroll_time_attendance_truck 
			WHERE created<='".date('Y-m-d H:i:s',strtotime($_after_date." -3 month"))."' 
			AND server_id='{$_REQUEST['server_id']}' 
			AND instance_server_id = '{$_REQUEST['instance_server_id']}' 
			AND instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}' ";
			//echo "$_sql<BR>";
			$this->Execute_Query($_sql);	
			
			//Delete Log Tracking
			// $_sql="	DELETE FROM {$GLOBALS['instanceServer']['instance_server_dbn']}.payroll_log_tracking 
			// WHERE created<='".date('Y-m-d H:i:s',strtotime($_after_date." -3 month"))."' 
			// AND server_id='{$_REQUEST['server_id']}' 
			// AND instance_server_id = '{$_REQUEST['instance_server_id']}' 
			// AND instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}' ";
			// //echo "$_sql<BR>";
			// $this->Execute_Query($_sql);	
		}

		function checkFinishMonth($_employee_id,$_dt){   
			
			if(strlen($_dt)<19){
				$_dt = date("Y-m-d",strtotime($_dt))." 08:00:00";
			}

			$result = array();

			$_sql="	SELECT _employee.*, 
			_split_round.config_key_1 AS split_round 
			FROM comp_employee _employee 
			INNER JOIN (
				SELECT instance_server_channel_id,config_key_1 FROM comp_config _config  
				WHERE _config.server_id = '{$_REQUEST['server_id']}' 
				AND _config.instance_server_id = '{$_REQUEST['instance_server_id']}' 
				AND _config.instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}'
				AND _config.config_code='split_round'
			) _split_round ON (_employee.instance_server_channel_id=_split_round.instance_server_channel_id) 
			WHERE _employee.server_id = '{$_REQUEST['server_id']}' 
			AND _employee.instance_server_id = '{$_REQUEST['instance_server_id']}' 
			AND _employee.instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}' 
			AND _employee.employee_id='{$_employee_id}' ";
			// echo "$_sql<BR>";
			$result['profile'] =  $this->_sqlget($_sql);  

			$_sql="	SELECT _report_month.master_salary_month, 
			_report_month.read_only_flag AS finish_month,
			_split_month.read_only_flag AS finish_split_month 
			FROM (
				SELECT _report.instance_server_channel_id,_report.master_salary_report_id,_report.master_salary_month,_report.read_only_flag 
				FROM {$GLOBALS['instanceServer']['instance_server_dbn']}.payroll_master_salary_report _report  
				WHERE _report.salary_report_start_dt<='{$_dt}' 
				AND _report.salary_report_end_dt>='{$_dt}' 
				AND _report.server_id='{$_REQUEST['server_id']}' 
				AND _report.instance_server_id = '{$_REQUEST['instance_server_id']}' 
				AND _report.instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}'
			) _report_month 
			LEFT JOIN (
				SELECT _split.master_salary_report_id,_split.master_salary_month,_split.read_only_flag 
				FROM {$GLOBALS['instanceServer']['instance_server_dbn']}.payroll_master_salary_split_report _split  
				WHERE _split.split_report_start_dt <= '{$_dt}' 
				AND _split.split_report_end_dt>= '{$_dt}' 
				AND _split.server_id = '{$_REQUEST['server_id']}' 
				AND _split.instance_server_id = '{$_REQUEST['instance_server_id']}' 
				AND _split.instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}'
			) _split_month ON (_report_month.master_salary_report_id=_split_month.master_salary_report_id) ";
			// echo "$_sql<BR>";
			$result['checking'] =  $this->_sqlget($_sql);  

			$finishMonth = 'N';
			// echo "split_round : ".$result['profile']['split_round']."<BR>";
			// echo "round_month_config : ".$result['profile']['round_month_config']."<BR>";
			// echo "finish_split_month : ".$result['checking']['finish_split_month']."<BR>";
			// echo "finish_month : ".$result['checking']['finish_month']."<BR>";
			if($result['profile']['split_round']!='A'&&$result['profile']['round_month_config']=='Split'){
				$finishMonth = $result['checking']['finish_split_month'] ? $result['checking']['finish_split_month'] : 'N';
			}else if($result['profile']['round_month_config']=='Full'){
				$finishMonth = $result['checking']['finish_month'] ? $result['checking']['finish_month'] : 'N';
			}
			// echo "Return : ".$finishMonth."<BR>";
			return $finishMonth;
		}

		function getOpenMasterReport() {
			$_sql = "SELECT _report.* FROM {$GLOBALS['instanceServer']['instance_server_dbn']}.payroll_master_salary_report _report 
			WHERE _report.read_only_flag = 'N' 
			AND _report.server_id = '{$_REQUEST['server_id']}' 
			AND _report.instance_server_id = '{$_REQUEST['instance_server_id']}' 
			AND _report.instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}'";
			$months = $this->_sqllists($_sql);
			
			return $months;
		}

		// TODO:
		// take instance_server_channel_id(s) list
		// yields: reports (of all related companies)
		function getMasterReportsByMonth($_month){
			// ? table [payroll_master_salary_report]: e.g.: bearhouse: hms_inst22
			$_sql="SELECT * FROM {$GLOBALS['instanceServer']['instance_server_dbn']}.payroll_master_salary_report _report  
			WHERE _report.master_salary_month= '{$_month}' 
			AND _report.server_id='{$_REQUEST['server_id']}' 
			AND _report.instance_server_id = '{$_REQUEST['instance_server_id']}'";
			// // AND _report.instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}'
			// echo "$_sql"; exit();
		    // $lists =  $this->_sqlget($_sql); // only 1 (first) object
			// ? AbstractBaseService.class.php
			$lists = $this->_sqllists($_sql);
			// echo json_encode($lists); exit();
			return $lists;
		}

		// TODO: 
		// get instance_server_channel_code: by $instance_server_channel_id
		// eg. "21sunpassion", "bearhugproduction", "sunsusolution"
		function getInstanceServerChannelCode($instance_server_channel_id){
			$_sql = "SELECT instance_server_channel_code
				FROM hms_api.sys_instance_server_channel
				WHERE instance_server_channel_id='$instance_server_channel_id'
				AND server_id='{$_REQUEST['server_id']}'";
			// echo $_sql; exit();
			$_query_result = $this->_sqlget($_sql);
			return $_query_result;
		}

		function getRoundDateCalcMonth($date, $maxLoops = 12) {
			if ($date == '') {
				throw new Exception("getRoundDateCalcMonth Parameter Required!!");
			}
		
			$data = array();
			$month = $this->getMasterReportByDate($date);
			$year_month = date('Y-m', strtotime($date));
			$day_of_month = date('d', strtotime($date));
			if (!empty($month)) {
				$data['master_salary_month'] = $month['master_salary_month'];
				$data['salary_report_start_dt'] = $month['salary_report_start_dt'];
				$data['salary_report_end_dt'] = $month['salary_report_end_dt'];
				$data['day_in_month'] = dateDiff($month['salary_report_start_dt'], $month['salary_report_end_dt']) + 1;
				$data['is_round_month'] =  true; 
				$data['master_salary_report_id'] = $month['master_salary_report_id'] ; 
			} else {
				$config = $GLOBALS['configService']->getSpecConfig('round_month');
				$_start = $config['config_key_1'];
				$_end = $config['config_key_2'];
		
				$loopCounter = 0; // ตัวนับจำนวนรอบของลูป
				$currentDate = $date;

				while ($loopCounter < $maxLoops) {
					$year_month = date('Y-m', strtotime($currentDate));
					$day_of_month = date('d', strtotime($currentDate));
					
					if ($_start != '' && $_end != '') {
						if (strtoupper($_end) == "EOM") {
							$salary_report_start_dt = $year_month . "-01 00:00:00";
							$salary_report_end_dt = date("Y-m-t", strtotime($year_month . "-01")) . " 23:59:59";
							$day_in_month = dateDiff($salary_report_start_dt, $salary_report_end_dt) + 1;
							// คืนค่าทันทีเมื่อเจอช่วงที่ต้องการ
							$data['master_salary_month'] = $year_month;
							$data['salary_report_start_dt'] = $salary_report_start_dt;
							$data['salary_report_end_dt'] = $salary_report_end_dt;
							$data['day_in_month'] = $day_in_month;
							$data['is_round_month'] =  false; 
							$data['master_salary_report_id'] = null ; 
							return $data;
						} else {
							$start_date = date('Y-m-d', strtotime($year_month . "-" . $_start . " -1 month"));
							$end_date = $year_month . "-" . $_end;

							// ตรวจสอบว่าค่าวันที่อยู่ในช่วงหรือไม่
							if (strtotime($date) >= strtotime($start_date) && strtotime($date) <= strtotime($end_date)) {
								$salary_report_start_dt = $start_date . " 00:00:00";
								$salary_report_end_dt = $end_date . " 23:59:00";
								$day_in_month = dateDiff($salary_report_start_dt, $salary_report_end_dt) + 1;

								// คืนค่าทันทีเมื่อเจอช่วงที่ต้องการ
								$data['master_salary_month'] = $year_month;
								$data['salary_report_start_dt'] = $salary_report_start_dt;
								$data['salary_report_end_dt'] = $salary_report_end_dt;
								$data['day_in_month'] = $day_in_month;
								$data['is_round_month'] =  false; 
								$data['master_salary_report_id'] = null ; 
								return $data;
							} else {
								
								// เลื่อนไปยังเดือนถัดไปถ้าไม่อยู่ในช่วง
								$currentDate = date('Y-m-d', strtotime("+1 month", strtotime($currentDate)));
							}
						}
					} else {
						// คืนค่า null หากการตั้งค่าไม่ครบถ้วน
						return null;
					}
		
					$loopCounter++; // เพิ่มจำนวนรอบที่ลูปทำงาน
				}
		
				// หากไม่พบช่วงที่ถูกต้องในลูปที่กำหนด
				return null;
			}
		
			return $data;
		}

		function findMatchingSplitRound($data, $inputTimestamp) {
			foreach ($data as $row) {
				$startTimestamp = strtotime($row['split_report_start_dt']);
				$endTimestamp = strtotime($row['split_report_end_dt']);
				
				// ตรวจสอบว่า input date อยู่ระหว่างช่วง start และ end หรือไม่
				if ($inputTimestamp >= $startTimestamp && $inputTimestamp <= $endTimestamp) {
					return $row; // คืนค่าข้อมูลที่ตรงกัน
				}
			}
			return null; // คืนค่า null หากไม่พบข้อมูลที่ตรงกัน
		}		

		function getRoundDateCalcMonthWithSplit($date, $maxLoops = 12) {
			if ($date == '') {
				throw new Exception("getRoundDateCalcMonth Parameter Required!!");
			}
		
			$data = array();
			$month = $this->getMasterReportByDate($date);
			$year_month = date('Y-m', strtotime($date));
			$day_of_month = date('d', strtotime($date));
			if (!empty($month)) {
				$dataSplitReport =  $GLOBALS['masterSalarySplitReportService']->getMasterSplitReportByReportID($month['master_salary_report_id']) ; 
				$data['master_salary_month'] = $month['master_salary_month'];
				$data['salary_report_start_dt'] = $month['salary_report_start_dt'];
				$data['salary_report_end_dt'] = $month['salary_report_end_dt'];
				$data['day_in_month'] = dateDiff($month['salary_report_start_dt'], $month['salary_report_end_dt']) + 1;
				$data['is_round_month'] =  true; 
				$data['master_salary_report_id'] = $month['master_salary_report_id'] ;
				$data['split_salary_report'] = $dataSplitReport ;
				$data['current_split_report'] =  $this->findMatchingSplitRound($dataSplitReport, strtotime($date)) ; 
			} else {
				$config = $GLOBALS['configService']->getMultipleConfig(array('round_month', 'split_fix_date', 'split_round'));
				$_start = $config['round_month']['config_key_1'];
				$_end = $config['round_month']['config_key_2'];

				$split = $config['split_round'] ;
				$split_round = 1 ;  
				if($split['config_key_1']=='A'){
					$split_round = 1;
				}else if($split['config_key_1']=='B'){
					$split_round = 2;
				}else if($split['config_key_1']=='C'){
					$split_round = 3;
				}else if($split['config_key_1']=='D'){
					$split_round = 4;
				}

				$configSplit = $config['split_fix_date'] ; 
				

		
				$loopCounter = 0; // ตัวนับจำนวนรอบของลูป
				$currentDate = $date;
		
				while ($loopCounter < $maxLoops) {
					
					$year_month = date('Y-m', strtotime($currentDate));
					$day_of_month = date('d', strtotime($currentDate));

					if ($_start != '' && $_end != '') {
						if (strtoupper($_end) == "EOM") {
							$salary_report_start_dt = $year_month . "-01 00:00:00";
							$salary_report_end_dt = date("Y-m-t", strtotime($year_month . "-01")) . " 23:59:59";
							$start_date = date('Y-m-d', strtotime($year_month . "-" . $_start));
							$end_date = date("Y-m-t", strtotime($year_month . "-01")) . " 23:59:59";
							$day_in_month = dateDiff($salary_report_start_dt, $salary_report_end_dt) + 1;
						} else {
							if ($_start > $_end) {
								// กรณี start อยู่เดือนที่แล้ว และ end อยู่เดือนปัจจุบัน
								$start_date = date('Y-m-d', strtotime($year_month . "-" . $_start . " -1 month"));
								$end_date = date('Y-m-d', strtotime($year_month . "-" . $_end)) . " 23:59:59";
							} else {
								// กรณี start และ end อยู่ในเดือนเดียวกัน
								$start_date = date('Y-m-d', strtotime($year_month . "-" . $_start));
								$end_date = date('Y-m-d', strtotime($year_month . "-" . $_end)) . " 23:59:59";
							}
							
						}
						// ตรวจสอบว่าค่าวันที่อยู่ในช่วงหรือไม่
						if (strtotime($date) >= strtotime($start_date . " 00:00:00") && strtotime($date) <= strtotime($end_date)) {
							$salary_report_start_dt = $start_date . " 00:00:00";
							$salary_report_end_dt = $end_date;
							$day_in_month = dateDiff($salary_report_start_dt, $salary_report_end_dt) + 1;

							$split_round_data = array();
							$seq =  $split_round ; 
							if ($seq == 1) { 
								$data = array();
								$data['master_salary_report_id'] = null;
								$data['master_salary_month'] = $year_month;
								$data['master_salary_split_seq'] = 1;
								$data['split_report_start_dt'] = $salary_report_start_dt;
								$data['split_report_end_dt'] = $salary_report_end_dt;
								$data['split_day_in_month'] = dateDiff($data['split_report_start_dt'],$data['split_report_end_dt'])+1;
								$data['split_divide'] = $seq;
								$split_round_data[]  = $data ; 
							} else if ($seq == 2) {
								$data = array();
								$data['master_salary_report_id'] = null;
								$data['master_salary_month'] = $year_month;
								$data['master_salary_split_seq'] = 1;
								$data['split_report_start_dt'] = $salary_report_start_dt;
								if($configSplit['config_key_1']=='Fixed'&&$configSplit['config_key_2']>0&&$configSplit['config_key_3']>0&&$configSplit['config_key_4']>0){
									$fixDateRound = date('Y-m-',strtotime($salary_report_start_dt)).fixDigits($configSplit['config_key_2'], 2)." 23:59:59";
									if(strtotime($fixDateRound)<strtotime($data['split_report_start_dt'])){
										$fixDateRound = date('Y-m-',strtotime($salary_report_end_dt)).fixDigits($configSplit['config_key_2'], 2)." 23:59:59";
									}
									$data['split_report_end_dt'] = $fixDateRound;
								}else{
									$data['split_report_end_dt'] = date('Y-m-d',strtotime($data['split_report_start_dt']." +14 days"))." 23:59:59";
								}
								$data['split_day_in_month'] = dateDiff($data['split_report_start_dt'],$data['split_report_end_dt'])+1;
								$data['split_divide'] = $seq;
								$split_round_data[]  = $data ; 

								$data = array();
								$data['master_salary_report_id'] = null;
								$data['master_salary_month'] = $year_month;
								$data['master_salary_split_seq'] = 2;
								if($configSplit['config_key_1']=='Fixed'&&$configSplit['config_key_2']>0){
									$data['split_report_start_dt'] = date('Y-m-d',strtotime($fixDateRound." +1 day"))." 00:00:00";
								}else{
									$data['split_report_start_dt'] = date('Y-m-d',strtotime($salary_report_start_dt." +15 days"))." 00:00:00";
								}
								$data['split_report_end_dt'] = $salary_report_end_dt;
								$data['split_day_in_month'] = dateDiff($data['split_report_start_dt'],$data['split_report_end_dt'])+1;
								$data['split_divide'] = $seq;
								$split_round_data[]  = $data ; 
			
							} else if ($seq == 3) {
								$data = array();
								$data['master_salary_report_id'] = null;
								$data['master_salary_month'] = $year_month;
								$data['master_salary_split_seq'] = 1;
								$data['split_report_start_dt'] = $salary_report_start_dt;
								if($configSplit['config_key_1']=='Fixed'&&$configSplit['config_key_2']>0&&$configSplit['config_key_3']>0){
									$fixDateRound = date('Y-m-',strtotime($salary_report_start_dt)).fixDigits($configSplit['config_key_2'], 2)." 23:59:59";
									if(strtotime($fixDateRound)<strtotime($data['split_report_start_dt'])){
										$fixDateRound = date('Y-m-',strtotime($salary_report_end_dt)).fixDigits($configSplit['config_key_2'], 2)." 23:59:59";
									}
									$data['split_report_end_dt'] = $fixDateRound;
								}else{
									$data['split_report_end_dt'] = date('Y-m-d',strtotime($salary_report_start_dt." +9 days"))." 23:59:59";
								}
								$data['split_day_in_month'] = dateDiff($data['split_report_start_dt'],$data['split_report_end_dt'])+1;
								$data['split_divide'] = $seq;
								$split_round_data[]  = $data ; 

								$data = array();
								$data['master_salary_report_id'] = null;
								$data['master_salary_month'] = $year_month;
								$data['master_salary_split_seq'] = 2;
								if($configSplit['config_key_1']=='Fixed'&&$configSplit['config_key_2']>0&&$configSplit['config_key_3']>0){
									$data['split_report_start_dt'] = date('Y-m-d',strtotime($fixDateRound." +1 day"))." 00:00:00";
									$fixDateRound = date('Y-m-',strtotime($salary_report_start_dt)).fixDigits($configSplit['config_key_3'], 2)." 23:59:59";
									if(strtotime($fixDateRound)<strtotime($data['split_report_start_dt'])){
										$fixDateRound = date('Y-m-',strtotime($salary_report_end_dt)).fixDigits($configSplit['config_key_3'], 2)." 23:59:59";
									}
									$data['split_report_end_dt'] = $fixDateRound;
								}else{
									$data['split_report_start_dt'] = date('Y-m-d',strtotime($salary_report_start_dt." +10 days"))." 00:00:00";
									$data['split_report_end_dt'] = date('Y-m-d',strtotime($data['split_report_start_dt']." +9 days"))." 23:59:59";
								}
								$data['split_day_in_month'] = dateDiff($data['split_report_start_dt'],$data['split_report_end_dt'])+1;
								$data['split_divide'] = $seq;
								$split_round_data[]  = $data ; 

								$data = array();
								$data['master_salary_report_id'] = null;
								$data['master_salary_month'] = $year_month;
								$data['master_salary_split_seq'] = 3;
								if($configSplit['config_key_1']=='Fixed'&&$configSplit['config_key_2']>0&&$configSplit['config_key_3']>0){
									$data['split_report_start_dt'] = date('Y-m-d',strtotime($fixDateRound." +1 day"))." 00:00:00";
								}else{
									$data['split_report_start_dt'] = date('Y-m-d',strtotime($salary_report_start_dt." +20 days"))." 00:00:00";
								}
								$data['split_report_end_dt'] = $salary_report_end_dt;
								$data['split_day_in_month'] = dateDiff($data['split_report_start_dt'],$data['split_report_end_dt'])+1;
								$data['split_divide'] = $seq;
								$split_round_data[]  = $data ; 

							} else if ($seq == 4) {
								$data = array();
								$data['master_salary_report_id'] = null;
								$data['master_salary_month'] = $year_month;
								$data['master_salary_split_seq'] = 1;
								$data['split_report_start_dt'] = $salary_report_start_dt ;
								if($configSplit['config_key_1']=='Fixed'&&$configSplit['config_key_2']>0&&$configSplit['config_key_3']>0&&$configSplit['config_key_4']>0){
									$fixDateRound = date('Y-m-',strtotime($salary_report_start_dt)).fixDigits($configSplit['config_key_2'], 2)." 23:59:59";
									if(strtotime($fixDateRound)<strtotime($data['split_report_start_dt'])){
										$fixDateRound = date('Y-m-',strtotime($salary_report_end_dt)).fixDigits($configSplit['config_key_2'], 2)." 23:59:59";
									}
									$data['split_report_end_dt'] = $fixDateRound;
								}else{
									$data['split_report_end_dt'] = date('Y-m-d',strtotime($data['split_report_start_dt']." +6 days"))." 23:59:59";
								}
								$data['split_day_in_month'] = dateDiff($data['split_report_start_dt'],$data['split_report_end_dt'])+1;
								$data['split_divide'] = $seq;
								$split_round_data[]  = $data ; 

								$data = array();
								$data['master_salary_report_id'] = null;
								$data['master_salary_month'] = $year_month;
								$data['master_salary_split_seq'] = 2;
								if($configSplit['config_key_1']=='Fixed'&&$configSplit['config_key_2']>0&&$configSplit['config_key_3']>0&&$configSplit['config_key_4']>0){
									$data['split_report_start_dt'] = date('Y-m-d',strtotime($fixDateRound." +1 day"))." 00:00:00";
									$fixDateRound = date('Y-m-',strtotime($salary_report_start_dt)).fixDigits($configSplit['config_key_3'], 2)." 23:59:59";
									if(strtotime($fixDateRound)<strtotime($data['split_report_start_dt'])){
										$fixDateRound = date('Y-m-',strtotime($salary_report_end_dt)).fixDigits($configSplit['config_key_3'], 2)." 23:59:59";
									}
									$data['split_report_end_dt'] = $fixDateRound;
								}else{
									$data['split_report_start_dt'] = date('Y-m-d',strtotime($salary_report_start_dt." +7 days"))." 00:00:00";
									$data['split_report_end_dt'] = date('Y-m-d',strtotime($data['split_report_start_dt']." +6 days"))." 23:59:59";
								}
								$data['split_day_in_month'] = dateDiff($data['split_report_start_dt'],$data['split_report_end_dt'])+1;
								$data['split_divide'] = $seq;
								$split_round_data[]  = $data ; 

								$data = array();
								$data['master_salary_report_id'] = null;
								$data['master_salary_month'] = $year_month;
								$data['master_salary_split_seq'] = 3;
								if($configSplit['config_key_1']=='Fixed'&&$configSplit['config_key_2']>0&&$configSplit['config_key_3']>0&&$configSplit['config_key_4']>0){
									$data['split_report_start_dt'] = date('Y-m-d',strtotime($fixDateRound." +1 day"))." 00:00:00";
									$fixDateRound = date('Y-m-',strtotime($salary_report_start_dt)).fixDigits($configSplit['config_key_4'], 2)." 23:59:59";
									if(strtotime($fixDateRound)<strtotime($data['split_report_start_dt'])){
										$fixDateRound = date('Y-m-',strtotime($salary_report_end_dt)).fixDigits($configSplit['config_key_4'], 2)." 23:59:59";
									}
									$data['split_report_end_dt'] = $fixDateRound;
								}else{
									$data['split_report_start_dt'] = date('Y-m-d',strtotime($salary_report_start_dt." +14 days"))." 00:00:00";
									$data['split_report_end_dt'] = date('Y-m-d',strtotime($data['split_report_start_dt']." +6 days"))." 23:59:59";
								}
								$data['split_day_in_month'] = dateDiff($data['split_report_start_dt'],$data['split_report_end_dt'])+1;
								$data['split_divide'] = $seq;
								$split_round_data[]  = $data ; 

								$data = array();
								$data['master_salary_report_id'] = null;
								$data['master_salary_month'] = $year_month;
								$data['master_salary_split_seq'] = 4;
								if($configSplit['config_key_1']=='Fixed'&&$configSplit['config_key_2']>0&&$configSplit['config_key_3']>0&&$configSplit['config_key_4']>0){
									$data['split_report_start_dt'] = date('Y-m-d',strtotime($fixDateRound." +1 day"))." 00:00:00";
								}else{
									$data['split_report_start_dt'] = date('Y-m-d',strtotime($salary_report_start_dt." +21 days"))." 00:00:00";
								}
								$data['split_report_end_dt'] = $salary_report_end_dt;
								$data['split_day_in_month'] = dateDiff($data['split_report_start_dt'],$data['split_report_end_dt'])+1;
								$data['split_divide'] = $seq;
								$split_round_data[]  = $data ; 

							}
							
							// คืนค่าทันทีเมื่อเจอช่วงที่ต้องการ
							$data = array(); 
							$data['master_salary_month'] = $year_month;
							$data['salary_report_start_dt'] = $salary_report_start_dt;
							$data['salary_report_end_dt'] = $salary_report_end_dt;
							$data['day_in_month'] = $day_in_month;
							$data['is_round_month'] =  false; 
							$data['master_salary_report_id'] = null ; 
							$data['split_salary_report'] =  $split_round_data; 
							$data['current_split_report'] =  $this->findMatchingSplitRound($split_round_data, strtotime($date)) ; 
							return $data;
						} else {
							// เลื่อนไปยังเดือนถัดไปถ้าไม่อยู่ในช่วง
							$currentDate = date('Y-m-d', strtotime("+1 month", strtotime($currentDate)));
						}
					} else {
						// คืนค่า null หากการตั้งค่าไม่ครบถ้วน
						return null;
					}
		
					$loopCounter++; // เพิ่มจำนวนรอบที่ลูปทำงาน
				}
		
				// หากไม่พบช่วงที่ถูกต้องในลูปที่กำหนด
				return null;
			}
		
			return $data;
		}

		
		function getListMonthByYear($_status,$_limit,$_order, $_year){     
			$_sql="	SELECT _report.master_salary_report_id,
			_report.master_salary_month,
			_report.salary_split_flag,
			_report.read_only_flag,
			_report.salary_report_start_dt,
			_report.salary_report_end_dt 
			FROM {$GLOBALS['instanceServer']['instance_server_dbn']}.payroll_master_salary_report _report 
			WHERE _report.server_id='{$_REQUEST['server_id']}' 
			AND _report.instance_server_id = '{$_REQUEST['instance_server_id']}' 
			AND _report.instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}' 
			AND DATE_FORMAT(CONCAT(_report.master_salary_month, '-01' ), '%Y') = '{$_year}'";
			if($_status!=''){
				$_sql .= "AND _report.read_only_flag='{$_status}' ";
			}
			$_sql .= "ORDER BY _report.master_salary_month {$_order} ";
			if($_limit!=''){
				$_sql .= "LIMIT {$_limit}";
			}
			//echo "$_sql";
			$lists =  $this->_sqllists($_sql);     
			$retLists = array();
			for($i=0;$i<sizeof($lists);$i++){
				if($GLOBALS['__LANGUAGE']=="th")
					$lists[$i]['month_name'] = month_year($lists[$i]['master_salary_month']);
				else if($GLOBALS['__LANGUAGE']=="en")
					$lists[$i]['month_name'] = date('F Y',strtotime($lists[$i]['master_salary_month']));
				
				$retLists[$i] = $lists[$i];
			}
			return $retLists;
		}

    function disableDocumentInBetweenDate($startDatetime, $endDatetime) {
      $this->disableTimeLeaveInBetweenDate($startDatetime, $endDatetime);
      $this->disableOTInBetweenDate($startDatetime, $endDatetime);
      $this->disableTimeAdjustInBetweenDate($startDatetime, $endDatetime);
      $this->disableWorkCycleChangeInBetweenDate($startDatetime, $endDatetime);
      $this->disableHolidayChangeInBetweenDate($startDatetime, $endDatetime);
      $this->disableResignDocInBetweenDate($startDatetime, $endDatetime);

      // recount notification
      $topics = ['time_leave', 'ot_work', 'time_adjust', 'work_cycle_change', 'holiday_change', 'resign'];
      $GLOBALS['notificationsCountManager']->RecountAllByTopics($topics);
    }

    function disableTimeLeaveInBetweenDate($startDatetime, $endDatetime) {
      // get waiting to approve time leaves
      $timeleaveSQL = "SELECT time_leave_id, employee_id, effective_dt, expire_dt, effective_hour, expire_hour, approve_flag 
      FROM {$GLOBALS['instanceServer']['instance_server_dbn']}.payroll_time_leave 
      WHERE server_id = '{$_REQUEST['server_id']}'
      AND instance_server_id = '{$_REQUEST['instance_server_id']}'
      AND instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}'
      AND effective_hour <= '{$endDatetime}' 
      AND expire_hour >= '{$startDatetime}'  
      AND approve_flag NOT IN ('02', '03') ";
      $waitingApproveTimeLeaves = $this->_sqllists($timeleaveSQL);

      if (sizeof($waitingApproveTimeLeaves) === 0) {
        return;
      }
      // find: min and max datetime, employee who have time leave
      $employeeTimeLeaves = [];
      $minDatetime = null;
      $maxDatetime = null;
      foreach ($waitingApproveTimeLeaves as $timeLeave) {
        if ($minDatetime === null || strtotime($timeLeave['effective_hour']) < strtotime($minDatetime)) {
          $minDatetime = $timeLeave['effective_hour'];
        }
        if ($maxDatetime === null || strtotime($timeLeave['expire_hour']) > strtotime($maxDatetime)) {
          $maxDatetime = $timeLeave['expire_hour'];
        }

        if (!isset($employeeTimeLeaves[$timeLeave['employee_id']])) {
          $employeeTimeLeaves[$timeLeave['employee_id']] = [];
        }
        $employeeTimeLeaves[$timeLeave['employee_id']][] = $timeLeave;
      }

      // get unfinished worktime report in between min and max datetime
      $employeeIds = array_keys($employeeTimeLeaves);
      $worktimeReportSQL = "SELECT rep.master_salary_worktime_report_id, rep.master_salary_report_id, rep.worktime_report_start_dt, rep.worktime_report_end_dt, slip.employee_id  
      FROM {$GLOBALS['instanceServer']['instance_server_dbn']}.payroll_master_salary_worktime_report rep 
      INNER JOIN {$GLOBALS['instanceServer']['instance_server_dbn']}.payroll_master_salary_worktime_slip slip 
      ON (rep.server_id = slip.server_id AND rep.instance_server_id = slip.instance_server_id AND rep.master_salary_worktime_report_id = slip.master_salary_worktime_report_id)
      WHERE rep.server_id = '{$_REQUEST['server_id']}' 
      AND rep.instance_server_id = '{$_REQUEST['instance_server_id']}' 
      AND rep.instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}' 
      AND rep.worktime_report_start_dt <= '{$maxDatetime}'
      AND rep.worktime_report_end_dt >= '{$minDatetime}' 
      AND rep.read_only_flag = 'N' 
      AND slip.employee_id IN ('" . implode("','", $employeeIds) . "') ";
      $worktimeSlips = $this->_sqllists($worktimeReportSQL);

      $employeeInWorktimeReport = [];
      foreach ($worktimeSlips as $wtSlip) {
        if (!isset($employeeInWorktimeReport[$wtSlip['employee_id']])) {
          $employeeInWorktimeReport[$wtSlip['employee_id']] = [];
        }
        if (!isset($employeeInWorktimeReport[$wtSlip['employee_id']][$wtSlip['master_salary_worktime_report_id']])) {
          $start = $wtSlip['worktime_report_start_dt'];
          $end = $wtSlip['worktime_report_end_dt'];
          $employeeInWorktimeReport[$wtSlip['employee_id']][$wtSlip['master_salary_worktime_report_id']] = array('start_dt' => $start, 'end_dt' => $end);
        }
      }

      // find time leave that should be disabled
      $targetTimeLeaves = [];
      foreach ($waitingApproveTimeLeaves as $timeLeave) {
        $employeeId = $timeLeave['employee_id'];
        $timeLeaveId = $timeLeave['time_leave_id'];
        $effectiveHour = $timeLeave['effective_hour'];
        $expireHour = $timeLeave['expire_hour'];

        $docIsInWorktimeReport = false;
        if (isset($employeeInWorktimeReport[$employeeId])) {
          foreach ($employeeInWorktimeReport[$employeeId] as $worktimeReportId => $worktimeReport) {
            $worktimeReportStartDt = $worktimeReport['start_dt'];
            $worktimeReportEndDt = $worktimeReport['end_dt'];
            if (strtotime($effectiveHour) <= strtotime($worktimeReportEndDt) && strtotime($expireHour) >= strtotime($worktimeReportStartDt)) {
              // if time leave is in worktime report, skip disable time leave
              $docIsInWorktimeReport = true;
              break;
            }
          }
        }

        if (!$docIsInWorktimeReport) {
          $targetTimeLeaves[] = $timeLeaveId;
        }
      }

      if (sizeof($targetTimeLeaves) > 0) {
        // disable time leave
        $updateTimeLeaveSQL = "UPDATE {$GLOBALS['instanceServer']['instance_server_dbn']}.payroll_time_leave 
        SET publish_flag = 'N' 
        WHERE server_id = '{$_REQUEST['server_id']}'
        AND instance_server_id = '{$_REQUEST['instance_server_id']}'
        AND instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}'
        AND time_leave_id IN ('" . implode("','", $targetTimeLeaves) . "') ";
        $this->Execute_Query($updateTimeLeaveSQL);
      }
    }

    function disableOTInBetweenDate($startDatetime, $endDatetime) {
      $startDatetime = date('Y-m-d', strtotime($startDatetime));
      $endDatetime = date('Y-m-d', strtotime($endDatetime));
      // get waiting to approve OTs
      $otSQL = "SELECT ot_work_id, employee_id, ot_work_dt, approve_flag   
      FROM {$GLOBALS['instanceServer']['instance_server_dbn']}.payroll_ot_work 
      WHERE server_id = '{$_REQUEST['server_id']}'
      AND instance_server_id = '{$_REQUEST['instance_server_id']}'
      AND instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}'
      AND ot_work_dt BETWEEN '{$startDatetime}'  AND '{$endDatetime}'
      AND approve_flag NOT IN ('02', '03') ";
      $waitingApproveOTs = $this->_sqllists($otSQL);

      if (sizeof($waitingApproveOTs) === 0) {
        return;
      }
      // find: min and max datetime, employee who have OT
      $employeeOTs = [];
      $minDatetime = null;
      $maxDatetime = null;
      foreach ($waitingApproveOTs as $ot) {
        $ot['ot_work_dt'] = $ot['ot_work_dt'] . ' 00:00:00';
        if ($minDatetime === null || strtotime($ot['ot_work_dt']) < strtotime($minDatetime)) {
          $minDatetime = $ot['ot_work_dt'];
        }
        if ($maxDatetime === null || strtotime($ot['ot_work_dt']) > strtotime($maxDatetime)) {
          $maxDatetime = $ot['ot_work_dt'];
        }

        if (!isset($employeeOTs[$ot['employee_id']])) {
          $employeeOTs[$ot['employee_id']] = [];
        }
        $employeeOTs[$ot['employee_id']][] = $ot;
      }

      // get unfinished worktime report in between min and max datetime
      $employeeIds = array_keys($employeeOTs);
      $otReportSQL = "SELECT rep.master_salary_ot_report_id, rep.master_salary_report_id, rep.ot_report_start_dt, rep.ot_report_end_dt, slip.employee_id  
      FROM {$GLOBALS['instanceServer']['instance_server_dbn']}.payroll_master_salary_ot_report rep 
      INNER JOIN {$GLOBALS['instanceServer']['instance_server_dbn']}.payroll_master_salary_ot_slip slip 
      ON (rep.server_id = slip.server_id AND rep.instance_server_id = slip.instance_server_id AND rep.master_salary_ot_report_id = slip.master_salary_ot_report_id)
      WHERE rep.server_id = '{$_REQUEST['server_id']}' 
      AND rep.instance_server_id = '{$_REQUEST['instance_server_id']}' 
      AND rep.instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}' 
      AND rep.ot_report_start_dt <= '{$maxDatetime}' 
      AND rep.ot_report_end_dt >= '{$minDatetime}' 
      AND rep.read_only_flag = 'N' 
      AND slip.employee_id IN ('" . implode("','", $employeeIds) . "') ";
      $otSlips = $this->_sqllists($otReportSQL);

      $employeeInOTReport = [];
      foreach ($otSlips as $otSlip) {
        if (!isset($employeeInOTReport[$otSlip['employee_id']])) {
          $employeeInOTReport[$otSlip['employee_id']] = [];
        }
        if (!isset($employeeInOTReport[$otSlip['employee_id']][$otSlip['master_salary_ot_report_id']])) {
          $start = $otSlip['ot_report_start_dt'];
          $end = $otSlip['ot_report_end_dt'];
          $employeeInOTReport[$otSlip['employee_id']][$otSlip['master_salary_ot_report_id']] = array('start_dt' => $start, 'end_dt' => $end);
        }
      }

      // find OT that should be disabled
      $targetOTs = [];
      foreach ($waitingApproveOTs as $ot) {
        $employeeId = $ot['employee_id'];
        $otId = $ot['ot_work_id'];
        $otWorkDt = $ot['ot_work_dt'];

        $docIsInOTReport = false;
        if (isset($employeeInOTReport[$employeeId])) {
          foreach ($employeeInOTReport[$employeeId] as $otReportId => $otReport) {
            $otReportStartDt = $otReport['start_dt'];
            $otReportEndDt = $otReport['end_dt'];
            if (strtotime($otWorkDt) >= strtotime($otReportStartDt) && strtotime($otWorkDt) <= strtotime($otReportEndDt) ) {
              // if OT is in worktime report, skip disable OT
              $docIsInOTReport = true;
              break;
            }
          }
        }

        if (!$docIsInOTReport) {
          $targetOTs[] = $otId;
        }
      }

      if (sizeof($targetOTs) > 0) {
        // disable OT
        $updateOTSQL = "UPDATE {$GLOBALS['instanceServer']['instance_server_dbn']}.payroll_ot_work 
        SET publish_flag = 'N'  
        WHERE server_id = '{$_REQUEST['server_id']}'
        AND instance_server_id = '{$_REQUEST['instance_server_id']}'
        AND instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}'
        AND ot_work_id IN ('" . implode("','", $targetOTs) . "') ";
        $this->Execute_Query($updateOTSQL);
      }
    }

    function disableTimeAdjustInBetweenDate($startDatetime, $endDatetime) {
      $startDatetime = date('Y-m-d', strtotime($startDatetime));
      $endDatetime = date('Y-m-d', strtotime($endDatetime));
      // get waiting to approve time adjusts
      $timeAdjustSQL = "SELECT time_adjust_id, employee_id, time_adjust_dt, approve_flag 
      FROM {$GLOBALS['instanceServer']['instance_server_dbn']}.payroll_time_adjust 
      WHERE server_id = '{$_REQUEST['server_id']}'
      AND instance_server_id = '{$_REQUEST['instance_server_id']}'
      AND instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}'
      AND time_adjust_dt BETWEEN '{$startDatetime}'  AND '{$endDatetime}'
      AND approve_flag NOT IN ('02', '03') ";
      $waitingApproveTimeAdjusts = $this->_sqllists($timeAdjustSQL);

      if (sizeof($waitingApproveTimeAdjusts) > 0) {
        $targetTimeAdjusts = [];
        foreach ($waitingApproveTimeAdjusts as $timeAdjust) {
          $targetTimeAdjusts[] = $timeAdjust['time_adjust_id'];
        }
  
        // disable time adjust
        $updateTimeAdjustSQL = "UPDATE {$GLOBALS['instanceServer']['instance_server_dbn']}.payroll_time_adjust
        SET publish_flag = 'N' 
        WHERE server_id = '{$_REQUEST['server_id']}'
        AND instance_server_id = '{$_REQUEST['instance_server_id']}'
        AND instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}'
        AND time_adjust_id IN ('" . implode("','", $targetTimeAdjusts) . "') ";
        $this->Execute_Query($updateTimeAdjustSQL);
      }
    }

    function disableWorkCycleChangeInBetweenDate($startDatetime, $endDatetime) {
      $startDatetime = date('Y-m-d', strtotime($startDatetime));
      $endDatetime = date('Y-m-d', strtotime($endDatetime));
      // get waiting to approve work cycle changes
      $workCycleChangeSQL = "SELECT work_cycle_change_id, employee_id, work_cycle_change_dt, approve_flag  
      FROM {$GLOBALS['instanceServer']['instance_server_dbn']}.payroll_work_cycle_change  
      WHERE server_id = '{$_REQUEST['server_id']}'
      AND instance_server_id = '{$_REQUEST['instance_server_id']}'
      AND instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}'
      AND work_cycle_change_dt BETWEEN '{$startDatetime}'  AND '{$endDatetime}'
      AND approve_flag NOT IN ('02', '03') ";
      $waitingApproveWorkCycleChanges = $this->_sqllists($workCycleChangeSQL);

      if (sizeof($waitingApproveWorkCycleChanges) > 0) {
        $targetWorkCycleChanges = [];
        foreach ($waitingApproveWorkCycleChanges as $workCycleChange) {
          $targetWorkCycleChanges[] = $workCycleChange['work_cycle_change_id'];
        }
        
        // disable work cycle change
        $updateWorkCycleChangeSQL = "UPDATE {$GLOBALS['instanceServer']['instance_server_dbn']}.payroll_work_cycle_change 
        SET publish_flag = 'N' 
        WHERE server_id = '{$_REQUEST['server_id']}'
        AND instance_server_id = '{$_REQUEST['instance_server_id']}'
        AND instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}'
        AND work_cycle_change_id IN ('" . implode("','", $targetWorkCycleChanges) . "') ";
        $this->Execute_Query($updateWorkCycleChangeSQL);
      }
    }

    function disableHolidayChangeInBetweenDate($startDatetime, $endDatetime) {
      $startDatetime = date('Y-m-d', strtotime($startDatetime));
      $endDatetime = date('Y-m-d', strtotime($endDatetime));
      // get waiting to approve holiday changes
      $holidayChangeSQL = "SELECT holiday_change_id, employee_id, holiday_change_dt, approve_flag 
      FROM {$GLOBALS['instanceServer']['instance_server_dbn']}.payroll_holiday_change  
      WHERE server_id = '{$_REQUEST['server_id']}'
      AND instance_server_id = '{$_REQUEST['instance_server_id']}'
      AND instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}'
      AND holiday_change_dt BETWEEN '{$startDatetime}'  AND '{$endDatetime}'
      AND approve_flag NOT IN ('02', '03') ";
      $waitingApproveHolidayChanges = $this->_sqllists($holidayChangeSQL);

      if (sizeof($waitingApproveHolidayChanges) > 0) {
        $targetHolidayChanges = [];
        foreach ($waitingApproveHolidayChanges as $holidayChange) {
          $targetHolidayChanges[] = $holidayChange['holiday_change_id'];
        }
  
        // disable holiday change
        $updateHolidayChangeSQL = "UPDATE {$GLOBALS['instanceServer']['instance_server_dbn']}.payroll_holiday_change 
        SET publish_flag = 'N' 
        WHERE server_id = '{$_REQUEST['server_id']}'
        AND instance_server_id = '{$_REQUEST['instance_server_id']}'
        AND instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}'
        AND holiday_change_id IN ('" . implode("','", $targetHolidayChanges) . "') ";
        $this->Execute_Query($updateHolidayChangeSQL);
      }
    }

    function disableResignDocInBetweenDate($startDatetime, $endDatetime) {
      $startDatetime = date('Y-m-d', strtotime($startDatetime));
      $endDatetime = date('Y-m-d', strtotime($endDatetime));
      // get waiting to approve resign docs
      $resignDocSQL = "SELECT resign_doc_id, employee_id, resign_doc_dt, approve_flag 
      FROM {$GLOBALS['instanceServer']['instance_server_dbn']}.payroll_resign_doc   
      WHERE server_id = '{$_REQUEST['server_id']}'
      AND instance_server_id = '{$_REQUEST['instance_server_id']}'
      AND instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}'
      AND resign_doc_dt BETWEEN '{$startDatetime}'  AND '{$endDatetime}'
      AND approve_flag NOT IN ('02', '03') ";
      $waitingApproveResignDocs = $this->_sqllists($resignDocSQL);

      if (sizeof($waitingApproveResignDocs) > 0) {
        $targetResignDocs = [];
        foreach ($waitingApproveResignDocs as $resignDoc) {
          $targetResignDocs[] = $resignDoc['resign_doc_id'];
        }
  
        // disable resign doc
        $updateResignDocSQL = "UPDATE {$GLOBALS['instanceServer']['instance_server_dbn']}.payroll_resign_doc  
        SET publish_flag = 'N' 
        WHERE server_id = '{$_REQUEST['server_id']}'
        AND instance_server_id = '{$_REQUEST['instance_server_id']}'
        AND instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}'
        AND resign_doc_id IN ('" . implode("','", $targetResignDocs) . "') ";
        $this->Execute_Query($updateResignDocSQL);
      }
    }
	
	}
			$masterSalaryReport_lists = array();
		$masterSalaryReportService = new MasterSalaryReportService();

?>