<?
include("AbstractEmployee.class.php");

class EmployeeService extends AbstractEmployee{

	function getEmployeeByOptCode($OptCode){
		$_sql = "	SELECT * FROM comp_employee _employee
						WHERE _employee.opt_code='{$OptCode}'
						AND _employee.line_user_id is not null
						AND _employee.server_id is not null
						AND _employee.instance_server_id is not null
						AND _employee.instance_server_channel_id is not null ";
		//return  $_sql;
		return $this->_sqlget($_sql);
	}

	function getNewEmployeeCode($plus){
		$year = date('Y');
		$_sql = "	SELECT * FROM comp_employee _employee
						WHERE SUBSTRING(_employee.created,1,4)='$year'  
						AND _employee.server_id='{$_REQUEST['server_id']}' 
						AND _employee.instance_server_id = '{$_REQUEST['instance_server_id']}' 
						AND _employee.instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}' ";
		//echo "$_sql";
		$lists = $this->_sqllists($_sql);
		$seq = sizeof($lists) + 1 + $plus;
		$nextSeq = str_pad($seq, 4, '0', STR_PAD_LEFT);
		$no = (date('y') + 43) . $nextSeq;

		$check = $this->getEmployeeByEmpCode($no);
		if($check['employee_id'] != ''){
			$no = $this->getNewEmployeeCode(($plus + 1));
		}

		return $no;

	}

	function getNewEmployeeCodeWithConfig($plus, $config ,$data){
		for ($i = 1; $i <= 4; $i++) {
			$key = "config_key_$i";
			$value = isset($config[$key]) ? $config[$key] : null;
			if($value == 'yearTh' || $value == 'year'){
				$year = date('Y');
				$condition .= "AND SUBSTRING(_employee.created,1,4)='$year' ";
			}
			if($value == 'yearTh_round' || $value == 'year_round'){
				$year = date('Y');
				if(strtoupper($config['round_end'])=="EOM"){
					$start_dt = $year."-01-01 00:00:00";
					$end_dt = date("Y-m-t", strtotime($year."-12-01"))." 23:59:59";
				}else{
					$start_dt = date('Y-m-d',strtotime($year."-12-".$config['round_start']." -1 year"))." 00:00:00";
					$end_dt = $year."-12-".$config['round_end']." 23:59:59";
				}
				$dt = date('Y-m-d H:i:s');
				if(strtotime($dt) > strtotime($end_dt)){
					$start_dt = $year."-12-".$config['round_start']." 00:00:00";
					$end_dt = date('Y-m-d',strtotime($year."-12-".$config['round_end']." +1 year"))." 23:59:59";
				}

				$condition .= "AND _employee.created BETWEEN '".$start_dt."' AND '".$end_dt."' ";
			}
			if($value == 'year_month' || $value == 'yearTh_month'){
				$year = date('Y-m');
				$condition .= "AND SUBSTRING(_employee.created,1,7)='$year' ";
			}
			if($value == 'year_month_round' || $value == 'yearTh_month_round'){
				$year_month = date('Y-m');
				if(strtoupper($config['round_end'])=="EOM"){
					$start_dt = $year_month."-01 00:00:00";
					$end_dt = date("Y-m-t", strtotime($year_month."-01"))." 23:59:59";
				}else{
					$start_dt = date('Y-m-d',strtotime($year_month."-".$config['round_start']." -1 month"))." 00:00:00";
					$end_dt = $year_month."-".$config['round_end']." 23:59:59";
				}
				$dt = date('Y-m-d H:i:s');
				if(strtotime($dt) > strtotime($end_dt)){
					$start_dt = $year_month."-".$config['round_start']." 00:00:00";
					$end_dt = date('Y-m-d',strtotime($year_month."-".$config['round_end']." +1 month"))." 23:59:59";
				}

				$condition .= "AND _employee.created BETWEEN '".$start_dt."' AND '".$end_dt."' ";
			}
			if($value == 'company'){
				$condition .= "AND _employee.company_id = '{$data['company_id']}' ";
			}
			if($value == 'branch_office'){
				$condition .= "AND _employee.branch_id = '{$data['branch_id']}' ";
			}
			if($value == 'department'){
				$condition .= "AND _employee.department_id = '{$data['department_id']}' ";
			}
		}

		$_sql = "	SELECT * FROM comp_employee _employee 
						WHERE _employee.server_id='{$_REQUEST['server_id']}'
						AND _employee.instance_server_id = '{$_REQUEST['instance_server_id']}' 
						AND _employee.instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}' ".$condition;
						
		$lists = $this->_sqllists($_sql);

		$seq = sizeof($lists) + 1 + $plus;

		for ($i = 1; $i <= 4; $i++) {
			$key = "config_key_$i";
			$value = isset($config[$key]) ? $config[$key] : null;
			if($value == 'yearTh'){
				$emp_code .= (date('y') + 43);
			}else if($value == 'yearTh_round'){
				$emp_code .= (substr($end_dt,2,2) + 43);
			}else if($value == 'year'){
				$emp_code .= date('y');
			}else if($value == 'year_round'){
				$emp_code .= substr($end_dt,2,2);
			}else if($value == 'yearTh_month'){
				$emp_code .= (date('y') + 43);
				$emp_code .= date('m');
			}else if($value == 'yearTh_month_round'){
				$emp_code .= (substr($end_dt,2,2) + 43);
				$emp_code .= substr($end_dt,5,2);
			}else if($value == 'year_month'){
				$emp_code .= date('y');
				$emp_code .= date('m');
			}else if($value == 'year_month_round'){
				$emp_code .= substr($end_dt,2,2);
				$emp_code .= substr($end_dt,5,2);
			}else if($value == 'company'){
				$sql_comp = "SELECT company_code FROM comp_company _company
						WHERE _company.company_id = '{$data['company_id']}' 
						AND _company.server_id='{$_REQUEST['server_id']}' 
						AND _company.instance_server_id = '{$_REQUEST['instance_server_id']}' 
						AND _company.instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}' ";
				$company_code = $this->_sqlget($sql_comp);
				$emp_code .= substr($company_code['company_code'],0,5);
			}else if($value == 'branch_office'){
				$sql_branch = "SELECT branch_code FROM comp_branch _branch
						WHERE _branch.branch_id = '{$data['branch_id']}' 
						AND _branch.server_id='{$_REQUEST['server_id']}' 
						AND _branch.instance_server_id = '{$_REQUEST['instance_server_id']}' 
						AND _branch.instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}' ";
				$branch_code = $this->_sqlget($sql_branch);
				$emp_code .= substr($branch_code['branch_code'],0,5);
			}else if($value == 'department'){
				$sql_depart = "SELECT department_code FROM comp_department _department
						WHERE _department.department_id = '{$data['department_id']}' 
						AND _department.server_id='{$_REQUEST['server_id']}' 
						AND _department.instance_server_id = '{$_REQUEST['instance_server_id']}' 
						AND _department.instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}' ";
				$department_code = $this->_sqlget($sql_depart);
				$emp_code .= substr($department_code['department_code'],0,5);
			}else if($value == 'emp_count_digit3'){
				$emp_code .= str_pad($seq, 3, '0', STR_PAD_LEFT);
			}else if($value == 'emp_count_digit4'){
				$emp_code .= str_pad($seq, 4, '0', STR_PAD_LEFT);
			}else if($value == 'emp_count_digit5'){
				$emp_code .= str_pad($seq, 5, '0', STR_PAD_LEFT);
			}else if($value == 'custom_code'){
				$new_key = (4 + $i);
				$desc = $config["config_key_$new_key"];
				$emp_code .= $desc;
			}
		}

		$check = $this->getEmployeeByEmpCode($emp_code);
		if($check['employee_id'] != ''){
			$emp_code = $this->getNewEmployeeCodeWithConfig(($plus + 1), $config, $data);
		}

		return $emp_code;

	}

	function getEmployeeByID($_employee_id){
		$_sql = "	SELECT *,IFNULL(_employee.photograph , 'images/userPlaceHolder.png') AS photograph FROM hms_api.comp_employee _employee 
						WHERE _employee.employee_id='{$_employee_id}'  
						AND _employee.server_id = '{$_REQUEST['server_id']}' 
						AND _employee.instance_server_id = '{$_REQUEST['instance_server_id']}' ";

		$lists = $this->_sqlget($_sql);

		unset($lists['auth_first']);
		unset($lists['auth_second']);

		return $lists;
	}
	function getEmployeeByIDForSignOut($_employee_id){
		$_sql = "	SELECT _user.user_id,_employee.*,IFNULL(_employee.photograph , 'images/userPlaceHolder.png') AS photograph 
					FROM hms_api.comp_employee _employee 
					INNER JOIN hms_api.suso_user _user ON (_employee.employee_id = _user.employee_id)
					WHERE _employee.employee_id='{$_employee_id}'  
					AND _employee.server_id = '{$_REQUEST['server_id']}' 
					AND _employee.instance_server_id = '{$_REQUEST['instance_server_id']}' ";
		$lists = $this->_sqlget($_sql);

		unset($lists['auth_first']);
		unset($lists['auth_second']);

		return $lists;
	}

	function getEmployeeByIDAndApprover($_employee_id, $check_step = true){
		$_sql = "	SELECT *,IFNULL(_employee.photograph , 'images/userPlaceHolder.png') AS photograph FROM hms_api.comp_employee _employee 
						WHERE _employee.employee_id='{$_employee_id}'  
						AND _employee.server_id = '{$_REQUEST['server_id']}' 
						AND _employee.instance_server_id = '{$_REQUEST['instance_server_id']}' ";

		$lists = $this->_sqlget($_sql);

		unset($lists['auth_first']);
		unset($lists['auth_second']);
		
		if($GLOBALS['instanceServer']['instance_server_dbn'] != ''){
			$approver_step = array("first","second","third","fourth","fifth");
			$approver_list = $this->getApproverList($_employee_id, true, $check_step);
	
			for($app_idx = 0; $app_idx < sizeof($approver_list); $app_idx++){
				$lists['auth_'.$approver_step[$approver_list[$app_idx]['approver_step']-1]] = $approver_list[$app_idx]['approver_employee_id'];
				$lists['auth_'.$approver_step[$approver_list[$app_idx]['approver_step']-1].'_id'] = $approver_list[$app_idx]['approver_employee_id'];
				$lists['auth_'.$approver_step[$approver_list[$app_idx]['approver_step']-1].'_code'] = $approver_list[$app_idx]['approver_employee_code'];
				// $lists['auth_'.$approver_step[$approver_list[$app_idx]['approver_step']-1].'_name'] = $_REQUEST['language_code'] == 'TH' ? $approver_list[$app_idx]['approver_employee_name'] : $approver_list[$app_idx]['approver_employee_name_en'];
				// $lists['auth_'.$approver_step[$approver_list[$app_idx]['approver_step']-1].'_last_name'] = $_REQUEST['language_code'] == 'TH' ? $approver_list[$app_idx]['approver_employee_last_name'] : $approver_list[$app_idx]['approver_employee_last_name_en'];
				// $lists['auth_'.$approver_step[$approver_list[$app_idx]['approver_step']-1].'_nickname'] = $_REQUEST['language_code'] == 'TH' ? $approver_list[$app_idx]['approver_employee_nickname'] : $approver_list[$app_idx]['approver_employee_nickname_en'];
				if($_REQUEST['language_code'] == 'TH'){
					$lists['auth_'.$approver_step[$approver_list[$app_idx]['approver_step']-1].'_name'] = $approver_list[$app_idx]['approver_employee_name'] ?? '';
					$lists['auth_'.$approver_step[$approver_list[$app_idx]['approver_step']-1].'_last_name'] = $approver_list[$app_idx]['approver_employee_last_name'] ?? '';
					$lists['auth_'.$approver_step[$approver_list[$app_idx]['approver_step']-1].'_nickname'] = $approver_list[$app_idx]['approver_employee_nickname'] ?? '';
				} else {
					if($approver_list[$app_idx]['approver_employee_name_en']){
						$lists['auth_'.$approver_step[$approver_list[$app_idx]['approver_step']-1].'_name'] = $approver_list[$app_idx]['approver_employee_name_en'] ?? '';
						$lists['auth_'.$approver_step[$approver_list[$app_idx]['approver_step']-1].'_last_name'] = $approver_list[$app_idx]['approver_employee_last_name_en'] ?? ''; 
						$lists['auth_'.$approver_step[$approver_list[$app_idx]['approver_step']-1].'_nickname'] = $approver_list[$app_idx]['approver_employee_nickname_en'] ?? '';
					} else {
						$lists['auth_'.$approver_step[$approver_list[$app_idx]['approver_step']-1].'_name'] = $approver_list[$app_idx]['approver_employee_name'] ?? '';
						$lists['auth_'.$approver_step[$approver_list[$app_idx]['approver_step']-1].'_last_name'] = $approver_list[$app_idx]['approver_employee_last_name'] ?? '';
						$lists['auth_'.$approver_step[$approver_list[$app_idx]['approver_step']-1].'_nickname'] = $approver_list[$app_idx]['approver_employee_nickname'] ?? '';
					}
				}
				
				$lists['auth_'.$approver_step[$approver_list[$app_idx]['approver_step']-1].'_name_en'] = $approver_list[$app_idx]['approver_employee_name_en'];
				$lists['auth_'.$approver_step[$approver_list[$app_idx]['approver_step']-1].'_last_name_en'] = $approver_list[$app_idx]['approver_employee_last_name_en'];
				$lists['auth_'.$approver_step[$approver_list[$app_idx]['approver_step']-1].'_nickname_en'] = $approver_list[$app_idx]['approver_employee_nickname_en'];
				$lists['auth_'.$approver_step[$approver_list[$app_idx]['approver_step']-1].'_photograph'] = $approver_list[$app_idx]['approver_photograph'];
				$lists['auth_'.$approver_step[$approver_list[$app_idx]['approver_step']-1].'_channel_id'] = $approver_list[$app_idx]['approver_channel_id'];
			}
		}

		return $lists;
	}
	function getEmployeeByIDAndCompany($_employee_id){
		$_sql = "	SELECT _employee.*,_company.company_name,_company.company_code,_branch.branch_name,_branch.branch_code,_department.department_name,_department.department_name_en,_position.position_name,_position.position_name_en,_position.position_code,IFNULL(_employee.photograph , 'images/userPlaceHolder.png') AS photograph FROM hms_api.comp_employee _employee 
					LEFT JOIN hms_api.comp_company _company ON(_employee.company_id = _company.company_id)
					LEFT JOIN hms_api.comp_branch _branch ON(_employee.branch_id = _branch.branch_id)
					LEFT JOIN hms_api.comp_department _department ON (_employee.department_id = _department.department_id)
					LEFT JOIN hms_api.comp_position _position ON (_employee.position_id = _position.position_id)
						WHERE _employee.employee_id='{$_employee_id}'  
						AND _employee.server_id = '{$_REQUEST['server_id']}' 
						AND _employee.instance_server_id = '{$_REQUEST['instance_server_id']}' ";
						// echo $_sql;
		$lists = $this->_sqlget($_sql);
		return $lists;
	}

	function getEmployeeByIDNonChannel($_employee_id){
		$_sql = "	SELECT *,IFNULL(_employee.photograph , 'images/userPlaceHolder.png') AS photograph FROM hms_api.comp_employee _employee 
						WHERE _employee.employee_id='{$_employee_id}'  
						AND _employee.server_id = '{$_REQUEST['server_id']}' 
						AND _employee.instance_server_id = '{$_REQUEST['instance_server_id']}'";
		// echo $_sql;
		$lists = $this->_sqlget($_sql);
		return $lists;
	}

	function getEmployeeInDomainByPositionCode($_position_list){
		$_sql = "SELECT _employee.*
		FROM hms_api.comp_employee _employee
		INNER JOIN hms_api.comp_position _pos ON (_employee.position_id = _pos.position_id)
		WHERE _employee.server_id = '{$_REQUEST['server_id']}'
		AND _employee.instance_server_id = '{$_REQUEST['instance_server_id']}'
		-- AND _employee.instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}'
		AND _pos.position_code IN ('".implode("','", $_position_list)."') ";
		$lists = $this->_sqllists($_sql);
		// echo $_sql;
		return $lists;
	}

	function getEmployeeNotAssignOrg(){
		$_sql = "UPDATE comp_employee SET branch_id = NULL WHERE branch_id NOT IN (SELECT branch_id FROM comp_branch WHERE sys_del_flag = 'N')
						AND server_id = '{$_REQUEST['server_id']}'  
						AND instance_server_id = '{$_REQUEST['instance_server_id']}'
						AND instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}' ";
		$this->Execute_Query($_sql);

		$_sql = "UPDATE comp_employee SET department_id = NULL WHERE department_id NOT IN (SELECT department_id FROM comp_department WHERE sys_del_flag = 'N')
						AND server_id = '{$_REQUEST['server_id']}'  
						AND instance_server_id = '{$_REQUEST['instance_server_id']}'
						AND instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}' ";
		$this->Execute_Query($_sql);

		$_sql = "UPDATE comp_employee SET division_id = NULL WHERE division_id NOT IN (SELECT division_id FROM comp_division WHERE sys_del_flag = 'N')
						AND server_id = '{$_REQUEST['server_id']}'  
						AND instance_server_id = '{$_REQUEST['instance_server_id']}'
						AND instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}' ";
		$this->Execute_Query($_sql);

		$_sql = "UPDATE comp_employee SET section_id = NULL WHERE section_id NOT IN (SELECT section_id FROM comp_section WHERE sys_del_flag = 'N')
						AND server_id = '{$_REQUEST['server_id']}'  
						AND instance_server_id = '{$_REQUEST['instance_server_id']}'
						AND instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}' ";
		$this->Execute_Query($_sql);

		$_sql = "UPDATE comp_employee SET section_lv01_id = NULL WHERE section_lv01_id NOT IN (SELECT section_lv01_id FROM comp_section_lv01 WHERE sys_del_flag = 'N')
						AND server_id = '{$_REQUEST['server_id']}'  
						AND instance_server_id = '{$_REQUEST['instance_server_id']}'
						AND instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}' ";
		$this->Execute_Query($_sql);

		$_sql = "UPDATE comp_employee SET section_lv02_id = NULL WHERE section_lv02_id NOT IN (SELECT section_lv02_id FROM comp_section_lv02 WHERE sys_del_flag = 'N')
						AND server_id = '{$_REQUEST['server_id']}'  
						AND instance_server_id = '{$_REQUEST['instance_server_id']}'
						AND instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}' ";
		$this->Execute_Query($_sql);

		$_sql = "UPDATE comp_employee SET section_lv03_id = NULL WHERE section_lv03_id NOT IN (SELECT section_lv03_id FROM comp_section_lv03 WHERE sys_del_flag = 'N')
						AND server_id = '{$_REQUEST['server_id']}'  
						AND instance_server_id = '{$_REQUEST['instance_server_id']}'
						AND instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}' ";
		$this->Execute_Query($_sql);

		$_sql = "UPDATE comp_employee SET section_lv04_id = NULL WHERE section_lv04_id NOT IN (SELECT section_lv04_id FROM comp_section_lv04 WHERE sys_del_flag = 'N')
						AND server_id = '{$_REQUEST['server_id']}'  
						AND instance_server_id = '{$_REQUEST['instance_server_id']}'
						AND instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}' ";
		$this->Execute_Query($_sql);

		$_sql = "UPDATE comp_employee SET section_lv05_id = NULL WHERE section_lv05_id NOT IN (SELECT section_lv05_id FROM comp_section_lv05 WHERE sys_del_flag = 'N')
						AND server_id = '{$_REQUEST['server_id']}'  
						AND instance_server_id = '{$_REQUEST['instance_server_id']}'
						AND instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}' ";
		$this->Execute_Query($_sql);

		$_sql = "	SELECT * FROM comp_employee _employee
						WHERE _employee.server_id = '{$_REQUEST['server_id']}'  
						AND _employee.instance_server_id = '{$_REQUEST['instance_server_id']}'
						AND _employee.instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}'
						AND (_employee.company_id IS NULL OR _employee.branch_id IS NULL OR _employee.department_id IS NULL OR 
						_employee.company_id='' OR _employee.branch_id='' OR _employee.department_id='') ";
		$lists = $this->_sqllists($_sql);
		return $lists;
	}

	function getEmployeeNotAssignPosition(){
		$_sql = "UPDATE comp_employee SET position_id = NULL WHERE position_id NOT IN (SELECT position_id FROM comp_position WHERE sys_del_flag = 'N')
						AND server_id = '{$_REQUEST['server_id']}'  
						AND instance_server_id = '{$_REQUEST['instance_server_id']}'
						AND instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}' ";
		$this->Execute_Query($_sql);

		$_sql = "	SELECT * FROM comp_employee _employee
						WHERE _employee.server_id = '{$_REQUEST['server_id']}'  
						AND _employee.instance_server_id = '{$_REQUEST['instance_server_id']}'
						AND _employee.instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}'
						AND (_employee.position_id IS NULL OR _employee.position_id='') ";
		$lists = $this->_sqllists($_sql);
		return $lists;
	}

	function getEmployeeByPin($_pin){
		$_sql = "	SELECT * FROM comp_employee _employee 
						WHERE _employee.pin_code='{$_pin}' 
						AND _employee.server_id = '{$_REQUEST['server_id']}'  
						AND _employee.instance_server_id = '{$_REQUEST['instance_server_id']}'
						AND _employee.instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}' ";
		$lists = $this->_sqlget($_sql);
		return $lists;
	}

	function getEmployeeByFacial($_person_id){
		$_sql = "	SELECT * FROM comp_employee _employee 
						WHERE _employee.person_id='{$_person_id}' 
						AND _employee.server_id = '{$_REQUEST['server_id']}'  
						AND _employee.instance_server_id = '{$_REQUEST['instance_server_id']}'
						AND _employee.instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}' ";
		$lists = $this->_sqlget($_sql);
		return $lists;
	}

	function getEmployeeByRFID($_rfid){
		$_sql = "	SELECT * FROM comp_employee _employee 
						WHERE _employee.rfid_code='{$_rfid}' 
						AND _employee.server_id = '{$_REQUEST['server_id']}' 
						AND _employee.instance_server_id = '{$_REQUEST['instance_server_id']}'
						AND _employee.instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}' ";
		//echo "$_sql";
		$lists = $this->_sqlget($_sql);
		return $lists;
	}

	function getEmployeeByEmpCode($_emp_code){
		$_sql = "	SELECT * FROM comp_employee _employee 
						WHERE _employee.employee_code='{$_emp_code}' 
						AND _employee.server_id = '{$_REQUEST['server_id']}' 
						AND _employee.instance_server_id = '{$_REQUEST['instance_server_id']}'
						AND _employee.instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}' ";
		//echo "$_sql";
		$lists = $this->_sqlget($_sql);
		return $lists;
	}

	function getEmployeeByIdNo($_id_no){
		$_id_no = str_replace('-', '', $_id_no);
		$_sql = "	SELECT * FROM comp_employee _employee 
						WHERE _employee.id_no='{$_id_no}' 
						AND _employee.server_id = '{$_REQUEST['server_id']}' 
						AND _employee.instance_server_id = '{$_REQUEST['instance_server_id']}'
						AND _employee.instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}' ";
		//echo "$_sql";
		$lists = $this->_sqlget($_sql);
		return $lists;
	}

	function getEmployeeByEmail($_emailaddress){
		$_sql = "	SELECT * FROM comp_employee _employee 
						WHERE _employee.emailaddress='{$_emailaddress}' 
						AND _employee.server_id = '{$_REQUEST['server_id']}' 
						AND _employee.instance_server_id = '{$_REQUEST['instance_server_id']}'
						AND _employee.instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}' ";
		//echo "$_sql";
		$lists = $this->_sqlget($_sql);
		return $lists;
	}

	function getEmployeeByPhone($_mobilephone){
		$_sql = "	SELECT * FROM comp_employee _employee 
						WHERE _employee.mobilephone='{$_mobilephone}' 
						AND _employee.server_id = '{$_REQUEST['server_id']}' 
						AND _employee.instance_server_id = '{$_REQUEST['instance_server_id']}'
						AND _employee.instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}' ";
		//echo "$_sql";
		$lists = $this->_sqlget($_sql);
		return $lists;
	}

	function getEmployeeByFingerscan($_finger_id){
		$_sql = "	SELECT * FROM comp_employee _employee 
						WHERE _employee.fing_code='{$_finger_id}' 
						AND _employee.server_id = '{$_REQUEST['server_id']}' 
						AND _employee.instance_server_id = '{$_REQUEST['instance_server_id']}'
						AND _employee.instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}' ";
		//echo "$_sql";
		$lists = $this->_sqlget($_sql);
		return $lists;
	}

	function getEmployeeByChannel(){
		$_sql = "SELECT * FROM comp_employee _employee  
				WHERE _employee.server_id = '{$_REQUEST['server_id']}'  
				AND _employee.instance_server_id = '{$_REQUEST['instance_server_id']}'  
				AND _employee.instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}' ";

		$lists = $this->_sqllists($_sql);
		return $lists;
	}

	function getEmployeeSignOut($_start_dt){
		$_sql = "	SELECT _user.user_id,_employee.* FROM hms_api.comp_employee _employee 
						LEFT JOIN hms_api.suso_user _user ON (_user.employee_id = _employee.employee_id)
						WHERE _employee.sys_del_flag='N' 
						AND _employee.signout_flag='Y' 
						AND _employee.out_dt IS NOT NULL 
						AND _employee.out_dt<'{$_start_dt}' 
						AND _employee.server_id = '{$_REQUEST['server_id']}' 
						AND _employee.instance_server_id = '{$_REQUEST['instance_server_id']}'
						AND _employee.instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}' ";
		//echo "$_sql";
		$lists = $this->_sqllists($_sql);

		return $lists;
	}

	function getEmployeeOrderDep(){
		$_sql = "	SELECT * FROM comp_employee _table WHERE _table.sys_del_flag='N' 
						AND _table.server_id = '{$_REQUEST['server_id']}' 
						AND _table.instance_server_id = '{$_REQUEST['instance_server_id']}'
						AND _table.instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}'
						ORDER BY	_table.department_id";
		//echo "$_sql";
		$lists = $this->_sqllists($_sql);
		$retLists = array();
		for ($i = 0; $i < sizeof($lists); $i++){
			$retLists[$i] = $lists[$i];
		}
		return $retLists;
	}

	function getListEmployeePositionLine($_employee_id){
		$_sql = "SELECT _position.*
						FROM comp_employee _employee
						INNER JOIN comp_position _position
						where _employee.sys_del_flag = 'N' 
						AND _employee.position_id = _position.position_id
						AND _employee.employee_id = '{$_employee_id}'
						AND _employee.server_id = '{$_REQUEST['server_id']}'
						AND _employee.instance_server_id = '{$_REQUEST['instance_server_id']}'
						AND _employee.instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}'	";
		$position = $this->_sqlget($_sql);

		if(empty($position))
			return null;

		// print_r($position);
		//echo "<hr>";
		if($position['position_sql'] != ''){
			$_sql = "SELECT emp.*
							FROM comp_employee emp
							INNER JOIN comp_company comp 
							INNER JOIN comp_branch br
							INNER JOIN comp_department dept
							INNER JOIN comp_position post
							WHERE emp.sys_del_flag = 'N'
							AND comp.company_id = emp.company_id 
							AND emp.branch_id = br.branch_id  
							AND emp.department_id = dept.department_id
							AND emp.position_id = post.position_id
							AND emp.position_id <> '{$position['position_id']}'
							AND emp.position_id in ({$position['position_sql']})
							AND emp.server_id = '{$_REQUEST['server_id']}'
							AND emp.instance_server_id = '{$_REQUEST['instance_server_id']}'
							AND emp.instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}' ";

			//echo "$_sql<hr>";
			return $this->_sqllists($_sql);
		} else {
			return null;
		}
	}
	function getListEmployeeIdAuthorize($_employee_id){
		$_sql = "SELECT _employee.employee_id
				FROM hms_api.comp_employee _employee 
				WHERE _employee.sys_del_flag = 'N' 
				AND _employee.employee_id!='{$_employee_id}' 
				AND _employee.server_id = '{$_REQUEST['server_id']}'
				AND _employee.instance_server_id = '{$_REQUEST['instance_server_id']}' 
				AND _employee.employee_id IN (
					SELECT employee_id 
					FROM {$GLOBALS['instanceServer']['instance_server_dbn']}.payroll_employee_approver 
					WHERE approver_id = '{$_employee_id}' 
					AND server_id = '{$_REQUEST['server_id']}'
					AND instance_server_id = '{$_REQUEST['instance_server_id']}'
				)";
// AND (_employee.auth_first='{$_employee_id}' OR _employee.auth_second='{$_employee_id}') 
		if($_REQUEST['_debug'] == 'Y')
			echo "$_sql<br>";
		return $this->_sqllists($_sql);
	}

	function getListEmployeeAuthorize($_employee_id){
		// $_sql = "SELECT _employee.*
		// 				FROM hms_api.comp_employee _employee
		// 				WHERE _employee.sys_del_flag = 'N' 
		// 				AND _employee.employee_id!='{$_employee_id}' 
		// 				AND (_employee.auth_first='{$_employee_id}' OR _employee.auth_second='{$_employee_id}') 
		// 				AND _employee.server_id = '{$_REQUEST['server_id']}'
		// 				AND _employee.instance_server_id = '{$_REQUEST['instance_server_id']}' ";
		$_sql = "SELECT employee_id  
		FROM {$GLOBALS['instanceServer']['instance_server_dbn']}.payroll_employee_approver  
		WHERE approver_id = '{$_employee_id}'  
		AND employee_id != '{$_employee_id}'  
		AND server_id = '{$_REQUEST['server_id']}' 
		AND instance_server_id = '{$_REQUEST['instance_server_id']}'";
		if($_REQUEST['_debug'] == 'Y')
			echo "$_sql<br>";
		return $this->_sqllists($_sql);
	}

	function getListEmployeeAuthorizeFirst($_employee_id){
		$_sql = "SELECT _employee.*
						FROM hms_api.comp_employee _employee
						WHERE _employee.sys_del_flag = 'N' 
						AND _employee.employee_id!='{$_employee_id}' 
						AND _employee.auth_first='{$_employee_id}' 
						AND _employee.server_id = '{$_REQUEST['server_id']}'
						AND _employee.instance_server_id = '{$_REQUEST['instance_server_id']}' ";
		if($_REQUEST['_debug'] == 'Y')
			echo "$_sql<br>";
		return $this->_sqllists($_sql);
	}

	function getListAuthorizeEmployee($_PARAM) 
	{
		$usergroup_code = array("SAL","SALINEX","SALBU","AUDIT","HRBU");
		$strOfarray = '';
		for ($i=0; $i<sizeof($usergroup_code); $i++) {
			$strOfarray .= "'".$usergroup_code[$i]."'";
			if ($i != (sizeof($usergroup_code) - 1)) {
				$strOfarray .= ",";
			}
		}

		$_sql = "SELECT _employee.employee_id
						, _employee.employee_code
						, _employee.employee_name
						, _employee.employee_last_name
						, _employee.employee_nickname
						, _employee.employee_name_en
						, _employee.employee_last_name_en
						, _employee.employee_nickname_en
						, _employee.auth_first
						, _employee.auth_second 
						, _user.user_secure_key 
						FROM hms_api.comp_employee _employee 
						LEFT JOIN (
							SELECT _t1.user_id
							, _t1.employee_id 
							, _t1.user_secure_key
							FROM hms_api.suso_user _t1 
							INNER JOIN hms_api.suso_user_usergroup _t2 ON (_t1.user_id = _t2.user_id) 
							INNER JOIN hms_api.suso_usergroup _t3 ON (_t2.usergroup_id = _t3.usergroup_id) 
							WHERE _t1.server_id = '{$_REQUEST['server_id']}' 
							AND _t1.instance_server_id = '{$_REQUEST['instance_server_id']}' 
							AND _t1.instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}' 
							AND _t3.usergroup_code IN ({$strOfarray}) 
							GROUP BY _t1.user_id 
						) _user ON (_employee.employee_id = _user.employee_id) 
						WHERE _employee.server_id = '{$_REQUEST['server_id']}' 
						AND _employee.instance_server_id = '{$_REQUEST['instance_server_id']}' 
						AND _employee.instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}' ";

		// $_sql = "SELECT _employee.employee_id
		// 				, _employee.employee_code
		// 				, _employee.employee_name
		// 				, _employee.employee_last_name
		// 				, _employee.employee_nickname
		// 				, _employee.employee_name_en
		// 				, _employee.employee_last_name_en
		// 				, _employee.employee_nickname_en
		// 				, _employee.auth_first
		// 				, _employee.auth_second 
		// 				, _user.user_secure_key 
		// 				FROM hms_api.comp_employee _employee 
		// 				INNER JOIN (
		// 					SELECT user_id, user_secure_key, employee_id
		// 					FROM hms_api.suso_user
		// 					WHERE server_id = '{$_REQUEST['server_id']}' 
		// 					AND instance_server_id = '{$_REQUEST['instance_server_id']}' 
		// 					AND instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}' 
		// 				) _user ON (_employee.employee_id = _user.employee_id) 
		// 				INNER JOIN (
		// 					SELECT user_id, usergroup_id 
		// 					FROM hms_api.suso_user_usergroup 
		// 					WHERE server_id = '{$_REQUEST['server_id']}' 
		// 					AND instance_server_id = '{$_REQUEST['instance_server_id']}' 
		// 					AND instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}' 
		// 				) _uugroup ON (_user.user_id = _uugroup.user_id) 
		// 				INNER JOIN (
		// 					SELECT usergroup_id, usergroup_code 
		// 					FROM hms_api.suso_usergroup 
		// 					WHERE server_id = '{$_REQUEST['server_id']}' 
		// 					AND instance_server_id = '{$_REQUEST['instance_server_id']}' 
		// 					AND instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}' 
		// 				) _ugroup ON (_uugroup.usergroup_id = _ugroup.usergroup_id) 
		// 				WHERE _employee.server_id = '{$_REQUEST['server_id']}' 
		// 				AND _employee.instance_server_id = '{$_REQUEST['instance_server_id']}' 
		// 				AND _employee.instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}' 
		// 				OR _ugroup.usergroup_code IN ({$strOfarray}) ";

		if (is_array($_PARAM["company_lists"]) && sizeof($_PARAM["company_lists"]) > 0) {
			$companyIds = "";
			for ($i = 0; $i < sizeof($_PARAM["company_lists"]); $i++) {
				if ($i == 0)
					$companyIds = "'" . base64_decode($_PARAM["company_lists"][$i]["id"]) . "' ";
				else
					$companyIds .= ", '" . base64_decode($_PARAM["company_lists"][$i]["id"]) . "' ";
			}
			$_sql .= "AND _employee.company_id IN ({$companyIds}) ";
		}

		if (is_array($_PARAM["branch_lists"]) && sizeof($_PARAM["branch_lists"]) > 0) {
			$branchIds = "";
			for ($i = 0; $i < sizeof($_PARAM["branch_lists"]); $i++) {
				if ($i == 0)
					$branchIds = "'" . base64_decode($_PARAM["branch_lists"][$i]["id"]) . "' ";
				else
					$branchIds .= ", '" . base64_decode($_PARAM["branch_lists"][$i]["id"]) . "' ";
			}
			$_branchSql = " _employee.department_id IN (SELECT department_id FROM hms_api.comp_department WHERE branch_id IN ({$branchIds}) 
							AND instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}' AND sys_del_flag='N') ";
		}

		if (is_array($_PARAM["department_lists"]) && sizeof($_PARAM["department_lists"]) > 0) {
			$departmentIds = "";
			for ($i = 0; $i < sizeof($_PARAM["department_lists"]); $i++) {
				if ($i == 0)
					$departmentIds = "'" . base64_decode($_PARAM["department_lists"][$i]["id"]) . "' ";
				else
					$departmentIds .= ", '" . base64_decode($_PARAM["department_lists"][$i]["id"]) . "' ";
			}
			$_departmentSql = "_employee.department_id IN ({$departmentIds}) ";
		}

		if (is_array($_PARAM["division_lists"]) && sizeof($_PARAM["division_lists"]) > 0) {
			$divisionIds = "";
			for ($i = 0; $i < sizeof($_PARAM["division_lists"]); $i++) {
				if ($i == 0) {
					$divisionIds = "'" . base64_decode($_PARAM["division_lists"][$i]["id"]) . "' ";
				} else {
					$divisionIds .= ", '" . base64_decode($_PARAM["division_lists"][$i]["id"]) . "' ";
				}
			}
			$_divisionSql = "_employee.division_id IN ({$divisionIds}) ";
		}

		if (is_array($_PARAM["section_lists"]) && sizeof($_PARAM["section_lists"]) > 0) {
			$sectionIds = "";
			for ($i = 0; $i < sizeof($_PARAM["section_lists"]); $i++) {
				if ($i == 0) {
					$sectionIds = "'" . base64_decode($_PARAM["section_lists"][$i]["id"]) . "' ";
				} else {
					$sectionIds .= ", '" . base64_decode($_PARAM["section_lists"][$i]["id"]) . "' ";
				}
			}
			$_sectionSql = "_employee.section_id IN ({$sectionIds}) ";
		}

		if (sizeof($_PARAM["branch_lists"]) > 0 && sizeof($_PARAM["department_lists"]) > 0) {
			$_sql .= "AND {$_branchSql} AND {$_departmentSql} ";
		} else if (sizeof($_PARAM["branch_lists"]) > 0 && sizeof($_PARAM["department_lists"]) == 0) {
			$_sql .= "AND {$_branchSql} ";
		} else if (sizeof($_PARAM["branch_lists"]) == 0 && sizeof($_PARAM["department_lists"]) > 0) {
			$_sql .= "AND {$_departmentSql}	";
		}

		if (sizeof($_PARAM["division_lists"]) > 0) {
			$_sql .= "AND {$_divisionSql} ";
		}

		if (sizeof($_PARAM["section_lists"]) > 0) {
			$_sql .= "AND {$_sectionSql} ";
		}

		if (is_array($_PARAM["position_lists"]) && sizeof($_PARAM["position_lists"]) > 0) {
			$positionIds = "";
			for ($i = 0; $i < sizeof($_PARAM["position_lists"]); $i++) {
				if ($i == 0)
					$positionIds = "'" . base64_decode($_PARAM["position_lists"][$i]["id"]) . "' ";
				else
					$positionIds .= ", '" . base64_decode($_PARAM["position_lists"][$i]["id"]) . "' ";
			}
			$_sql .= "AND _employee.position_id IN ({$positionIds}) ";
		}

		$_sql .= " GROUP BY _employee.employee_id 
						ORDER BY _employee.employee_code ";
		// echo $_sql;
		$lists = $this->_sqllists($_sql);
		return $lists;
	}

	// function checkAuthorize($login, $verify,$doc_type="")
	// {
	// 	if ($login['authorize_line'] == ''){
	// 		$login['authorize_line'] = false;
	// 	}
	// 	$result = false;
	// 	if($login['employee_id'] == null){
	// 		if($GLOBALS['userInfo']['user_type'] == 'admin-acp' || $GLOBALS['userInfo']['user_type'] == 'dealers' || $GLOBALS['userInfo']['user_type'] == 'admin-ccs' || $GLOBALS['userInfo']['user_type'] == 'ccs'){
	// 			$result = true;
	// 		}
	// 	} else {
	// 		$step = $this->configApproveStep($doc_type);
	// 		// echo json_encode($step)."<br>";
	// 		if($step=='1'){
	// 			// echo $verify['approve_flag']."=>".$login['employee_id']."=".$verify['employee_id']."=".$verify['auth_first']."=".$verify['auth_second']."<BR>";
	// 			if($login['employee_id'] != $verify['employee_id']){
	// 				if($verify['auth_first'] == $login['employee_id']){
	// 					if($verify['approve_flag'] != ''){
	// 						if($verify['approve_flag'] == '01'){
	// 							if($verify['auth_first'] == $login['employee_id']){
	// 								$result = true;
	// 							}else{
	// 								$result = false;
	// 							}
	// 						}else{
	// 							$result = false;
	// 						}
	// 					}else{
	// 						$result = true;
	// 					}
	// 				}else{
	// 					$result = false;
	// 				}

	// 				if(!$login['authorize_line']){
	// 					if($login['instance_server_channel_id'] == $verify['instance_server_channel_id']){
	// 						$auth = PageAuthorizeService::getAuthorizeByUserGroup(array("SAL", "SALBU", "AUDIT", "SALINEX", "HRBU"));
	// 						if($auth === true){
	// 							$result = true;
	// 						}
	// 					}
	// 				}
	// 			}else{
	// 				if(!$login['authorize_line']){
	// 					$auth = PageAuthorizeService::getAuthorizeByUserGroup(array("SAL"));
	// 					if($auth === true){
	// 						$result = true;
	// 					}
	// 				}
	// 			}
	// 		}else if($step=='HR'){
	// 			if($GLOBALS['userInfo']['user_type'] == 'admin-acp' || $GLOBALS['userInfo']['user_type'] == 'dealers' || $GLOBALS['userInfo']['user_type'] == 'admin-ccs' || $GLOBALS['userInfo']['user_type'] == 'ccs'){
	// 				$result = true;
	// 			}else{
	// 				$auth = PageAuthorizeService::getAuthorizeByUserGroup(array("SAL", "SALBU", "AUDIT", "SALINEX", "HRBU"));
	// 				if($auth === true){
	// 					$result = true;
	// 				}
	// 			}
	// 		}else{
	// 			// echo $verify['approve_flag']."=>".$login['employee_id']."=".$verify['employee_id']."=".$verify['auth_first']."=".$verify['auth_second']."<BR>";
	// 			if($login['employee_id'] != $verify['employee_id']){
	// 				if($verify['auth_first'] == $login['employee_id'] || $verify['auth_second'] == $login['employee_id']){
	// 					if($verify['approve_flag'] != ''){
	// 						if($verify['approve_flag'] == '04'){
	// 							if($verify['auth_second'] == $login['employee_id']){
	// 								$result = true;
	// 							}else{
	// 								$result = false;
	// 							}
	// 						}else if($verify['approve_flag'] == '01' || $verify['approve_flag'] == '05' || $verify['approve_flag'] == '02' || $verify['approve_flag'] == '03'){
	// 							if($verify['auth_first'] == $login['employee_id'] || $verify['auth_second'] == $login['employee_id']){
	// 								$result = true;
	// 							}else{
	// 								$result = false;
	// 							}
	// 						}else{
	// 							$result = false;
	// 						}
	// 					}else{
	// 						$result = true;
	// 					}
	// 				}else if($verify['auth_first'] == '' && $verify['auth_second'] == ''){
	// 					$result = false;
	// 				}else{
	// 					$result = false;
	// 				}

	// 				if(!$login['authorize_line']){
	// 					if($login['instance_server_channel_id'] == $verify['instance_server_channel_id']){
	// 						$auth = PageAuthorizeService::getAuthorizeByUserGroup(array("SAL", "SALBU", "AUDIT", "SALINEX", "HRBU"));
	// 						if($auth === true){
	// 							$result = true;
	// 						}
	// 					}
	// 				}
	// 			} else {
	// 				if(!$login['authorize_line']){
	// 					$auth = PageAuthorizeService::getAuthorizeByUserGroup(array("SAL", "SALBU", "AUDIT", "SALINEX", "HRBU"));
	// 					if($auth === true){
	// 						$result = true;
	// 					}
	// 				}
	// 			}
	// 		}
	// 	}

	// 	return $result;
	// }

	function checkAuthorize($login, $verify, $step = "", $doc_type = ""){
		if ($login['authorize_line'] == ''){
			$login['authorize_line'] = false;
		}
		$result = false;
		if($login['employee_id'] == null){
			if($GLOBALS['userInfo']['user_type'] == 'admin-acp' || $GLOBALS['userInfo']['user_type'] == 'dealers' || $GLOBALS['userInfo']['user_type'] == 'admin-dealers' || $GLOBALS['userInfo']['user_type'] == 'admin-ccs' || $GLOBALS['userInfo']['user_type'] == 'ccs'){
				$result = true;
			}
		} else {
			if($step == ''){
				$step = $this->configApproveStep($doc_type, $verify['instance_server_channel_id']);
			}
			if($login['employee_id'] == $verify['employee_id']){
				if(!$login['authorize_line']){
					$auth = PageAuthorizeService::getAuthorizeByUserGroup(array("SAL", "SALBU", "AUDIT", "SALINEX", "HRBU"));
					if($auth === true){
						$result = true;
					}
				}
			}else if(($step == 'HR' || $step == '')){
				if($GLOBALS['userInfo']['user_type'] == 'admin-acp' || $GLOBALS['userInfo']['user_type'] == 'dealers' || $GLOBALS['userInfo']['user_type'] == 'admin-dealers' || $GLOBALS['userInfo']['user_type'] == 'admin-ccs' || $GLOBALS['userInfo']['user_type'] == 'ccs'){
					$result = true;
				}else if(!$login['authorize_line']){
					$auth = PageAuthorizeService::getAuthorizeByUserGroup(array("SAL", "SALBU", "AUDIT", "SALINEX", "HRBU"));
					if($auth === true){
						$result = true;
					}
				}
			} else {
				if($step == '1'){
					if($verify['auth_first'] == $login['employee_id']){
						if($verify['approve_flag'] == '01'|| $verify['approve_flag'] == '05' || $verify['approve_flag'] == '02' || $verify['approve_flag'] == '03'){
							$result = true;
						}else{
							$result = false;
						}
					}else{
						$result = false;
					}

					if(!$login['authorize_line']){
						if($login['instance_server_channel_id'] == $verify['instance_server_channel_id']){
							$auth = PageAuthorizeService::getAuthorizeByUserGroup(array("SAL", "SALBU", "AUDIT", "SALINEX", "HRBU"));
							if($auth === true){
								$result = true;
							}
						}
					}
				}else if($step == '2') {
					if($verify['auth_first'] == $login['employee_id'] || $verify['auth_second'] == $login['employee_id']){
						if($verify['approve_flag'] == '04'){
							if($verify['auth_second'] == $login['employee_id']){
								$result = true;
							}else{
								$result = false;
							}
						}else if($verify['approve_flag'] == '01' || $verify['approve_flag'] == '05' || $verify['approve_flag'] == '02' || $verify['approve_flag'] == '03'){
							$result = true;
						}else{
							$result = false;
						}
					}else{
						$result = false;
					}

					if(!$login['authorize_line']){
						if($login['instance_server_channel_id'] == $verify['instance_server_channel_id']){
							$auth = PageAuthorizeService::getAuthorizeByUserGroup(array("SAL", "SALBU", "AUDIT", "SALINEX", "HRBU"));
							if($auth === true){
								$result = true;
							}
						}
					}
				}else if($step == '3') {
					if($verify['auth_first'] == $login['employee_id'] || $verify['auth_second'] == $login['employee_id'] || $verify['auth_third'] == $login['employee_id']){
						if($verify['approve_flag'] == '04'){
							if($verify['auth_second'] == $login['employee_id'] || $verify['auth_third'] == $login['employee_id']){
								$result = true;
							}else{
								$result = false;
							}
						}else if($verify['approve_flag'] == '06'){
							if($verify['auth_third'] == $login['employee_id']){
								$result = true;
							}else{
								$result = false;
							}
						}else if($verify['approve_flag'] == '01' || $verify['approve_flag'] == '05' || $verify['approve_flag'] == '02' || $verify['approve_flag'] == '03'){
							$result = true;
						}else{
							$result = false;
						}
					}else{
						$result = false;
					}

					if(!$login['authorize_line']){
						if($login['instance_server_channel_id'] == $verify['instance_server_channel_id']){
							$auth = PageAuthorizeService::getAuthorizeByUserGroup(array("SAL", "SALBU", "AUDIT", "SALINEX", "HRBU"));
							if($auth === true){
								$result = true;
							}
						}
					}
				}else if($step == '4') {
					if($verify['auth_first'] == $login['employee_id'] || $verify['auth_second'] == $login['employee_id'] || $verify['auth_third'] == $login['employee_id'] || $verify['auth_fourth'] == $login['employee_id']){
						if($verify['approve_flag'] == '04'){
							if($verify['auth_second'] == $login['employee_id'] || $verify['auth_third'] == $login['employee_id'] || $verify['auth_fourth'] == $login['employee_id']){
								$result = true;
							}else{
								$result = false;
							}
						}else if($verify['approve_flag'] == '06'){
							if($verify['auth_third'] == $login['employee_id'] || $verify['auth_fourth'] == $login['employee_id']){
								$result = true;
							}else{
								$result = false;
							}
						}else if($verify['approve_flag'] == '07'){
							if($verify['auth_fourth'] == $login['employee_id']){
								$result = true;
							}else{
								$result = false;
							}
						}else if($verify['approve_flag'] == '01' || $verify['approve_flag'] == '05' || $verify['approve_flag'] == '02' || $verify['approve_flag'] == '03'){
							$result = true;
						}else{
							$result = false;
						}
					}else{
						$result = false;
					}

					if(!$login['authorize_line']){
						if($login['instance_server_channel_id'] == $verify['instance_server_channel_id']){
							$auth = PageAuthorizeService::getAuthorizeByUserGroup(array("SAL", "SALBU", "AUDIT", "SALINEX", "HRBU"));
							if($auth === true){
								$result = true;
							}
						}
					}
				}else if($step == '5') {
					// echo $verify['approve_flag']." => ".$login['employee_id'] . " => ". $verify['auth_third']."<br>";
					if($verify['auth_first'] == $login['employee_id'] || $verify['auth_second'] == $login['employee_id'] || $verify['auth_third'] == $login['employee_id'] || $verify['auth_fourth'] == $login['employee_id'] || $verify['auth_fifth'] == $login['employee_id']){
						if($verify['approve_flag'] == '04'){
							if($verify['auth_second'] == $login['employee_id'] || $verify['auth_third'] == $login['employee_id'] || $verify['auth_fourth'] == $login['employee_id'] || $verify['auth_fifth'] == $login['employee_id']){
								$result = true;
							}else{
								$result = false;
							}
						}else if($verify['approve_flag'] == '06'){
							if($verify['auth_third'] == $login['employee_id'] || $verify['auth_fourth'] == $login['employee_id'] || $verify['auth_fifth'] == $login['employee_id']){
								$result = true;
							}else{
								$result = false;
							}
						}else if($verify['approve_flag'] == '07'){
							if($verify['auth_fourth'] == $login['employee_id'] || $verify['auth_fifth'] == $login['employee_id']){
								$result = true;
							}else{
								$result = false;
							}
						}else if($verify['approve_flag'] == '08'){
							if($verify['auth_fifth'] == $login['employee_id']){
								$result = true;
							}else{
								$result = false;
							}
						}else if($verify['approve_flag'] == '01' || $verify['approve_flag'] == '05' || $verify['approve_flag'] == '02' || $verify['approve_flag'] == '03'){
							$result = true;
						}else{
							$result = false;
						}
					}else{
						$result = false;
					}

					if(!$login['authorize_line']){
						if($login['instance_server_channel_id'] == $verify['instance_server_channel_id']){
							$auth = PageAuthorizeService::getAuthorizeByUserGroup(array("SAL", "SALBU", "AUDIT", "SALINEX", "HRBU"));
							if($auth === true){
								$result = true;
							}
						}
					}
				}
			}
		}

		return $result;
	}

	function checkAuthorize2NewDoc($login, $verify, $step = '', $flag_type, $doc_type){
		// echo $step."<br>";
		if ($login['authorize_line'] == ''){
			$login['authorize_line'] = false;
		}
		$result = false;
		if($login['employee_id'] == null){
			if($GLOBALS['userInfo']['user_type'] == 'admin-acp' || $GLOBALS['userInfo']['user_type'] == 'dealers' || $GLOBALS['userInfo']['user_type'] == 'admin-ccs' || $GLOBALS['userInfo']['user_type'] == 'ccs'){
				$result = true;
			}
		} else {
			if($step == ''){
				$step = $this->configApproveStep($doc_type, $verify['instance_server_channel_id']);
			}
			// echo $step."<br>";
			if($login['employee_id'] == $verify['employee_id']){
				if(!$login['authorize_line']){
					$auth = PageAuthorizeService::getAuthorizeByUserGroup(array("SAL"));
					if($auth === true){
						$result = true;
					}
				}
			}else if(($step == 'HR' || $step == '') && !$login['authorize_line']){
				if($GLOBALS['userInfo']['user_type'] == 'admin-acp' || $GLOBALS['userInfo']['user_type'] == 'dealers' || $GLOBALS['userInfo']['user_type'] == 'admin-ccs' || $GLOBALS['userInfo']['user_type'] == 'ccs'){
					$result = true;
				}else{
					$auth = PageAuthorizeService::getAuthorizeByUserGroup(array("SAL", "SALBU", "AUDIT", "SALINEX", "HRBU"));
					if($auth === true){
						$result = true;
					}
				}
			} else {
				if($step == '1'){
					if($verify['auth_first'] == $login['employee_id']){
						if($verify[$flag_type] == '01' || $verify[$flag_type] == '05' || $verify[$flag_type] == '02' || $verify[$flag_type] == '03' || $verify[$flag_type] == '04' || $verify[$flag_type] == '12'){
							$result = true;
						}else{
							$result = false;
						}
					}else{
						$result = false;
					}

					if(!$login['authorize_line']){
						if($login['instance_server_channel_id'] == $verify['instance_server_channel_id']){
							$auth = PageAuthorizeService::getAuthorizeByUserGroup(array("SAL", "SALBU", "AUDIT", "SALINEX", "HRBU"));
							if($auth === true){
								$result = true;
							}
						}
					}
				}else if($step == '2') {
					if($verify['auth_first'] == $login['employee_id'] || $verify['auth_second'] == $login['employee_id']){
						if($verify[$flag_type] == '07'){
							if($verify['auth_second'] == $login['employee_id']){
								$result = true;
							}else{
								$result = false;
							}
						}else if($verify[$flag_type] == '01' || $verify[$flag_type] == '05' || $verify[$flag_type] == '02' || $verify[$flag_type] == '03' || $verify[$flag_type] == '04' || $verify[$flag_type] == '06' || $verify[$flag_type] == '12' ){
							$result = true;
						}else{
							$result = false;
						}
					}else{
						$result = false;
					}

					if(!$login['authorize_line']){
						if($login['instance_server_channel_id'] == $verify['instance_server_channel_id']){
							$auth = PageAuthorizeService::getAuthorizeByUserGroup(array("SAL", "SALBU", "AUDIT", "SALINEX", "HRBU"));
							if($auth === true){
								$result = true;
							}
						}
					}
				}else if($step == '3') {
					if($verify['auth_first'] == $login['employee_id'] || $verify['auth_second'] == $login['employee_id'] || $verify['auth_third'] == $login['employee_id']){
						if($verify[$flag_type] == '07'){
							if($verify['auth_second'] == $login['employee_id'] || $verify['auth_third'] == $login['employee_id']){
								$result = true;
							}else{
								$result = false;
							}
						}else if($verify[$flag_type] == '08'){
							if($verify['auth_third'] == $login['employee_id']){
								$result = true;
							}else{
								$result = false;
							}
						}else if($verify[$flag_type] == '01' || $verify[$flag_type] == '05' || $verify[$flag_type] == '02' || $verify[$flag_type] == '03' || $verify[$flag_type] == '04' || $verify[$flag_type] == '06' || $verify[$flag_type] == '12'){
							$result = true;
						}else{
							$result = false;
						}
					}else{
						$result = false;
					}

					if(!$login['authorize_line']){
						if($login['instance_server_channel_id'] == $verify['instance_server_channel_id']){
							$auth = PageAuthorizeService::getAuthorizeByUserGroup(array("SAL", "SALBU", "AUDIT", "SALINEX", "HRBU"));
							if($auth === true){
								$result = true;
							}
						}
					}
				}else if($step == '4') {
					if($verify['auth_first'] == $login['employee_id'] || $verify['auth_second'] == $login['employee_id'] || $verify['auth_third'] == $login['employee_id'] || $verify['auth_fourth'] == $login['employee_id']){
						if($verify[$flag_type] == '07'){
							if($verify['auth_second'] == $login['employee_id'] || $verify['auth_third'] == $login['employee_id'] || $verify['auth_fourth'] == $login['employee_id']){
								$result = true;
							}else{
								$result = false;
							}
						}else if($verify[$flag_type] == '08'){
							if($verify['auth_third'] == $login['employee_id'] || $verify['auth_fourth'] == $login['employee_id']){
								$result = true;
							}else{
								$result = false;
							}
						}else if($verify[$flag_type] == '09'){
							if($verify['auth_fourth'] == $login['employee_id']){
								$result = true;
							}else{
								$result = false;
							}
						}else if($verify[$flag_type] == '01' || $verify[$flag_type] == '05' || $verify[$flag_type] == '02' || $verify[$flag_type] == '03' || $verify[$flag_type] == '04' || $verify[$flag_type] == '06' || $verify[$flag_type] == '12'){
							$result = true;
						}else{
							$result = false;
						}
					}else{
						$result = false;
					}

					if(!$login['authorize_line']){
						if($login['instance_server_channel_id'] == $verify['instance_server_channel_id']){
							$auth = PageAuthorizeService::getAuthorizeByUserGroup(array("SAL", "SALBU", "AUDIT", "SALINEX", "HRBU"));
							if($auth === true){
								$result = true;
							}
						}
					}
				}else if($step == '5') {
					// echo $verify[$flag_type]." => ".$verify['auth_first'];
					if($verify['auth_first'] == $login['employee_id'] || $verify['auth_second'] == $login['employee_id'] || $verify['auth_third'] == $login['employee_id'] || $verify['auth_fourth'] == $login['employee_id'] || $verify['auth_fifth'] == $login['employee_id']){
						if($verify[$flag_type] == '07'){
							if($verify['auth_second'] == $login['employee_id'] || $verify['auth_third'] == $login['employee_id'] || $verify['auth_fourth'] == $login['employee_id'] || $verify['auth_fifth'] == $login['employee_id']){
								$result = true;
							}else{
								$result = false;
							}
						}else if($verify[$flag_type] == '08'){
							// echo json_encode($verify['auth_first'] == $login['employee_id'])."<br>";
							if($verify['auth_third'] == $login['employee_id'] || $verify['auth_fourth'] == $login['employee_id'] || $verify['auth_fifth'] == $login['employee_id']){
								$result = true;
							}else{
								$result = false;
							}
						}else if($verify[$flag_type] == '09'){
							if($verify['auth_fourth'] == $login['employee_id'] || $verify['auth_fifth'] == $login['employee_id']){
								$result = true;
							}else{
								$result = false;
							}
						}else if($verify[$flag_type] == '10'){
							if($verify['auth_fifth'] == $login['employee_id']){
								$result = true;
							}else{
								$result = false;
							}
						}else if($verify[$flag_type] == '01' || $verify[$flag_type] == '05' || $verify[$flag_type] == '02' || $verify[$flag_type] == '03' || $verify[$flag_type] == '04' || $verify[$flag_type] == '06' || $verify[$flag_type] == '12'){
							$result = true;
						}else{
							$result = false;
						}
					}else{
						$result = false;
					}

					if(!$login['authorize_line']){
						if($login['instance_server_channel_id'] == $verify['instance_server_channel_id']){
							$auth = PageAuthorizeService::getAuthorizeByUserGroup(array("SAL", "SALBU", "AUDIT", "SALINEX", "HRBU"));
							if($auth === true){
								$result = true;
							}
						}
					}
				}
			}
		}

		return $result;
	}

	function checkAuthorize2Type($login, $verify, $key_type,$step = '', $doc_type = ''){
		if ($login['authorize_line'] == ''){
			$login['authorize_line'] = false;
		}
		$result = false;
		if($login['employee_id'] == null){
			if($GLOBALS['userInfo']['user_type'] == 'admin-acp' || $GLOBALS['userInfo']['user_type'] == 'dealers' || $GLOBALS['userInfo']['user_type'] == 'admin-ccs' || $GLOBALS['userInfo']['user_type'] == 'ccs'){
				$result = true;
			}
		} else {
			if($step == ''){
				$step = $this->configApproveStep($doc_type, $verify['instance_server_channel_id']);
			}
			
			if($login['employee_id'] == $verify['employee_id']){
				if(!$login['authorize_line']){
					$auth = PageAuthorizeService::getAuthorizeByUserGroup(array("SAL"));
					if($auth === true){
						$result = true;
					}
				}
			}else if(($step == 'HR' || $step == '') && !$login['authorize_line']){
				if($GLOBALS['userInfo']['user_type'] == 'admin-acp' || $GLOBALS['userInfo']['user_type'] == 'dealers' || $GLOBALS['userInfo']['user_type'] == 'admin-ccs' || $GLOBALS['userInfo']['user_type'] == 'ccs'){
					$result = true;
				}else{
					$auth = PageAuthorizeService::getAuthorizeByUserGroup(array("SAL", "SALBU", "AUDIT", "SALINEX", "HRBU"));
					if($auth === true){
						$result = true;
					}
				}
			} else {
				if($step == '1'){
					if($verify['auth_first'] == $login['employee_id']){
						// echo ($verify[$key_type]);
						if($verify[$key_type] == '01' || $verify[$key_type] == '07'){
							$result = true;
						}else{
							$result = false;
						}
					}else{
						$result = false;
					}

					if(!$login['authorize_line']){
						if($login['instance_server_channel_id'] == $verify['instance_server_channel_id']){
							$auth = PageAuthorizeService::getAuthorizeByUserGroup(array("SAL", "SALBU", "AUDIT", "SALINEX", "HRBU"));
							if($auth === true){
								$result = true;
							}
						}
					}
				}else if($step == '2') {
					// echo json_encode($verify, JSON_UNESCAPED_UNICODE)."<br>";
					if($verify['auth_first'] == $login['employee_id'] || $verify['auth_second'] == $login['employee_id']){
						if($verify[$key_type] == '04'){
							if($verify['auth_second'] == $login['employee_id']){
								$result = true;
							}else{
								$result = false;
							}
						}else if($verify[$key_type] == '01' || $verify[$key_type] == '05' || $verify[$key_type] == '02' || $verify[$key_type] == '03'){
							$result = true;
						}else{
							$result = false;
						}
					}else{
						$result = false;
					}

					if(!$login['authorize_line']){
						if($login['instance_server_channel_id'] == $verify['instance_server_channel_id']){
							$auth = PageAuthorizeService::getAuthorizeByUserGroup(array("SAL", "SALBU", "AUDIT", "SALINEX", "HRBU"));
							if($auth === true){
								$result = true;
							}
						}
					}
				}else if($step == '3') {
					if($verify['auth_first'] == $login['employee_id'] || $verify['auth_second'] == $login['employee_id'] || $verify['auth_third'] == $login['employee_id']){
						// echo $verify['auth_first'] ." == ". $login['employee_id'] ."<br>";
						if($verify[$key_type] == '04'){
							if($verify['auth_second'] == $login['employee_id'] || $verify['auth_third'] == $login['employee_id']){
								$result = true;
							}else{
								$result = false;
							}
						}else if($verify[$key_type] == '06'){
							if($verify['auth_third'] == $login['employee_id']){
								$result = true;
							}else{
								$result = false;
							}
						}else if($verify[$key_type] == '01' || $verify[$key_type] == '05' || $verify[$key_type] == '02' || $verify[$key_type] == '03'){
							$result = true;
						}else{
							$result = false;
						}
					}else{
						$result = false;
					}

					if(!$login['authorize_line']){
						if($login['instance_server_channel_id'] == $verify['instance_server_channel_id']){
							$auth = PageAuthorizeService::getAuthorizeByUserGroup(array("SAL", "SALBU", "AUDIT", "SALINEX", "HRBU"));
							if($auth === true){
								$result = true;
							}
						}
					}
				}else if($step == '4') {
					if($verify['auth_first'] == $login['employee_id'] || $verify['auth_second'] == $login['employee_id'] || $verify['auth_third'] == $login['employee_id'] || $verify['auth_fourth'] == $login['employee_id']){
						if($verify[$key_type] == '04'){
							if($verify['auth_second'] == $login['employee_id'] || $verify['auth_third'] == $login['employee_id'] || $verify['auth_fourth'] == $login['employee_id']){
								$result = true;
							}else{
								$result = false;
							}
						}else if($verify[$key_type] == '06'){
							if($verify['auth_third'] == $login['employee_id'] || $verify['auth_fourth'] == $login['employee_id']){
								$result = true;
							}else{
								$result = false;
							}
						}else if($verify[$key_type] == '07'){
							if($verify['auth_fourth'] == $login['employee_id']){
								$result = true;
							}else{
								$result = false;
							}
						}else if($verify[$key_type] == '01' || $verify[$key_type] == '05' || $verify[$key_type] == '02' || $verify[$key_type] == '03'){
							$result = true;
						}else{
							$result = false;
						}
					}else{
						$result = false;
					}

					if(!$login['authorize_line']){
						if($login['instance_server_channel_id'] == $verify['instance_server_channel_id']){
							$auth = PageAuthorizeService::getAuthorizeByUserGroup(array("SAL", "SALBU", "AUDIT", "SALINEX", "HRBU"));
							if($auth === true){
								$result = true;
							}
						}
					}
				}else if($step == '5') {
					// echo $verify['auth_fifth'] ." => ". $login['employee_id']."<br>";
					if($verify['auth_first'] == $login['employee_id'] || $verify['auth_second'] == $login['employee_id'] || $verify['auth_third'] == $login['employee_id'] || $verify['auth_fourth'] == $login['employee_id'] || $verify['auth_fifth'] == $login['employee_id']){
						if($verify[$key_type] == '04'){
							if($verify['auth_second'] == $login['employee_id'] || $verify['auth_third'] == $login['employee_id'] || $verify['auth_fourth'] == $login['employee_id'] || $verify['auth_fifth'] == $login['employee_id']){
								$result = true;
							}else{
								$result = false;
							}
						}else if($verify[$key_type] == '06'){
							if($verify['auth_third'] == $login['employee_id'] || $verify['auth_fourth'] == $login['employee_id'] || $verify['auth_fifth'] == $login['employee_id']){
								$result = true;
							}else{
								$result = false;
							}
						}else if($verify[$key_type] == '07'){
							if($verify['auth_fourth'] == $login['employee_id'] || $verify['auth_fifth'] == $login['employee_id']){
								$result = true;
							}else{
								$result = false;
							}
						}else if($verify[$key_type] == '08'){
							if($verify['auth_fifth'] == $login['employee_id']){
								$result = true;
							}else{
								$result = false;
							}
						}else if($verify[$key_type] == '01' || $verify[$key_type] == '05' || $verify[$key_type] == '02' || $verify[$key_type] == '03'){
							$result = true;
						}else{
							$result = false;
						}
					}else{
						$result = false;
					}

					if(!$login['authorize_line']){
						if($login['instance_server_channel_id'] == $verify['instance_server_channel_id']){
							$auth = PageAuthorizeService::getAuthorizeByUserGroup(array("SAL", "SALBU", "AUDIT", "SALINEX", "HRBU"));
							if($auth === true){
								$result = true;
							}
						}
					}
				}
			}
		}
		return $result;
	}

	function checkAuthorize2Complaint($login, $verify, $key_type,$step = '', $doc_type = ''){
		if ($login['authorize_line'] == ''){
			$login['authorize_line'] = false;
		}
		$result = false;
		if($login['employee_id'] == null){
			if($GLOBALS['userInfo']['user_type'] == 'admin-acp' || $GLOBALS['userInfo']['user_type'] == 'dealers' || $GLOBALS['userInfo']['user_type'] == 'admin-ccs' || $GLOBALS['userInfo']['user_type'] == 'ccs'){
				$result = true;
			}
		} else {
			if($step == ''){
				$step = $this->configApproveStep($doc_type);
			}
			if($login['employee_id'] == $verify['employee_id']){
				if(!$login['authorize_line']){
					$auth = PageAuthorizeService::getAuthorizeByUserGroup(array("SAL"));
					if($auth === true){
						$result = true;
					}
				}
			}else if(($step == 'HR' || $step == '')){
				if($GLOBALS['userInfo']['user_type'] == 'admin-acp' || $GLOBALS['userInfo']['user_type'] == 'dealers' || $GLOBALS['userInfo']['user_type'] == 'admin-ccs' || $GLOBALS['userInfo']['user_type'] == 'ccs'){
					$result = true;
				}else{
					$auth = PageAuthorizeService::getAuthorizeByUserGroup(array("SAL", "SALBU", "AUDIT", "SALINEX", "HRBU"));
					if($auth === true){
						$result = true;
					}
				}
			} else {
				if($step == '1'){
					if($verify['auth_first'] == $login['employee_id']){
						if($verify[$key_type] == '01' || $verify[$key_type] == '08'){
							$result = true;
						}else{
							$result = false;
						}
					}else{
						$result = false;
					}

					if(!$login['authorize_line']){
						if($login['instance_server_channel_id'] == $verify['instance_server_channel_id']){
							$auth = PageAuthorizeService::getAuthorizeByUserGroup(array("SAL", "SALBU", "AUDIT", "SALINEX", "HRBU"));
							if($auth === true){
								$result = true;
							}
						}
					}
				}else if($step == '2') {
					// echo json_encode($verify, JSON_UNESCAPED_UNICODE)."<br>";
					if($verify['auth_first'] == $login['employee_id'] || $verify['auth_second'] == $login['employee_id']){
						if($verify[$key_type] == '03'){
							if($verify['auth_second'] == $login['employee_id']){
								$result = true;
							}else{
								$result = false;
							}
						}else if($verify[$key_type] == '01' || $verify[$key_type] == '05' || $verify[$key_type] == '02'){
							$result = true;
						}else if($verify[$key_type] == '08'){
							if($verify['auth_second'] == $login['employee_id']){
								$result = true;
							}else if(empty($verify['auth_second'])){
								if($verify['auth_first'] == $login['employee_id']){
									$result = true;
								}else{
									$result = false;
								}
							}else{
								$result = false;
							}
						}else{
							$result = false;
						}
					}else{
						$result = false;
					}

					if(!$login['authorize_line']){
						if($login['instance_server_channel_id'] == $verify['instance_server_channel_id']){
							$auth = PageAuthorizeService::getAuthorizeByUserGroup(array("SAL", "SALBU", "AUDIT", "SALINEX", "HRBU"));
							if($auth === true){
								$result = true;
							}
						}
					}
				}else if($step == '3') {
					if($verify['auth_first'] == $login['employee_id'] || $verify['auth_second'] == $login['employee_id'] || $verify['auth_third'] == $login['employee_id']){
						// echo $verify['auth_first'] ." == ". $login['employee_id'] ."<br>";
						if($verify[$key_type] == '03'){
							if($verify['auth_second'] == $login['employee_id'] || $verify['auth_third'] == $login['employee_id']){
								$result = true;
							}else{
								$result = false;
							}
						}else if($verify[$key_type] == '04'){
							if($verify['auth_third'] == $login['employee_id']){
								$result = true;
							}else{
								$result = false;
							}
						}else if($verify[$key_type] == '01' || $verify[$key_type] == '05' || $verify[$key_type] == '02'){
							$result = true;
						}else if($verify[$key_type] == '08'){
							if($verify['auth_third'] == $login['employee_id']){
								$result = true;
							}else if(empty($verify['auth_third'])){
								if($verify['auth_second'] == $login['employee_id']){
									$result = true;
								}else if(empty($verify['auth_second'])){
									if($verify['auth_first'] == $login['employee_id']){
										$result = true;
									}else{
										$result = false;
									}
								}else{
									$result = false;
								}
							}else{
								$result = false;
							}
						}else{
							$result = false;
						}
					}else{
						$result = false;
					}

					if(!$login['authorize_line']){
						if($login['instance_server_channel_id'] == $verify['instance_server_channel_id']){
							$auth = PageAuthorizeService::getAuthorizeByUserGroup(array("SAL", "SALBU", "AUDIT", "SALINEX", "HRBU"));
							if($auth === true){
								$result = true;
							}
						}
					}
				}else if($step == '4') {
					if($verify['auth_first'] == $login['employee_id'] || $verify['auth_second'] == $login['employee_id'] || $verify['auth_third'] == $login['employee_id'] || $verify['auth_fourth'] == $login['employee_id']){
						if($verify[$key_type] == '03'){
							if($verify['auth_second'] == $login['employee_id'] || $verify['auth_third'] == $login['employee_id'] || $verify['auth_fourth'] == $login['employee_id']){
								$result = true;
							}else{
								$result = false;
							}
						}else if($verify[$key_type] == '04'){
							if($verify['auth_third'] == $login['employee_id'] || $verify['auth_fourth'] == $login['employee_id']){
								$result = true;
							}else{
								$result = false;
							}
						}else if($verify[$key_type] == '06'){
							if($verify['auth_fourth'] == $login['employee_id']){
								$result = true;
							}else{
								$result = false;
							}
						}else if($verify[$key_type] == '01' || $verify[$key_type] == '05' || $verify[$key_type] == '02'){
							$result = true;
						}else if($verify[$key_type] == '08'){
							if($verify['auth_fourth'] == $login['employee_id']){
								$result = true;
							}else if(empty($verify['auth_fourth'])){
								if($verify['auth_third'] == $login['employee_id']){
									$result = true;
								}else if(empty($verify['auth_third'])){
									if($verify['auth_second'] == $login['employee_id']){
										$result = true;
									}else if(empty($verify['auth_second'])){
										if($verify['auth_first'] == $login['employee_id']){
											$result = true;
										}else{
											$result = false;
										}
									}else{
										$result = false;
									}
								}else{
									$result = false;
								}
							}else{
								$result = false;
							}
						}else{
							$result = false;
						}
					}else{
						$result = false;
					}

					if(!$login['authorize_line']){
						if($login['instance_server_channel_id'] == $verify['instance_server_channel_id']){
							$auth = PageAuthorizeService::getAuthorizeByUserGroup(array("SAL", "SALBU", "AUDIT", "SALINEX", "HRBU"));
							if($auth === true){
								$result = true;
							}
						}
					}
				}else if($step == '5') {
					if($verify['auth_first'] == $login['employee_id'] || $verify['auth_second'] == $login['employee_id'] || $verify['auth_third'] == $login['employee_id'] || $verify['auth_fourth'] == $login['employee_id'] || $verify['auth_fifth'] == $login['employee_id']){
						if($verify[$key_type] == '03'){
							if($verify['auth_second'] == $login['employee_id'] || $verify['auth_third'] == $login['employee_id'] || $verify['auth_fourth'] == $login['employee_id'] || $verify['auth_fifth'] == $login['employee_id']){
								$result = true;
							}else{
								$result = false;
							}
						}else if($verify[$key_type] == '04'){
							if($verify['auth_third'] == $login['employee_id'] || $verify['auth_fourth'] == $login['employee_id'] || $verify['auth_fifth'] == $login['employee_id']){
								$result = true;
							}else{
								$result = false;
							}
						}else if($verify[$key_type] == '06'){
							if($verify['auth_fourth'] == $login['employee_id'] || $verify['auth_fifth'] == $login['employee_id']){
								$result = true;
							}else{
								$result = false;
							}
						}else if($verify[$key_type] == '07'){
							if($verify['auth_fifth'] == $login['employee_id']){
								$result = true;
							}else{
								$result = false;
							}
						}else if($verify[$key_type] == '01' || $verify[$key_type] == '05' || $verify[$key_type] == '02'){
							$result = true;
						}else if($verify[$key_type] == '08'){
							if($verify['auth_fifth'] == $login['employee_id']){
								$result = true;
							}else if(empty($verify['auth_fifth'])){
								if($verify['auth_fourth'] == $login['employee_id']){
									$result = true;
								}else if(empty($verify['auth_fourth'])){
									if($verify['auth_third'] == $login['employee_id']){
										$result = true;
									}else if(empty($verify['auth_third'])){
										if($verify['auth_second'] == $login['employee_id']){
											$result = true;
										}else if(empty($verify['auth_second'])){
											if($verify['auth_first'] == $login['employee_id']){
												$result = true;
											}else{
												$result = false;
											}
										}else{
											$result = false;
										}
									}else{
										$result = false;
									}
								}else{
									$result = false;
								}
							}else{
								$result = false;
							}
						}else{
							$result = false;
						}
					}else{
						$result = false;
					}

					if(!$login['authorize_line']){
						if($login['instance_server_channel_id'] == $verify['instance_server_channel_id']){
							$auth = PageAuthorizeService::getAuthorizeByUserGroup(array("SAL", "SALBU", "AUDIT", "SALINEX", "HRBU"));
							if($auth === true){
								$result = true;
							}
						}
					}
				}
			}
		}

		return $result;
	}

	function checkAuthorize2Pettycash($login, $verify, $key_type,$step = '', $doc_type = ''){
		if ($login['authorize_line'] == ''){
			$login['authorize_line'] = false;
		}
		$result = false;
		if($login['employee_id'] == null){
			if($GLOBALS['userInfo']['user_type'] == 'admin-acp' || $GLOBALS['userInfo']['user_type'] == 'dealers' || $GLOBALS['userInfo']['user_type'] == 'admin-ccs' || $GLOBALS['userInfo']['user_type'] == 'ccs'){
				$result = true;
			}
		} else {
			if($step == ''){
				$step = $this->configApproveStep($doc_type);
			}
			if($login['employee_id'] == $verify['employee_id']){
				if(!$login['authorize_line']){
					$auth = PageAuthorizeService::getAuthorizeByUserGroup(array("SAL"));
					if($auth === true){
						$result = true;
					}
				}
			}else if(($step == 'HR' || $step == '')){
				if($GLOBALS['userInfo']['user_type'] == 'admin-acp' || $GLOBALS['userInfo']['user_type'] == 'dealers' || $GLOBALS['userInfo']['user_type'] == 'admin-ccs' || $GLOBALS['userInfo']['user_type'] == 'ccs'){
					$result = true;
				}else{
					$auth = PageAuthorizeService::getAuthorizeByUserGroup(array("SAL", "SALBU", "AUDIT", "SALINEX", "HRBU"));
					if($auth === true){
						$result = true;
					}
				}
			} else {
				if($step == '1'){
					if($verify['auth_first'] == $login['employee_id']){
						if($verify[$key_type] == '01' || $verify[$key_type] == '08'){
							$result = true;
						}else{
							$result = false;
						}
					}else{
						$result = false;
					}

					if(!$login['authorize_line']){
						if($login['instance_server_channel_id'] == $verify['instance_server_channel_id']){
							$auth = PageAuthorizeService::getAuthorizeByUserGroup(array("SAL", "SALBU", "AUDIT", "SALINEX", "HRBU"));
							if($auth === true){
								$result = true;
							}
						}
					}
				}else if($step == '2') {
					// echo json_encode($verify, JSON_UNESCAPED_UNICODE)."<br>";
					if($verify['auth_first'] == $login['employee_id'] || $verify['auth_second'] == $login['employee_id']){
						if($verify[$key_type] == '03'){
							if($verify['auth_second'] == $login['employee_id']){
								$result = true;
							}else{
								$result = false;
							}
						}else if($verify[$key_type] == '01' || $verify[$key_type] == '05' || $verify[$key_type] == '02'){
							$result = true;
						}else if($verify[$key_type] == '08'){
							if($verify['auth_second'] == $login['employee_id']){
								$result = true;
							}else if(empty($verify['auth_second'])){
								if($verify['auth_first'] == $login['employee_id']){
									$result = true;
								}else{
									$result = false;
								}
							}else{
								$result = false;
							}
						}else{
							$result = false;
						}
					}else{
						$result = false;
					}

					if(!$login['authorize_line']){
						if($login['instance_server_channel_id'] == $verify['instance_server_channel_id']){
							$auth = PageAuthorizeService::getAuthorizeByUserGroup(array("SAL", "SALBU", "AUDIT", "SALINEX", "HRBU"));
							if($auth === true){
								$result = true;
							}
						}
					}
				}else if($step == '3') {
					if($verify['auth_first'] == $login['employee_id'] || $verify['auth_second'] == $login['employee_id'] || $verify['auth_third'] == $login['employee_id']){
						// echo $verify['auth_first'] ." == ". $login['employee_id'] ."<br>";
						if($verify[$key_type] == '03'){
							if($verify['auth_second'] == $login['employee_id'] || $verify['auth_third'] == $login['employee_id']){
								$result = true;
							}else{
								$result = false;
							}
						}else if($verify[$key_type] == '04'){
							if($verify['auth_third'] == $login['employee_id']){
								$result = true;
							}else{
								$result = false;
							}
						}else if($verify[$key_type] == '01' || $verify[$key_type] == '05' || $verify[$key_type] == '02'){
							$result = true;
						}else if($verify[$key_type] == '08'){
							if($verify['auth_third'] == $login['employee_id']){
								$result = true;
							}else if(empty($verify['auth_third'])){
								if($verify['auth_second'] == $login['employee_id']){
									$result = true;
								}else if(empty($verify['auth_second'])){
									if($verify['auth_first'] == $login['employee_id']){
										$result = true;
									}else{
										$result = false;
									}
								}else{
									$result = false;
								}
							}else{
								$result = false;
							}
						}else{
							$result = false;
						}
					}else{
						$result = false;
					}

					if(!$login['authorize_line']){
						if($login['instance_server_channel_id'] == $verify['instance_server_channel_id']){
							$auth = PageAuthorizeService::getAuthorizeByUserGroup(array("SAL", "SALBU", "AUDIT", "SALINEX", "HRBU"));
							if($auth === true){
								$result = true;
							}
						}
					}
				}else if($step == '4') {
					if($verify['auth_first'] == $login['employee_id'] || $verify['auth_second'] == $login['employee_id'] || $verify['auth_third'] == $login['employee_id'] || $verify['auth_fourth'] == $login['employee_id']){
						if($verify[$key_type] == '03'){
							if($verify['auth_second'] == $login['employee_id'] || $verify['auth_third'] == $login['employee_id'] || $verify['auth_fourth'] == $login['employee_id']){
								$result = true;
							}else{
								$result = false;
							}
						}else if($verify[$key_type] == '04'){
							if($verify['auth_third'] == $login['employee_id'] || $verify['auth_fourth'] == $login['employee_id']){
								$result = true;
							}else{
								$result = false;
							}
						}else if($verify[$key_type] == '06'){
							if($verify['auth_fourth'] == $login['employee_id']){
								$result = true;
							}else{
								$result = false;
							}
						}else if($verify[$key_type] == '01' || $verify[$key_type] == '05' || $verify[$key_type] == '02'){
							$result = true;
						}else if($verify[$key_type] == '08'){
							if($verify['auth_fourth'] == $login['employee_id']){
								$result = true;
							}else if(empty($verify['auth_fourth'])){
								if($verify['auth_third'] == $login['employee_id']){
									$result = true;
								}else if(empty($verify['auth_third'])){
									if($verify['auth_second'] == $login['employee_id']){
										$result = true;
									}else if(empty($verify['auth_second'])){
										if($verify['auth_first'] == $login['employee_id']){
											$result = true;
										}else{
											$result = false;
										}
									}else{
										$result = false;
									}
								}else{
									$result = false;
								}
							}else{
								$result = false;
							}
						}else{
							$result = false;
						}
					}else{
						$result = false;
					}

					if(!$login['authorize_line']){
						if($login['instance_server_channel_id'] == $verify['instance_server_channel_id']){
							$auth = PageAuthorizeService::getAuthorizeByUserGroup(array("SAL", "SALBU", "AUDIT", "SALINEX", "HRBU"));
							if($auth === true){
								$result = true;
							}
						}
					}
				}else if($step == '5') {
					if($verify['auth_first'] == $login['employee_id'] || $verify['auth_second'] == $login['employee_id'] || $verify['auth_third'] == $login['employee_id'] || $verify['auth_fourth'] == $login['employee_id'] || $verify['auth_fifth'] == $login['employee_id']){
						if($verify[$key_type] == '03'){
							if($verify['auth_second'] == $login['employee_id'] || $verify['auth_third'] == $login['employee_id'] || $verify['auth_fourth'] == $login['employee_id'] || $verify['auth_fifth'] == $login['employee_id']){
								$result = true;
							}else{
								$result = false;
							}
						}else if($verify[$key_type] == '04'){
							if($verify['auth_third'] == $login['employee_id'] || $verify['auth_fourth'] == $login['employee_id'] || $verify['auth_fifth'] == $login['employee_id']){
								$result = true;
							}else{
								$result = false;
							}
						}else if($verify[$key_type] == '06'){
							if($verify['auth_fourth'] == $login['employee_id'] || $verify['auth_fifth'] == $login['employee_id']){
								$result = true;
							}else{
								$result = false;
							}
						}else if($verify[$key_type] == '07'){
							if($verify['auth_fifth'] == $login['employee_id']){
								$result = true;
							}else{
								$result = false;
							}
						}else if($verify[$key_type] == '01' || $verify[$key_type] == '05' || $verify[$key_type] == '02'){
							$result = true;
						}else if($verify[$key_type] == '08'){
							if($verify['auth_fifth'] == $login['employee_id']){
								$result = true;
							}else if(empty($verify['auth_fifth'])){
								if($verify['auth_fourth'] == $login['employee_id']){
									$result = true;
								}else if(empty($verify['auth_fourth'])){
									if($verify['auth_third'] == $login['employee_id']){
										$result = true;
									}else if(empty($verify['auth_third'])){
										if($verify['auth_second'] == $login['employee_id']){
											$result = true;
										}else if(empty($verify['auth_second'])){
											if($verify['auth_first'] == $login['employee_id']){
												$result = true;
											}else{
												$result = false;
											}
										}else{
											$result = false;
										}
									}else{
										$result = false;
									}
								}else{
									$result = false;
								}
							}else{
								$result = false;
							}
						}else{
							$result = false;
						}
					}else{
						$result = false;
					}

					if(!$login['authorize_line']){
						if($login['instance_server_channel_id'] == $verify['instance_server_channel_id']){
							$auth = PageAuthorizeService::getAuthorizeByUserGroup(array("SAL", "SALBU", "AUDIT", "SALINEX", "HRBU"));
							if($auth === true){
								$result = true;
							}
						}
					}
				}
			}
		}

		return $result;
	}

	function getApprovedFlag($_employee_id, $_flag, $_authorize_line = false,$doc_type = ""){
		$auth = PageAuthorizeService::getAuthorizeByUserGroup(array("SAL", "SALBU", "AUDIT", "HRBU", "SALINEX"));
		// echo $_flag." : ".json_encode($auth)." : ".json_encode($_authorize_line)."<br><hr>";
		if($auth === true && $_authorize_line !== true){
			return $_flag;
		} else {
			if($_flag == '02'){
				$step = $this->configApproveStep($doc_type);
				// echo $step."<br>";
				if(($step == 'HR' || $step == '') && $auth === true){
					return '02';
				}else{
					$employee = $this->getEmployeeByIDAndApprover($_employee_id);
					// echo $step."<br>";
					if($step == '1'){
						if($employee['auth_first'] != ''){
							if($employee['auth_first'] == $GLOBALS['employeeLogin']['employee_id']){
								return '02';
							} else {
								return '01';
							}
						} else {
							return '02';
						}
					}else if($step == '2'){
						if($employee['auth_second'] != ''){
							if($employee['auth_second'] == $GLOBALS['employeeLogin']['employee_id']){
								return '02';
							} else if($employee['auth_first'] == $GLOBALS['employeeLogin']['employee_id']){
								return '04';
							} else {
								return '01';
							}
						} else if($employee['auth_first'] != ''){
							if($employee['auth_first'] == $GLOBALS['employeeLogin']['employee_id']){
								return '02';
							} else {
								return '01';
							}
						} else {
							return '02';
						}
					}else if($step == '3'){
						if($employee['auth_third'] != ''){
							if($employee['auth_third'] == $GLOBALS['employeeLogin']['employee_id']){
								return '02';
							} else if($employee['auth_second'] == $GLOBALS['employeeLogin']['employee_id']){
								return '06';
							} else if($employee['auth_first'] == $GLOBALS['employeeLogin']['employee_id']){
								return '04';
							} else {
								return '01';
							}
						} else if($employee['auth_second'] != ''){
							if($employee['auth_second'] == $GLOBALS['employeeLogin']['employee_id']){
								return '02';
							} else if($employee['auth_first'] == $GLOBALS['employeeLogin']['employee_id']){
								return '04';
							} else {
								return '01';
							}
						} else if($employee['auth_first'] != ''){
							if($employee['auth_first'] == $GLOBALS['employeeLogin']['employee_id']){
								return '02';
							} else {
								return '01';
							}
						}else {
							return '02';
						}
					}else if($step == '4'){
						if($employee['auth_fourth'] != ''){
							if($employee['auth_fourth'] == $GLOBALS['employeeLogin']['employee_id']){
								return '02';
							} else if($employee['auth_third'] == $GLOBALS['employeeLogin']['employee_id']){
								return '07';
							} else if($employee['auth_second'] == $GLOBALS['employeeLogin']['employee_id']){
								return '06';
							} else if($employee['auth_first'] == $GLOBALS['employeeLogin']['employee_id']){
								return '04';
							} else {
								return '01';
							}
						}else if($employee['auth_third'] != ''){
							if($employee['auth_third'] == $GLOBALS['employeeLogin']['employee_id']){
								return '02';
							} else if($employee['auth_second'] == $GLOBALS['employeeLogin']['employee_id']){
								return '06';
							} else if($employee['auth_first'] == $GLOBALS['employeeLogin']['employee_id']){
								return '04';
							} else {
								return '01';
							}
						} else if($employee['auth_second'] != ''){
							if($employee['auth_second'] == $GLOBALS['employeeLogin']['employee_id']){
								return '02';
							} else if($employee['auth_first'] == $GLOBALS['employeeLogin']['employee_id']){
								return '04';
							} else {
								return '01';
							}
						} else if($employee['auth_first'] != ''){
							if($employee['auth_first'] == $GLOBALS['employeeLogin']['employee_id']){
								return '02';
							} else {
								return '01';
							}
						} else {
							return '02';
						}
					}else if($step == '5'){
						// echo $GLOBALS['employeeLogin']['employee_id']." => ".$employee['auth_fifth']."<br>";
						if($employee['auth_fifth'] != ''){
							if($employee['auth_fifth'] == $GLOBALS['employeeLogin']['employee_id']){
								return '02';
							}else if($employee['auth_fourth'] == $GLOBALS['employeeLogin']['employee_id']){
								return '08';
							} else if($employee['auth_third'] == $GLOBALS['employeeLogin']['employee_id']){
								return '07';
							} else if($employee['auth_second'] == $GLOBALS['employeeLogin']['employee_id']){
								return '06';
							} else if($employee['auth_first'] == $GLOBALS['employeeLogin']['employee_id']){
								return '04';
							} else {
								return '01';
							}
						}else if($employee['auth_fourth'] != ''){
							if($employee['auth_fourth'] == $GLOBALS['employeeLogin']['employee_id']){
								return '02';
							} else if($employee['auth_third'] == $GLOBALS['employeeLogin']['employee_id']){
								return '07';
							} else if($employee['auth_second'] == $GLOBALS['employeeLogin']['employee_id']){
								return '06';
							} else if($employee['auth_first'] == $GLOBALS['employeeLogin']['employee_id']){
								return '04';
							} else {
								return '01';
							}
						}else if($employee['auth_third'] != ''){
							if($employee['auth_third'] == $GLOBALS['employeeLogin']['employee_id']){
								return '02';
							} else if($employee['auth_second'] == $GLOBALS['employeeLogin']['employee_id']){
								return '06';
							} else if($employee['auth_first'] == $GLOBALS['employeeLogin']['employee_id']){
								return '04';
							} else {
								return '01';
							}
						} else if($employee['auth_second'] != ''){
							if($employee['auth_second'] == $GLOBALS['employeeLogin']['employee_id']){
								return '02';
							} else if($employee['auth_first'] == $GLOBALS['employeeLogin']['employee_id']){
								return '04';
							} else {
								return '01';
							}
						} else if($employee['auth_first'] != ''){
							if($employee['auth_first'] == $GLOBALS['employeeLogin']['employee_id']){
								return '02';
							} else {
								return '01';
							}
						} else {
							return '02';
						}
					}
				}
			}else {
				return $_flag;
			}
			// if($_flag == '02'){
			// 	$employee = $this->getEmployeeByID($_employee_id);
			// 	$step = $this->configApproveStep($doc_type);
			// 	if($step=='1'){
			// 		if($employee['auth_first'] != ''){
			// 			if($employee['auth_first'] == $GLOBALS['employeeLogin']['employee_id']){
			// 				return '02';
			// 			} else {
			// 				return '01';
			// 			}
			// 		} else {
			// 			return '02';
			// 		}
			// 	}else if($step=='HR'){
			// 		if($auth === true){
			// 			return '02';
			// 		}
			// 	}else{
			// 		if($employee['auth_second'] != ''){
			// 			if($employee['auth_second'] == $GLOBALS['employeeLogin']['employee_id']){
			// 				return '02';
			// 			} else if($employee['auth_first'] == $GLOBALS['employeeLogin']['employee_id']){
			// 				return '04';
			// 			} else {
			// 				return '01';
			// 			}
			// 		} else if($employee['auth_first'] != ''){
			// 			if($employee['auth_first'] == $GLOBALS['employeeLogin']['employee_id']){
			// 				return '02';
			// 			} else {
			// 				return '01';
			// 			}
			// 		} else {
			// 			return '02';
			// 		}
			// 	}
				
			// } else {
			// 	return $_flag;
			// }
		}
	}

	function getDocumentFlag($_employee_id, $_flag, $_authorize_line = false, $doc_type = ""){
		$auth = PageAuthorizeService::getAuthorizeByUserGroup(array("SAL", "SALBU", "AUDIT", "HRBU", "SALINEX"));
		if($auth === true && !$_authorize_line){
			return $_flag;
		}else{
			if($_flag == '02'){
				$step = $this->configApproveStep($doc_type);
				if(($step == 'HR' || $step == '') && $auth === true){
					return '02';
				}else{
					$employee = $this->getEmployeeByIDAndApprover($_employee_id);

					if($step == '1'){
						if($employee['auth_first'] != ''){
							if($employee['auth_first'] == $GLOBALS['employeeLogin']['employee_id']){
								if ($doc_type == 'Visa_Permit' || $doc_type == 'Salary_Certificate' || $doc_type == 'Work_Permit' || $doc_type == 'Petty_Cash'){
									return '11';
								} else {
									return '02';
								}
							} else {
								return '01';
							}
						} else {
							return '02';
						}
					}else if($step == '2'){
						if($employee['auth_second'] != ''){
							if($employee['auth_second'] == $GLOBALS['employeeLogin']['employee_id']){
								if ($doc_type == 'Visa_Permit' || $doc_type == 'Salary_Certificate' || $doc_type == 'Work_Permit' || $doc_type == 'Petty_Cash'){
									return '11';
								} else {
									return '02';
								}
							} else if($employee['auth_first'] == $GLOBALS['employeeLogin']['employee_id']){
								return '07';
							} else {
								return '01';
							}
						} else if($employee['auth_first'] != ''){
							// echo $employee['auth_first'] ." => ". $GLOBALS['employeeLogin']['employee_id']."<br>";
							if($employee['auth_first'] == $GLOBALS['employeeLogin']['employee_id']){
								if ($doc_type == 'Visa_Permit' || $doc_type == 'Salary_Certificate' || $doc_type == 'Work_Permit' || $doc_type == 'Petty_Cash'){
									return '11';
								} else {
									return '02';
								}
							} else {
								return '01';
							}
						} else {
							return '02';
						}
					}else if($step == '3'){
						if($employee['auth_third'] != ''){
							if($employee['auth_third'] == $GLOBALS['employeeLogin']['employee_id']){
								if ($doc_type == 'Visa_Permit' || $doc_type == 'Salary_Certificate' || $doc_type == 'Work_Permit' || $doc_type == 'Petty_Cash'){
									return '11';
								} else {
									return '02';
								}
							} else if($employee['auth_second'] == $GLOBALS['employeeLogin']['employee_id']){
								return '08';
							} else if($employee['auth_first'] == $GLOBALS['employeeLogin']['employee_id']){
								return '07';
							} else {
								return '01';
							}
						} else if($employee['auth_second'] != ''){
							if($employee['auth_second'] == $GLOBALS['employeeLogin']['employee_id']){
								if ($doc_type == 'Visa_Permit' || $doc_type == 'Salary_Certificate' || $doc_type == 'Work_Permit' || $doc_type == 'Petty_Cash'){
									return '11';
								} else {
									return '02';
								}
							} else if($employee['auth_first'] == $GLOBALS['employeeLogin']['employee_id']){
								return '07';
							} else {
								return '01';
							}
						} else if($employee['auth_first'] != ''){
							if($employee['auth_first'] == $GLOBALS['employeeLogin']['employee_id']){
								if ($doc_type == 'Visa_Permit' || $doc_type == 'Salary_Certificate' || $doc_type == 'Work_Permit' || $doc_type == 'Petty_Cash'){
									return '11';
								} else {
									return '02';
								}
							} else {
								return '01';
							}
						}else {
							return '02';
						}
					}else if($step == '4'){
						if($employee['auth_fourth'] != ''){
							if($employee['auth_fourth'] == $GLOBALS['employeeLogin']['employee_id']){
								if ($doc_type == 'Visa_Permit' || $doc_type == 'Salary_Certificate' || $doc_type == 'Work_Permit' || $doc_type == 'Petty_Cash'){
									return '11';
								} else {
									return '02';
								}
							} else if($employee['auth_third'] == $GLOBALS['employeeLogin']['employee_id']){
								return '09';
							} else if($employee['auth_second'] == $GLOBALS['employeeLogin']['employee_id']){
								return '08';
							} else if($employee['auth_first'] == $GLOBALS['employeeLogin']['employee_id']){
								return '07';
							} else {
								return '01';
							}
						}else if($employee['auth_third'] != ''){
							if($employee['auth_third'] == $GLOBALS['employeeLogin']['employee_id']){
								if ($doc_type == 'Visa_Permit' || $doc_type == 'Salary_Certificate' || $doc_type == 'Work_Permit' || $doc_type == 'Petty_Cash'){
									return '11';
								} else {
									return '02';
								}
							} else if($employee['auth_second'] == $GLOBALS['employeeLogin']['employee_id']){
								return '08';
							} else if($employee['auth_first'] == $GLOBALS['employeeLogin']['employee_id']){
								return '07';
							} else {
								return '01';
							}
						} else if($employee['auth_second'] != ''){
							if($employee['auth_second'] == $GLOBALS['employeeLogin']['employee_id']){
								if ($doc_type == 'Visa_Permit' || $doc_type == 'Salary_Certificate' || $doc_type == 'Work_Permit' || $doc_type == 'Petty_Cash'){
									return '11';
								} else {
									return '02';
								}
							} else if($employee['auth_first'] == $GLOBALS['employeeLogin']['employee_id']){
								return '07';
							} else {
								return '01';
							}
						} else if($employee['auth_first'] != ''){
							if($employee['auth_first'] == $GLOBALS['employeeLogin']['employee_id']){
								if ($doc_type == 'Visa_Permit' || $doc_type == 'Salary_Certificate' || $doc_type == 'Work_Permit' || $doc_type == 'Petty_Cash'){
									return '11';
								} else {
									return '02';
								}
							} else {
								return '01';
							}
						} else {
							return '02';
						}
					}else if($step == '5'){
						if($employee['auth_fifth'] != ''){
							if($employee['auth_fifth'] == $GLOBALS['employeeLogin']['employee_id']){
								if ($doc_type == 'Visa_Permit' || $doc_type == 'Salary_Certificate' || $doc_type == 'Work_Permit' || $doc_type == 'Petty_Cash'){
									return '11';
								} else {
									return '02';
								}
							} else if($employee['auth_fourth'] == $GLOBALS['employeeLogin']['employee_id']){
								return '10';
							} else if($employee['auth_third'] == $GLOBALS['employeeLogin']['employee_id']){
								return '09';
							} else if($employee['auth_second'] == $GLOBALS['employeeLogin']['employee_id']){
								return '08';
							} else if($employee['auth_first'] == $GLOBALS['employeeLogin']['employee_id']){
								return '07';
							} else {
								return '01';
							}
						}else if($employee['auth_fourth'] != ''){
							if($employee['auth_fourth'] == $GLOBALS['employeeLogin']['employee_id']){
								if ($doc_type == 'Visa_Permit' || $doc_type == 'Salary_Certificate' || $doc_type == 'Work_Permit' || $doc_type == 'Petty_Cash'){
									return '11';
								} else {
									return '02';
								}
							} else if($employee['auth_third'] == $GLOBALS['employeeLogin']['employee_id']){
								return '09';
							} else if($employee['auth_second'] == $GLOBALS['employeeLogin']['employee_id']){
								return '08';
							} else if($employee['auth_first'] == $GLOBALS['employeeLogin']['employee_id']){
								return '07';
							} else {
								return '01';
							}
						}else if($employee['auth_third'] != ''){
							if($employee['auth_third'] == $GLOBALS['employeeLogin']['employee_id']){
								if ($doc_type == 'Visa_Permit' || $doc_type == 'Salary_Certificate' || $doc_type == 'Work_Permit' || $doc_type == 'Petty_Cash'){
									return '11';
								} else {
									return '02';
								}
							} else if($employee['auth_second'] == $GLOBALS['employeeLogin']['employee_id']){
								return '08';
							} else if($employee['auth_first'] == $GLOBALS['employeeLogin']['employee_id']){
								return '07';
							} else {
								return '01';
							}
						} else if($employee['auth_second'] != ''){
							if($employee['auth_second'] == $GLOBALS['employeeLogin']['employee_id']){
								if ($doc_type == 'Visa_Permit' || $doc_type == 'Salary_Certificate' || $doc_type == 'Work_Permit' || $doc_type == 'Petty_Cash'){
									return '11';
								} else {
									return '02';
								}
							} else if($employee['auth_first'] == $GLOBALS['employeeLogin']['employee_id']){
								return '07';
							} else {
								return '01';
							}
						} else if($employee['auth_first'] != ''){
							if($employee['auth_first'] == $GLOBALS['employeeLogin']['employee_id']){
								if ($doc_type == 'Visa_Permit' || $doc_type == 'Salary_Certificate' || $doc_type == 'Work_Permit' || $doc_type == 'Petty_Cash'){
									return '11';
								} else {
									return '02';
								}
							} else {
								return '01';
							}
						} else {
							return '02';
						}
					}
				}
			}else {
				return $_flag;
			}
		}
	}

	function getComplaintFlag($_employee_id, $_flag, $_authorize_line = false){
		$auth = PageAuthorizeService::getAuthorizeByUserGroup(array("SAL", "SALBU", "AUDIT", "HRBU", "SALINEX"));
		if($auth === true && $_authorize_line === false){
			return $_flag;
		} else {
			if($_flag == '02'){
				$step = $this->configApproveStep('Complaint');
				if(($step == 'HR' || $step == '') && $auth === true){
					return '08';
				}else{
					$employee = $this->getEmployeeByIDAndApprover($_employee_id);

					if($step == '1'){
						if($employee['auth_first'] != ''){
							if($employee['auth_first'] == $GLOBALS['employeeLogin']['employee_id']){
								return '08';
							} else {
								return '01';
							}
						} else {
							return '08';
						}
					}else if($step == '2'){
						if($employee['auth_second'] != ''){
							if($employee['auth_second'] == $GLOBALS['employeeLogin']['employee_id']){
								return '08';
							} else if($employee['auth_first'] == $GLOBALS['employeeLogin']['employee_id']){
								return '03';
							} else {
								return '01';
							}
						} else if($employee['auth_first'] != ''){
							if($employee['auth_first'] == $GLOBALS['employeeLogin']['employee_id']){
								return '08';
							} else {
								return '01';
							}
						} else {
							return '08';
						}
					}else if($step == '3'){
						if($employee['auth_third'] != ''){
							if($employee['auth_third'] == $GLOBALS['employeeLogin']['employee_id']){
								return '08';
							} else if($employee['auth_second'] == $GLOBALS['employeeLogin']['employee_id']){
								return '04';
							} else if($employee['auth_first'] == $GLOBALS['employeeLogin']['employee_id']){
								return '03';
							} else {
								return '01';
							}
						} else if($employee['auth_second'] != ''){
							if($employee['auth_second'] == $GLOBALS['employeeLogin']['employee_id']){
								return '08';
							} else if($employee['auth_first'] == $GLOBALS['employeeLogin']['employee_id']){
								return '03';
							} else {
								return '01';
							}
						} else if($employee['auth_first'] != ''){
							if($employee['auth_first'] == $GLOBALS['employeeLogin']['employee_id']){
								return '08';
							} else {
								return '01';
							}
						}else {
							return '08';
						}
					}else if($step == '4'){
						if($employee['auth_fourth'] != ''){
							if($employee['auth_fourth'] == $GLOBALS['employeeLogin']['employee_id']){
								return '08';
							} else if($employee['auth_third'] == $GLOBALS['employeeLogin']['employee_id']){
								return '06';
							} else if($employee['auth_second'] == $GLOBALS['employeeLogin']['employee_id']){
								return '04';
							} else if($employee['auth_first'] == $GLOBALS['employeeLogin']['employee_id']){
								return '03';
							} else {
								return '01';
							}
						}else if($employee['auth_third'] != ''){
							if($employee['auth_third'] == $GLOBALS['employeeLogin']['employee_id']){
								return '08';
							} else if($employee['auth_second'] == $GLOBALS['employeeLogin']['employee_id']){
								return '04';
							} else if($employee['auth_first'] == $GLOBALS['employeeLogin']['employee_id']){
								return '03';
							} else {
								return '01';
							}
						} else if($employee['auth_second'] != ''){
							if($employee['auth_second'] == $GLOBALS['employeeLogin']['employee_id']){
								return '08';
							} else if($employee['auth_first'] == $GLOBALS['employeeLogin']['employee_id']){
								return '03';
							} else {
								return '01';
							}
						} else if($employee['auth_first'] != ''){
							if($employee['auth_first'] == $GLOBALS['employeeLogin']['employee_id']){
								return '08';
							} else {
								return '01';
							}
						} else {
							return '08';
						}
					}else if($step == '5'){
						if($employee['auth_fifth'] != ''){
							if($employee['auth_fifth'] == $GLOBALS['employeeLogin']['employee_id']){
								return '08';
							}else if($employee['auth_fourth'] == $GLOBALS['employeeLogin']['employee_id']){
								return '07';
							} else if($employee['auth_third'] == $GLOBALS['employeeLogin']['employee_id']){
								return '06';
							} else if($employee['auth_second'] == $GLOBALS['employeeLogin']['employee_id']){
								return '04';
							} else if($employee['auth_first'] == $GLOBALS['employeeLogin']['employee_id']){
								return '03';
							} else {
								return '01';
							}
						}else if($employee['auth_fourth'] != ''){
							if($employee['auth_fourth'] == $GLOBALS['employeeLogin']['employee_id']){
								return '08';
							} else if($employee['auth_third'] == $GLOBALS['employeeLogin']['employee_id']){
								return '06';
							} else if($employee['auth_second'] == $GLOBALS['employeeLogin']['employee_id']){
								return '04';
							} else if($employee['auth_first'] == $GLOBALS['employeeLogin']['employee_id']){
								return '03';
							} else {
								return '01';
							}
						}else if($employee['auth_third'] != ''){
							if($employee['auth_third'] == $GLOBALS['employeeLogin']['employee_id']){
								return '08';
							} else if($employee['auth_second'] == $GLOBALS['employeeLogin']['employee_id']){
								return '04';
							} else if($employee['auth_first'] == $GLOBALS['employeeLogin']['employee_id']){
								return '03';
							} else {
								return '01';
							}
						} else if($employee['auth_second'] != ''){
							if($employee['auth_second'] == $GLOBALS['employeeLogin']['employee_id']){
								return '08';
							} else if($employee['auth_first'] == $GLOBALS['employeeLogin']['employee_id']){
								return '03';
							} else {
								return '01';
							}
						} else if($employee['auth_first'] != ''){
							if($employee['auth_first'] == $GLOBALS['employeeLogin']['employee_id']){
								return '08';
							} else {
								return '01';
							}
						} else {
							return '08';
						}
					}
				}
			}else {
				return $_flag;
			}
			// if($_flag == '02'){
			// 	$employee = $this->getEmployeeByID($_employee_id);
			// 	$step = $this->configApproveStep($doc_type);
			// 	if($step=='1'){
			// 		if($employee['auth_first'] != ''){
			// 			if($employee['auth_first'] == $GLOBALS['employeeLogin']['employee_id']){
			// 				return '02';
			// 			} else {
			// 				return '01';
			// 			}
			// 		} else {
			// 			return '02';
			// 		}
			// 	}else if($step=='HR'){
			// 		if($auth === true){
			// 			return '02';
			// 		}
			// 	}else{
			// 		if($employee['auth_second'] != ''){
			// 			if($employee['auth_second'] == $GLOBALS['employeeLogin']['employee_id']){
			// 				return '02';
			// 			} else if($employee['auth_first'] == $GLOBALS['employeeLogin']['employee_id']){
			// 				return '04';
			// 			} else {
			// 				return '01';
			// 			}
			// 		} else if($employee['auth_first'] != ''){
			// 			if($employee['auth_first'] == $GLOBALS['employeeLogin']['employee_id']){
			// 				return '02';
			// 			} else {
			// 				return '01';
			// 			}
			// 		} else {
			// 			return '02';
			// 		}
			// 	}
				
			// } else {
			// 	return $_flag;
			// }
		}
	}

	function configApproveStep($doc_type, $channel_id = ''){
		switch($doc_type){
			case "Leave" : $code = "step_approve_leave"; break;
			case "OT" : $code = "step_approve_ot"; break;
			case "Work_Cycle" : $code = "step_approve_work_cycle"; break;
			case "Holiday" : $code = "step_approve_holiday"; break;
			case "Time_Adjust" : $code = "step_approve_time_adjust"; break;
			case "Withdraw" : $code = "step_approve_withdraw"; break;
			case "Work_Permit" : $code = "step_approve_work_permit"; break;
			case "Salary_Certificate" : $code = "step_approve_salary_certificate"; break;
			case "Petty_Cash" : $code = "step_approve_petty_cash"; break;
			case "Welfare" : $code = "step_approve_welfare"; break;
			case "Complaint" : $code = "step_approve_complaint"; break;
			case "Resign" : $code = "step_approve_resign"; break;
			case "Visa_Permit" : $code = "step_approve_visa_permit"; break;
			default : $code = ""; break;
		}
		if($code!=''){
			$_sql = "SELECT _config.*
			FROM hms_api.comp_config _config
			WHERE _config.config_code = '{$code}' 
			AND _config.server_id = '{$_REQUEST['server_id']}'
			AND _config.instance_server_id = '{$_REQUEST['instance_server_id']}' ";
			if($channel_id == ''){
				$_sql .= " AND _config.instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}' ";
			}else{
				$_sql .= " AND _config.instance_server_channel_id = '{$channel_id}' ";
			}
			
			$step = $this->_sqlget($_sql);
			if (!empty($step)) {
				return $step['config_key_1'];
			} else {
				if($code == 'step_approve_work_permit' || 'step_approve_visa_permit' || $code == 'step_approve_salary_certificate' || $code == 'step_approve_petty_cash' || $code == 'step_approve_welfare' || $code == 'step_approve_complaint' || $code == 'step_approve_resign'){
					return "HR";
				}
				return "2";
			}
		}else{
			if($code == 'step_approve_work_permit' || 'step_approve_visa_permit' || $code == 'step_approve_salary_certificate' || $code == 'step_approve_petty_cash' || $code == 'step_approve_welfare' || $code == 'step_approve_complaint' || $code == 'step_approve_resign'){
				return "HR";
			}
			return "2";
		}
		
	}

	function configApproveStepDomain($doc_type){
		switch($doc_type){
			case "Leave" : $code = "step_approve_leave"; break;
			case "OT" : $code = "step_approve_ot"; break;
			case "Work_Cycle" : $code = "step_approve_work_cycle"; break;
			case "Holiday" : $code = "step_approve_holiday"; break;
			case "Time_Adjust" : $code = "step_approve_time_adjust"; break;
			case "Withdraw" : $code = "step_approve_withdraw"; break;
			case "Work_Permit" : $code = "step_approve_work_permit"; break;
			case "Salary_Certificate" : $code = "step_approve_salary_certificate"; break;
			case "Petty_Cash" : $code = "step_approve_petty_cash"; break;
			case "Welfare" : $code = "step_approve_welfare"; break;
			case "Complaint" : $code = "step_approve_complaint"; break;
			case "Resign" : $code = "step_approve_resign"; break;
			case "Visa_Permit" : $code = "step_approve_visa_permit"; break;
			default : $code = ""; break;
		}

		$step_list = array();
		if($code!=''){
			$_sql = "SELECT _config.*
			FROM hms_api.comp_config _config
			WHERE _config.config_code = '{$code}' 
			AND _config.server_id = '{$_REQUEST['server_id']}'
			AND _config.instance_server_id = '{$_REQUEST['instance_server_id']}' ";
			// echo "$_sql<br>";
			$step = $this->_sqllists($_sql);
			
			for($step_idx = 0; $step_idx < sizeof($step); $step_idx++){
				$step_list[$step[$step_idx]['instance_server_channel_id']] = $step[$step_idx]['config_key_1'];
			}
		}

		if(empty($step_list)){
			$step_list[$_REQUEST['instance_server_channel_id']] = "2";
		}

		return $step_list;
	}

	function configApproveStepMultiple($doc_type){
		$field = array();
		$result = array();
		foreach($doc_type as $item){
			switch($item){
				case "Leave" : $code = "step_approve_leave"; break;
				case "OT" : $code = "step_approve_ot"; break;
				case "Work_Cycle" : $code = "step_approve_time_adjust"; break;
				case "Holiday" : $code = "step_approve_holiday"; break;
				case "Time_Adjust" : $code = "step_approve_work_cycle"; break;
				case "Withdraw" : $code = "step_approve_withdraw"; break;
				case "Work_Permit" : $code = "step_approve_work_permit"; break;
				case "Salary_Certificate" : $code = "step_approve_salary_certificate"; break;
				case "Petty_Cash" : $code = "step_approve_petty_cash"; break;
				case "Welfare" : $code = "step_approve_welfare"; break;
				case "Complaint" : $code = "step_approve_complaint"; break;
				case "Visa_Permit" : $code = "step_approve_visa_permit"; break;
				default : $code = ""; break;
			}

			$field[] = $code;
			$result[$item] = 2;
		}


		if(!empty($field)){
			$_sql = "SELECT _config.*
			FROM hms_api.comp_config _config
			WHERE _config.config_code IN ('".implode("','" , $field)."')  
			AND _config.server_id = '{$_REQUEST['server_id']}'
			AND _config.instance_server_id = '{$_REQUEST['instance_server_id']}' 
			AND _config.instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}' ";
			// echo "$_sql<br>";
			$lists = $this->_sqllists($_sql);

			foreach($lists as $item){
				switch($item['config_code']){
					case "step_approve_leave" : $type = "Leave"; break;
					case "step_approve_ot" : $type = "OT"; break;
					case "step_approve_time_adjust" : $type = "Time_Adjust"; break;
					case "step_approve_holiday" : $type = "Holiday"; break;
					case "step_approve_work_cycle" : $type = "Work_Cycle"; break;
					case "step_approve_withdraw" : $type = "Withdraw"; break;
					case "step_approve_work_permit" : $type = "Work_Permit"; break;
					case "step_approve_salary_certificate" : $type = "Salary_Certificate"; break;
					case "step_approve_petty_cash" : $type = "Petty_Cash"; break;
					case "step_approve_welfare" : $type = "Welfare"; break;
					case "step_approve_complaint" : $type = "Complaint"; break;
					case "step_approve_visa_permit" : $type = "Visa_Permit"; break;
					default : $type = ""; break;
				}

				$result[$type] = $item['config_key_1'];

			}

		}

		return $result;
		
	}
	function getListEmployeeWithFilterModified($_PARAM)
	{
		// print_r($GLOBALS['employeeLogin']);
		$_sql = "SELECT _employee.employee_id,
						_employee.employee_code,
						_employee.fing_code,
						_employee.employee_type_code,
						_employee.employee_type_group_id,
						_employee.employee_nickname,
						_employee.employee_nickname_en,
						_employee.employee_name,
						_employee.employee_last_name,
						_employee.employee_name_en,
						_employee.employee_last_name_en,
						_employee.employee_title_lv,
						_employee.employee_gender,
						_employee.employee_foreigner,
						_employee.employee_status,
						_employee.position_id,
						_employee.company_id,
						_employee.branch_id,
						_employee.department_id,
						_employee.division_id,
						_employee.section_id,
						_employee.section_lv01_id,
						_employee.section_lv02_id,
                        _employee.section_lv03_id,
                        _employee.section_lv04_id,
                        _employee.section_lv05_id,
						_employee.mobilephone,
						_employee.emailaddress,
						_employee.salary,
						_employee.salary_law,
						_employee.salary_per_week_type_lv,
						_employee.salary_per_week,
						_employee.payment_method,
						_employee.social_insurance_method_lv,
						_employee.social_insurance_method_constant,
						_employee.social_insurance_deduct_lv,
						_employee.tax_method_lv,
						_employee.tax_method_constant,
						_employee.tax_method_rate,
						_employee.tax_deduct_lv,
						_employee.days_per_month,
						_employee.hours_per_day,
						_employee.birth_dt,
						_employee.id_no,
						_employee.sso_no,
						_employee.opt_code,
						_employee.person_id,
						_employee.line_user_id,
						_employee.player_id,
						_employee.apple_id,
						_employee.line_token_id,
						_employee.line_token_todolist_id,
						_employee.chat_token,
						IFNULL(_employee.photograph , 'images/userPlaceHolder.png') AS photograph,
						_employee.bank_id,
						_employee.coa_account_group_id,
						_employee.company_payment_account_id,
						_employee.bank_branch_code,
						_employee.bank_account_code,
						_employee.work_cycle_id_json,
						_employee.work_cycle_format,
						_employee.holiday_day_json,
						_employee.holiday_format,
						_employee.clock_inout,
						_employee.trial_range,
						_employee.effective_dt,
						_employee.begin_dt,
						_employee.signout_flag,
						_employee.signout_request_dt,
						_employee.signout_dt,
						_employee.out_dt,
						_employee.sso_out_dt,
						_employee.signout_type_flag,
						_employee.signout_remark,
						_employee.round_month_config,
						_employee.round_xtra_config,
						_employee.round_ot_config,
						_employee.round_worktime_config,
						_employee.holiday_apply_config,
						_employee.import_log_id,
						_employee.personal_config,
						_employee.address,
						_employee.address1,
						_employee.address2,
						_employee.address3,
						_employee.address4,
						_employee.address5,
						_employee.address6,
						_employee.address7,
						_employee.address8,
						_employee.address9,
						_employee.country_id,
						_employee.country_code,
						_employee.state_code,
						_employee.district_code,
						_employee.subdistrict_code,
						_employee.post_code,
						_employee.current_address,
						_employee.current_address1,
						_employee.current_address2,
						_employee.current_address3,
						_employee.current_address4,
						_employee.current_address5,
						_employee.current_address6,
						_employee.current_address7,
						_employee.current_address8,
						_employee.current_address9,
						_employee.current_country_code,
						_employee.current_state_code,
						_employee.current_district_code,
						_employee.current_subdistrict_code,
						_employee.current_post_code,
						_employee.hashtag_desc,
						_employee.order_no,
						_employee.server_id,
						_employee.instance_server_id,
						_employee.instance_server_channel_id,
						_employee.sys_del_flag,
						_employee.reference_code_1,					
						_employee.reference_code_2,					
						_employee.reference_code_3,					
						_employee.reference_code_4,					
						_employee.reference_code_5,
						_company.company_code,
						_company.company_name,
						_company.company_name_en,
						_branch.branch_code,
						_branch.branch_name,
						_branch.branch_name_en,
						_department.department_code,
						_department.department_name,
						_department.department_name_en,
						_division.division_code,
						_division.division_name,
						_division.division_name_en,
						_section.section_code,
						_section.section_name,
						_section.section_name_en,
						_section_lv01.section_lv01_code,
						_section_lv01.section_lv01_name,
						_section_lv01.section_lv01_name_en,
						_section_lv02.section_lv02_code,
						_section_lv02.section_lv02_name,
						_section_lv02.section_lv02_name_en,
						_section_lv03.section_lv03_code,
						_section_lv03.section_lv03_name,
						_section_lv03.section_lv03_name_en,
						_section_lv04.section_lv04_code,
						_section_lv04.section_lv04_name,
						_section_lv04.section_lv04_name_en,
						_section_lv05.section_lv05_code,
						_section_lv05.section_lv05_name,
						_section_lv05.section_lv05_name_en,
						_position.position_code,
						_position.position_name,
						_position.position_name_en,
						_taxperson.person_tax_transac_id AS person_tax_id,
						 _employee.publish_flag  ,
						 _typegroup.tax_type ,
						 _typegroup.employee_type_group_id,
						 _typegroup.employee_type_group_code,
						 _typegroup.employee_type_group_name,
						 _typegroup.employee_type_group_name_en 
						FROM (select * from hms_api.comp_employee  where instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}') _employee 
						INNER JOIN (select company_id, company_code, company_name, company_name_en FROM hms_api.comp_company  where instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}') _company ON (_company.company_id=_employee.company_id) 
						INNER JOIN (select branch_id, branch_code, branch_name, branch_name_en FROM hms_api.comp_branch  where instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}') _branch ON (_branch.branch_id=_employee.branch_id) 
						INNER JOIN (select department_id, department_code, department_name, department_name_en 
						                       FROM hms_api.comp_department  where instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}') _department ON (_department.department_id=_employee.department_id) 
						LEFT JOIN (select division_id, division_code, division_name, division_name_en 
						                       FROM hms_api.comp_division where instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}') _division ON (_division.division_id=_employee.division_id) 
						LEFT JOIN (select section_id, section_code, section_name, section_name_en 
						                       FROM hms_api.comp_section where instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}') _section ON (_section.section_id=_employee.section_id) 
						LEFT JOIN (select section_lv01_id, section_lv01_code, section_lv01_name, section_lv01_name_en 
						                       FROM hms_api.comp_section_lv01 where instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}') _section_lv01 ON (_section_lv01.section_lv01_id=_employee.section_lv01_id) 
						LEFT JOIN (select section_lv02_id, section_lv02_code, section_lv02_name, section_lv02_name_en 
						                       FROM hms_api.comp_section_lv02 where instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}') _section_lv02 ON (_section_lv02.section_lv02_id=_employee.section_lv02_id) 
						LEFT JOIN (select section_lv03_id, section_lv03_code, section_lv03_name, section_lv03_name_en 
						                       FROM hms_api.comp_section_lv03 where instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}') _section_lv03 ON (_section_lv03.section_lv03_id=_employee.section_lv03_id) 
						LEFT JOIN (select section_lv04_id, section_lv04_code, section_lv04_name, section_lv04_name_en 
						                       FROM hms_api.comp_section_lv04 where instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}') _section_lv04 ON (_section_lv04.section_lv04_id=_employee.section_lv04_id) 
						LEFT JOIN (select section_lv05_id, section_lv05_code, section_lv05_name, section_lv05_name_en 
						                       FROM hms_api.comp_section_lv05 where instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}') _section_lv05 ON (_section_lv05.section_lv05_id=_employee.section_lv05_id)
						INNER JOIN (select position_id, position_code, position_name, position_name_en 
												FROM hms_api.comp_position where instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}') _position ON (_position.position_id=_employee.position_id) 
						LEFT JOIN (
							SELECT person_tax_transac_id, tax_year_code, tax_month_code, tax_category_id, employee_id 
							FROM {$GLOBALS['instanceServer']['instance_server_dbn']}.payroll_person_tax_transac 
							WHERE instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}' 
							AND tax_year_code = '" . date('Y') . "' 
							AND tax_month_code = '12' 
							AND tax_category_id = '60'
						) _taxperson ON (_taxperson.employee_id = _employee.employee_id) 
						LEFT JOIN hms_api.comp_employee_type_group _typegroup ON (_typegroup.employee_type_group_id = _employee.employee_type_group_id) 
						WHERE _employee.server_id = '{$_REQUEST['server_id']}'
						AND _employee.instance_server_id = '{$_REQUEST['instance_server_id']}'
						AND _employee.instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}' ";

		if($_PARAM["keyword"] != ''){
			$_sql .= "AND (
								_employee.employee_nickname LIKE '%{$_PARAM["keyword"]}%' OR
								_employee.employee_name LIKE '%{$_PARAM["keyword"]}%' OR
								_employee.employee_last_name LIKE '%{$_PARAM["keyword"]}%' OR
								_employee.employee_nickname_en LIKE '%{$_PARAM["keyword"]}%' OR
								_employee.employee_name_en LIKE '%{$_PARAM["keyword"]}%' OR
								_employee.employee_last_name_en LIKE '%{$_PARAM["keyword"]}%' OR
								_employee.fing_code LIKE '%{$_PARAM["keyword"]}%' OR
								_employee.employee_code LIKE '%{$_PARAM["keyword"]}%') ";
		}

		if(sizeof($_PARAM["hashtags"]) == 1){
			$_sql .= " AND _employee.hashtag_desc LIKE '%{$_PARAM["hashtags"][0]}%' ";
		} else if(sizeof($_PARAM["hashtags"]) > 1){
			$_sql .= " AND ( ";
			for ($i = 0; $i < sizeof($_PARAM["hashtags"]); $i++){
				if($i == 0)
					$_sql .= " _employee.hashtag_desc LIKE '%{$_PARAM["hashtags"][$i]}%' ";
				else
					$_sql .= " OR _employee.hashtag_desc LIKE '%{$_PARAM["hashtags"][$i]}%' ";
			}
			$_sql .= " ) ";
		}

		if(is_array($_PARAM["except"]) && sizeof($_PARAM["except"]) > 0){
			$excepIds = "";
			for ($i = 0; $i < sizeof($_PARAM["except"]); $i++){
				if($i == 0)
					$excepIds = "'" . base64_decode($_PARAM["except"][$i]["id"]) . "' ";
				else
					$excepIds .= ", '" . base64_decode($_PARAM["except"][$i]["id"]) . "' ";
			}
			$_sql .= "AND _employee.employee_id NOT IN ({$excepIds}) ";
		}
		
		$operator = "";
		$_sql_org = "";
		if(is_array($_PARAM['company_lists']) && sizeof($_PARAM['company_lists']) > 0){
			$_sql_org .= " {$operator} _employee.company_id IN ('".implode("','", array_map('base64_decode', array_column($_PARAM['company_lists'], 'id')))."') ";
			$operator = "OR";
		}
		if(is_array($_PARAM['branch_lists']) && sizeof($_PARAM['branch_lists']) > 0){
			$_sql_org .= " {$operator} _employee.branch_id IN ('".implode("','", array_map('base64_decode', array_column($_PARAM['branch_lists'], 'id')))."') ";
			$operator = "OR";
		}
		if(is_array($_PARAM['department_lists']) && sizeof($_PARAM['department_lists']) > 0){
			$_sql_org .= " {$operator} _employee.department_id IN ('".implode("','", array_map('base64_decode', array_column($_PARAM['department_lists'], 'id')))."') ";
			$operator = "OR";
		}
		if(is_array($_PARAM['division_lists']) && sizeof($_PARAM['division_lists']) > 0){
			$_sql_org .= " {$operator} _employee.division_id IN ('".implode("','", array_map('base64_decode', array_column($_PARAM['division_lists'], 'id')))."') ";
			$operator = "OR";
		}
		if(is_array($_PARAM['section_lists']) && sizeof($_PARAM['section_lists']) > 0){
			$_sql_org .= " {$operator} _employee.section_id IN ('".implode("','", array_map('base64_decode', array_column($_PARAM['section_lists'], 'id')))."') ";
			$operator = "OR";
		}
		if(is_array($_PARAM['section_lists_lv01']) && sizeof($_PARAM['section_lists_lv01']) > 0){
			$_sql_org .= " {$operator} _employee.section_lv01_id IN ('".implode("','", array_map('base64_decode', array_column($_PARAM['section_lists_lv01'], 'id')))."') ";
			$operator = "OR";
		}
		if(is_array($_PARAM['section_lists_lv02']) && sizeof($_PARAM['section_lists_lv02']) > 0){
			$_sql_org .= " {$operator} _employee.section_lv02_id IN ('".implode("','", array_map('base64_decode', array_column($_PARAM['section_lists_lv02'], 'id')))."') ";
			$operator = "OR";
		}
		if(is_array($_PARAM['section_lists_lv03']) && sizeof($_PARAM['section_lists_lv03']) > 0){
			$_sql_org .= " {$operator} _employee.section_lv03_id IN ('".implode("','", array_map('base64_decode', array_column($_PARAM['section_lists_lv03'], 'id')))."') ";
			$operator = "OR";
		}
		if(is_array($_PARAM['section_lists_lv04']) && sizeof($_PARAM['section_lists_lv04']) > 0){
			$_sql_org .= " {$operator} _employee.section_lv04_id IN ('".implode("','", array_map('base64_decode', array_column($_PARAM['section_lists_lv04'], 'id')))."') ";
			$operator = "OR";
		}
		if(is_array($_PARAM['section_lists_lv05']) && sizeof($_PARAM['section_lists_lv05']) > 0){
			$_sql_org .= " {$operator} _employee.section_lv05_id IN ('".implode("','", array_map('base64_decode', array_column($_PARAM['section_lists_lv05'], 'id')))."') ";
			$operator = "OR";
		}
		if($_sql_org != ""){
			$_sql .= " AND ({$_sql_org}) ";
		}

		if (is_array($_PARAM["position_lists"]) && sizeof($_PARAM["position_lists"]) > 0) {
			$positionIds = "";
			for ($i = 0; $i < sizeof($_PARAM["position_lists"]); $i++){
				if($i == 0)
					$positionIds = "'" . base64_decode($_PARAM["position_lists"][$i]["id"]) . "' ";
				else
					$positionIds .= ", '" . base64_decode($_PARAM["position_lists"][$i]["id"]) . "' ";
			}
			$_sql .= "AND _employee.position_id IN ({$positionIds}) ";
		}

		if(is_array($_PARAM["employee_lists"]) && sizeof($_PARAM["employee_lists"]) > 0){
			$employeeIds = "";
			for ($i = 0; $i < sizeof($_PARAM["employee_lists"]); $i++){
				if($i == 0)
					$employeeIds = "'" . base64_decode($_PARAM["employee_lists"][$i]["id"]) . "' ";
				else
					$employeeIds .= ", '" . base64_decode($_PARAM["employee_lists"][$i]["id"]) . "' ";
			}
			$_sql .= "AND _employee.employee_id IN ({$employeeIds}) ";

			if($_PARAM['sys_del_flag'] == 'N' || $_PARAM['sys_del_flag'] == 'Y'){
				$_sql .= "AND _employee.sys_del_flag = '{$_PARAM["sys_del_flag"]}' ";
			}
		} else {
			if($_PARAM['sys_del_flag'] == 'N' || $_PARAM['sys_del_flag'] == 'Y'){
				$_sql .= "AND _employee.sys_del_flag = '{$_PARAM["sys_del_flag"]}' ";
			} else if($_PARAM['sys_del_flag'] == 'A'){

			} else {
				$_sql .= "AND _employee.sys_del_flag = 'N' ";
			}
		}

		if($_PARAM['signout_flag']){
			$_sql .= "AND _employee.signout_flag = '{$_PARAM['signout_flag']}' ";
		}
		if($_PARAM['round_xtra_config']){
			$_sql .= "AND _employee.round_xtra_config = '{$_PARAM['round_xtra_config']}' ";
		}
		if($_PARAM['round_ot_config']){
			$_sql .= "AND _employee.round_ot_config = '{$_PARAM['round_ot_config']}' ";
		}
		if($_PARAM['round_worktime_config']){
			$_sql .= "AND _employee.round_worktime_config = '{$_PARAM['round_worktime_config']}' ";
		}

		if($GLOBALS['employeeLogin']['employee_id'] != ''){
			$auth = PageAuthorizeService::getAuthorizeByUserGroup(array("SAL", "SALINEX", "SALBU", "AUDIT", "HRBU"));
			if($_PARAM['only_in_position_line'] == true || $auth === false){
				$posEmpList = $this->getListEmployeeAuthorize($GLOBALS['employeeLogin']['employee_id']);
				$arrayEOH = array();
				if($_PARAM['not_include_employee_login'] === true){
					// not add employee login
				}else{
					$arrayEOH[] = $GLOBALS['employeeLogin']['employee_id'];

				}
				for ($i = 0; $i < sizeof($posEmpList); $i++){
					$arrayEOH[] = $posEmpList[$i]['employee_id'];
				}
				$_sql .= "AND _employee.employee_id IN ('" . implode("','" , $arrayEOH) . "') ";
				// $posEmpList = $this->getListEmployeePositionLine($GLOBALS['employeeLogin']['employee_id']);
				// $arrayEOH = array();
				// for($i=0;$i<sizeof($posEmpList);$i++){
				// 	$arrayEOH[] = $posEmpList[$i]['employee_id'];
				// }
				// if(sizeof($arrayEOH)>0){
				// 	$_sql .= "AND _employee.employee_id IN ('".implode("','" , $arrayEOH)."') ";
				// }
			} else if ($auth == true) {
				// echo "Test 	2";
				// if($_REQUEST['_beta'] == "Y"){
					$tmp_supervisor = $this->filterSupervisorBeta();
					$supervisor_count = $tmp_supervisor['supervisor_count'];
					$arrayEOH = $tmp_supervisor['employee_list'];
				// }else{
				// 	$tmp_supervisor = $this->filterSupervisor();
				// 	$supervisor_count = $tmp_supervisor['supervisor_count'];
				// 	$arrayEOH = $tmp_supervisor['employee_list'];
				// }
				
				// if (sizeof($arrayEOH) > 0) {
					if($supervisor_count > 0){
						$_sql .= "AND _employee.employee_id IN ('" . implode("','" , $arrayEOH) . "') ";
					}
				// }
			}
		}

		if(is_array($_PARAM["employee_type_code"]) && sizeof($_PARAM["employee_type_code"]) > 0){
			$employee_type_code = "";
				for ($i = 0; $i < sizeof($_REQUEST["employee_type_code"]); $i++) {
					if ($i == 0)
						$employee_type_code = "'" . ($_REQUEST["employee_type_code"][$i]) . "' ";
					else
						$employee_type_code .= ", '" . ($_REQUEST["employee_type_code"][$i]) . "' ";
				}

			$_sql .= "AND _employee.employee_type_code IN ({$employee_type_code}) ";
		}
		
		$_sql .= "ORDER BY _company.company_code,_branch.branch_code,_department.department_code,_division.division_code,_section.section_code,_section_lv01.section_lv01_code,_section_lv02.section_lv02_code,_section_lv03.section_lv03_code,_section_lv04.section_lv04_code,_section_lv05.section_lv05_code,_employee.employee_code ";
		if(!empty($_PARAM['_PAGE']) && !empty($_PARAM['_NUMBER_PER_PAGE']) && $_PARAM['_PAGE'] > 0 && $_PARAM['_NUMBER_PER_PAGE'] > 0){
			$_LIMIT = $_PARAM['_NUMBER_PER_PAGE'];
			$_OFFSET = ($_PARAM['_PAGE'] - 1) * $_PARAM['_NUMBER_PER_PAGE'];
			$_sql .= "LIMIT {$_LIMIT} OFFSET {$_OFFSET}";
		}
		// $channel = $GLOBALS['instanceServerChannelService']->getInstanceServerSpecificChannels($_REQUEST['server_id'], $_REQUEST['instance_server_id'],$_REQUEST['instance_server_channel_id']);
		// if($channel['max_user_limit']>0){
		// 	$_sql .= "LIMIT ".$channel['max_user_limit']; 
		// }
		// if($_REQUEST['_debug']=='Y'){
		// echo "$_sql<br><hr>";
		// }

		// if(!empty($_PARAM['sqlCondition'])){
		// 	$_sql .= $_PARAM['sqlCondition'];
		// }

		// echo "$_sql<hr>";
		// exit;

		$lists = $this->_sqllists($_sql);

		if ($_PARAM['check_count_of_employee'] === true && sizeof($lists) > $_PARAM['count_of_employee_limit']) {
			throw new Exception('employee-overlimit');
		}

		$_sql = "SELECT _cycle.*
						FROM hms_api.comp_work_cycle _cycle
						WHERE _cycle.server_id = '{$_REQUEST['server_id']}'
						AND _cycle.instance_server_id = '{$_REQUEST['instance_server_id']}' 
						AND _cycle.instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}' 
						ORDER BY _cycle.work_cycle_code ";
		// echo "$_sql<br>";
		$cycleLists = $this->_sqllists($_sql);
		$labelCycle = array();
		for ($i = 0; $i < sizeof($cycleLists); $i++){
			$labelCycle[$cycleLists[$i]['work_cycle_id']] = $cycleLists[$i];
		}

		$_sql = "SELECT user_id AS identify_user_id, user_name, first_singin_flag, employee_id   
		FROM hms_api.suso_user 
		WHERE server_id = '{$_REQUEST['server_id']}' 
		AND instance_server_id = '{$_REQUEST['instance_server_id']}'  
		AND instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}' ";

		$user_tmp = $GLOBALS['userService']->_sqllists($_sql);

		$approver_list = $this->getApproverList('', false, $_PARAM['check_step']);
		$approver_step = array("first","second","third","fourth","fifth");
		$hashmapApprover = array();
		foreach($approver_list as $approver){
			$hashmapApprover[$approver['employee_id']]['auth_'.$approver_step[$approver['approver_step']-1]] = $approver['approver_employee_id'];
			$hashmapApprover[$approver['employee_id']]['auth_'.$approver_step[$approver['approver_step']-1].'_id'] = $approver['approver_employee_id'];
			$hashmapApprover[$approver['employee_id']]['auth_'.$approver_step[$approver['approver_step']-1].'_code'] = $approver['approver_employee_code'];
			$hashmapApprover[$approver['employee_id']]['auth_'.$approver_step[$approver['approver_step']-1].'_name'] = $approver['approver_employee_name'];
			$hashmapApprover[$approver['employee_id']]['auth_'.$approver_step[$approver['approver_step']-1].'_last_name'] = $approver['approver_employee_last_name'];
			$hashmapApprover[$approver['employee_id']]['auth_'.$approver_step[$approver['approver_step']-1].'_nickname'] = $approver['approver_employee_nickname'];
			$hashmapApprover[$approver['employee_id']]['auth_'.$approver_step[$approver['approver_step']-1].'_name_en'] = $approver['approver_employee_name_en'];
			$hashmapApprover[$approver['employee_id']]['auth_'.$approver_step[$approver['approver_step']-1].'_last_name_en'] = $approver['approver_employee_last_name_en'];
			$hashmapApprover[$approver['employee_id']]['auth_'.$approver_step[$approver['approver_step']-1].'_nickname_en'] = $approver['approver_employee_nickname_en'];
			$hashmapApprover[$approver['employee_id']]['auth_'.$approver_step[$approver['approver_step']-1].'_photograph'] = $approver['approver_photograph'];
			$hashmapApprover[$approver['employee_id']]['auth_'.$approver_step[$approver['approver_step']-1].'_channel_id'] = $approver['approver_channel_id'];
		}

		for ($i = 0; $i < sizeof($lists); $i++){
			$cycleListsEmployee = json_decode($lists[$i]['work_cycle_id_json'], true);
			$cycleKey = array_keys($cycleListsEmployee);
			for ($x = 0; $x < sizeof($cycleKey); $x++){
				$lists[$i]['work_cycle_lists'][$x][$cycleKey[$x]] = $labelCycle[$cycleListsEmployee[$cycleKey[$x]]];
			}

			$holidayListsEmployee = json_decode($lists[$i]['holiday_day_json'], true);
			$holidayKey = array_keys($holidayListsEmployee);
			for ($x = 0; $x < sizeof($holidayKey); $x++){
				$lists[$i]['holiday_lists'][$x][$holidayKey[$x]] = $holidayListsEmployee[$holidayKey[$x]];
			}

			$key_user = array_search($lists['employee_id'], array_column($user_tmp, 'employee'));
			if($key_user != false){
				$lists[$i] = array_unique(array_merge($lists[$i], $user_tmp[$key_user]));
			}else{
				$lists[$i]['identify_user_id'] = null;
				$lists[$i]['user_name'] = null;
				$lists[$i]['first_singin_flag'] = null;
			}

			$arrayApprover = $hashmapApprover[$lists[$i]['employee_id']]?? array();
			$lists[$i] = array_merge($lists[$i], $arrayApprover);

			// $approver_step = array("first","second","third","fourth","fifth");
			// for($app_idx = 0; $app_idx < sizeof($approver_list); $app_idx++){
			// 	if($approver_list[$app_idx]['employee_id'] == $lists[$i]['employee_id']){
			// 		$lists[$i]['auth_'.$approver_step[$approver_list[$app_idx]['approver_step']-1]] = $approver_list[$app_idx]['approver_employee_id'];
			// 		$lists[$i]['auth_'.$approver_step[$approver_list[$app_idx]['approver_step']-1].'_id'] = $approver_list[$app_idx]['approver_employee_id'];
			// 		$lists[$i]['auth_'.$approver_step[$approver_list[$app_idx]['approver_step']-1].'_code'] = $approver_list[$app_idx]['approver_employee_code'];
			// 		// $lists[$i]['auth_'.$approver_step[$approver_list[$app_idx]['approver_step']-1].'_name'] = $_REQUEST['language_code'] == 'TH' ? $approver_list[$app_idx]['approver_employee_name'] : $approver_list[$app_idx]['approver_employee_name_en'];
			// 		// $lists[$i]['auth_'.$approver_step[$approver_list[$app_idx]['approver_step']-1].'_last_name'] = $_REQUEST['language_code'] == 'TH' ? $approver_list[$app_idx]['approver_employee_last_name'] : $approver_list[$app_idx]['approver_employee_last_name_en'];
			// 		// $lists[$i]['auth_'.$approver_step[$approver_list[$app_idx]['approver_step']-1].'_nickname'] = $_REQUEST['language_code'] == 'TH' ? $approver_list[$app_idx]['approver_employee_nickname'] : $approver_list[$app_idx]['approver_employee_nickname_en'];
					
			// 		$lists[$i]['auth_'.$approver_step[$approver_list[$app_idx]['approver_step']-1].'_name'] = $approver_list[$app_idx]['approver_employee_name'];
			// 		$lists[$i]['auth_'.$approver_step[$approver_list[$app_idx]['approver_step']-1].'_last_name'] = $approver_list[$app_idx]['approver_employee_last_name'];
			// 		$lists[$i]['auth_'.$approver_step[$approver_list[$app_idx]['approver_step']-1].'_nickname'] = $approver_list[$app_idx]['approver_employee_nickname'];
			// 		$lists[$i]['auth_'.$approver_step[$approver_list[$app_idx]['approver_step']-1].'_name_en'] = $approver_list[$app_idx]['approver_employee_name_en'];
			// 		$lists[$i]['auth_'.$approver_step[$approver_list[$app_idx]['approver_step']-1].'_last_name_en'] = $approver_list[$app_idx]['approver_employee_last_name_en'];
			// 		$lists[$i]['auth_'.$approver_step[$approver_list[$app_idx]['approver_step']-1].'_nickname_en'] = $approver_list[$app_idx]['approver_employee_nickname_en'];
			// 		$lists[$i]['auth_'.$approver_step[$approver_list[$app_idx]['approver_step']-1].'_photograph'] = $approver_list[$app_idx]['approver_photograph'];
			// 		$lists[$i]['auth_'.$approver_step[$approver_list[$app_idx]['approver_step']-1].'_channel_id'] = $approver_list[$app_idx]['approver_channel_id'];
			// 	}
			// }
		}

		return $lists;
	}

	function getListEmployeeWithFilter($_PARAM)
	{
		// print_r($GLOBALS['employeeLogin']);
		$_sql = "SELECT _employee.employee_id,
						_employee.employee_code,
						_employee.fing_code,
						_employee.employee_type_code,
						_employee.employee_type_group_id,
						_employee.employee_nickname,
						_employee.employee_nickname_en,
						_employee.employee_name,
						_employee.employee_last_name,
						_employee.employee_name_en,
						_employee.employee_last_name_en,
						_employee.employee_title_lv,
						_employee.employee_gender,
						_employee.employee_foreigner,
						_employee.employee_status,
						_employee.position_id,
						_employee.company_id,
						_employee.branch_id,
						_employee.department_id,
						_employee.division_id,
						_employee.section_id,
						_employee.section_lv01_id,
						_employee.section_lv02_id,
                        _employee.section_lv03_id,
                        _employee.section_lv04_id,
                        _employee.section_lv05_id,
						_employee.mobilephone,
						_employee.emailaddress,
						_employee.salary,
						_employee.salary_law,
						_employee.salary_per_week_type_lv,
						_employee.salary_per_week,
						_employee.payment_method,
						_employee.social_insurance_method_lv,
						_employee.social_insurance_method_constant,
						_employee.social_insurance_deduct_lv,
						_employee.tax_method_lv,
						_employee.tax_method_constant,
						_employee.tax_method_rate,
						_employee.tax_deduct_lv,
						_employee.tax_start_month,
						_employee.days_per_month,
						_employee.hours_per_day,
						_employee.birth_dt,
						_employee.id_no,
						_employee.sso_no,
						_employee.sso_start_month,
						_employee.opt_code,
						_employee.person_id,
						_employee.line_user_id,
						_employee.player_id,
						_employee.apple_id,
						_employee.line_token_id,
						_employee.line_token_todolist_id,
						_employee.chat_token,
						IFNULL(_employee.photograph , 'images/userPlaceHolder.png') AS photograph,
						_employee.bank_id,
						_employee.coa_account_group_id,
						_employee.company_payment_account_id,
						_employee.bank_branch_code,
						_employee.bank_account_code,
						_employee.work_cycle_id_json,
						_employee.work_cycle_format,
						_employee.holiday_day_json,
						_employee.holiday_format,
						_employee.clock_inout,
						_employee.trial_range,
						_employee.effective_dt,
						_employee.begin_dt,
						_employee.signout_flag,
						_employee.signout_request_dt,
						_employee.signout_dt,
						_employee.out_dt,
						_employee.sso_out_dt,
						_employee.signout_type_flag,
						_employee.signout_remark,
						_employee.round_month_config,
						_employee.round_xtra_config,
						_employee.round_ot_config,
						_employee.round_worktime_config,
						_employee.holiday_apply_config,
						_employee.import_log_id,
						_employee.personal_config,
						_employee.address,
						_employee.address1,
						_employee.address2,
						_employee.address3,
						_employee.address4,
						_employee.address5,
						_employee.address6,
						_employee.address7,
						_employee.address8,
						_employee.address9,
						_employee.country_id,
						_employee.country_code,
						_employee.state_code,
						_employee.district_code,
						_employee.subdistrict_code,
						_employee.post_code,
						_employee.current_address,
						_employee.current_address1,
						_employee.current_address2,
						_employee.current_address3,
						_employee.current_address4,
						_employee.current_address5,
						_employee.current_address6,
						_employee.current_address7,
						_employee.current_address8,
						_employee.current_address9,
						_employee.current_country_code,
						_employee.current_state_code,
						_employee.current_district_code,
						_employee.current_subdistrict_code,
						_employee.current_post_code,
						_employee.hashtag_desc,
						_employee.order_no,
						_employee.server_id,
						_employee.instance_server_id,
						_employee.instance_server_channel_id,
						_employee.sys_del_flag,
						_employee.reference_code_1,					
						_employee.reference_code_2,					
						_employee.reference_code_3,					
						_employee.reference_code_4,					
						_employee.reference_code_5,
						_company.company_code,
						_company.company_name,
						_company.company_name_en,
						_branch.branch_code,
						_branch.branch_name,
						_branch.branch_name_en,
						_department.department_code,
						_department.department_name,
						_department.department_name_en,
						_division.division_code,
						_division.division_name,
						_division.division_name_en,
						_section.section_code,
						_section.section_name,
						_section.section_name_en,
						_section_lv01.section_lv01_code,
						_section_lv01.section_lv01_name,
						_section_lv01.section_lv01_name_en,
						_section_lv02.section_lv02_code,
						_section_lv02.section_lv02_name,
						_section_lv02.section_lv02_name_en,
						_section_lv03.section_lv03_code,
						_section_lv03.section_lv03_name,
						_section_lv03.section_lv03_name_en,
						_section_lv04.section_lv04_code,
						_section_lv04.section_lv04_name,
						_section_lv04.section_lv04_name_en,
						_section_lv05.section_lv05_code,
						_section_lv05.section_lv05_name,
						_section_lv05.section_lv05_name_en,
						_position.position_code,
						_position.position_name,
						_position.position_name_en,
						_taxperson.person_tax_transac_id AS person_tax_id,
						 _employee.publish_flag  ,
						 _typegroup.tax_type ,
						 _typegroup.employee_type_group_id,
						 _typegroup.employee_type_group_code,
						 _typegroup.employee_type_group_name,
						 _typegroup.employee_type_group_name_en 
						FROM (select * from hms_api.comp_employee  where instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}') _employee 
						INNER JOIN (select company_id, company_code, company_name, company_name_en FROM hms_api.comp_company  where instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}') _company ON (_company.company_id=_employee.company_id) 
						INNER JOIN (select branch_id, branch_code, branch_name, branch_name_en FROM hms_api.comp_branch  where instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}') _branch ON (_branch.branch_id=_employee.branch_id) 
						INNER JOIN (select department_id, department_code, department_name, department_name_en 
						                       FROM hms_api.comp_department  where instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}') _department ON (_department.department_id=_employee.department_id) 
						LEFT JOIN (select division_id, division_code, division_name, division_name_en 
						                       FROM hms_api.comp_division where instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}') _division ON (_division.division_id=_employee.division_id) 
						LEFT JOIN (select section_id, section_code, section_name, section_name_en 
						                       FROM hms_api.comp_section where instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}') _section ON (_section.section_id=_employee.section_id) 
						LEFT JOIN (select section_lv01_id, section_lv01_code, section_lv01_name, section_lv01_name_en 
						                       FROM hms_api.comp_section_lv01 where instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}') _section_lv01 ON (_section_lv01.section_lv01_id=_employee.section_lv01_id) 
						LEFT JOIN (select section_lv02_id, section_lv02_code, section_lv02_name, section_lv02_name_en 
						                       FROM hms_api.comp_section_lv02 where instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}') _section_lv02 ON (_section_lv02.section_lv02_id=_employee.section_lv02_id) 
						LEFT JOIN (select section_lv03_id, section_lv03_code, section_lv03_name, section_lv03_name_en 
						                       FROM hms_api.comp_section_lv03 where instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}') _section_lv03 ON (_section_lv03.section_lv03_id=_employee.section_lv03_id) 
						LEFT JOIN (select section_lv04_id, section_lv04_code, section_lv04_name, section_lv04_name_en 
						                       FROM hms_api.comp_section_lv04 where instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}') _section_lv04 ON (_section_lv04.section_lv04_id=_employee.section_lv04_id) 
						LEFT JOIN (select section_lv05_id, section_lv05_code, section_lv05_name, section_lv05_name_en 
						                       FROM hms_api.comp_section_lv05 where instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}') _section_lv05 ON (_section_lv05.section_lv05_id=_employee.section_lv05_id) 
						INNER JOIN (select position_id, position_code, position_name, position_name_en 
												FROM hms_api.comp_position where instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}') _position ON (_position.position_id=_employee.position_id) 
						LEFT JOIN (
							SELECT person_tax_transac_id, tax_year_code, tax_month_code, tax_category_id, employee_id 
							FROM {$GLOBALS['instanceServer']['instance_server_dbn']}.payroll_person_tax_transac 
							WHERE instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}' 
							AND tax_year_code = '" . date('Y') . "' 
							AND tax_month_code = '12' 
							AND tax_category_id = '60'
						) _taxperson ON (_taxperson.employee_id = _employee.employee_id) 
						LEFT JOIN hms_api.comp_employee_type_group _typegroup ON (_typegroup.employee_type_group_id = _employee.employee_type_group_id) 
						WHERE _employee.server_id = '{$_REQUEST['server_id']}'
						AND _employee.instance_server_id = '{$_REQUEST['instance_server_id']}'
						AND _employee.instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}' ";
		// echo $_sql;
		// exit;
		if($_PARAM["keyword"] != ''){
			$_sql .= "AND (
								_employee.employee_nickname LIKE '%{$_PARAM["keyword"]}%' OR
								_employee.employee_name LIKE '%{$_PARAM["keyword"]}%' OR
								_employee.employee_last_name LIKE '%{$_PARAM["keyword"]}%' OR
								_employee.employee_nickname_en LIKE '%{$_PARAM["keyword"]}%' OR
								_employee.employee_name_en LIKE '%{$_PARAM["keyword"]}%' OR
								_employee.employee_last_name_en LIKE '%{$_PARAM["keyword"]}%' OR
								_employee.fing_code LIKE '%{$_PARAM["keyword"]}%' OR
								_employee.employee_code LIKE '%{$_PARAM["keyword"]}%') ";
		}

		if(sizeof($_PARAM["hashtags"]) == 1){
			$_sql .= " AND _employee.hashtag_desc LIKE '%{$_PARAM["hashtags"][0]}%' ";
		} else if(sizeof($_PARAM["hashtags"]) > 1){
			$_sql .= " AND ( ";
			for ($i = 0; $i < sizeof($_PARAM["hashtags"]); $i++){
				if($i == 0)
					$_sql .= " _employee.hashtag_desc LIKE '%{$_PARAM["hashtags"][$i]}%' ";
				else
					$_sql .= " OR _employee.hashtag_desc LIKE '%{$_PARAM["hashtags"][$i]}%' ";
			}
			$_sql .= " ) ";
		}

		if(is_array($_PARAM["except"]) && sizeof($_PARAM["except"]) > 0){
			$excepIds = "";
			for ($i = 0; $i < sizeof($_PARAM["except"]); $i++){
				if($i == 0)
					$excepIds = "'" . base64_decode($_PARAM["except"][$i]["id"]) . "' ";
				else
					$excepIds .= ", '" . base64_decode($_PARAM["except"][$i]["id"]) . "' ";
			}
			$_sql .= "AND _employee.employee_id NOT IN ({$excepIds}) ";
		}

		// if($_PARAM['sys_del_flag']=='N'||$_PARAM['sys_del_flag']=='Y'){
		// 	$_sql .= "AND _employee.sys_del_flag = '{$_PARAM["sys_del_flag"]}' ";
		// }else if($_PARAM['sys_del_flag']=='A'){

		// }else{
		// 	$_sql .= "AND _employee.sys_del_flag = 'N' "; 
		// }

		if(is_array($_PARAM["company_lists"]) && sizeof($_PARAM["company_lists"]) > 0){
			$companyIds = "";
			for ($i = 0; $i < sizeof($_PARAM["company_lists"]); $i++){
				if($i == 0)
					$companyIds = "'" . base64_decode($_PARAM["company_lists"][$i]["id"]) . "' ";
				else
					$companyIds .= ", '" . base64_decode($_PARAM["company_lists"][$i]["id"]) . "' ";
			}
			$_sql .= "AND _employee.company_id IN ({$companyIds}) ";
		}

		if(is_array($_PARAM["branch_lists"]) && sizeof($_PARAM["branch_lists"]) > 0){
			$branchIds = "";
			for ($i = 0; $i < sizeof($_PARAM["branch_lists"]); $i++){
				if($i == 0)
					$branchIds = "'" . base64_decode($_PARAM["branch_lists"][$i]["id"]) . "' ";
				else
					$branchIds .= ", '" . base64_decode($_PARAM["branch_lists"][$i]["id"]) . "' ";
			}
			$_branchSql = " _employee.department_id IN (SELECT department_id FROM hms_api.comp_department WHERE branch_id IN ({$branchIds}) 
							AND instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}' AND sys_del_flag='N') ";
		}

		if(is_array($_PARAM["department_lists"]) && sizeof($_PARAM["department_lists"]) > 0){
			$departmentIds = "";
			for ($i = 0; $i < sizeof($_PARAM["department_lists"]); $i++){
				if($i == 0)
					$departmentIds = "'" . base64_decode($_PARAM["department_lists"][$i]["id"]) . "' ";
				else
					$departmentIds .= ", '" . base64_decode($_PARAM["department_lists"][$i]["id"]) . "' ";
			}
			$_departmentSql = "_employee.department_id IN ({$departmentIds}) ";
		}

		if(is_array($_PARAM["division_lists"]) && sizeof($_PARAM["division_lists"]) > 0){
			$divisionIds = "";
			for ($i = 0; $i < sizeof($_PARAM["division_lists"]); $i++){
				if($i == 0){
					$divisionIds = "'" . base64_decode($_PARAM["division_lists"][$i]["id"]) . "' ";
				} else {
					$divisionIds .= ", '" . base64_decode($_PARAM["division_lists"][$i]["id"]) . "' ";
				}
			}
			$_divisionSql = "_employee.division_id IN ({$divisionIds}) ";
		}

		if(is_array($_PARAM["section_lists"]) && sizeof($_PARAM["section_lists"]) > 0){
			$sectionIds = "";
			for ($i = 0; $i < sizeof($_PARAM["section_lists"]); $i++){
				if($i == 0){
					$sectionIds = "'" . base64_decode($_PARAM["section_lists"][$i]["id"]) . "' ";
				} else {
					$sectionIds .= ", '" . base64_decode($_PARAM["section_lists"][$i]["id"]) . "' ";
				}
			}
			$_sectionSql = "_employee.section_id IN ({$sectionIds}) ";
		}

		if(is_array($_PARAM["section_lists_lv01"]) && sizeof($_PARAM["section_lists_lv01"]) > 0){
			$sectionLv01Ids = "";
			for ($i = 0; $i < sizeof($_PARAM["section_lists_lv01"]); $i++){
				if($i == 0){
					$sectionLv01Ids = "'" . base64_decode($_PARAM["section_lists_lv01"][$i]["id"]) . "' ";
				} else {
					$sectionLv01Ids .= ", '" . base64_decode($_PARAM["section_lists_lv01"][$i]["id"]) . "' ";
				}
			}
			$_sectionLv01Sql = "_employee.section_lv01_id IN ({$sectionLv01Ids}) ";
		}

		if(is_array($_PARAM["section_lists_lv02"]) && sizeof($_PARAM["section_lists_lv02"]) > 0){
			$sectionLv02Ids = "";
			for ($i = 0; $i < sizeof($_PARAM["section_lists_lv02"]); $i++){
				if($i == 0){
					$sectionLv02Ids = "'" . base64_decode($_PARAM["section_lists_lv02"][$i]["id"]) . "' ";
				} else {
					$sectionLv02Ids .= ", '" . base64_decode($_PARAM["section_lists_lv02"][$i]["id"]) . "' ";
				}
			}
			$_sectionLv02Sql = "_employee.section_lv02_id IN ({$sectionLv02Ids}) ";
		}

		if(is_array($_PARAM["section_lists_lv03"]) && sizeof($_PARAM["section_lists_lv03"]) > 0){
			$sectionLv03Ids = "";
			for ($i = 0; $i < sizeof($_PARAM["section_lists_lv03"]); $i++){
				if($i == 0){
					$sectionLv03Ids = "'" . base64_decode($_PARAM["section_lists_lv03"][$i]["id"]) . "' ";
				} else {
					$sectionLv03Ids .= ", '" . base64_decode($_PARAM["section_lists_lv03"][$i]["id"]) . "' ";
				}
			}
			$_sectionLv03Sql = "_employee.section_lv03_id IN ({$sectionLv03Ids}) ";
		}

		if(is_array($_PARAM["section_lists_lv04"]) && sizeof($_PARAM["section_lists_lv04"]) > 0){
			$sectionLv04Ids = "";
			for ($i = 0; $i < sizeof($_PARAM["section_lists_lv04"]); $i++){
				if($i == 0){
					$sectionLv04Ids = "'" . base64_decode($_PARAM["section_lists_lv04"][$i]["id"]) . "' ";
				} else {
					$sectionLv04Ids .= ", '" . base64_decode($_PARAM["section_lists_lv04"][$i]["id"]) . "' ";
				}
			}
			$_sectionLv04Sql = "_employee.section_lv04_id IN ({$sectionLv04Ids}) ";
		}

		if(is_array($_PARAM["section_lists_lv05"]) && sizeof($_PARAM["section_lists_lv05"]) > 0){
			$sectionLv05Ids = "";
			for ($i = 0; $i < sizeof($_PARAM["section_lists_lv05"]); $i++){
				if($i == 0){
					$sectionLv05Ids = "'" . base64_decode($_PARAM["section_lists_lv05"][$i]["id"]) . "' ";
				} else {
					$sectionLv05Ids .= ", '" . base64_decode($_PARAM["section_lists_lv05"][$i]["id"]) . "' ";
				}
			}
			$_sectionLv05Sql = "_employee.section_lv05_id IN ({$sectionLv05Ids}) ";
		}


		if(sizeof($_PARAM["branch_lists"]) > 0 && sizeof($_PARAM["department_lists"]) > 0){
			$_sql .= "AND {$_branchSql} AND {$_departmentSql} ";
		} else if(sizeof($_PARAM["branch_lists"]) > 0 && sizeof($_PARAM["department_lists"]) == 0){
			$_sql .= "AND {$_branchSql} ";
		} else if(sizeof($_PARAM["branch_lists"]) == 0 && sizeof($_PARAM["department_lists"]) > 0){
			$_sql .= "AND {$_departmentSql}	";
		}

		if(sizeof($_PARAM["division_lists"]) > 0){
			$_sql .= "AND {$_divisionSql} ";
		}

		if(sizeof($_PARAM["section_lists"]) > 0){
			$_sql .= "AND {$_sectionSql} ";
		}

		if(sizeof($_PARAM["section_lists_lv01"]) > 0){
			$_sql .= "AND {$_sectionLv01Sql} ";
		}

		if(sizeof($_PARAM["section_lists_lv02"]) > 0){
			$_sql .= "AND {$_sectionLv02Sql} ";
		}
		if(sizeof($_PARAM["section_lists_lv03"]) > 0){
			$_sql .= "AND {$_sectionLv03Sql} ";
		}
		if(sizeof($_PARAM["section_lists_lv04"]) > 0){
			$_sql .= "AND {$_sectionLv04Sql} ";
		}
		if(sizeof($_PARAM["section_lists_lv05"]) > 0){
			$_sql .= "AND {$_sectionLv05Sql} ";
		}

		if (is_array($_PARAM["position_lists"]) && sizeof($_PARAM["position_lists"]) > 0) {
			$positionIds = "";
			for ($i = 0; $i < sizeof($_PARAM["position_lists"]); $i++){
				if($i == 0)
					$positionIds = "'" . base64_decode($_PARAM["position_lists"][$i]["id"]) . "' ";
				else
					$positionIds .= ", '" . base64_decode($_PARAM["position_lists"][$i]["id"]) . "' ";
			}
			$_sql .= "AND _employee.position_id IN ({$positionIds}) ";
		}

		if(is_array($_PARAM["employee_lists"]) && sizeof($_PARAM["employee_lists"]) > 0){
			$employeeIds = "";
			for ($i = 0; $i < sizeof($_PARAM["employee_lists"]); $i++){
				if($i == 0)
					$employeeIds = "'" . base64_decode($_PARAM["employee_lists"][$i]["id"]) . "' ";
				else
					$employeeIds .= ", '" . base64_decode($_PARAM["employee_lists"][$i]["id"]) . "' ";
			}
			$_sql .= "AND _employee.employee_id IN ({$employeeIds}) ";

			if($_PARAM['sys_del_flag'] == 'N' || $_PARAM['sys_del_flag'] == 'Y'){
				$_sql .= "AND _employee.sys_del_flag = '{$_PARAM["sys_del_flag"]}' ";
			}
		} else {
			if($_PARAM['sys_del_flag'] == 'N' || $_PARAM['sys_del_flag'] == 'Y'){
				$_sql .= "AND _employee.sys_del_flag = '{$_PARAM["sys_del_flag"]}' ";
			} else if($_PARAM['sys_del_flag'] == 'A'){

			} else {
				$_sql .= "AND _employee.sys_del_flag = 'N' ";
			}
		}

		if($_PARAM['signout_flag']){
			$_sql .= "AND _employee.signout_flag = '{$_PARAM['signout_flag']}' ";
		}
		if($_PARAM['round_xtra_config']){
			$_sql .= "AND _employee.round_xtra_config = '{$_PARAM['round_xtra_config']}' ";
		}
		if($_PARAM['round_ot_config']){
			$_sql .= "AND _employee.round_ot_config = '{$_PARAM['round_ot_config']}' ";
		}
		if($_PARAM['round_worktime_config']){
			$_sql .= "AND _employee.round_worktime_config = '{$_PARAM['round_worktime_config']}' ";
		}

		// เงื่อนไขหาวันเริ่มต้นของการทำงานอยู่ระหว่างช่วงเวลาที่เราระบุมาหรือเปล่า
		// หน้าประกาศข่าวสารใช้เงื่อนไขนี้
		if(isset($_REQUEST['effective_dt_range']) && is_array($_REQUEST['effective_dt_range']) && sizeof($_REQUEST['effective_dt_range']) > 0){
			$startDate = date('Y-m-d', strtotime($_REQUEST['effective_dt_range'][0]));
			$endDate = date('Y-m-d', strtotime($_REQUEST['effective_dt_range'][1]));
		    if (!empty($startDate) && !empty($endDate)) {
		        $_sql .= "AND _employee.effective_dt BETWEEN '{$startDate}' AND '{$endDate}' ";
		    }
		}
		
		if($GLOBALS['employeeLogin']['employee_id'] != ''){
			$auth = PageAuthorizeService::getAuthorizeByUserGroup(array("SAL", "SALINEX", "SALBU", "AUDIT", "HRBU"));
			if(!empty($_PARAM['sub_menu_approve_employee']) && $_PARAM['sub_menu_approve_employee']) {
				$auth = true;
			}
			if($_PARAM['only_in_position_line'] == true || $auth === false){
				$posEmpList = $this->getListEmployeeAuthorize($GLOBALS['employeeLogin']['employee_id']);
				$arrayEOH = array();
				if($_PARAM['not_include_employee_login'] === true){
					// not add employee login
				}else{
					$arrayEOH[] = $GLOBALS['employeeLogin']['employee_id'];

				}
				for ($i = 0; $i < sizeof($posEmpList); $i++){
					$arrayEOH[] = $posEmpList[$i]['employee_id'];
				}
				$_sql .= "AND _employee.employee_id IN ('" . implode("','" , $arrayEOH) . "') ";
				// $posEmpList = $this->getListEmployeePositionLine($GLOBALS['employeeLogin']['employee_id']);
				// $arrayEOH = array();
				// for($i=0;$i<sizeof($posEmpList);$i++){
				// 	$arrayEOH[] = $posEmpList[$i]['employee_id'];
				// }
				// if(sizeof($arrayEOH)>0){
				// 	$_sql .= "AND _employee.employee_id IN ('".implode("','" , $arrayEOH)."') ";
				// }
			} else if ($auth == true) {
				// echo "Test 	2";
				// if($_REQUEST['_beta'] == "Y"){
					$tmp_supervisor = $this->filterSupervisorBeta();
					$supervisor_count = $tmp_supervisor['supervisor_count'];
					$arrayEOH = $tmp_supervisor['employee_list'];
				// }else{
				// 	$tmp_supervisor = $this->filterSupervisor();
				// 	$supervisor_count = $tmp_supervisor['supervisor_count'];
				// 	$arrayEOH = $tmp_supervisor['employee_list'];
				// }
				
				// if (sizeof($arrayEOH) > 0) {
					if($supervisor_count > 0){
						$_sql .= "AND _employee.employee_id IN ('" . implode("','" , $arrayEOH) . "') ";
					}
				// }
			}
		}

		if(is_array($_PARAM["employee_type_code"]) && sizeof($_PARAM["employee_type_code"]) > 0){
			$employee_type_code = "";
				for ($i = 0; $i < sizeof($_REQUEST["employee_type_code"]); $i++) {
					if ($i == 0)
						$employee_type_code = "'" . ($_REQUEST["employee_type_code"][$i]) . "' ";
					else
						$employee_type_code .= ", '" . ($_REQUEST["employee_type_code"][$i]) . "' ";
				}

			$_sql .= "AND _employee.employee_type_code IN ({$employee_type_code}) ";
		}
		$_sql .= "ORDER BY _company.company_code,_branch.branch_code,_department.department_code,_division.division_code,_section.section_code,_section_lv01.section_lv01_code,_section_lv02.section_lv02_code,_section_lv03.section_lv03_code,_section_lv04.section_lv04_code,_section_lv05.section_lv05_code,_employee.employee_code ";
		if(!empty($_PARAM['_PAGE']) && !empty($_PARAM['_NUMBER_PER_PAGE']) && $_PARAM['_PAGE'] > 0 && $_PARAM['_NUMBER_PER_PAGE'] > 0){
			$_LIMIT = $_PARAM['_NUMBER_PER_PAGE'];
			$_OFFSET = ($_PARAM['_PAGE'] - 1) * $_PARAM['_NUMBER_PER_PAGE'];
			$_sql .= "LIMIT {$_LIMIT} OFFSET {$_OFFSET}";
		}
		// $channel = $GLOBALS['instanceServerChannelService']->getInstanceServerSpecificChannels($_REQUEST['server_id'], $_REQUEST['instance_server_id'],$_REQUEST['instance_server_channel_id']);
		// if($channel['max_user_limit']>0){
		// 	$_sql .= "LIMIT ".$channel['max_user_limit']; 
		// }
		// if($_REQUEST['_debug']=='Y'){
		// echo "$_sql<br><hr>";
		// }

		// if(!empty($_PARAM['sqlCondition'])){
		// 	$_sql .= $_PARAM['sqlCondition'];
		// }

		// echo "$_sql<hr>";
		// exit;

		$lists = $this->_sqllists($_sql);

		if ($_PARAM['check_count_of_employee'] === true && sizeof($lists) > $_PARAM['count_of_employee_limit']) {
			throw new Exception('employee-overlimit');
		}

		$_sql = "SELECT _cycle.*
						FROM hms_api.comp_work_cycle _cycle
						WHERE _cycle.server_id = '{$_REQUEST['server_id']}'
						AND _cycle.instance_server_id = '{$_REQUEST['instance_server_id']}' 
						AND _cycle.instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}' 
						ORDER BY _cycle.work_cycle_code ";
		// echo "$_sql<br>";
		$cycleLists = $this->_sqllists($_sql);
		$labelCycle = array();
		for ($i = 0; $i < sizeof($cycleLists); $i++){
			$labelCycle[$cycleLists[$i]['work_cycle_id']] = $cycleLists[$i];
		}

		$_sql = "SELECT user_id AS identify_user_id, user_name, first_singin_flag, employee_id   
		FROM hms_api.suso_user 
		WHERE server_id = '{$_REQUEST['server_id']}' 
		AND instance_server_id = '{$_REQUEST['instance_server_id']}'  
		AND instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}' ";

		$user_tmp = $GLOBALS['userService']->_sqllists($_sql);

		$approver_list = $this->getApproverList('', false, $_PARAM['check_step']);
		$approver_step = array("first","second","third","fourth","fifth");
		$hashmapApprover = array();
		foreach($approver_list as $approver){
			$hashmapApprover[$approver['employee_id']]['auth_'.$approver_step[$approver['approver_step']-1]] = $approver['approver_employee_id'];
			$hashmapApprover[$approver['employee_id']]['auth_'.$approver_step[$approver['approver_step']-1].'_id'] = $approver['approver_employee_id'];
			$hashmapApprover[$approver['employee_id']]['auth_'.$approver_step[$approver['approver_step']-1].'_code'] = $approver['approver_employee_code'];
			$hashmapApprover[$approver['employee_id']]['auth_'.$approver_step[$approver['approver_step']-1].'_name'] = $approver['approver_employee_name'];
			$hashmapApprover[$approver['employee_id']]['auth_'.$approver_step[$approver['approver_step']-1].'_last_name'] = $approver['approver_employee_last_name'];
			$hashmapApprover[$approver['employee_id']]['auth_'.$approver_step[$approver['approver_step']-1].'_nickname'] = $approver['approver_employee_nickname'];
			$hashmapApprover[$approver['employee_id']]['auth_'.$approver_step[$approver['approver_step']-1].'_name_en'] = $approver['approver_employee_name_en'];
			$hashmapApprover[$approver['employee_id']]['auth_'.$approver_step[$approver['approver_step']-1].'_last_name_en'] = $approver['approver_employee_last_name_en'];
			$hashmapApprover[$approver['employee_id']]['auth_'.$approver_step[$approver['approver_step']-1].'_nickname_en'] = $approver['approver_employee_nickname_en'];
			$hashmapApprover[$approver['employee_id']]['auth_'.$approver_step[$approver['approver_step']-1].'_photograph'] = $approver['approver_photograph'];
			$hashmapApprover[$approver['employee_id']]['auth_'.$approver_step[$approver['approver_step']-1].'_channel_id'] = $approver['approver_channel_id'];
		}

		for ($i = 0; $i < sizeof($lists); $i++){
			$cycleListsEmployee = json_decode($lists[$i]['work_cycle_id_json'], true);
			$cycleKey = array_keys($cycleListsEmployee);
			for ($x = 0; $x < sizeof($cycleKey); $x++){
				$lists[$i]['work_cycle_lists'][$x][$cycleKey[$x]] = $labelCycle[$cycleListsEmployee[$cycleKey[$x]]];
			}

			$holidayListsEmployee = json_decode($lists[$i]['holiday_day_json'], true);
			$holidayKey = array_keys($holidayListsEmployee);
			for ($x = 0; $x < sizeof($holidayKey); $x++){
				$lists[$i]['holiday_lists'][$x][$holidayKey[$x]] = $holidayListsEmployee[$holidayKey[$x]];
			}

			$key_user = array_search($lists['employee_id'], array_column($user_tmp, 'employee'));
			if($key_user != false){
				$lists[$i] = array_unique(array_merge($lists[$i], $user_tmp[$key_user]));
			}else{
				$lists[$i]['identify_user_id'] = null;
				$lists[$i]['user_name'] = null;
				$lists[$i]['first_singin_flag'] = null;
			}

			$arrayApprover = $hashmapApprover[$lists[$i]['employee_id']]?? array();
			$lists[$i] = array_merge($lists[$i], $arrayApprover);

			// $approver_step = array("first","second","third","fourth","fifth");
			// for($app_idx = 0; $app_idx < sizeof($approver_list); $app_idx++){
			// 	if($approver_list[$app_idx]['employee_id'] == $lists[$i]['employee_id']){
			// 		$lists[$i]['auth_'.$approver_step[$approver_list[$app_idx]['approver_step']-1]] = $approver_list[$app_idx]['approver_employee_id'];
			// 		$lists[$i]['auth_'.$approver_step[$approver_list[$app_idx]['approver_step']-1].'_id'] = $approver_list[$app_idx]['approver_employee_id'];
			// 		$lists[$i]['auth_'.$approver_step[$approver_list[$app_idx]['approver_step']-1].'_code'] = $approver_list[$app_idx]['approver_employee_code'];
			// 		// $lists[$i]['auth_'.$approver_step[$approver_list[$app_idx]['approver_step']-1].'_name'] = $_REQUEST['language_code'] == 'TH' ? $approver_list[$app_idx]['approver_employee_name'] : $approver_list[$app_idx]['approver_employee_name_en'];
			// 		// $lists[$i]['auth_'.$approver_step[$approver_list[$app_idx]['approver_step']-1].'_last_name'] = $_REQUEST['language_code'] == 'TH' ? $approver_list[$app_idx]['approver_employee_last_name'] : $approver_list[$app_idx]['approver_employee_last_name_en'];
			// 		// $lists[$i]['auth_'.$approver_step[$approver_list[$app_idx]['approver_step']-1].'_nickname'] = $_REQUEST['language_code'] == 'TH' ? $approver_list[$app_idx]['approver_employee_nickname'] : $approver_list[$app_idx]['approver_employee_nickname_en'];
					
			// 		$lists[$i]['auth_'.$approver_step[$approver_list[$app_idx]['approver_step']-1].'_name'] = $approver_list[$app_idx]['approver_employee_name'];
			// 		$lists[$i]['auth_'.$approver_step[$approver_list[$app_idx]['approver_step']-1].'_last_name'] = $approver_list[$app_idx]['approver_employee_last_name'];
			// 		$lists[$i]['auth_'.$approver_step[$approver_list[$app_idx]['approver_step']-1].'_nickname'] = $approver_list[$app_idx]['approver_employee_nickname'];
			// 		$lists[$i]['auth_'.$approver_step[$approver_list[$app_idx]['approver_step']-1].'_name_en'] = $approver_list[$app_idx]['approver_employee_name_en'];
			// 		$lists[$i]['auth_'.$approver_step[$approver_list[$app_idx]['approver_step']-1].'_last_name_en'] = $approver_list[$app_idx]['approver_employee_last_name_en'];
			// 		$lists[$i]['auth_'.$approver_step[$approver_list[$app_idx]['approver_step']-1].'_nickname_en'] = $approver_list[$app_idx]['approver_employee_nickname_en'];
			// 		$lists[$i]['auth_'.$approver_step[$approver_list[$app_idx]['approver_step']-1].'_photograph'] = $approver_list[$app_idx]['approver_photograph'];
			// 		$lists[$i]['auth_'.$approver_step[$approver_list[$app_idx]['approver_step']-1].'_channel_id'] = $approver_list[$app_idx]['approver_channel_id'];
			// 	}
			// }
		}

		return $lists;
	}

	function getListEmployeeWithFilterCount2($_PARAM)
	{
		// print_r($GLOBALS['employeeLogin']);
		$_sql = "SELECT _employee.employee_id,
						_employee.employee_code,
						_employee.fing_code,
						_employee.employee_type_code,
						_employee.employee_type_group_id,
						_employee.employee_nickname,
						_employee.employee_nickname_en,
						_employee.employee_name,
						_employee.employee_last_name,
						_employee.employee_name_en,
						_employee.employee_last_name_en,
						_employee.employee_title_lv,
						_employee.employee_gender,
						_employee.employee_foreigner,
						_employee.employee_status,
						_employee.position_id,
						_employee.company_id,
						_employee.branch_id,
						_employee.department_id,
						_employee.division_id,
						_employee.section_id,
						_employee.section_lv01_id,
						_employee.section_lv02_id,
                        _employee.section_lv03_id,
                        _employee.section_lv04_id,
                        _employee.section_lv05_id,
						_employee.mobilephone,
						_employee.emailaddress,
						_employee.salary,
						_employee.salary_law,
						_employee.salary_per_week_type_lv,
						_employee.salary_per_week,
						_employee.payment_method,
						_employee.social_insurance_method_lv,
						_employee.social_insurance_method_constant,
						_employee.social_insurance_deduct_lv,
						_employee.tax_method_lv,
						_employee.tax_method_constant,
						_employee.tax_method_rate,
						_employee.tax_deduct_lv,
						_employee.days_per_month,
						_employee.hours_per_day,
						_employee.birth_dt,
						_employee.id_no,
						_employee.sso_no,
						_employee.opt_code,
						_employee.person_id,
						_employee.line_user_id,
						_employee.player_id,
						_employee.apple_id,
						_employee.line_token_id,
						_employee.line_token_todolist_id,
						IFNULL(_employee.photograph , 'images/userPlaceHolder.png') AS photograph,
						_employee.bank_id,
						_employee.coa_account_group_id,
						_employee.company_payment_account_id,
						_employee.bank_branch_code,
						_employee.bank_account_code,
						_employee.work_cycle_id_json,
						_employee.work_cycle_format,
						_employee.holiday_day_json,
						_employee.holiday_format,
						_employee.clock_inout,
						_employee.trial_range,
						_employee.effective_dt,
						_employee.begin_dt,
						_employee.signout_flag,
						_employee.signout_request_dt,
						_employee.signout_dt,
						_employee.out_dt,
						_employee.sso_out_dt,
						_employee.signout_type_flag,
						_employee.signout_remark,
						_employee.round_month_config,
						_employee.round_xtra_config,
						_employee.round_ot_config,
						_employee.round_worktime_config,
						_employee.holiday_apply_config,
						_employee.import_log_id,
						_employee.personal_config,
						_employee.address,
						_employee.address1,
						_employee.address2,
						_employee.address3,
						_employee.address4,
						_employee.address5,
						_employee.address6,
						_employee.address7,
						_employee.address8,
						_employee.address9,
						_employee.country_id,
						_employee.country_code,
						_employee.state_code,
						_employee.district_code,
						_employee.subdistrict_code,
						_employee.post_code,
						_employee.current_address,
						_employee.current_address1,
						_employee.current_address2,
						_employee.current_address3,
						_employee.current_address4,
						_employee.current_address5,
						_employee.current_address6,
						_employee.current_address7,
						_employee.current_address8,
						_employee.current_address9,
						_employee.current_country_code,
						_employee.current_state_code,
						_employee.current_district_code,
						_employee.current_subdistrict_code,
						_employee.current_post_code,
						_employee.hashtag_desc,
						_employee.order_no,
						_employee.server_id,
						_employee.instance_server_id,
						_employee.instance_server_channel_id,
						_employee.sys_del_flag,
						_employee.reference_code_1,					
						_employee.reference_code_2,					
						_employee.reference_code_3,					
						_employee.reference_code_4,					
						_employee.reference_code_5,
						 _employee.publish_flag  
						FROM hms_api.comp_employee_lookup _employee
						WHERE _employee.server_id = '{$_REQUEST['server_id']}'
						AND _employee.instance_server_id = '{$_REQUEST['instance_server_id']}'
						AND _employee.instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}' ";

		if($_PARAM["keyword"] != ''){
			$_sql .= "AND (
								_employee.employee_nickname LIKE '%{$_PARAM["keyword"]}%' OR
								_employee.employee_name LIKE '%{$_PARAM["keyword"]}%' OR
								_employee.employee_last_name LIKE '%{$_PARAM["keyword"]}%' OR
								_employee.employee_nickname_en LIKE '%{$_PARAM["keyword"]}%' OR
								_employee.employee_name_en LIKE '%{$_PARAM["keyword"]}%' OR
								_employee.employee_last_name_en LIKE '%{$_PARAM["keyword"]}%' OR
								_employee.fing_code LIKE '%{$_PARAM["keyword"]}%' OR
								_employee.employee_code LIKE '%{$_PARAM["keyword"]}%') ";
		}

		if(sizeof($_PARAM["hashtags"]) == 1){
			$_sql .= " AND _employee.hashtag_desc LIKE '%{$_PARAM["hashtags"][0]}%' ";
		} else if(sizeof($_PARAM["hashtags"]) > 1){
			$_sql .= " AND ( ";
			for ($i = 0; $i < sizeof($_PARAM["hashtags"]); $i++){
				if($i == 0)
					$_sql .= " _employee.hashtag_desc LIKE '%{$_PARAM["hashtags"][$i]}%' ";
				else
					$_sql .= " OR _employee.hashtag_desc LIKE '%{$_PARAM["hashtags"][$i]}%' ";
			}
			$_sql .= " ) ";
		}

		if(is_array($_PARAM["except"]) && sizeof($_PARAM["except"]) > 0){
			$excepIds = "";
			for ($i = 0; $i < sizeof($_PARAM["except"]); $i++){
				if($i == 0)
					$excepIds = "'" . base64_decode($_PARAM["except"][$i]["id"]) . "' ";
				else
					$excepIds .= ", '" . base64_decode($_PARAM["except"][$i]["id"]) . "' ";
			}
			$_sql .= "AND _employee.employee_id NOT IN ({$excepIds}) ";
		}

		if(is_array($_PARAM["company_lists"]) && sizeof($_PARAM["company_lists"]) > 0){
			$companyIds = "";
			for ($i = 0; $i < sizeof($_PARAM["company_lists"]); $i++){
				if($i == 0)
					$companyIds = "'" . base64_decode($_PARAM["company_lists"][$i]["id"]) . "' ";
				else
					$companyIds .= ", '" . base64_decode($_PARAM["company_lists"][$i]["id"]) . "' ";
			}
			$_sql .= "AND _employee.company_id IN ({$companyIds}) ";
		}

		if(is_array($_PARAM["branch_lists"]) && sizeof($_PARAM["branch_lists"]) > 0){
			$branchIds = "";
			for ($i = 0; $i < sizeof($_PARAM["branch_lists"]); $i++){
				if($i == 0)
					$branchIds = "'" . base64_decode($_PARAM["branch_lists"][$i]["id"]) . "' ";
				else
					$branchIds .= ", '" . base64_decode($_PARAM["branch_lists"][$i]["id"]) . "' ";
			}
			$_branchSql = " _employee.department_id IN (SELECT department_id FROM hms_api.comp_department WHERE branch_id IN ({$branchIds}) 
							AND instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}' AND sys_del_flag='N') ";
		}

		if(is_array($_PARAM["department_lists"]) && sizeof($_PARAM["department_lists"]) > 0){
			$departmentIds = "";
			for ($i = 0; $i < sizeof($_PARAM["department_lists"]); $i++){
				if($i == 0)
					$departmentIds = "'" . base64_decode($_PARAM["department_lists"][$i]["id"]) . "' ";
				else
					$departmentIds .= ", '" . base64_decode($_PARAM["department_lists"][$i]["id"]) . "' ";
			}
			$_departmentSql = "_employee.department_id IN ({$departmentIds}) ";
		}

		if(is_array($_PARAM["division_lists"]) && sizeof($_PARAM["division_lists"]) > 0){
			$divisionIds = "";
			for ($i = 0; $i < sizeof($_PARAM["division_lists"]); $i++){
				if($i == 0){
					$divisionIds = "'" . base64_decode($_PARAM["division_lists"][$i]["id"]) . "' ";
				} else {
					$divisionIds .= ", '" . base64_decode($_PARAM["division_lists"][$i]["id"]) . "' ";
				}
			}
			$_divisionSql = "_employee.division_id IN ({$divisionIds}) ";
		}

		if(is_array($_PARAM["section_lists"]) && sizeof($_PARAM["section_lists"]) > 0){
			$sectionIds = "";
			for ($i = 0; $i < sizeof($_PARAM["section_lists"]); $i++){
				if($i == 0){
					$sectionIds = "'" . base64_decode($_PARAM["section_lists"][$i]["id"]) . "' ";
				} else {
					$sectionIds .= ", '" . base64_decode($_PARAM["section_lists"][$i]["id"]) . "' ";
				}
			}
			$_sectionSql = "_employee.section_id IN ({$sectionIds}) ";
		}

		if(is_array($_PARAM["section_lists_lv01"]) && sizeof($_PARAM["section_lists_lv01"]) > 0){
			$sectionLv01Ids = "";
			for ($i = 0; $i < sizeof($_PARAM["section_lists_lv01"]); $i++){
				if($i == 0){
					$sectionLv01Ids = "'" . base64_decode($_PARAM["section_lists_lv01"][$i]["id"]) . "' ";
				} else {
					$sectionLv01Ids .= ", '" . base64_decode($_PARAM["section_lists_lv01"][$i]["id"]) . "' ";
				}
			}
			$_sectionLv01Sql = "_employee.section_lv01_id IN ({$sectionLv01Ids}) ";
		}

		if(is_array($_PARAM["section_lists_lv02"]) && sizeof($_PARAM["section_lists_lv02"]) > 0){
			$sectionLv02Ids = "";
			for ($i = 0; $i < sizeof($_PARAM["section_lists_lv02"]); $i++){
				if($i == 0){
					$sectionLv02Ids = "'" . base64_decode($_PARAM["section_lists_lv02"][$i]["id"]) . "' ";
				} else {
					$sectionLv02Ids .= ", '" . base64_decode($_PARAM["section_lists_lv02"][$i]["id"]) . "' ";
				}
			}
			$_sectionLv02Sql = "_employee.section_lv02_id IN ({$sectionLv02Ids}) ";
		}

		if(is_array($_PARAM["section_lists_lv03"]) && sizeof($_PARAM["section_lists_lv03"]) > 0){
			$sectionLv03Ids = "";
			for ($i = 0; $i < sizeof($_PARAM["section_lists_lv03"]); $i++){
				if($i == 0){
					$sectionLv03Ids = "'" . base64_decode($_PARAM["section_lists_lv03"][$i]["id"]) . "' ";
				} else {
					$sectionLv03Ids .= ", '" . base64_decode($_PARAM["section_lists_lv03"][$i]["id"]) . "' ";
				}
			}
			$_sectionLv03Sql = "_employee.section_lv03_id IN ({$sectionLv03Ids}) ";
		}

		if(is_array($_PARAM["section_lists_lv04"]) && sizeof($_PARAM["section_lists_lv04"]) > 0){
			$sectionLv04Ids = "";
			for ($i = 0; $i < sizeof($_PARAM["section_lists_lv04"]); $i++){
				if($i == 0){
					$sectionLv04Ids = "'" . base64_decode($_PARAM["section_lists_lv04"][$i]["id"]) . "' ";
				} else {
					$sectionLv04Ids .= ", '" . base64_decode($_PARAM["section_lists_lv04"][$i]["id"]) . "' ";
				}
			}
			$_sectionLv04Sql = "_employee.section_lv04_id IN ({$sectionLv04Ids}) ";
		}

		if(is_array($_PARAM["section_lists_lv05"]) && sizeof($_PARAM["section_lists_lv05"]) > 0){
			$sectionLv05Ids = "";
			for ($i = 0; $i < sizeof($_PARAM["section_lists_lv05"]); $i++){
				if($i == 0){
					$sectionLv05Ids = "'" . base64_decode($_PARAM["section_lists_lv05"][$i]["id"]) . "' ";
				} else {
					$sectionLv05Ids .= ", '" . base64_decode($_PARAM["section_lists_lv05"][$i]["id"]) . "' ";
				}
			}
			$_sectionLv05Sql = "_employee.section_lv05_id IN ({$sectionLv05Ids}) ";
		}


		if(sizeof($_PARAM["branch_lists"]) > 0 && sizeof($_PARAM["department_lists"]) > 0){
			$_sql .= "AND {$_branchSql} AND {$_departmentSql} ";
		} else if(sizeof($_PARAM["branch_lists"]) > 0 && sizeof($_PARAM["department_lists"]) == 0){
			$_sql .= "AND {$_branchSql} ";
		} else if(sizeof($_PARAM["branch_lists"]) == 0 && sizeof($_PARAM["department_lists"]) > 0){
			$_sql .= "AND {$_departmentSql}	";
		}

		if(sizeof($_PARAM["division_lists"]) > 0){
			$_sql .= "AND {$_divisionSql} ";
		}

		if(sizeof($_PARAM["section_lists"]) > 0){
			$_sql .= "AND {$_sectionSql} ";
		}
	
		if(sizeof($_PARAM["section_lists_lv01"]) > 0){
			$_sql .= "AND {$_sectionLv01Sql} ";
		}

		if(sizeof($_PARAM["section_lists_lv02"]) > 0){
			$_sql .= "AND {$_sectionLv02Sql} ";
		}
		if(sizeof($_PARAM["section_lists_lv03"]) > 0){
			$_sql .= "AND {$_sectionLv03Sql} ";
		}
		if(sizeof($_PARAM["section_lists_lv04"]) > 0){
			$_sql .= "AND {$_sectionLv04Sql} ";
		}
		if(sizeof($_PARAM["section_lists_lv05"]) > 0){
			$_sql .= "AND {$_sectionLv05Sql} ";
		}

		if (is_array($_PARAM["position_lists"]) && sizeof($_PARAM["position_lists"]) > 0) {
			$positionIds = "";
			for ($i = 0; $i < sizeof($_PARAM["position_lists"]); $i++){
				if($i == 0)
					$positionIds = "'" . base64_decode($_PARAM["position_lists"][$i]["id"]) . "' ";
				else
					$positionIds .= ", '" . base64_decode($_PARAM["position_lists"][$i]["id"]) . "' ";
			}
			$_sql .= "AND _employee.position_id IN ({$positionIds}) ";
		}

		if(is_array($_PARAM["employee_lists"]) && sizeof($_PARAM["employee_lists"]) > 0){
			$employeeIds = "";
			for ($i = 0; $i < sizeof($_PARAM["employee_lists"]); $i++){
				if($i == 0)
					$employeeIds = "'" . base64_decode($_PARAM["employee_lists"][$i]["id"]) . "' ";
				else
					$employeeIds .= ", '" . base64_decode($_PARAM["employee_lists"][$i]["id"]) . "' ";
			}
			$_sql .= "AND _employee.employee_id IN ({$employeeIds}) ";

			if($_PARAM['sys_del_flag'] == 'N' || $_PARAM['sys_del_flag'] == 'Y'){
				$_sql .= "AND _employee.sys_del_flag = '{$_PARAM["sys_del_flag"]}' ";
			}
		} else {
			if($_PARAM['sys_del_flag'] == 'N' || $_PARAM['sys_del_flag'] == 'Y'){
				$_sql .= "AND _employee.sys_del_flag = '{$_PARAM["sys_del_flag"]}' ";
			} else if($_PARAM['sys_del_flag'] == 'A'){

			} else {
				$_sql .= "AND _employee.sys_del_flag = 'N' ";
			}
		}

		if($_PARAM['signout_flag']){
			$_sql .= "AND _employee.signout_flag = '{$_PARAM['signout_flag']}' ";
		}
		if($_PARAM['round_xtra_config']){
			$_sql .= "AND _employee.round_xtra_config = '{$_PARAM['round_xtra_config']}' ";
		}
		if($_PARAM['round_ot_config']){
			$_sql .= "AND _employee.round_ot_config = '{$_PARAM['round_ot_config']}' ";
		}
		if($_PARAM['round_worktime_config']){
			$_sql .= "AND _employee.round_worktime_config = '{$_PARAM['round_worktime_config']}' ";
		}

		if($GLOBALS['employeeLogin']['employee_id'] != ''){
			$auth = PageAuthorizeService::getAuthorizeByUserGroup(array("SAL", "SALINEX", "SALBU", "AUDIT", "HRBU"));
			if(!empty($_PARAM['sub_menu_approve_employee']) && $_PARAM['sub_menu_approve_employee']) {
				$auth = true;
			}

			if($_PARAM['only_in_position_line'] == true || $auth === false){
				$posEmpList = $this->getListEmployeeAuthorize($GLOBALS['employeeLogin']['employee_id']);
				$arrayEOH = array();
				if($_PARAM['not_include_employee_login'] === true){
					// not add employee login
				}else{
					$arrayEOH[] = $GLOBALS['employeeLogin']['employee_id'];

				}
				for ($i = 0; $i < sizeof($posEmpList); $i++){
					$arrayEOH[] = $posEmpList[$i]['employee_id'];
				}
				$_sql .= "AND _employee.employee_id IN ('" . implode("','" , $arrayEOH) . "') ";
				// $posEmpList = $this->getListEmployeePositionLine($GLOBALS['employeeLogin']['employee_id']);
				// $arrayEOH = array();
				// for($i=0;$i<sizeof($posEmpList);$i++){
				// 	$arrayEOH[] = $posEmpList[$i]['employee_id'];
				// }
				// if(sizeof($arrayEOH)>0){
				// 	$_sql .= "AND _employee.employee_id IN ('".implode("','" , $arrayEOH)."') ";
				// }
			} else if ($auth == true) {
					$tmp_supervisor = $this->filterSupervisorBeta();
					$supervisor_count = $tmp_supervisor['supervisor_count'];
					$arrayEOH = $tmp_supervisor['employee_list'];
					if($supervisor_count > 0){
						$_sql .= "AND _employee.employee_id IN ('" . implode("','" , $arrayEOH) . "') ";
					}
			}
		}
		$_sql .= "ORDER BY _employee.employee_code ";
		if(!empty($_PARAM['_PAGE']) && !empty($_PARAM['_NUMBER_PER_PAGE']) && $_PARAM['_PAGE'] > 0 && $_PARAM['_NUMBER_PER_PAGE'] > 0){
			$_LIMIT = $_PARAM['_NUMBER_PER_PAGE'];
			$_OFFSET = ($_PARAM['_PAGE'] - 1) * $_PARAM['_NUMBER_PER_PAGE'];
			$_sql .= "LIMIT {$_LIMIT} OFFSET {$_OFFSET}";
		}

		$lists = $this->_sqllists($_sql);

		if ($_PARAM['check_count_of_employee'] === true && sizeof($lists) > $_PARAM['count_of_employee_limit']) {
			throw new Exception('employee-overlimit');
		}

		return $lists;
	}
	function getListEmployeeWithFilterForReCount($_PARAM)
	{
		// print_r($GLOBALS['employeeLogin']);
		$_sql = "SELECT _employee.employee_id,
						_employee.employee_code,
						_employee.fing_code,
						_employee.employee_type_code,
						_employee.employee_type_group_id,
						_employee.employee_nickname,
						_employee.employee_nickname_en,
						_employee.employee_name,
						_employee.employee_last_name,
						_employee.employee_name_en,
						_employee.employee_last_name_en,
						_employee.employee_title_lv,
						_employee.employee_gender,
						_employee.employee_foreigner,
						_employee.employee_status,
						_employee.position_id,
						_employee.company_id,
						_employee.branch_id,
						_employee.department_id,
						_employee.division_id,
						_employee.section_id,
						_employee.mobilephone,
						_employee.emailaddress,
						_employee.salary,
						_employee.salary_law,
						_employee.salary_per_week_type_lv,
						_employee.salary_per_week,
						_employee.payment_method,
						_employee.social_insurance_method_lv,
						_employee.social_insurance_method_constant,
						_employee.social_insurance_deduct_lv,
						_employee.tax_method_lv,
						_employee.tax_method_constant,
						_employee.tax_method_rate,
						_employee.tax_deduct_lv,
						_employee.days_per_month,
						_employee.hours_per_day,
						_employee.birth_dt,
						_employee.id_no,
						_employee.sso_no,
						_employee.opt_code,
						_employee.person_id,
						_employee.line_user_id,
						_employee.player_id,
						_employee.apple_id,
						_employee.line_token_id,
						_employee.line_token_todolist_id,
						IFNULL(_employee.photograph , 'images/userPlaceHolder.png') AS photograph,
						_employee.bank_id,
						_employee.coa_account_group_id,
						_employee.company_payment_account_id,
						_employee.bank_branch_code,
						_employee.bank_account_code,
						_employee.work_cycle_id_json,
						_employee.work_cycle_format,
						_employee.holiday_day_json,
						_employee.holiday_format,
						_employee.clock_inout,
						_employee.trial_range,
						_employee.effective_dt,
						_employee.begin_dt,
						_employee.signout_flag,
						_employee.signout_request_dt,
						_employee.signout_dt,
						_employee.out_dt,
						_employee.sso_out_dt,
						_employee.signout_type_flag,
						_employee.signout_remark,
						_employee.round_month_config,
						_employee.round_xtra_config,
						_employee.round_ot_config,
						_employee.round_worktime_config,
						_employee.holiday_apply_config,
						_employee.import_log_id,
						_employee.personal_config,
						_employee.address,
						_employee.address1,
						_employee.address2,
						_employee.address3,
						_employee.address4,
						_employee.address5,
						_employee.address6,
						_employee.address7,
						_employee.address8,
						_employee.address9,
						_employee.country_id,
						_employee.country_code,
						_employee.state_code,
						_employee.district_code,
						_employee.subdistrict_code,
						_employee.post_code,
						_employee.current_address,
						_employee.current_address1,
						_employee.current_address2,
						_employee.current_address3,
						_employee.current_address4,
						_employee.current_address5,
						_employee.current_address6,
						_employee.current_address7,
						_employee.current_address8,
						_employee.current_address9,
						_employee.current_country_code,
						_employee.current_state_code,
						_employee.current_district_code,
						_employee.current_subdistrict_code,
						_employee.current_post_code,
						_employee.hashtag_desc,
						_employee.order_no,
						_employee.server_id,
						_employee.instance_server_id,
						_employee.instance_server_channel_id,
						_employee.sys_del_flag,
						_employee.reference_code_1,					
						_employee.reference_code_2,					
						_employee.reference_code_3,					
						_employee.reference_code_4,					
						_employee.reference_code_5,
						 _employee.publish_flag  
						FROM hms_api.comp_employee_lookup _employee
						WHERE _employee.server_id = '{$_REQUEST['server_id']}'
						AND _employee.instance_server_id = '{$_REQUEST['instance_server_id']}'";

		if($GLOBALS['employeeLogin']['employee_id'] != ''){
			// $auth = PageAuthorizeService::getAuthorizeByUserGroup(array("SAL", "SALINEX", "SALBU", "AUDIT", "HRBU"));
			// if($_PARAM['only_in_position_line'] == true || $auth === false){
				$posEmpList = $this->getListEmployeeAuthorize($GLOBALS['employeeLogin']['employee_id']);
				// echo json_encode($posEmpList);
				// exit;
				$arrayEOH = array();
				// if($_PARAM['not_include_employee_login'] === true){
				// 	// not add employee login
				// }else{
				// 	$arrayEOH[] = $GLOBALS['employeeLogin']['employee_id'];

				// }
				for ($i = 0; $i < sizeof($posEmpList); $i++){
					$arrayEOH[] = $posEmpList[$i]['employee_id'];
				}

				if (sizeof($arrayEOH)>0){
					$_sql .= "AND _employee.employee_id IN ('" . implode("','" , $arrayEOH) . "') ";
					
				}
				// $posEmpList = $this->getListEmployeePositionLine($GLOBALS['employeeLogin']['employee_id']);
				// $arrayEOH = array();
				// for($i=0;$i<sizeof($posEmpList);$i++){
				// 	$arrayEOH[] = $posEmpList[$i]['employee_id'];
				// }
				// if(sizeof($arrayEOH)>0){
				// 	$_sql .= "AND _employee.employee_id IN ('".implode("','" , $arrayEOH)."') ";
				// }
			// } else if ($auth == true) {
			// 		$tmp_supervisor = $this->filterSupervisorBeta();
			// 		$supervisor_count = $tmp_supervisor['supervisor_count'];
			// 		$arrayEOH = $tmp_supervisor['employee_list'];
			// 		if($supervisor_count > 0){
			// 			$_sql .= "AND _employee.employee_id IN ('" . implode("','" , $arrayEOH) . "') ";
			// 		}
			// }
		}
		$_sql .= "ORDER BY _employee.employee_code ";
		// echo $_sql;
	
		$lists = $this->_sqllists($_sql);

		if ($_PARAM['check_count_of_employee'] === true && sizeof($lists) > $_PARAM['count_of_employee_limit']) {
			throw new Exception('employee-overlimit');
		}

		return $lists;
	}
	function getListArrayEOHBySlip($_PARAM)
	{
		// print_r($GLOBALS['employeeLogin']);
		$_sql = "SELECT distinct _slip.employee_id
						FROM (select * from {$GLOBALS['instanceServer']['instance_server_dbn']}.payroll_master_salary_slip     
						where instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}') _slip
						WHERE _slip.server_id = '{$_REQUEST['server_id']}'
						AND _slip.instance_server_id = '{$_REQUEST['instance_server_id']}'
						AND SUBSTRING(_slip.master_salary_month,1,4) = '{$_PARAM['year']}'
						AND _slip.instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}' ";
				if($_PARAM['month'] != '' && $_PARAM['year'] != ''){
					$_sql .= "AND SUBSTRING(_slip.master_salary_month,6,7) = '{$_PARAM['month']}'";
				}

		$operator = "";
		$_sql_org = "";
		//นำไปใช้กับรายงานตรวจภาษีประจำเดือน tax month audit
		if(is_array($_PARAM['company_lists']) && sizeof($_PARAM['company_lists']) > 0){
			$_sql_org .= " {$operator} _slip.company_id IN ('".implode("','", array_map('base64_decode', array_column($_PARAM['company_lists'], 'id')))."') ";
			$operator = "OR";
		}
		if(is_array($_PARAM['branch_lists']) && sizeof($_PARAM['branch_lists']) > 0){
			$_sql_org .= " {$operator} _slip.branch_id IN ('".implode("','", array_map('base64_decode', array_column($_PARAM['branch_lists'], 'id')))."') ";
			$operator = "OR";
		}
		if(is_array($_PARAM['department_lists']) && sizeof($_PARAM['department_lists']) > 0){
			$_sql_org .= " {$operator} _slip.department_id IN ('".implode("','", array_map('base64_decode', array_column($_PARAM['department_lists'], 'id')))."') ";
			$operator = "OR";
		}
		if(is_array($_PARAM['division_lists']) && sizeof($_PARAM['division_lists']) > 0){
			$_sql_org .= " {$operator} _slip.division_id IN ('".implode("','", array_map('base64_decode', array_column($_PARAM['division_lists'], 'id')))."') ";
			$operator = "OR";
		}
		if(is_array($_PARAM['section_lists']) && sizeof($_PARAM['section_lists']) > 0){
			$_sql_org .= " {$operator} _slip.section_id IN ('".implode("','", array_map('base64_decode', array_column($_PARAM['section_lists'], 'id')))."') ";
			$operator = "OR";
		}
			if(is_array($_PARAM['section_lists_lv01']) && sizeof($_PARAM['section_lists_lv01']) > 0){
			$_sql_org .= " {$operator} _slip.section_lv01_id IN ('".implode("','", array_map('base64_decode', array_column($_PARAM['section_lists_lv01'], 'id')))."') ";
			$operator = "OR";
		}
		if(is_array($_PARAM['section_lists_lv02']) && sizeof($_PARAM['section_lists_lv02']) > 0){
			$_sql_org .= " {$operator} _slip.section_lv02_id IN ('".implode("','", array_map('base64_decode', array_column($_PARAM['section_lists_lv02'], 'id')))."') ";
			$operator = "OR";
		}
		if(is_array($_PARAM['section_lists_lv03']) && sizeof($_PARAM['section_lists_lv03']) > 0){
			$_sql_org .= " {$operator} _slip.section_lv03_id IN ('".implode("','", array_map('base64_decode', array_column($_PARAM['section_lists_lv03'], 'id')))."') ";
			$operator = "OR";
		}
		if(is_array($_PARAM['section_lists_lv04']) && sizeof($_PARAM['section_lists_lv04']) > 0){
			$_sql_org .= " {$operator} _slip.section_lv04_id IN ('".implode("','", array_map('base64_decode', array_column($_PARAM['section_lists_lv04'], 'id')))."') ";
			$operator = "OR";
		}
		if(is_array($_PARAM['section_lists_lv05']) && sizeof($_PARAM['section_lists_lv05']) > 0){
			$_sql_org .= " {$operator} _slip.section_lv05_id IN ('".implode("','", array_map('base64_decode', array_column($_PARAM['section_lists_lv05'], 'id')))."') ";
			$operator = "OR";
		}
		if($_sql_org != ""){
			$_sql .= " AND ({$_sql_org}) ";
		}
		if(is_array($_PARAM["employee_lists"]) && sizeof($_PARAM["employee_lists"]) > 0){
			$employeeIds = "";
			for ($i = 0; $i < sizeof($_PARAM["employee_lists"]); $i++){
				if($i == 0)
					$employeeIds = "'" . base64_decode($_PARAM["employee_lists"][$i]["id"]) . "' ";
				else
					$employeeIds .= ", '" . base64_decode($_PARAM["employee_lists"][$i]["id"]) . "' ";
			}
			$_sql .= "AND _slip.employee_id IN ({$employeeIds}) ";

		
		} 
		
		if($GLOBALS['employeeLogin']['employee_id'] != ''){
			$auth = PageAuthorizeService::getAuthorizeByUserGroup(array("SAL", "SALINEX", "SALBU", "AUDIT", "HRBU"));
			if($_PARAM['only_in_position_line'] == true || $auth === false){
				$posEmpList = $this->getListEmployeeAuthorize($GLOBALS['employeeLogin']['employee_id']);
				$arrayEOH = array();
				$arrayEOH[] = $GLOBALS['employeeLogin']['employee_id'];
				for ($i = 0; $i < sizeof($posEmpList); $i++){
					$arrayEOH[] = $posEmpList[$i]['employee_id'];
				}
				$_sql .= "AND _slip.employee_id IN ('" . implode("','" , $arrayEOH) . "') ";
		
			} else if ($auth == true) {
				// echo "Test 	2";
				// if($_REQUEST['_beta'] == "Y"){
					$tmp_supervisor = $this->filterSupervisorBeta();
					$supervisor_count = $tmp_supervisor['supervisor_count'];
					$arrayEOH = $tmp_supervisor['employee_list'];
			
					if($supervisor_count > 0){
						$_sql .= "AND _slip.employee_id IN ('" . implode("','" , $arrayEOH) . "') ";
					}
				
			}
		}
		if(!empty($_PARAM['_PAGE']) && !empty($_PARAM['_NUMBER_PER_PAGE']) && $_PARAM['_PAGE'] > 0 && $_PARAM['_NUMBER_PER_PAGE'] > 0){
			$_LIMIT = $_PARAM['_NUMBER_PER_PAGE'];
			$_OFFSET = ($_PARAM['_PAGE'] - 1) * $_PARAM['_NUMBER_PER_PAGE'];
			$_sql .= "LIMIT {$_LIMIT} OFFSET {$_OFFSET}";
		}
		// $channel = $GLOBALS['instanceServerChannelService']->getInstanceServerSpecificChannels($_REQUEST['server_id'], $_REQUEST['instance_server_id'],$_REQUEST['instance_server_channel_id']);
		// if($channel['max_user_limit']>0){
		// 	$_sql .= "LIMIT ".$channel['max_user_limit']; 
		// }
		// if($_REQUEST['_debug']=='Y'){
		// echo "$_sql<br><hr>";
		// }

	
		// echo "$_sql<hr>";
		// exit;

		$lists = $this->_sqllists($_sql);

		if ($_PARAM['check_count_of_employee'] === true && sizeof($lists) > $_PARAM['count_of_employee_limit']) {
			throw new Exception('employee-overlimit');
		}

		return $lists;
	}

	function getListArrayMonthEOHBySlip($_PARAM){
		// print_r($GLOBALS['employeeLogin']);
		$_sql = "SELECT distinct _slip.*, _emp.*
						FROM (select * from {$GLOBALS['instanceServer']['instance_server_dbn']}.payroll_master_salary_slip     
						where instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}') _slip
						INNER JOIN comp_employee _emp ON (_slip.employee_id = _emp.employee_id)
						WHERE _slip.server_id = '{$_REQUEST['server_id']}'
						AND _slip.instance_server_id = '{$_REQUEST['instance_server_id']}'
						AND SUBSTRING(_slip.master_salary_month,1,4) = '{$_PARAM['year']}'
						AND _slip.instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}' ";
		if($_PARAM['month'] != '' && $_PARAM['year'] != ''){
			$_sql .= "AND SUBSTRING(_slip.master_salary_month,6,7) = '{$_PARAM['month']}'";
		}
		if($_PARAM['delete_social'] == 'Y'){
			$_sql .= "AND _slip.social_insurance_method_lv <> '00' AND _slip.social_insurance_method_lv <> '04'";
		}

		$operator = "";
		$_sql_org = "";
		if(is_array($_PARAM['company_lists']) && sizeof($_PARAM['company_lists']) > 0){
			$_sql_org .= " {$operator} _slip.company_id IN ('".implode("','", array_map('base64_decode', array_column($_PARAM['company_lists'], 'id')))."') ";
			$operator = "OR";
		}
		if(is_array($_PARAM['branch_lists']) && sizeof($_PARAM['branch_lists']) > 0){
			$_sql_org .= " {$operator} _slip.branch_id IN ('".implode("','", array_map('base64_decode', array_column($_PARAM['branch_lists'], 'id')))."') ";
			$operator = "OR";
		}
		if(is_array($_PARAM['department_lists']) && sizeof($_PARAM['department_lists']) > 0){
			$_sql_org .= " {$operator} _slip.department_id IN ('".implode("','", array_map('base64_decode', array_column($_PARAM['department_lists'], 'id')))."') ";
			$operator = "OR";
		}
		if(is_array($_PARAM['division_lists']) && sizeof($_PARAM['division_lists']) > 0){
			$_sql_org .= " {$operator} _slip.division_id IN ('".implode("','", array_map('base64_decode', array_column($_PARAM['division_lists'], 'id')))."') ";
			$operator = "OR";
		}
		if(is_array($_PARAM['section_lists']) && sizeof($_PARAM['section_lists']) > 0){
			$_sql_org .= " {$operator} _slip.section_id IN ('".implode("','", array_map('base64_decode', array_column($_PARAM['section_lists'], 'id')))."') ";
			$operator = "OR";
		}
		if(is_array($_PARAM['section_lists_lv01']) && sizeof($_PARAM['section_lists_lv01']) > 0){
			$_sql_org .= " {$operator} _slip.section_lv01_id IN ('".implode("','", array_map('base64_decode', array_column($_PARAM['section_lists_lv01'], 'id')))."') ";
			$operator = "OR";
		}
		if(is_array($_PARAM['section_lists_lv02']) && sizeof($_PARAM['section_lists_lv02']) > 0){
			$_sql_org .= " {$operator} _slip.section_lv02_id IN ('".implode("','", array_map('base64_decode', array_column($_PARAM['section_lists_lv02'], 'id')))."') ";
			$operator = "OR";
		}
		if(is_array($_PARAM['section_lists_lv03']) && sizeof($_PARAM['section_lists_lv03']) > 0){
			$_sql_org .= " {$operator} _slip.section_lv03_id IN ('".implode("','", array_map('base64_decode', array_column($_PARAM['section_lists_lv03'], 'id')))."') ";
			$operator = "OR";
		}
		if(is_array($_PARAM['section_lists_lv04']) && sizeof($_PARAM['section_lists_lv04']) > 0){
			$_sql_org .= " {$operator} _slip.section_lv04_id IN ('".implode("','", array_map('base64_decode', array_column($_PARAM['section_lists_lv04'], 'id')))."') ";
			$operator = "OR";
		}
		if(is_array($_PARAM['section_lists_lv05']) && sizeof($_PARAM['section_lists_lv05']) > 0){
			$_sql_org .= " {$operator} _slip.section_lv05_id IN ('".implode("','", array_map('base64_decode', array_column($_PARAM['section_lists_lv05'], 'id')))."') ";
			$operator = "OR";
		}

		if($_sql_org != ""){
			$_sql .= " AND ({$_sql_org}) ";
		}
		if(is_array($_PARAM["employee_lists"]) && sizeof($_PARAM["employee_lists"]) > 0){
			$employeeIds = "";
			for ($i = 0; $i < sizeof($_PARAM["employee_lists"]); $i++){
				if($i == 0)
					$employeeIds = "'" . base64_decode($_PARAM["employee_lists"][$i]["id"]) . "' ";
				else
					$employeeIds .= ", '" . base64_decode($_PARAM["employee_lists"][$i]["id"]) . "' ";
			}
			$_sql .= "AND _slip.employee_id IN ({$employeeIds}) ";

		
		} 
		
		if($GLOBALS['employeeLogin']['employee_id'] != ''){
			$auth = PageAuthorizeService::getAuthorizeByUserGroup(array("SAL", "SALINEX", "SALBU", "AUDIT", "HRBU"));
			if($_PARAM['only_in_position_line'] == true || $auth === false){
				$posEmpList = $this->getListEmployeeAuthorize($GLOBALS['employeeLogin']['employee_id']);
				$arrayEOH = array();
				$arrayEOH[] = $GLOBALS['employeeLogin']['employee_id'];
				for ($i = 0; $i < sizeof($posEmpList); $i++){
					$arrayEOH[] = $posEmpList[$i]['employee_id'];
				}
				$_sql .= "AND _slip.employee_id IN ('" . implode("','" , $arrayEOH) . "') ";
		
			} else if ($auth == true) {
					$tmp_supervisor = $this->filterSupervisorBeta();
					$supervisor_count = $tmp_supervisor['supervisor_count'];
					$arrayEOH = $tmp_supervisor['employee_list'];
			
					if($supervisor_count > 0){
						$_sql .= "AND _slip.employee_id IN ('" . implode("','" , $arrayEOH) . "') ";
					}
				
			}
		}
		
		if(!empty($_PARAM['_PAGE']) && !empty($_PARAM['_NUMBER_PER_PAGE']) && $_PARAM['_PAGE'] > 0 && $_PARAM['_NUMBER_PER_PAGE'] > 0){
			$_LIMIT = $_PARAM['_NUMBER_PER_PAGE'];
			$_OFFSET = ($_PARAM['_PAGE'] - 1) * $_PARAM['_NUMBER_PER_PAGE'];
			$_sql .= "LIMIT {$_LIMIT} OFFSET {$_OFFSET}";
		}

		$lists = $this->_sqllists($_sql);

		if ($_PARAM['check_count_of_employee'] === true && sizeof($lists) > $_PARAM['count_of_employee_limit']) {
			throw new Exception('employee-overlimit');
		}

		return $lists;
	}

	function getApproverList($_emp = '', $domain = false, $check_step = true){
		if($GLOBALS['instanceServerChannel']['package_id'] == '3' || $GLOBALS['instanceServerChannel']['package_id'] == '4' || $GLOBALS['instanceServerChannel']['package_id'] == '6' || $GLOBALS['instanceServerChannel']['package_id'] == '10' || $check_step === false){
			$_sql = "SELECT _appr.* ,
			_appr.approver_id AS approver_employee_id,
			IFNULL(_appr.approver_photograph , 'images/userPlaceHolder.png') AS approver_photograph 
			FROM {$GLOBALS['instanceServer']['instance_server_dbn']}.payroll_employee_approver _appr 
			WHERE _appr.server_id = '{$_REQUEST['server_id']}' 
			AND _appr.instance_server_id = '{$_REQUEST['instance_server_id']}' ";

			if($GLOBALS['instanceServerChannel']['package_id'] != '3' && $GLOBALS['instanceServerChannel']['package_id'] != '4' && $check_step === true){
				$_sql .= " AND _appr.approver_step <= '2' ";
			}
			if($domain === false){
				$_sql .= " AND _appr.instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}' ";
			}
			

			if(!empty($_emp)){
				$_sql .= " AND _appr.employee_id = '{$_emp}' ";
			}
			$_sql .= " ORDER BY _appr.approver_step ASC ";
			// echo $_sql;
			$approver_list = $this->_sqllists($_sql);
		}else{
			$approver_list = array();
		}
		

		return $approver_list;
	}
	function getApproverListGroupByEmployeeId($step_approve, $_emp = '', $domain = false, $check_step = true){
		if($GLOBALS['instanceServerChannel']['package_id'] == '3' || $GLOBALS['instanceServerChannel']['package_id'] == '4' || $GLOBALS['instanceServerChannel']['package_id'] == '6' || $GLOBALS['instanceServerChannel']['package_id'] == '10' || $check_step === false){
			$_sql = "SELECT _appr.*
			, _appr.approver_id AS approver_employee_id
			, IFNULL(_appr.approver_photograph , 'images/userPlaceHolder.png') AS approver_photograph
			FROM {$GLOBALS['instanceServer']['instance_server_dbn']}.payroll_employee_approver _appr
			WHERE _appr.server_id = '{$_REQUEST['server_id']}'
			AND _appr.instance_server_id = '{$_REQUEST['instance_server_id']}' ";
			if($GLOBALS['instanceServerChannel']['package_id'] != '3' && $GLOBALS['instanceServerChannel']['package_id'] != '4' && $check_step === true){
				$_sql .= " AND _appr.approver_step <= '2' ";
			}
			if($domain === false){
				$_sql .= " AND _appr.instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}' ";
			}
			if(!empty($_emp)){
				$_sql .= " AND _appr.employee_id = '{$_emp}' ";
			}
			$_sql .= " ORDER BY _appr.approver_step ASC ";
			$approver_list = $this->_sqllists($_sql);
		}else{
			$approver_list = array();
		}

		$approver_step = array("first","second","third","fourth","fifth");

		$hashmapApprover = array();
		foreach($approver_list as $approver){
			if($step_approve > 0){
				if($approver['approver_step'] <= $step_approve){
					$hashmapApprover[$approver['employee_id']]['step_approve'] = $step_approve;
					$hashmapApprover[$approver['employee_id']]['auth_'.$approver_step[$approver['approver_step']-1]] = $approver['approver_employee_id'];
					$hashmapApprover[$approver['employee_id']]['auth_'.$approver_step[$approver['approver_step']-1].'_id'] = $approver['approver_employee_id'];
					$hashmapApprover[$approver['employee_id']]['auth_'.$approver_step[$approver['approver_step']-1].'_code'] = $approver['approver_employee_code'];
					$hashmapApprover[$approver['employee_id']]['auth_'.$approver_step[$approver['approver_step']-1].'_name'] = $_REQUEST['language_code'] == 'TH' ? $approver['approver_employee_name'] : $approver['approver_employee_name_en'];
					$hashmapApprover[$approver['employee_id']]['auth_'.$approver_step[$approver['approver_step']-1].'_last_name'] = $_REQUEST['language_code'] == 'TH' ? ($approver['approver_employee_last_name'] ?? ""): ($approver['approver_employee_last_name_en'] ?? "");
					$hashmapApprover[$approver['employee_id']]['auth_'.$approver_step[$approver['approver_step']-1].'_nickname'] = $_REQUEST['language_code'] == 'TH' ? ($approver['approver_employee_nickname'] ?? ""): ($approver['approver_employee_nickname_en'] ?? "");
					$hashmapApprover[$approver['employee_id']]['auth_'.$approver_step[$approver['approver_step']-1].'_photograph'] = $approver['approver_photograph'];

					// check employee null
					
				}
			}
		}
		
		return $hashmapApprover;
	}

	function getEmployeeAuthorize($_approve_emp_id, $_step = ""){
		$_sql_step = "";
		if(!empty($step)){
			$_sql_step = " AND _app.approver_step <= '{$_step}'";
		}

		$_sql = "SELECT _emp.* FROM hms_api.comp_employee _emp 
				INNER JOIN {$GLOBALS['instanceServer']['instance_server_dbn']}.payroll_employee_approver _app ON (_emp.employee_id = _app.employee_id AND _app.approver_id = '{$_approve_emp_id}' {$_sql_step}) 
				WHERE _emp.sys_del_flag = 'N' 
				AND _emp.employee_id != '{$_approve_emp_id}' 
				AND _emp.server_id = '{$_REQUEST['server_id']}' 
				AND _emp.instance_server_id = '{$_REQUEST['instance_server_id']}' 
				GROUP BY _emp.employee_id ";
		// echo $_sql;
		$emp_list = $this->_sqllists($_sql);

		return $emp_list;
	}

	function filterSupervisor(){
		$supervisor_count = 0;
		$_sql2 = "SELECT DISTINCT _master.employee_id
				FROM (
					SELECT _t2.employee_id 
					FROM hms_api.comp_supervisor _t1, hms_api.comp_employee _t2 
					WHERE _t1.employee_id = '{$GLOBALS['employeeLogin']['employee_id']}' 
					AND _t1.position_id = _t2.position_id 
					AND _t1.server_id = '{$_REQUEST['server_id']}' 
					AND _t1.instance_server_id = '{$_REQUEST['instance_server_id']}' 
					AND _t1.instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}' 
					UNION
					SELECT _t2.employee_id 
					FROM hms_api.comp_supervisor _t1, hms_api.comp_employee _t2 
					WHERE _t1.employee_id = '{$GLOBALS['employeeLogin']['employee_id']}' 
					AND _t1.specific_employee_id = _t2.employee_id 
					AND _t1.server_id = '{$_REQUEST['server_id']}' 
					AND _t1.instance_server_id = '{$_REQUEST['instance_server_id']}' 
					AND _t1.instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}' 
					UNION
					SELECT _t2.employee_id 
					FROM hms_api.comp_supervisor _t1, hms_api.comp_employee _t2 
					WHERE _t1.employee_id = '{$GLOBALS['employeeLogin']['employee_id']}' 
					AND _t1.company_id = _t2.company_id  
					AND _t1.branch_id IS NULL 
					AND _t1.department_id IS NULL 
					AND _t1.division_id IS NULL 
					AND _t1.section_id IS NULL 
					AND _t1.server_id = '{$_REQUEST['server_id']}' 
					AND _t1.instance_server_id = '{$_REQUEST['instance_server_id']}' 
					AND _t1.instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}' 
					UNION
					SELECT _t2.employee_id 
					FROM hms_api.comp_supervisor _t1, hms_api.comp_employee _t2 
					WHERE _t1.employee_id = '{$GLOBALS['employeeLogin']['employee_id']}' 
					AND _t1.company_id = _t2.company_id 
					AND _t1.branch_id = _t2.branch_id 
					AND _t1.department_id IS NULL 
					AND _t1.division_id IS NULL 
					AND _t1.section_id IS NULL 
					AND _t1.server_id = '{$_REQUEST['server_id']}' 
					AND _t1.instance_server_id = '{$_REQUEST['instance_server_id']}' 
					AND _t1.instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}' 
					UNION
					SELECT _t2.employee_id 
					FROM hms_api.comp_supervisor _t1, hms_api.comp_employee _t2 
					WHERE _t1.employee_id = '{$GLOBALS['employeeLogin']['employee_id']}' 
					AND _t1.company_id = _t2.company_id  
					AND _t1.branch_id = _t2.branch_id  
					AND _t1.department_id = _t2.department_id 
					AND _t1.division_id IS NULL 
					AND _t1.section_id IS NULL 
					AND _t1.server_id = '{$_REQUEST['server_id']}' 
					AND _t1.instance_server_id = '{$_REQUEST['instance_server_id']}' 
					AND _t1.instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}' 
					UNION
					SELECT _t2.employee_id 
					FROM hms_api.comp_supervisor _t1, hms_api.comp_employee _t2 
					WHERE _t1.employee_id = '{$GLOBALS['employeeLogin']['employee_id']}' 
					AND _t1.company_id = _t2.company_id  
					AND _t1.branch_id = _t2.branch_id  
					AND _t1.department_id = _t2.department_id 
					AND _t1.division_id = _t2.division_id 
					AND _t1.section_id IS NULL 
					AND _t1.server_id = '{$_REQUEST['server_id']}' 
					AND _t1.instance_server_id = '{$_REQUEST['instance_server_id']}' 
					AND _t1.instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}' 
					UNION
					SELECT _t2.employee_id 
					FROM hms_api.comp_supervisor _t1, hms_api.comp_employee _t2 
					WHERE _t1.employee_id = '{$GLOBALS['employeeLogin']['employee_id']}' 
					AND _t1.company_id = _t2.company_id  
					AND _t1.branch_id = _t2.branch_id  
					AND _t1.department_id = _t2.department_id 
					AND _t1.division_id = _t2.division_id 
					AND _t1.section_id = _t2.section_id  
					AND _t1.server_id = '{$_REQUEST['server_id']}' 
					AND _t1.instance_server_id = '{$_REQUEST['instance_server_id']}' 
					AND _t1.instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}' 
				) _master";
		$posEmpList = $this->_sqllists($_sql2);

		$_sql3 = "SELECT _t2.employee_id
					FROM hms_api.comp_supervisor _t1, hms_api.comp_employee _t2
					WHERE _t1.employee_id = '{$GLOBALS['employeeLogin']['employee_id']}'
					AND _t1.employee_type_code = _t2.employee_type_code 
					AND _t1.server_id = '{$_REQUEST['server_id']}'
					AND _t1.instance_server_id = '{$_REQUEST['instance_server_id']}'
					AND _t1.instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}' 
					AND _t2.instance_server_id = '{$_REQUEST['instance_server_id']}'
					AND _t2.instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}' ";
		$typeEmpList = $this->_sqllists($_sql3);

		if(sizeof($posEmpList) > 0){
			$supervisor_count++;
			for ($i = 0; $i < sizeof($posEmpList); $i++){
				if(sizeof($typeEmpList) > 0){
					$index = array_search($posEmpList[$i]['employee_id'], array_column($typeEmpList, 'employee_id'));
					if(json_encode($index) !== false){
						$arrayEOH[] = $posEmpList[$i]['employee_id'];
					}
				}else{
					$arrayEOH[] = $posEmpList[$i]['employee_id'];
				}
				
			}
		}else{
			if(sizeof($typeEmpList) > 0){
				for ($i = 0; $i < sizeof($typeEmpList); $i++){
					$supervisor_count++;
					$arrayEOH[] = $typeEmpList[$i]['employee_id'];
				}
			}
		}

		return array("employee_list"=>$arrayEOH, "supervisor_count"=>$supervisor_count);
	}

	function filterSupervisorBeta(){   
		$_sql2 = "SELECT * FROM hms_api.comp_supervisor 
				WHERE employee_id = '{$GLOBALS['employeeLogin']['employee_id']}' 
				AND server_id = '{$_REQUEST['server_id']}' 
		 		AND instance_server_id = '{$_REQUEST['instance_server_id']}' 
				AND instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}' ";

		$supervisor_list = $this->_sqllists($_sql2);
		$supervisor_count = 0;
		$arrayEOH = array();
		
		if(sizeof($supervisor_list) > 0){
			$_sql3 = "SELECT DISTINCT _master.employee_id FROM (";
			foreach($supervisor_list AS $key => $value){
				$supervisor_count++;
				$_tmp_sql = "SELECT employee_id 
							FROM hms_api.comp_employee 
							WHERE server_id = '{$_REQUEST['server_id']}' 
							AND instance_server_id = '{$_REQUEST['instance_server_id']}' 
							AND instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}' ";
				if(!empty($value['company_id']) && $value['company_id'] != 'null'){
					$_tmp_sql .= " AND company_id = '{$value['company_id']}'";
				}
				if(!empty($value['branch_id']) && $value['branch_id'] != 'null'){
					$_tmp_sql .= " AND branch_id = '{$value['branch_id']}'";
				}
				if(!empty($value['department_id']) && $value['department_id'] != 'null'){
					$_tmp_sql .= " AND department_id = '{$value['department_id']}'";
				}
				if(!empty($value['division_id']) && $value['division_id'] != 'null'){
					$_tmp_sql .= " AND division_id = '{$value['division_id']}'";
				}
				if(!empty($value['section_id']) && $value['section_id'] != 'null'){
					$_tmp_sql .= " AND section_id = '{$value['section_id']}'";
				}
				if(!empty($value['position_id']) && $value['position_id'] != 'null'){
					$_tmp_sql .= " AND position_id = '{$value['position_id']}'";
				}
				if(!empty($value['employee_type_code']) && $value['employee_type_code'] != 'null'){
					$_tmp_sql .= " AND employee_type_code = '{$value['employee_type_code']}'";
				}
				if(!empty($value['specific_employee_id']) && $value['specific_employee_id'] != 'null'){
					$_tmp_sql .= " AND employee_id = '{$value['specific_employee_id']}'";
				}

				if($key != 0){
					$_sql3 .= " UNION ";
				}
				$_sql3 .= " {$_tmp_sql} ";
			}
			$_sql3 .= ") _master";
			// echo $_sql3."<br><hr>";
			$empList = $this->_sqllists($_sql3);

			foreach($empList as $value){
				$arrayEOH[] = $value['employee_id'];
			}
		}

		return array("employee_list"=>$arrayEOH, "supervisor_count"=>$supervisor_count);
	}

	function getListEmployeeForFilter($_PARAM){
		// print_r($GLOBALS['employeeLogin']);
		$_sql = "SELECT _employee.employee_id,
						_employee.employee_code,
						_employee.fing_code,
						_employee.employee_type_code,
						_employee.employee_nickname,
						_employee.employee_nickname_en,
						_employee.employee_name,
						_employee.employee_last_name,
						_employee.employee_name_en,
						_employee.employee_last_name_en,
						_employee.employee_title_lv,
						_employee.employee_gender,
						_employee.employee_foreigner,
						_employee.employee_status,
						_employee.position_id,
						_employee.company_id,
						_employee.branch_id,
						_employee.department_id,
						_employee.division_id,
						_employee.section_id,
						_employee.mobilephone,
						_employee.emailaddress,
						_employee.salary,
						_employee.salary_law,
						_employee.salary_per_week_type_lv,
						_employee.salary_per_week,
						_employee.payment_method,
						_employee.social_insurance_method_lv,
						_employee.social_insurance_method_constant,
						_employee.social_insurance_deduct_lv,
						_employee.tax_method_lv,
						_employee.tax_method_constant,
						_employee.tax_method_rate,
						_employee.tax_deduct_lv,
						_employee.days_per_month,
						_employee.hours_per_day,
						_employee.birth_dt,
						_employee.id_no,
						_employee.sso_no,
						_employee.opt_code,
						_employee.person_id,
						_employee.line_user_id,
						_employee.player_id,
						_employee.apple_id,
						_employee.line_token_id,
						_employee.line_token_todolist_id,
						IFNULL(_employee.photograph , 'images/userPlaceHolder.png') AS photograph,
						_employee.bank_id,
						_employee.bank_branch_code,
						_employee.bank_account_code,
						_employee.work_cycle_id_json,
						_employee.work_cycle_format,
						_employee.holiday_day_json,
						_employee.holiday_format,
						_employee.auth_first,
						_employee.auth_second,
						_employee.clock_inout,
						_employee.trial_range,
						_employee.effective_dt,
						_employee.begin_dt,
						_employee.signout_flag,
						_employee.signout_request_dt,
						_employee.signout_dt,
						_employee.out_dt,
						_employee.sso_out_dt,
						_employee.signout_type_flag,
						_employee.signout_remark,
						_employee.round_month_config,
						_employee.round_xtra_config,
						_employee.round_ot_config,
						_employee.round_worktime_config,
						_employee.holiday_apply_config,
						_employee.import_log_id,
						_employee.personal_config,
						_employee.address,
						_employee.address1,
						_employee.address2,
						_employee.address3,
						_employee.address4,
						_employee.address5,
						_employee.address6,
						_employee.address7,
						_employee.address8,
						_employee.address9,
						_employee.country_code,
						_employee.state_code,
						_employee.district_code,
						_employee.subdistrict_code,
						_employee.post_code,
						_employee.current_address,
						_employee.current_address1,
						_employee.current_address2,
						_employee.current_address3,
						_employee.current_address4,
						_employee.current_address5,
						_employee.current_address6,
						_employee.current_address7,
						_employee.current_address8,
						_employee.current_address9,
						_employee.current_country_code,
						_employee.current_state_code,
						_employee.current_district_code,
						_employee.current_subdistrict_code,
						_employee.current_post_code,
						_employee.hashtag_desc,
						_employee.order_no,
						_employee.server_id,
						_employee.instance_server_id,
						_employee.instance_server_channel_id,
						_employee.sys_del_flag,
						_company.company_code,
						_company.company_name,
						_company.company_name_en,
						_branch.branch_code,
						_branch.branch_name,
						_branch.branch_name_en,
						_department.department_code,
						_department.department_name,
						_department.department_name_en,
						_division.division_code,
						_division.division_name,
						_division.division_name_en,
						_section.section_code,
						_section.section_name,
						_section.section_name_en,
						_position.position_code,
						_position.position_name,
						_position.position_name_en,
						_auth_first.employee_id AS auth_first_employee_id,
						_auth_first.employee_code AS auth_first_employee_code,
						_auth_first.employee_name AS auth_first_employee_name,
						_auth_first.employee_last_name AS auth_first_employee_last_name,
						_auth_first.employee_nickname AS auth_first_employee_nickname,
						_auth_first.employee_name_en AS auth_first_employee_name_en,
						_auth_first.employee_last_name_en AS auth_first_employee_last_name_en,
						_auth_first.employee_nickname_en AS auth_first_employee_nickname_en,
						_auth_second.employee_id AS auth_second_employee_id,
						_auth_second.employee_code AS auth_second_employee_code,
						_auth_second.employee_name AS auth_second_employee_name,
						_auth_second.employee_last_name AS auth_second_employee_last_name,
						_auth_second.employee_nickname AS auth_second_employee_nickname,
						_auth_second.employee_name_en AS auth_second_employee_name_en,
						_auth_second.employee_last_name_en AS auth_second_employee_last_name_en,
						_auth_second.employee_nickname_en AS auth_second_employee_nickname_en,
						_taxperson.person_tax_transac_id AS person_tax_id,
						_user.user_id AS identify_user_id, 
						_user.user_name, _user.first_singin_flag, _employee.publish_flag  
						FROM (select * from comp_employee  where instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}') _employee 
						INNER JOIN (select company_id, company_code, company_name, company_name_en FROM comp_company  where instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}') _company ON (_company.company_id=_employee.company_id) 
						INNER JOIN (select branch_id, branch_code, branch_name, branch_name_en FROM comp_branch  where instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}') _branch ON (_branch.branch_id=_employee.branch_id) 
						INNER JOIN (select department_id, department_code, department_name, department_name_en 
						                       FROM comp_department  where instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}') _department ON (_department.department_id=_employee.department_id) 
						LEFT JOIN (select division_id, division_code, division_name, division_name_en 
						                       FROM comp_division  where instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}') _division ON (_division.division_id=_employee.division_id) 
						LEFT JOIN (select section_id, section_code, section_name, section_name_en 
						                       FROM comp_section  where instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}') _section ON (_section.section_id=_employee.section_id) 
						INNER JOIN (select position_id, position_code, position_name, position_name_en 
												FROM comp_position  where instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}') _position ON (_position.position_id=_employee.position_id) 
						LEFT JOIN (select user_id, employee_id, user_name, first_singin_flag, publish_flag FROM suso_user  where instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}') _user ON (_employee.employee_id=_user.employee_id)
						LEFT JOIN (
							SELECT person_tax_transac_id, tax_year_code, tax_month_code, tax_category_id, employee_id 
							FROM {$GLOBALS['instanceServer']['instance_server_dbn']}.payroll_person_tax_transac 
							WHERE instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}' 
							AND tax_year_code = '" . date('Y') . "' 
							AND tax_month_code = '12' 
							AND tax_category_id = '60'
						) _taxperson ON (_taxperson.employee_id = _employee.employee_id) 
						LEFT JOIN (select * from comp_employee where instance_server_id = '{$_REQUEST['instance_server_id']}') _auth_first ON (_employee.auth_first=_auth_first.employee_id) 
						LEFT JOIN (select * from comp_employee where instance_server_id = '{$_REQUEST['instance_server_id']}') _auth_second ON (_employee.auth_second=_auth_second.employee_id) 
						WHERE _employee.server_id = '{$_REQUEST['server_id']}'
						AND _employee.instance_server_id = '{$_REQUEST['instance_server_id']}'
						AND _employee.instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}' ";

		if($_PARAM["keyword"] != ''){
			$_sql .= "AND (
								_employee.employee_nickname LIKE '%{$_PARAM["keyword"]}%' OR
								_employee.employee_name LIKE '%{$_PARAM["keyword"]}%' OR
								_employee.employee_last_name LIKE '%{$_PARAM["keyword"]}%' OR
								_employee.employee_nickname_en LIKE '%{$_PARAM["keyword"]}%' OR
								_employee.employee_name_en LIKE '%{$_PARAM["keyword"]}%' OR
								_employee.employee_last_name_en LIKE '%{$_PARAM["keyword"]}%' OR
								_employee.fing_code LIKE '%{$_PARAM["keyword"]}%' OR
								_employee.employee_code LIKE '%{$_PARAM["keyword"]}%') ";
		}

		if(sizeof($_PARAM["hashtags"]) == 1){
			$_sql .= " AND _employee.hashtag_desc LIKE '%{$_PARAM["hashtags"][0]}%' ";
		} else if(sizeof($_PARAM["hashtags"]) > 1){
			$_sql .= " AND ( ";
			for ($i = 0; $i < sizeof($_PARAM["hashtags"]); $i++){
				if($i == 0)
					$_sql .= " _employee.hashtag_desc LIKE '%{$_PARAM["hashtags"][$i]}%' ";
				else
					$_sql .= " OR _employee.hashtag_desc LIKE '%{$_PARAM["hashtags"][$i]}%' ";
			}
			$_sql .= " ) ";
		}

		if(is_array($_PARAM["except"]) && sizeof($_PARAM["except"]) > 0){
			$excepIds = "";
			for ($i = 0; $i < sizeof($_PARAM["except"]); $i++){
				if($i == 0)
					$excepIds = "'" . base64_decode($_PARAM["except"][$i]["id"]) . "' ";
				else
					$excepIds .= ", '" . base64_decode($_PARAM["except"][$i]["id"]) . "' ";
			}
			$_sql .= "AND _employee.employee_id NOT IN ({$excepIds}) ";
		}

		// if($_PARAM['sys_del_flag']=='N'||$_PARAM['sys_del_flag']=='Y'){
		// 	$_sql .= "AND _employee.sys_del_flag = '{$_PARAM["sys_del_flag"]}' ";
		// }else if($_PARAM['sys_del_flag']=='A'){

		// }else{
		// 	$_sql .= "AND _employee.sys_del_flag = 'N' "; 
		// }

		if(is_array($_PARAM["company_lists"]) && sizeof($_PARAM["company_lists"]) > 0){
			$companyIds = "";
			for ($i = 0; $i < sizeof($_PARAM["company_lists"]); $i++){
				if($i == 0)
					$companyIds = "'" . base64_decode($_PARAM["company_lists"][$i]["id"]) . "' ";
				else
					$companyIds .= ", '" . base64_decode($_PARAM["company_lists"][$i]["id"]) . "' ";
			}
			$_sql .= "AND _employee.company_id IN ({$companyIds}) ";
		}

		if(is_array($_PARAM["branch_lists"]) && sizeof($_PARAM["branch_lists"]) > 0){
			$branchIds = "";
			for ($i = 0; $i < sizeof($_PARAM["branch_lists"]); $i++){
				if($i == 0)
					$branchIds = "'" . base64_decode($_PARAM["branch_lists"][$i]["id"]) . "' ";
				else
					$branchIds .= ", '" . base64_decode($_PARAM["branch_lists"][$i]["id"]) . "' ";
			}
			$_branchSql = " _employee.department_id IN (SELECT department_id FROM comp_department WHERE branch_id IN ({$branchIds}) 
							                                                                              AND instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}' AND sys_del_flag='N') ";
		}

		if(is_array($_PARAM["department_lists"]) && sizeof($_PARAM["department_lists"]) > 0){
			$departmentIds = "";
			for ($i = 0; $i < sizeof($_PARAM["department_lists"]); $i++){
				if($i == 0)
					$departmentIds = "'" . base64_decode($_PARAM["department_lists"][$i]["id"]) . "' ";
				else
					$departmentIds .= ", '" . base64_decode($_PARAM["department_lists"][$i]["id"]) . "' ";
			}
			$_departmentSql = "_employee.department_id IN ({$departmentIds}) ";
		}

		if(is_array($_PARAM["division_lists"]) && sizeof($_PARAM["division_lists"]) > 0){
			$divisionIds = "";
			for ($i = 0; $i < sizeof($_PARAM["division_lists"]); $i++){
				if($i == 0){
					$divisionIds = "'" . base64_decode($_PARAM["division_lists"][$i]["id"]) . "' ";
				} else {
					$divisionIds .= ", '" . base64_decode($_PARAM["division_lists"][$i]["id"]) . "' ";
				}
			}
			$_divisionSql = "_employee.division_id IN ({$divisionIds}) ";
		}

		if(is_array($_PARAM["section_lists"]) && sizeof($_PARAM["section_lists"]) > 0){
			$sectionIds = "";
			for ($i = 0; $i < sizeof($_PARAM["section_lists"]); $i++){
				if($i == 0){
					$sectionIds = "'" . base64_decode($_PARAM["section_lists"][$i]["id"]) . "' ";
				} else {
					$sectionIds .= ", '" . base64_decode($_PARAM["section_lists"][$i]["id"]) . "' ";
				}
			}
			$_sectionSql = "_employee.section_id IN ({$sectionIds}) ";
		}

		if(sizeof($_PARAM["branch_lists"]) > 0 && sizeof($_PARAM["department_lists"]) > 0){
			$_sql .= "AND {$_branchSql} AND {$_departmentSql} ";
		} else if(sizeof($_PARAM["branch_lists"]) > 0 && sizeof($_PARAM["department_lists"]) == 0){
			$_sql .= "AND {$_branchSql} ";
		} else if(sizeof($_PARAM["branch_lists"]) == 0 && sizeof($_PARAM["department_lists"]) > 0){
			$_sql .= "AND {$_departmentSql}	";
		}

		if(sizeof($_PARAM["division_lists"]) > 0){
			$_sql .= "AND (({$_divisionSql})) ";
			// $_sql .= "AND (({$_divisionSql}) OR (_employee.division_id = '0' OR _employee.division_id = '' OR _employee.division_id IS NULL)) ";
		}

		if(sizeof($_PARAM["section_lists"]) > 0){
			$_sql .= "AND (({$_sectionSql})) ";
			// $_sql .= "AND (({$_sectionSql}) OR (_employee.section_id = '0' OR _employee.section_id = '' OR _employee.section_id IS NULL)) ";
		}

		if(is_array($_PARAM["position_lists"]) && sizeof($_PARAM["position_lists"]) > 0){
			$positionIds = "";
			for ($i = 0; $i < sizeof($_PARAM["position_lists"]); $i++){
				if($i == 0)
					$positionIds = "'" . base64_decode($_PARAM["position_lists"][$i]["id"]) . "' ";
				else
					$positionIds .= ", '" . base64_decode($_PARAM["position_lists"][$i]["id"]) . "' ";
			}
			$_sql .= "AND _employee.position_id IN ({$positionIds}) ";
		}

		if(is_array($_PARAM["employee_lists"]) && sizeof($_PARAM["employee_lists"]) > 0){
			$employeeIds = "";
			for ($i = 0; $i < sizeof($_PARAM["employee_lists"]); $i++){
				if($i == 0)
					$employeeIds = "'" . base64_decode($_PARAM["employee_lists"][$i]["id"]) . "' ";
				else
					$employeeIds .= ", '" . base64_decode($_PARAM["employee_lists"][$i]["id"]) . "' ";
			}
			$_sql .= "AND _employee.employee_id IN ({$employeeIds}) ";

			if($_PARAM['sys_del_flag'] == 'N' || $_PARAM['sys_del_flag'] == 'Y'){
				$_sql .= "AND _employee.sys_del_flag = '{$_PARAM["sys_del_flag"]}' ";
			}
		} else {
			if($_PARAM['sys_del_flag'] == 'N' || $_PARAM['sys_del_flag'] == 'Y'){
				$_sql .= "AND _employee.sys_del_flag = '{$_PARAM["sys_del_flag"]}' ";
			} else if($_PARAM['sys_del_flag'] == 'A'){

			} else {
				$_sql .= "AND _employee.sys_del_flag = 'N' ";
			}
		}

		if($_PARAM['signout_flag']){
			$_sql .= "AND _employee.signout_flag = '{$_PARAM['signout_flag']}' ";
		}
		if($_PARAM['round_xtra_config']){
			$_sql .= "AND _employee.round_xtra_config = '{$_PARAM['round_xtra_config']}' ";
		}
		if($_PARAM['round_ot_config']){
			$_sql .= "AND _employee.round_ot_config = '{$_PARAM['round_ot_config']}' ";
		}
		if($_PARAM['round_worktime_config']){
			$_sql .= "AND _employee.round_worktime_config = '{$_PARAM['round_worktime_config']}' ";
		}
		if($GLOBALS['employeeLogin']['employee_id'] != ''){
			$auth = PageAuthorizeService::getAuthorizeByUserGroup(array("SAL", "SALINEX", "SALBU", "AUDIT", "HRBU"));
			if($_PARAM['only_in_position_line'] == true || $auth === false){
				$posEmpList = $this->getListEmployeeAuthorize($GLOBALS['employeeLogin']['employee_id']);
				$arrayEOH = array();
				$arrayEOH[] = $GLOBALS['employeeLogin']['employee_id'];
				for ($i = 0; $i < sizeof($posEmpList); $i++){
					$arrayEOH[] = $posEmpList[$i]['employee_id'];
				}
				$_sql .= "AND _employee.employee_id IN ('" . implode("','" , $arrayEOH) . "') ";
				// $posEmpList = $this->getListEmployeePositionLine($GLOBALS['employeeLogin']['employee_id']);
				// $arrayEOH = array();
				// for($i=0;$i<sizeof($posEmpList);$i++){
				// 	$arrayEOH[] = $posEmpList[$i]['employee_id'];
				// }
				// if(sizeof($arrayEOH)>0){
				// 	$_sql .= "AND _employee.employee_id IN ('".implode("','" , $arrayEOH)."') ";
				// }
			} else if ($auth == true) {
				// if($_REQUEST['_beta'] == "Y"){
					$tmp_supervisor = $this->filterSupervisorBeta();
					$supervisor_count = $tmp_supervisor['supervisor_count'];
					$arrayEOH = $tmp_supervisor['employee_list'];
				// }else{
				// 	$tmp_supervisor = $this->filterSupervisor();
				// 	$supervisor_count = $tmp_supervisor['supervisor_count'];
				// 	$arrayEOH = $tmp_supervisor['employee_list'];
				// }
				
				// if (sizeof($arrayEOH) > 0) {
					if($supervisor_count > 0){
						$_sql .= "AND _employee.employee_id IN ('" . implode("','" , $arrayEOH) . "') ";
					}
				// }
			}
		}
		$_sql .= "ORDER BY _company.company_code,_branch.branch_code,_department.department_code,_division.division_code,_section.section_code,_employee.employee_code ";
		// $channel = $GLOBALS['instanceServerChannelService']->getInstanceServerSpecificChannels($_REQUEST['server_id'], $_REQUEST['instance_server_id'],$_REQUEST['instance_server_channel_id']);
		// if($channel['max_user_limit']>0){
		// 	$_sql .= "LIMIT ".$channel['max_user_limit']; 
		// }
		// if($_REQUEST['_debug']=='Y'){
		// echo "$_sql<hr>";
		// }

		// echo "$_sql<hr>";
		//exit;

		$lists = $this->_sqllists($_sql);

		if ($_PARAM['check_count_of_employee'] === true && sizeof($lists) > $_PARAM['count_of_employee_limit']) {
			throw new Exception('employee-overlimit');
		}

		$_sql = "SELECT _cycle.*
						FROM comp_work_cycle _cycle
						WHERE _cycle.server_id = '{$_REQUEST['server_id']}'
						AND _cycle.instance_server_id = '{$_REQUEST['instance_server_id']}' 
						AND _cycle.instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}' 
						ORDER BY _cycle.work_cycle_code ";
		// echo "$_sql<br>";
		$cycleLists = $this->_sqllists($_sql);
		$labelCycle = array();
		for ($i = 0; $i < sizeof($cycleLists); $i++){
			$labelCycle[$cycleLists[$i]['work_cycle_id']] = $cycleLists[$i];
		}

		for ($i = 0; $i < sizeof($lists); $i++){
			$cycleListsEmployee = json_decode($lists[$i]['work_cycle_id_json'], true);
			$cycleKey = array_keys($cycleListsEmployee);
			for ($x = 0; $x < sizeof($cycleKey); $x++){
				$lists[$i]['work_cycle_lists'][$x][$cycleKey[$x]] = $labelCycle[$cycleListsEmployee[$cycleKey[$x]]];
			}

			$holidayListsEmployee = json_decode($lists[$i]['holiday_day_json'], true);
			$holidayKey = array_keys($holidayListsEmployee);
			for ($x = 0; $x < sizeof($holidayKey); $x++){
				$lists[$i]['holiday_lists'][$x][$holidayKey[$x]] = $holidayListsEmployee[$holidayKey[$x]];
			}
		}

		return $lists;
	}

	function getListEmployeeForFilterModified($_PARAM){
		$_sql = "SELECT _employee.employee_id
		, _employee.employee_code
		, _employee.fing_code
		, _employee.employee_type_code
		, _employee.employee_nickname
		, _employee.employee_nickname_en
		, _employee.employee_name
		, _employee.employee_last_name
		, _employee.employee_name_en
		, _employee.employee_last_name_en
		, _employee.employee_title_lv
		, _employee.employee_gender
		, _employee.employee_foreigner
		, _employee.employee_status
		, _employee.position_id
		, _employee.company_id
		, _employee.branch_id
		, _employee.department_id
		, _employee.division_id
		, _employee.section_id
		, _employee.section_lv01_id
		, _employee.section_lv02_id
        , _employee.section_lv03_id
        , _employee.section_lv04_id
        , _employee.section_lv05_id
		, _employee.mobilephone
		, _employee.emailaddress
		, _employee.salary
		, _employee.salary_law
		, _employee.salary_per_week_type_lv
		, _employee.salary_per_week
		, _employee.payment_method
		, _employee.social_insurance_method_lv
		, _employee.social_insurance_method_constant
		, _employee.social_insurance_deduct_lv
		, _employee.tax_method_lv
		, _employee.tax_method_constant
		, _employee.tax_method_rate
		, _employee.tax_deduct_lv
		, _employee.days_per_month
		, _employee.hours_per_day
		, _employee.birth_dt
		, _employee.id_no
		, _employee.sso_no
		, _employee.opt_code
		, _employee.person_id
		, _employee.line_user_id
		, _employee.player_id
		, _employee.apple_id
		, _employee.line_token_id
		, _employee.line_token_todolist_id
		, IFNULL(_employee.photograph , 'images/userPlaceHolder.png') AS photograph
		, _employee.bank_id
		, _employee.bank_branch_code
		, _employee.bank_account_code
		, _employee.work_cycle_id_json
		, _employee.work_cycle_format
		, _employee.holiday_day_json
		, _employee.holiday_format
		, _employee.auth_first
		, _employee.auth_second
		, _employee.clock_inout
		, _employee.trial_range
		, _employee.effective_dt
		, _employee.begin_dt
		, _employee.signout_flag
		, _employee.signout_request_dt
		, _employee.signout_dt
		, _employee.out_dt
		, _employee.sso_out_dt
		, _employee.signout_type_flag
		, _employee.signout_remark
		, _employee.round_month_config
		, _employee.round_xtra_config
		, _employee.round_ot_config
		, _employee.round_worktime_config
		, _employee.holiday_apply_config
		, _employee.import_log_id
		, _employee.personal_config
		, _employee.address
		, _employee.address1
		, _employee.address2
		, _employee.address3
		, _employee.address4
		, _employee.address5
		, _employee.address6
		, _employee.address7
		, _employee.address8
		, _employee.address9
		, _employee.country_code
		, _employee.state_code
		, _employee.district_code
		, _employee.subdistrict_code
		, _employee.post_code
		, _employee.current_address
		, _employee.current_address1
		, _employee.current_address2
		, _employee.current_address3
		, _employee.current_address4
		, _employee.current_address5
		, _employee.current_address6
		, _employee.current_address7
		, _employee.current_address8
		, _employee.current_address9
		, _employee.current_country_code
		, _employee.current_state_code
		, _employee.current_district_code
		, _employee.current_subdistrict_code
		, _employee.current_post_code
		, _employee.hashtag_desc
		, _employee.order_no
		, _employee.server_id
		, _employee.instance_server_id
		, _employee.instance_server_channel_id
		, _employee.sys_del_flag
		, _company.company_code
		, _company.company_name
		, _company.company_name_en
		, _branch.branch_code
		, _branch.branch_name
		, _branch.branch_name_en
		, _department.department_code
		, _department.department_name
		, _department.department_name_en
		, _division.division_code
		, _division.division_name
		, _division.division_name_en
		, _section.section_code
		, _section.section_name
		, _section.section_name_en
		, _section_lv01.section_lv01_code
		, _section_lv01.section_lv01_name
		, _section_lv01.section_lv01_name_en
		, _section_lv02.section_lv02_code
		, _section_lv02.section_lv02_name
		, _section_lv02.section_lv02_name_en
		, _section_lv03.section_lv03_code
		, _section_lv03.section_lv03_name
		, _section_lv03.section_lv03_name_en
		, _section_lv04.section_lv04_code
		, _section_lv04.section_lv04_name
		, _section_lv04.section_lv04_name_en
		, _section_lv05.section_lv05_code
		, _section_lv05.section_lv05_name
		, _section_lv05.section_lv05_name_en
		, _position.position_code
		, _position.position_name
		, _position.position_name_en
		, _auth_first.employee_id AS auth_first_employee_id
		, _auth_first.employee_code AS auth_first_employee_code
		, _auth_first.employee_name AS auth_first_employee_name
		, _auth_first.employee_last_name AS auth_first_employee_last_name
		, _auth_first.employee_nickname AS auth_first_employee_nickname
		, _auth_first.employee_name_en AS auth_first_employee_name_en
		, _auth_first.employee_last_name_en AS auth_first_employee_last_name_en
		, _auth_first.employee_nickname_en AS auth_first_employee_nickname_en
		, _auth_second.employee_id AS auth_second_employee_id
		, _auth_second.employee_code AS auth_second_employee_code
		, _auth_second.employee_name AS auth_second_employee_name
		, _auth_second.employee_last_name AS auth_second_employee_last_name
		, _auth_second.employee_nickname AS auth_second_employee_nickname
		, _auth_second.employee_name_en AS auth_second_employee_name_en
		, _auth_second.employee_last_name_en AS auth_second_employee_last_name_en
		, _auth_second.employee_nickname_en AS auth_second_employee_nickname_en
		, _taxperson.person_tax_transac_id AS person_tax_id
		, _employee.publish_flag
		FROM (select * from comp_employee where instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}') _employee
		INNER JOIN (select company_id, company_code, company_name, company_name_en FROM comp_company where instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}') _company ON (_company.company_id=_employee.company_id)
		INNER JOIN (select branch_id, branch_code, branch_name, branch_name_en FROM comp_branch where instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}') _branch ON (_branch.branch_id=_employee.branch_id)
		INNER JOIN (select department_id, department_code, department_name, department_name_en
								FROM comp_department where instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}') _department ON (_department.department_id=_employee.department_id)
		LEFT JOIN (select division_id, division_code, division_name, division_name_en
								FROM comp_division where instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}') _division ON (_division.division_id=_employee.division_id)
		LEFT JOIN (select section_id, section_code, section_name, section_name_en
								FROM comp_section where instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}') _section ON (_section.section_id=_employee.section_id)
		LEFT JOIN (select section_lv01_id, section_lv01_code, section_lv01_name, section_lv01_name_en 
						        FROM comp_section_lv01 where instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}') _section_lv01 ON (_section_lv01.section_lv01_id=_employee.section_lv01_id) 
		LEFT JOIN (select section_lv02_id, section_lv02_code, section_lv02_name, section_lv02_name_en 
						        FROM comp_section_lv02 where instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}') _section_lv02 ON (_section_lv02.section_lv02_id=_employee.section_lv02_id) 
		LEFT JOIN (select section_lv03_id, section_lv03_code, section_lv03_name, section_lv03_name_en 
						        FROM comp_section_lv03 where instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}') _section_lv03 ON (_section_lv03.section_lv03_id=_employee.section_lv03_id) 
		LEFT JOIN (select section_lv04_id, section_lv04_code, section_lv04_name, section_lv04_name_en 
						        FROM comp_section_lv04 where instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}') _section_lv04 ON (_section_lv04.section_lv04_id=_employee.section_lv04_id) 
		LEFT JOIN (select section_lv05_id, section_lv05_code, section_lv05_name, section_lv05_name_en 
						        FROM comp_section_lv05 where instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}') _section_lv05 ON (_section_lv05.section_lv05_id=_employee.section_lv05_id) 
		INNER JOIN (select position_id, position_code, position_name, position_name_en
								FROM comp_position where instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}') _position ON (_position.position_id=_employee.position_id)
		LEFT JOIN (
			SELECT person_tax_transac_id, tax_year_code, tax_month_code, tax_category_id, employee_id
			FROM {$GLOBALS['instanceServer']['instance_server_dbn']}.payroll_person_tax_transac
			WHERE instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}'
			AND tax_year_code = '" . date('Y') . "'
			AND tax_month_code = '12'
			AND tax_category_id = '60'
		) _taxperson ON (_taxperson.employee_id = _employee.employee_id)
		LEFT JOIN (select * from comp_employee where instance_server_id = '{$_REQUEST['instance_server_id']}') _auth_first ON (_employee.auth_first=_auth_first.employee_id)
		LEFT JOIN (select * from comp_employee where instance_server_id = '{$_REQUEST['instance_server_id']}') _auth_second ON (_employee.auth_second=_auth_second.employee_id)
		WHERE _employee.server_id = '{$_REQUEST['server_id']}'
		AND _employee.instance_server_id = '{$_REQUEST['instance_server_id']}'
		AND _employee.instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}' ";

		if($_PARAM["keyword"] != ''){
			$_sql .= " AND (
								_employee.employee_nickname LIKE '%{$_PARAM["keyword"]}%' OR
								_employee.employee_name LIKE '%{$_PARAM["keyword"]}%' OR
								_employee.employee_last_name LIKE '%{$_PARAM["keyword"]}%' OR
								_employee.employee_nickname_en LIKE '%{$_PARAM["keyword"]}%' OR
								_employee.employee_name_en LIKE '%{$_PARAM["keyword"]}%' OR
								_employee.employee_last_name_en LIKE '%{$_PARAM["keyword"]}%' OR
								_employee.fing_code LIKE '%{$_PARAM["keyword"]}%' OR
								_employee.employee_code LIKE '%{$_PARAM["keyword"]}%'
							) ";
		}

		if(sizeof($_PARAM["hashtags"]) == 1){
			$_sql .= " AND _employee.hashtag_desc LIKE '%{$_PARAM["hashtags"][0]}%' ";
		} else if(sizeof($_PARAM["hashtags"]) > 1){
			$_sql .= " AND ( ";
			for ($i = 0; $i < sizeof($_PARAM["hashtags"]); $i++){
				if($i == 0)
					$_sql .= " _employee.hashtag_desc LIKE '%{$_PARAM["hashtags"][$i]}%' ";
				else
					$_sql .= " OR _employee.hashtag_desc LIKE '%{$_PARAM["hashtags"][$i]}%' ";
			}
			$_sql .= " ) ";
		}

		if(is_array($_PARAM["except"]) && sizeof($_PARAM["except"]) > 0){
			$_sql .= " AND _employee.employee_id NOT IN ('".implode("','", array_map('base64_decode', array_column($_PARAM['except'], 'id')))."') ";
		}

		$operator = "";
		$_sql_org = "";
		if(is_array($_PARAM['company_lists']) && sizeof($_PARAM['company_lists']) > 0){
			$_sql_org .= " {$operator} _employee.company_id IN ('".implode("','", array_map('base64_decode', array_column($_PARAM['company_lists'], 'id')))."') ";
			$operator = "OR";
		}
		if(is_array($_PARAM['branch_lists']) && sizeof($_PARAM['branch_lists']) > 0){
			$_sql_org .= " {$operator} _employee.branch_id IN ('".implode("','", array_map('base64_decode', array_column($_PARAM['branch_lists'], 'id')))."') ";
			$operator = "OR";
		}
		if(is_array($_PARAM['department_lists']) && sizeof($_PARAM['department_lists']) > 0){
			$_sql_org .= " {$operator} _employee.department_id IN ('".implode("','", array_map('base64_decode', array_column($_PARAM['department_lists'], 'id')))."') ";
			$operator = "OR";
		}
		if(is_array($_PARAM['division_lists']) && sizeof($_PARAM['division_lists']) > 0){
			$_sql_org .= " {$operator} _employee.division_id IN ('".implode("','", array_map('base64_decode', array_column($_PARAM['division_lists'], 'id')))."') ";
			$operator = "OR";
		}
		if(is_array($_PARAM['section_lists']) && sizeof($_PARAM['section_lists']) > 0){
			$_sql_org .= " {$operator} _employee.section_id IN ('".implode("','", array_map('base64_decode', array_column($_PARAM['section_lists'], 'id')))."') ";
			$operator = "OR";
		}
		if(is_array($_PARAM['section_lists_lv01']) && sizeof($_PARAM['section_lists_lv01']) > 0){
			$_sql_org .= " {$operator} _employee.section_lv01_id IN ('".implode("','", array_map('base64_decode', array_column($_PARAM['section_lists_lv01'], 'id')))."') ";
			$operator = "OR";
		}
		if(is_array($_PARAM['section_lists_lv02']) && sizeof($_PARAM['section_lists_lv02']) > 0){
			$_sql_org .= " {$operator} _employee.section_lv02_id IN ('".implode("','", array_map('base64_decode', array_column($_PARAM['section_lists_lv02'], 'id')))."') ";
			$operator = "OR";
		}
		if(is_array($_PARAM['section_lists_lv03']) && sizeof($_PARAM['section_lists_lv03']) > 0){
			$_sql_org .= " {$operator} _employee.section_lv03_id IN ('".implode("','", array_map('base64_decode', array_column($_PARAM['section_lists_lv03'], 'id')))."') ";
			$operator = "OR";
		}
		if(is_array($_PARAM['section_lists_lv04']) && sizeof($_PARAM['section_lists_lv04']) > 0){
			$_sql_org .= " {$operator} _employee.section_lv04_id IN ('".implode("','", array_map('base64_decode', array_column($_PARAM['section_lists_lv04'], 'id')))."') ";
			$operator = "OR";
		}
		if(is_array($_PARAM['section_lists_lv05']) && sizeof($_PARAM['section_lists_lv05']) > 0){
			$_sql_org .= " {$operator} _employee.section_lv05_id IN ('".implode("','", array_map('base64_decode', array_column($_PARAM['section_lists_lv05'], 'id')))."') ";
			$operator = "OR";
		}
		if($_sql_org != ""){
			$_sql .= " AND ({$_sql_org}) ";
		}

		if(is_array($_PARAM["position_lists"]) && sizeof($_PARAM["position_lists"]) > 0){
			$_sql .= " AND _employee.position_id IN ('".implode("','", array_map('base64_decode', array_column($_PARAM['position_lists'], 'id')))."') ";
		}

		if(is_array($_PARAM["employee_lists"]) && sizeof($_PARAM["employee_lists"]) > 0){
			$_sql .= " AND _employee.employee_id IN ('".implode("','", array_map('base64_decode', array_column($_PARAM['employee_lists'], 'id')))."') ";

			if($_PARAM['sys_del_flag'] == 'N' || $_PARAM['sys_del_flag'] == 'Y'){
				$_sql .= " AND _employee.sys_del_flag = '{$_PARAM["sys_del_flag"]}' ";
			}
		} else {
			if($_PARAM['sys_del_flag'] == 'N' || $_PARAM['sys_del_flag'] == 'Y'){
				$_sql .= " AND _employee.sys_del_flag = '{$_PARAM["sys_del_flag"]}' ";
			} else if($_PARAM['sys_del_flag'] == 'A'){

			} else {
				$_sql .= " AND _employee.sys_del_flag = 'N' ";
			}
		}

		if($_PARAM['signout_flag']){
			$_sql .= "AND _employee.signout_flag = '{$_PARAM['signout_flag']}' ";
		}
		if($_PARAM['round_xtra_config']){
			$_sql .= "AND _employee.round_xtra_config = '{$_PARAM['round_xtra_config']}' ";
		}
		if($_PARAM['round_ot_config']){
			$_sql .= "AND _employee.round_ot_config = '{$_PARAM['round_ot_config']}' ";
		}
		if($_PARAM['round_worktime_config']){
			$_sql .= "AND _employee.round_worktime_config = '{$_PARAM['round_worktime_config']}' ";
		}
		if($GLOBALS['employeeLogin']['employee_id'] != ''){
			$auth = PageAuthorizeService::getAuthorizeByUserGroup(array("SAL", "SALINEX", "SALBU", "AUDIT", "HRBU"));
			if($_PARAM['only_in_position_line'] == true || $auth === false){
				$posEmpList = $this->getListEmployeeAuthorize($GLOBALS['employeeLogin']['employee_id']);
				$arrayEOH = array();
				$arrayEOH[] = $GLOBALS['employeeLogin']['employee_id'];
				for ($i = 0; $i < sizeof($posEmpList); $i++){
					$arrayEOH[] = $posEmpList[$i]['employee_id'];
				}
				$_sql .= "AND _employee.employee_id IN ('" . implode("','" , $arrayEOH) . "') ";
			} else if ($auth == true) {
				$tmp_supervisor = $this->filterSupervisorBeta();
				$supervisor_count = $tmp_supervisor['supervisor_count'];
				$arrayEOH = $tmp_supervisor['employee_list'];
				if($supervisor_count > 0){
					$_sql .= "AND _employee.employee_id IN ('" . implode("','" , $arrayEOH) . "') ";
				}
			}
		}
		$_sql .= "ORDER BY _company.company_code, _branch.branch_code, _department.department_code, _division.division_code, _section.section_code , _section_lv01.section_lv01_code , _section_lv02.section_lv02_code , _section_lv03.section_lv03_code , _section_lv04.section_lv04_code , _section_lv05.section_lv05_code , _employee.employee_code ";
		$lists = $this->_sqllists($_sql);

		if ($_PARAM['check_count_of_employee'] === true && sizeof($lists) > $_PARAM['count_of_employee_limit']) {
			throw new Exception('employee-overlimit');
		}

		$_sql = "SELECT user_id
		, employee_id
		, user_name
		, first_singin_flag
		, publish_flag
		FROM hms_api.suso_user
		WHERE server_id = '{$_REQUEST['server_id']}'
		AND instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}' ";
		$user_list = $this->_sqllists($_sql);
		$mapEmployeeId = array();
		foreach($user_list as $user){
			$mapEmployeeId[$user['employee_id']] = $user;
		}
		foreach($lists as &$emp){
			if($mapEmployeeId[$emp['employee_id']]){
				$emp['identify_user_id'] = $mapEmployeeId[$emp['employee_id']]['user_id'];
				$emp['user_name'] = $mapEmployeeId[$emp['employee_id']]['user_name'];
				$emp['first_singin_flag'] = $mapEmployeeId[$emp['employee_id']]['first_singin_flag'];
			}else{
				$emp['identify_user_id'] = null;
				$emp['user_name'] = null;
				$emp['first_singin_flag'] = null;
			}
		}
		// echo json_encode($lists);exit;

		$_sql = "SELECT _cycle.*
		FROM hms_api.comp_work_cycle _cycle
		WHERE _cycle.server_id = '{$_REQUEST['server_id']}'
		AND _cycle.instance_server_id = '{$_REQUEST['instance_server_id']}'
		AND _cycle.instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}'
		ORDER BY _cycle.work_cycle_code ";
		$cycleLists = $this->_sqllists($_sql);
		$labelCycle = array();
		for($i=0; $i<sizeof($cycleLists); $i++){
			$labelCycle[$cycleLists[$i]['work_cycle_id']] = $cycleLists[$i];
		}

		for($i=0; $i<sizeof($lists); $i++){
			$cycleListsEmployee = json_decode($lists[$i]['work_cycle_id_json'], true);
			$cycleKey = array_keys($cycleListsEmployee);
			for($x=0; $x<sizeof($cycleKey); $x++){
				$lists[$i]['work_cycle_lists'][$x][$cycleKey[$x]] = $labelCycle[$cycleListsEmployee[$cycleKey[$x]]];
			}

			$holidayListsEmployee = json_decode($lists[$i]['holiday_day_json'], true);
			$holidayKey = array_keys($holidayListsEmployee);
			for ($x=0; $x<sizeof($holidayKey); $x++){
				$lists[$i]['holiday_lists'][$x][$holidayKey[$x]] = $holidayListsEmployee[$holidayKey[$x]];
			}
		}

		return $lists;
	}
	function getListEmployeeForReportModified($_PARAM){
		$_sql = "SELECT _employee.employee_id
		, _employee.employee_code
		, _employee.fing_code
		, _employee.employee_type_code
		, _employee.employee_nickname
		, _employee.employee_nickname_en
		, _employee.employee_name
		, _employee.employee_last_name
		, _employee.employee_name_en
		, _employee.employee_last_name_en
		, _employee.employee_title_lv
		, _employee.employee_gender
		, _employee.employee_foreigner
		, _employee.employee_status
		, _employee.position_id
		, _employee.company_id
		, _employee.branch_id
		, _employee.department_id
		, _employee.division_id
		, _employee.section_id
		, _employee.section_lv01_id
		, _employee.section_lv02_id
		, _employee.section_lv03_id
		, _employee.section_lv04_id
		, _employee.section_lv05_id
		, _employee.mobilephone
		, _employee.emailaddress
		, _employee.salary
		, _employee.salary_law
		, _employee.salary_per_week_type_lv
		, _employee.salary_per_week
		, _employee.payment_method
		, _employee.social_insurance_method_lv
		, _employee.social_insurance_method_constant
		, _employee.social_insurance_deduct_lv
		, _employee.tax_method_lv
		, _employee.tax_method_constant
		, _employee.tax_method_rate
		, _employee.tax_deduct_lv
		, _employee.days_per_month
		, _employee.hours_per_day
		, _employee.birth_dt
		, _employee.id_no
		, _employee.sso_no
		, _employee.opt_code
		, _employee.person_id
		, _employee.line_user_id
		, _employee.player_id
		, _employee.apple_id
		, _employee.line_token_id
		, _employee.line_token_todolist_id
		, IFNULL(_employee.photograph , 'images/userPlaceHolder.png') AS photograph
		, _employee.bank_id
		, _employee.bank_branch_code
		, _employee.bank_account_code
		, _employee.work_cycle_id_json
		, _employee.work_cycle_format
		, _employee.holiday_day_json
		, _employee.holiday_format
		, _employee.auth_first
		, _employee.auth_second
		, _employee.clock_inout
		, _employee.trial_range
		, _employee.effective_dt
		, _employee.begin_dt
		, _employee.signout_flag
		, _employee.signout_request_dt
		, _employee.signout_dt
		, _employee.out_dt
		, _employee.sso_out_dt
		, _employee.signout_type_flag
		, _employee.signout_remark
		, _employee.round_month_config
		, _employee.round_xtra_config
		, _employee.round_ot_config
		, _employee.round_worktime_config
		, _employee.holiday_apply_config
		, _employee.import_log_id
		, _employee.personal_config
		, _employee.address
		, _employee.address1
		, _employee.address2
		, _employee.address3
		, _employee.address4
		, _employee.address5
		, _employee.address6
		, _employee.address7
		, _employee.address8
		, _employee.address9
		, _employee.country_code
		, _employee.state_code
		, _employee.district_code
		, _employee.subdistrict_code
		, _employee.post_code
		, _employee.current_address
		, _employee.current_address1
		, _employee.current_address2
		, _employee.current_address3
		, _employee.current_address4
		, _employee.current_address5
		, _employee.current_address6
		, _employee.current_address7
		, _employee.current_address8
		, _employee.current_address9
		, _employee.current_country_code
		, _employee.current_state_code
		, _employee.current_district_code
		, _employee.current_subdistrict_code
		, _employee.current_post_code
		, _employee.hashtag_desc
		, _employee.order_no
		, _employee.server_id
		, _employee.instance_server_id
		, _employee.instance_server_channel_id
		, _employee.sys_del_flag
		, _employee.publish_flag
		FROM (select * from comp_employee where instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}') _employee ";
		if(!empty($_PARAM['salary_report_start_dt']) && !empty($_PARAM['salary_report_end_dt'])){
			$_sql .= "INNER JOIN  ( SELECT employee_id,branch_id,department_id,division_id,section_id 
								FROM {$GLOBALS['instanceServer']['instance_server_dbn']}.payroll_master_salary_slip 
								WHERE server_id = '{$_REQUEST['server_id']}'
								AND instance_server_id = '{$_REQUEST['instance_server_id']}'
								AND instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}' 
								AND master_salary_month =  '{$_REQUEST['year_month']}' ";
								$slip_org = "";
								$slip_sql_org = "";
								if(is_array($_PARAM['branch_lists']) && sizeof($_PARAM['branch_lists']) > 0){
									$slip_sql_org .= " {$slip_org} branch_id IN ('".implode("','", array_map('base64_decode', array_column($_PARAM['branch_lists'], 'id')))."') ";
									$slip_org = "OR";
								}
								if(is_array($_PARAM['department_lists']) && sizeof($_PARAM['department_lists']) > 0){
									$slip_sql_org .= " {$slip_org} department_id IN ('".implode("','", array_map('base64_decode', array_column($_PARAM['department_lists'], 'id')))."') ";
									$slip_org = "OR";
								}
								if(is_array($_PARAM['division_lists']) && sizeof($_PARAM['division_lists']) > 0){
									$slip_sql_org .= " {$slip_org} division_id IN ('".implode("','", array_map('base64_decode', array_column($_PARAM['division_lists'], 'id')))."') ";
									$slip_org = "OR";
								}
								if(is_array($_PARAM['section_lists']) && sizeof($_PARAM['section_lists']) > 0){
									$slip_sql_org .= " {$slip_org} section_id IN ('".implode("','", array_map('base64_decode', array_column($_PARAM['section_lists'], 'id')))."') ";
									$slip_org = "OR";
								}
								if($slip_sql_org != ""){
									$_sql .= " AND ({$slip_sql_org}) ";
								}
								$_sql .= ") _slip ON (_employee.employee_id = _slip.employee_id) ";
		}
		$_sql .= "WHERE _employee.server_id = '{$_REQUEST['server_id']}'
		AND _employee.instance_server_id = '{$_REQUEST['instance_server_id']}'
		AND _employee.instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}' ";

		if($_PARAM["keyword"] != ''){
			$_sql .= " AND (
								_employee.employee_nickname LIKE '%{$_PARAM["keyword"]}%' OR
								_employee.employee_name LIKE '%{$_PARAM["keyword"]}%' OR
								_employee.employee_last_name LIKE '%{$_PARAM["keyword"]}%' OR
								_employee.employee_nickname_en LIKE '%{$_PARAM["keyword"]}%' OR
								_employee.employee_name_en LIKE '%{$_PARAM["keyword"]}%' OR
								_employee.employee_last_name_en LIKE '%{$_PARAM["keyword"]}%' OR
								_employee.fing_code LIKE '%{$_PARAM["keyword"]}%' OR
								_employee.employee_code LIKE '%{$_PARAM["keyword"]}%'
							) ";
		}

		if(sizeof($_PARAM["hashtags"]) == 1){
			$_sql .= " AND _employee.hashtag_desc LIKE '%{$_PARAM["hashtags"][0]}%' ";
		} else if(sizeof($_PARAM["hashtags"]) > 1){
			$_sql .= " AND ( ";
			for ($i = 0; $i < sizeof($_PARAM["hashtags"]); $i++){
				if($i == 0)
					$_sql .= " _employee.hashtag_desc LIKE '%{$_PARAM["hashtags"][$i]}%' ";
				else
					$_sql .= " OR _employee.hashtag_desc LIKE '%{$_PARAM["hashtags"][$i]}%' ";
			}
			$_sql .= " ) ";
		}

		if(is_array($_PARAM["except"]) && sizeof($_PARAM["except"]) > 0){
			$_sql .= " AND _employee.employee_id NOT IN ('".implode("','", array_map('base64_decode', array_column($_PARAM['except'], 'id')))."') ";
		}

		$operator = "";
		$_sql_org = "";
		if(is_array($_PARAM['company_lists']) && sizeof($_PARAM['company_lists']) > 0){
			$_sql_org .= " {$operator} _employee.company_id IN ('".implode("','", array_map('base64_decode', array_column($_PARAM['company_lists'], 'id')))."') ";
			$operator = "OR";
		}
		if(is_array($_PARAM['branch_lists']) && sizeof($_PARAM['branch_lists']) > 0){
			$_sql_org .= " {$operator} _employee.branch_id IN ('".implode("','", array_map('base64_decode', array_column($_PARAM['branch_lists'], 'id')))."') ";
			$operator = "OR";
		}
		if(is_array($_PARAM['department_lists']) && sizeof($_PARAM['department_lists']) > 0){
			$_sql_org .= " {$operator} _employee.department_id IN ('".implode("','", array_map('base64_decode', array_column($_PARAM['department_lists'], 'id')))."') ";
			$operator = "OR";
		}
		if(is_array($_PARAM['division_lists']) && sizeof($_PARAM['division_lists']) > 0){
			$_sql_org .= " {$operator} _employee.division_id IN ('".implode("','", array_map('base64_decode', array_column($_PARAM['division_lists'], 'id')))."') ";
			$operator = "OR";
		}
		if(is_array($_PARAM['section_lists']) && sizeof($_PARAM['section_lists']) > 0){
			$_sql_org .= " {$operator} _employee.section_id IN ('".implode("','", array_map('base64_decode', array_column($_PARAM['section_lists'], 'id')))."') ";
			$operator = "OR";
		}
		if(is_array($_PARAM['section_lists_lv01']) && sizeof($_PARAM['section_lists_lv01']) > 0){
			$_sql_org .= " {$operator} _employee.section_lv01_id IN ('".implode("','", array_map('base64_decode', array_column($_PARAM['section_lists_lv01'], 'id')))."') ";
			$operator = "OR";
		}
		if(is_array($_PARAM['section_lists_lv02']) && sizeof($_PARAM['section_lists_lv02']) > 0){
			$_sql_org .= " {$operator} _employee.section_lv02_id IN ('".implode("','", array_map('base64_decode', array_column($_PARAM['section_lists_lv02'], 'id')))."') ";
			$operator = "OR";
		}
		if(is_array($_PARAM['section_lists_lv03']) && sizeof($_PARAM['section_lists_lv03']) > 0){
			$_sql_org .= " {$operator} _employee.section_lv03_id IN ('".implode("','", array_map('base64_decode', array_column($_PARAM['section_lists_lv03'], 'id')))."') ";
			$operator = "OR";
		}
		if(is_array($_PARAM['section_lists_lv04']) && sizeof($_PARAM['section_lists_lv04']) > 0){
			$_sql_org .= " {$operator} _employee.section_lv04_id IN ('".implode("','", array_map('base64_decode', array_column($_PARAM['section_lists_lv04'], 'id')))."') ";
			$operator = "OR";
		}
		if(is_array($_PARAM['section_lists_lv05']) && sizeof($_PARAM['section_lists_lv05']) > 0){
			$_sql_org .= " {$operator} _employee.section_lv05_id IN ('".implode("','", array_map('base64_decode', array_column($_PARAM['section_lists_lv05'], 'id')))."') ";
			$operator = "OR";
		}
		if($_sql_org != ""){
			$_sql .= " AND ({$_sql_org}) ";
		}

		if(is_array($_PARAM["position_lists"]) && sizeof($_PARAM["position_lists"]) > 0){
			$_sql .= " AND _employee.position_id IN ('".implode("','", array_map('base64_decode', array_column($_PARAM['position_lists'], 'id')))."') ";
		}

		if(is_array($_PARAM["employee_lists"]) && sizeof($_PARAM["employee_lists"]) > 0){
			$_sql .= " AND _employee.employee_id IN ('".implode("','", array_map('base64_decode', array_column($_PARAM['employee_lists'], 'id')))."') ";

			if($_PARAM['sys_del_flag'] == 'N' || $_PARAM['sys_del_flag'] == 'Y'){
				$_sql .= " AND _employee.sys_del_flag = '{$_PARAM["sys_del_flag"]}' ";
			}
		} else {
			if($_PARAM['sys_del_flag'] == 'N' || $_PARAM['sys_del_flag'] == 'Y'){
				$_sql .= " AND _employee.sys_del_flag = '{$_PARAM["sys_del_flag"]}' ";
			} else if($_PARAM['sys_del_flag'] == 'A'){

			} else {
				$_sql .= " AND _employee.sys_del_flag = 'N' ";
			}
		}

		if($_PARAM['signout_flag']){
			$_sql .= "AND _employee.signout_flag = '{$_PARAM['signout_flag']}' ";
		}
		if($_PARAM['round_xtra_config']){
			$_sql .= "AND _employee.round_xtra_config = '{$_PARAM['round_xtra_config']}' ";
		}
		if($_PARAM['round_ot_config']){
			$_sql .= "AND _employee.round_ot_config = '{$_PARAM['round_ot_config']}' ";
		}
		if($_PARAM['round_worktime_config']){
			$_sql .= "AND _employee.round_worktime_config = '{$_PARAM['round_worktime_config']}' ";
		}
		if($GLOBALS['employeeLogin']['employee_id'] != ''){
			$auth = PageAuthorizeService::getAuthorizeByUserGroup(array("SAL", "SALINEX", "SALBU", "AUDIT", "HRBU"));
			if($_PARAM['only_in_position_line'] == true || $auth === false){
				$posEmpList = $this->getListEmployeeAuthorize($GLOBALS['employeeLogin']['employee_id']);
				$arrayEOH = array();
				$arrayEOH[] = $GLOBALS['employeeLogin']['employee_id'];
				for ($i = 0; $i < sizeof($posEmpList); $i++){
					$arrayEOH[] = $posEmpList[$i]['employee_id'];
				}
				$_sql .= "AND _employee.employee_id IN ('" . implode("','" , $arrayEOH) . "') ";
			} else if ($auth == true) {
				$tmp_supervisor = $this->filterSupervisorBeta();
				$supervisor_count = $tmp_supervisor['supervisor_count'];
				$arrayEOH = $tmp_supervisor['employee_list'];
				if($supervisor_count > 0){
					$_sql .= "AND _employee.employee_id IN ('" . implode("','" , $arrayEOH) . "') ";
				}
			}
		}

		$_sql .= "ORDER BY  _employee.employee_code ";
		// echo $_sql;
		$lists = $this->_sqllists($_sql);

		if ($_PARAM['check_count_of_employee'] === true && sizeof($lists) > $_PARAM['count_of_employee_limit']) {
			throw new Exception('employee-overlimit');
		}
		// echo json_encode($lists);exit



		return $lists;
	}

	function getListEmployeeFilterSlipOnly($_PARAM){
		$_sql = "SELECT _employee.employee_id
		, _employee.employee_code
		, _employee.fing_code
		, _employee.employee_type_code
		, _employee.employee_nickname
		, _employee.employee_nickname_en
		, _employee.employee_name
		, _employee.employee_last_name
		, _employee.employee_name_en
		, _employee.employee_last_name_en
		, _employee.employee_title_lv
		, _employee.employee_gender
		, _employee.employee_foreigner
		, _employee.employee_status
		, _employee.position_id
		, _employee.company_id
		, _employee.branch_id
		, _employee.department_id
		, _employee.division_id
		, _employee.section_id
		, _employee.section_lv01_id
		, _employee.section_lv02_id
		, _employee.section_lv03_id
		, _employee.section_lv04_id
		, _employee.section_lv05_id
		, _employee.mobilephone
		, _employee.emailaddress
		, _employee.salary
		, _employee.salary_law
		, _employee.salary_per_week_type_lv
		, _employee.salary_per_week
		, _employee.payment_method
		, _employee.social_insurance_method_lv
		, _employee.social_insurance_method_constant
		, _employee.social_insurance_deduct_lv
		, _employee.tax_method_lv
		, _employee.tax_method_constant
		, _employee.tax_method_rate
		, _employee.tax_deduct_lv
		, _employee.days_per_month
		, _employee.hours_per_day
		, _employee.birth_dt
		, _employee.id_no
		, _employee.sso_no
		, _employee.opt_code
		, _employee.person_id
		, _employee.line_user_id
		, _employee.player_id
		, _employee.apple_id
		, _employee.line_token_id
		, _employee.line_token_todolist_id
		, IFNULL(_employee.photograph , 'images/userPlaceHolder.png') AS photograph
		, _employee.bank_id
		, _employee.bank_branch_code
		, _employee.bank_account_code
		, _employee.work_cycle_id_json
		, _employee.work_cycle_format
		, _employee.holiday_day_json
		, _employee.holiday_format
		, _employee.auth_first
		, _employee.auth_second
		, _employee.clock_inout
		, _employee.trial_range
		, _employee.effective_dt
		, _employee.begin_dt
		, _employee.signout_flag
		, _employee.signout_request_dt
		, _employee.signout_dt
		, _employee.out_dt
		, _employee.sso_out_dt
		, _employee.signout_type_flag
		, _employee.signout_remark
		, _employee.round_month_config
		, _employee.round_xtra_config
		, _employee.round_ot_config
		, _employee.round_worktime_config
		, _employee.holiday_apply_config
		, _employee.import_log_id
		, _employee.personal_config
		, _employee.address
		, _employee.address1
		, _employee.address2
		, _employee.address3
		, _employee.address4
		, _employee.address5
		, _employee.address6
		, _employee.address7
		, _employee.address8
		, _employee.address9
		, _employee.country_code
		, _employee.state_code
		, _employee.district_code
		, _employee.subdistrict_code
		, _employee.post_code
		, _employee.current_address
		, _employee.current_address1
		, _employee.current_address2
		, _employee.current_address3
		, _employee.current_address4
		, _employee.current_address5
		, _employee.current_address6
		, _employee.current_address7
		, _employee.current_address8
		, _employee.current_address9
		, _employee.current_country_code
		, _employee.current_state_code
		, _employee.current_district_code
		, _employee.current_subdistrict_code
		, _employee.current_post_code
		, _employee.hashtag_desc
		, _employee.order_no
		, _employee.server_id
		, _employee.instance_server_id
		, _employee.instance_server_channel_id
		, _employee.sys_del_flag
		, _employee.publish_flag
		FROM (select * from comp_employee where instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}') _employee ";
		if(!empty($_PARAM['salary_report_start_dt']) && !empty($_PARAM['salary_report_end_dt'])){
			$_sql .= "INNER JOIN  ( SELECT employee_id,branch_id,department_id,division_id,section_id,section_lv01_id,section_lv02_id,section_lv03_id,section_lv04_id,section_lv05_id
								FROM {$GLOBALS['instanceServer']['instance_server_dbn']}.payroll_master_salary_slip 
								WHERE server_id = '{$_REQUEST['server_id']}'
								AND instance_server_id = '{$_REQUEST['instance_server_id']}'
								AND instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}' 
								AND master_salary_month =  '{$_REQUEST['year_month']}' ";
								$slip_org = "";
								$slip_sql_org = "";
								if(is_array($_PARAM['branch_lists']) && sizeof($_PARAM['branch_lists']) > 0){
									$slip_sql_org .= " {$slip_org} branch_id IN ('".implode("','", array_map('base64_decode', array_column($_PARAM['branch_lists'], 'id')))."') ";
									$slip_org = "OR";
								}
								if(is_array($_PARAM['department_lists']) && sizeof($_PARAM['department_lists']) > 0){
									$slip_sql_org .= " {$slip_org} department_id IN ('".implode("','", array_map('base64_decode', array_column($_PARAM['department_lists'], 'id')))."') ";
									$slip_org = "OR";
								}
								if(is_array($_PARAM['division_lists']) && sizeof($_PARAM['division_lists']) > 0){
									$slip_sql_org .= " {$slip_org} division_id IN ('".implode("','", array_map('base64_decode', array_column($_PARAM['division_lists'], 'id')))."') ";
									$slip_org = "OR";
								}
								if(is_array($_PARAM['section_lists']) && sizeof($_PARAM['section_lists']) > 0){
									$slip_sql_org .= " {$slip_org} section_id IN ('".implode("','", array_map('base64_decode', array_column($_PARAM['section_lists'], 'id')))."') ";
									$slip_org = "OR";
								}
								if(is_array($_PARAM['section_lists_lv01']) && sizeof($_PARAM['section_lists_lv01']) > 0){
									$slip_sql_org .= " {$slip_org} section_lv01_id IN ('".implode("','", array_map('base64_decode', array_column($_PARAM['section_lists_lv01'], 'id')))."') ";
									$slip_org = "OR";
								}
								if(is_array($_PARAM['section_lists_lv02']) && sizeof($_PARAM['section_lists_lv02']) > 0){
									$slip_sql_org .= " {$slip_org} section_lv02_id IN ('".implode("','", array_map('base64_decode', array_column($_PARAM['section_lists_lv02'], 'id')))."') ";
									$slip_org = "OR";
								}
								if(is_array($_PARAM['section_lists_lv03']) && sizeof($_PARAM['section_lists_lv03']) > 0){
									$slip_sql_org .= " {$slip_org} section_lv03_id IN ('".implode("','", array_map('base64_decode', array_column($_PARAM['section_lists_lv03'], 'id')))."') ";
									$slip_org = "OR";
								}
								if(is_array($_PARAM['section_lists_lv04']) && sizeof($_PARAM['section_lists_lv04']) > 0){
									$slip_sql_org .= " {$slip_org} section_lv04_id IN ('".implode("','", array_map('base64_decode', array_column($_PARAM['section_lists_lv04'], 'id')))."') ";
									$slip_org = "OR";
								}
								if(is_array($_PARAM['section_lists_lv05']) && sizeof($_PARAM['section_lists_lv05']) > 0){
									$slip_sql_org .= " {$slip_org} section_lv05_id IN ('".implode("','", array_map('base64_decode', array_column($_PARAM['section_lists_lv05'], 'id')))."') ";
									$slip_org = "OR";
								}
								if($slip_sql_org != ""){
									$_sql .= " AND ({$slip_sql_org}) ";
								}
								$_sql .= ") _slip ON (_employee.employee_id = _slip.employee_id) ";
		}
		$_sql .= "WHERE _employee.server_id = '{$_REQUEST['server_id']}'
		AND _employee.instance_server_id = '{$_REQUEST['instance_server_id']}'
		AND _employee.instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}' ";

		if($_PARAM["keyword"] != ''){
			$_sql .= " AND (
								_employee.employee_nickname LIKE '%{$_PARAM["keyword"]}%' OR
								_employee.employee_name LIKE '%{$_PARAM["keyword"]}%' OR
								_employee.employee_last_name LIKE '%{$_PARAM["keyword"]}%' OR
								_employee.employee_nickname_en LIKE '%{$_PARAM["keyword"]}%' OR
								_employee.employee_name_en LIKE '%{$_PARAM["keyword"]}%' OR
								_employee.employee_last_name_en LIKE '%{$_PARAM["keyword"]}%' OR
								_employee.fing_code LIKE '%{$_PARAM["keyword"]}%' OR
								_employee.employee_code LIKE '%{$_PARAM["keyword"]}%'
							) ";
		}

		if(sizeof($_PARAM["hashtags"]) == 1){
			$_sql .= " AND _employee.hashtag_desc LIKE '%{$_PARAM["hashtags"][0]}%' ";
		} else if(sizeof($_PARAM["hashtags"]) > 1){
			$_sql .= " AND ( ";
			for ($i = 0; $i < sizeof($_PARAM["hashtags"]); $i++){
				if($i == 0)
					$_sql .= " _employee.hashtag_desc LIKE '%{$_PARAM["hashtags"][$i]}%' ";
				else
					$_sql .= " OR _employee.hashtag_desc LIKE '%{$_PARAM["hashtags"][$i]}%' ";
			}
			$_sql .= " ) ";
		}

		if(is_array($_PARAM["except"]) && sizeof($_PARAM["except"]) > 0){
			$_sql .= " AND _employee.employee_id NOT IN ('".implode("','", array_map('base64_decode', array_column($_PARAM['except'], 'id')))."') ";
		}

		if(is_array($_PARAM["employee_lists"]) && sizeof($_PARAM["employee_lists"]) > 0){
			$_sql .= " AND _employee.employee_id IN ('".implode("','", array_map('base64_decode', array_column($_PARAM['employee_lists'], 'id')))."') ";

			if($_PARAM['sys_del_flag'] == 'N' || $_PARAM['sys_del_flag'] == 'Y'){
				$_sql .= " AND _employee.sys_del_flag = '{$_PARAM["sys_del_flag"]}' ";
			}
		} else {
			if($_PARAM['sys_del_flag'] == 'N' || $_PARAM['sys_del_flag'] == 'Y'){
				$_sql .= " AND _employee.sys_del_flag = '{$_PARAM["sys_del_flag"]}' ";
			} else if($_PARAM['sys_del_flag'] == 'A'){

			} else {
				$_sql .= " AND _employee.sys_del_flag = 'N' ";
			}
		}

		if($_PARAM['signout_flag']){
			$_sql .= "AND _employee.signout_flag = '{$_PARAM['signout_flag']}' ";
		}
		if($_PARAM['round_xtra_config']){
			$_sql .= "AND _employee.round_xtra_config = '{$_PARAM['round_xtra_config']}' ";
		}
		if($_PARAM['round_ot_config']){
			$_sql .= "AND _employee.round_ot_config = '{$_PARAM['round_ot_config']}' ";
		}
		if($_PARAM['round_worktime_config']){
			$_sql .= "AND _employee.round_worktime_config = '{$_PARAM['round_worktime_config']}' ";
		}
		if($GLOBALS['employeeLogin']['employee_id'] != ''){
			$auth = PageAuthorizeService::getAuthorizeByUserGroup(array("SAL", "SALINEX", "SALBU", "AUDIT", "HRBU"));
			if($_PARAM['only_in_position_line'] == true || $auth === false){
				$posEmpList = $this->getListEmployeeAuthorize($GLOBALS['employeeLogin']['employee_id']);
				$arrayEOH = array();
				$arrayEOH[] = $GLOBALS['employeeLogin']['employee_id'];
				for ($i = 0; $i < sizeof($posEmpList); $i++){
					$arrayEOH[] = $posEmpList[$i]['employee_id'];
				}
				$_sql .= "AND _employee.employee_id IN ('" . implode("','" , $arrayEOH) . "') ";
			} else if ($auth == true) {
				$tmp_supervisor = $this->filterSupervisorBeta();
				$supervisor_count = $tmp_supervisor['supervisor_count'];
				$arrayEOH = $tmp_supervisor['employee_list'];
				if($supervisor_count > 0){
					$_sql .= "AND _employee.employee_id IN ('" . implode("','" , $arrayEOH) . "') ";
				}
			}
		}

		$_sql .= "ORDER BY  _employee.employee_code ";
		// echo $_sql;
		// exit;
		$lists = $this->_sqllists($_sql);

		if ($_PARAM['check_count_of_employee'] === true && sizeof($lists) > $_PARAM['count_of_employee_limit']) {
			throw new Exception('employee-overlimit');
		}
		// echo json_encode($lists);exit



		return $lists;
	}

	function getListEmployeeWithFilterMobile($_PARAM){
		// print_r($GLOBALS['employeeLogin']);
		$_sql = "SELECT _employee.employee_id,
						_employee.employee_code,
						_employee.fing_code,
						_employee.employee_type_code,
						_employee.employee_nickname,
						_employee.employee_nickname_en,
						_employee.employee_name,
						_employee.employee_last_name,
						_employee.employee_name_en,
						_employee.employee_last_name_en,
						_employee.employee_title_lv,
						_employee.employee_gender,
						_employee.employee_foreigner,
						_employee.employee_status,
						_employee.position_id,
						_employee.company_id,
						_employee.branch_id,
						_employee.department_id,
						_employee.division_id,
						_employee.section_id,
						_employee.mobilephone,
						_employee.emailaddress,
						_employee.salary,
						_employee.salary_law,
						_employee.salary_per_week_type_lv,
						_employee.salary_per_week,
						_employee.payment_method,
						_employee.social_insurance_method_lv,
						_employee.social_insurance_method_constant,
						_employee.social_insurance_deduct_lv,
						_employee.tax_method_lv,
						_employee.tax_method_constant,
						_employee.tax_method_rate,
						_employee.tax_deduct_lv,
						_employee.days_per_month,
						_employee.hours_per_day,
						_employee.birth_dt,
						_employee.id_no,
						_employee.sso_no,
						_employee.opt_code,
						_employee.person_id,
						_employee.line_user_id,
						_employee.player_id,
						_employee.apple_id,
						_employee.line_token_id,
						_employee.line_token_todolist_id,
						IFNULL(_employee.photograph , 'images/userPlaceHolder.png') AS photograph,
						_employee.bank_id,
						_employee.bank_branch_code,
						_employee.bank_account_code,
						_employee.work_cycle_id_json,
						_employee.work_cycle_format,
						_employee.holiday_day_json,
						_employee.holiday_format,
						_employee.clock_inout,
						_employee.trial_range,
						_employee.effective_dt,
						_employee.begin_dt,
						_employee.signout_flag,
						_employee.signout_request_dt,
						_employee.signout_dt,
						_employee.out_dt,
						_employee.sso_out_dt,
						_employee.signout_type_flag,
						_employee.signout_remark,
						_employee.round_month_config,
						_employee.round_xtra_config,
						_employee.round_ot_config,
						_employee.round_worktime_config,
						_employee.holiday_apply_config,
						_employee.import_log_id,
						_employee.personal_config,
						_employee.address,
						_employee.address1,
						_employee.address2,
						_employee.address3,
						_employee.address4,
						_employee.address5,
						_employee.address6,
						_employee.address7,
						_employee.address8,
						_employee.address9,
						_employee.country_code,
						_employee.state_code,
						_employee.district_code,
						_employee.subdistrict_code,
						_employee.post_code,
						_employee.current_address,
						_employee.current_address1,
						_employee.current_address2,
						_employee.current_address3,
						_employee.current_address4,
						_employee.current_address5,
						_employee.current_address6,
						_employee.current_address7,
						_employee.current_address8,
						_employee.current_address9,
						_employee.current_country_code,
						_employee.current_state_code,
						_employee.current_district_code,
						_employee.current_subdistrict_code,
						_employee.current_post_code,
						_employee.hashtag_desc,
						_employee.order_no,
						_employee.server_id,
						_employee.instance_server_id,
						_employee.instance_server_channel_id,
						_employee.sys_del_flag,
						_company.company_code,
						_company.company_name,
						_company.company_name_en,
						_branch.branch_code,
						_branch.branch_name,
						_branch.branch_name_en,
						_department.department_code,
						_department.department_name,
						_department.department_name_en,
						_division.division_code,
						_division.division_name,
						_division.division_name_en,
						_section.section_code,
						_section.section_name,
						_section.section_name_en,
						_position.position_code,
						_position.position_name,
						_position.position_name_en,
						_taxperson.person_tax_transac_id AS person_tax_id,
						_user.user_id AS identify_user_id
						FROM (select * from comp_employee  where instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}') _employee 
						INNER JOIN (select company_id, company_code, company_name, company_name_en FROM comp_company  where instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}') _company ON (_company.company_id=_employee.company_id) 
						INNER JOIN (select branch_id, branch_code, branch_name, branch_name_en FROM comp_branch  where instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}') _branch ON (_branch.branch_id=_employee.branch_id) 
						INNER JOIN (select department_id, department_code, department_name, department_name_en 
						                       FROM comp_department  where instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}') _department ON (_department.department_id=_employee.department_id) 
						LEFT JOIN (select division_id, division_code, division_name, division_name_en 
						                       FROM comp_division  where instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}') _division ON (_division.division_id=_employee.division_id) 
						LEFT JOIN (select section_id, section_code, section_name, section_name_en 
						                       FROM comp_section  where instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}') _section ON (_section.section_id=_employee.section_id) 
						INNER JOIN (select position_id, position_code, position_name, position_name_en 
												FROM comp_position  where instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}') _position ON (_position.position_id=_employee.position_id) 
						LEFT JOIN (select user_id, employee_id FROM suso_user  where instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}') _user ON (_employee.employee_id=_user.employee_id)
						LEFT JOIN (
							SELECT person_tax_transac_id, tax_year_code, tax_month_code, tax_category_id, employee_id
							FROM {$GLOBALS['instanceServer']['instance_server_dbn']}.payroll_person_tax_transac 
							WHERE instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}' 
							AND tax_year_code = '" . date('Y') . "' 
							AND tax_month_code = '12' 
							AND tax_category_id = '60'
						) _taxperson ON (_taxperson.employee_id = _employee.employee_id) 
						WHERE _employee.server_id = '{$_REQUEST['server_id']}'
						AND _employee.instance_server_id = '{$_REQUEST['instance_server_id']}'
						AND _employee.instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}' ";

		if($_PARAM["keyword"] != ''){
			$_sql .= "AND (
								_employee.employee_nickname LIKE '%{$_PARAM["keyword"]}%' OR
								_employee.employee_name LIKE '%{$_PARAM["keyword"]}%' OR
								_employee.employee_last_name LIKE '%{$_PARAM["keyword"]}%' OR
								_employee.employee_nickname_en LIKE '%{$_PARAM["keyword"]}%' OR
								_employee.employee_name_en LIKE '%{$_PARAM["keyword"]}%' OR
								_employee.employee_last_name_en LIKE '%{$_PARAM["keyword"]}%' OR
								_employee.fing_code LIKE '%{$_PARAM["keyword"]}%' OR
								_employee.employee_code LIKE '%{$_PARAM["keyword"]}%') ";
		}

		if(sizeof($_PARAM["hashtags"]) == 1){
			$_sql .= " AND _employee.hashtag_desc LIKE '%{$_PARAM["hashtags"][0]}%' ";
		} else if(sizeof($_PARAM["hashtags"]) > 1){
			$_sql .= " AND ( ";
			for ($i = 0; $i < sizeof($_PARAM["hashtags"]); $i++){
				if($i == 0)
					$_sql .= " _employee.hashtag_desc LIKE '%{$_PARAM["hashtags"][$i]}%' ";
				else
					$_sql .= " OR _employee.hashtag_desc LIKE '%{$_PARAM["hashtags"][$i]}%' ";
			}
			$_sql .= " ) ";
		}

		if(is_array($_PARAM["except"]) && sizeof($_PARAM["except"]) > 0){
			$excepIds = "";
			for ($i = 0; $i < sizeof($_PARAM["except"]); $i++){
				if($i == 0)
					$excepIds = "'" . base64_decode($_PARAM["except"][$i]["id"]) . "' ";
				else
					$excepIds .= ", '" . base64_decode($_PARAM["except"][$i]["id"]) . "' ";
			}
			$_sql .= "AND _employee.employee_id NOT IN ({$excepIds}) ";
		}

		// if($_PARAM['sys_del_flag']=='N'||$_PARAM['sys_del_flag']=='Y'){
		// 	$_sql .= "AND _employee.sys_del_flag = '{$_PARAM["sys_del_flag"]}' ";
		// }else if($_PARAM['sys_del_flag']=='A'){

		// }else{
		// 	$_sql .= "AND _employee.sys_del_flag = 'N' "; 
		// }

		if(is_array($_PARAM["company_lists"]) && sizeof($_PARAM["company_lists"]) > 0){
			$companyIds = "";
			for ($i = 0; $i < sizeof($_PARAM["company_lists"]); $i++){
				if($i == 0)
					$companyIds = "'" . base64_decode($_PARAM["company_lists"][$i]["id"]) . "' ";
				else
					$companyIds .= ", '" . base64_decode($_PARAM["company_lists"][$i]["id"]) . "' ";
			}
			$_sql .= "AND _employee.company_id IN ({$companyIds}) ";
		}

		if(is_array($_PARAM["branch_lists"]) && sizeof($_PARAM["branch_lists"]) > 0){
			$branchIds = "";
			for ($i = 0; $i < sizeof($_PARAM["branch_lists"]); $i++){
				if($i == 0)
					$branchIds = "'" . base64_decode($_PARAM["branch_lists"][$i]["id"]) . "' ";
				else
					$branchIds .= ", '" . base64_decode($_PARAM["branch_lists"][$i]["id"]) . "' ";
			}
			$_branchSql = " _employee.department_id IN (SELECT department_id FROM comp_department WHERE branch_id IN ({$branchIds}) 
							                                                                              AND instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}' AND sys_del_flag='N') ";
		}

		if(is_array($_PARAM["department_lists"]) && sizeof($_PARAM["department_lists"]) > 0){
			$departmentIds = "";
			for ($i = 0; $i < sizeof($_PARAM["department_lists"]); $i++){
				if($i == 0)
					$departmentIds = "'" . base64_decode($_PARAM["department_lists"][$i]["id"]) . "' ";
				else
					$departmentIds .= ", '" . base64_decode($_PARAM["department_lists"][$i]["id"]) . "' ";
			}
			$_departmentSql = "_employee.department_id IN ({$departmentIds}) ";
		}

		if(is_array($_PARAM["division_lists"]) && sizeof($_PARAM["division_lists"]) > 0){
			$divisionIds = "";
			for ($i = 0; $i < sizeof($_PARAM["division_lists"]); $i++){
				if($i == 0){
					$divisionIds = "'" . base64_decode($_PARAM["division_lists"][$i]["id"]) . "' ";
				} else {
					$divisionIds .= ", '" . base64_decode($_PARAM["division_lists"][$i]["id"]) . "' ";
				}
			}
			$_divisionSql = "_employee.division_id IN ({$divisionIds}) ";
		}

		if(is_array($_PARAM["section_lists"]) && sizeof($_PARAM["section_lists"]) > 0){
			$sectionIds = "";
			for ($i = 0; $i < sizeof($_PARAM["section_lists"]); $i++){
				if($i == 0){
					$sectionIds = "'" . base64_decode($_PARAM["section_lists"][$i]["id"]) . "' ";
				} else {
					$sectionIds .= ", '" . base64_decode($_PARAM["section_lists"][$i]["id"]) . "' ";
				}
			}
			$_sectionSql = "_employee.section_id IN ({$sectionIds}) ";
		}

		if(sizeof($_PARAM["branch_lists"]) > 0 && sizeof($_PARAM["department_lists"]) > 0 && sizeof($_PARAM["division_lists"]) > 0 && sizeof($_PARAM["section_lists"]) > 0){
			if($_PARAM["include_or_flag"]){
				$_sql .= " AND ({$_branchSql} OR {$_departmentSql} OR {$_divisionSql} OR {$_sectionSql}) "; 
			} else {
				$_sql .= " AND {$_branchSql} AND {$_departmentSql} AND {$_divisionSql} AND {$_sectionSql} ";
			}
		} else if(sizeof($_PARAM["branch_lists"]) > 0 && sizeof($_PARAM["department_lists"]) > 0 && sizeof($_PARAM["division_lists"]) > 0 && sizeof($_PARAM["section_lists"]) == 0){
			if($_PARAM["include_or_flag"]){
				$_sql .= " AND ({$_branchSql} OR {$_departmentSql} OR {$_divisionSql} ) "; 
			} else {
				$_sql .= " AND {$_branchSql} AND {$_departmentSql} AND {$_divisionSql} ";
			}
		} else if(sizeof($_PARAM["branch_lists"]) > 0 && sizeof($_PARAM["department_lists"]) > 0 && sizeof($_PARAM["division_lists"]) == 0 && sizeof($_PARAM["section_lists"]) == 0){
			if($_PARAM["include_or_flag"]){
				$_sql .= "AND ({$_branchSql} OR {$_departmentSql} )"; 
			} else {
				$_sql .= "AND {$_branchSql} AND {$_departmentSql} ";
			}
		} else if(sizeof($_PARAM["branch_lists"]) > 0 && sizeof($_PARAM["department_lists"]) == 0 && sizeof($_PARAM["division_lists"]) == 0 && sizeof($_PARAM["section_lists"]) == 0){
			$_sql .= "AND {$_branchSql} ";
		} else if(sizeof($_PARAM["branch_lists"]) == 0 && sizeof($_PARAM["department_lists"]) > 0 && sizeof($_PARAM["division_lists"]) == 0 && sizeof($_PARAM["section_lists"]) == 0){
			$_sql .= "AND {$_departmentSql} ";
		} else if(sizeof($_PARAM["branch_lists"]) == 0 && sizeof($_PARAM["department_lists"]) == 0 && sizeof($_PARAM["division_lists"]) > 0 && sizeof($_PARAM["section_lists"]) == 0){
			$_sql .= "AND {$_divisionSql} ";
		} else if(sizeof($_PARAM["branch_lists"]) == 0 && sizeof($_PARAM["department_lists"]) == 0 && sizeof($_PARAM["division_lists"]) == 0 && sizeof($_PARAM["section_lists"]) > 0){
			$_sql .= "AND {$_sectionSql} ";
		}


		if(is_array($_PARAM["position_lists"]) && sizeof($_PARAM["position_lists"]) > 0){
			$positionIds = "";
			for ($i = 0; $i < sizeof($_PARAM["position_lists"]); $i++){
				if($i == 0)
					$positionIds = "'" . base64_decode($_PARAM["position_lists"][$i]["id"]) . "' ";
				else
					$positionIds .= ", '" . base64_decode($_PARAM["position_lists"][$i]["id"]) . "' ";
			}
			$_sql .= "AND _employee.position_id IN ({$positionIds}) ";
		}

		if(is_array($_PARAM["employee_lists"]) && sizeof($_PARAM["employee_lists"]) > 0){
			$employeeIds = "";
			for ($i = 0; $i < sizeof($_PARAM["employee_lists"]); $i++){
				if($i == 0)
					$employeeIds = "'" . base64_decode($_PARAM["employee_lists"][$i]["id"]) . "' ";
				else
					$employeeIds .= ", '" . base64_decode($_PARAM["employee_lists"][$i]["id"]) . "' ";
			}
			$_sql .= "AND _employee.employee_id IN ({$employeeIds}) ";

			if($_PARAM['sys_del_flag'] == 'N' || $_PARAM['sys_del_flag'] == 'Y'){
				$_sql .= "AND _employee.sys_del_flag = '{$_PARAM["sys_del_flag"]}' ";
			}
		} else {
			if($_PARAM['sys_del_flag'] == 'N' || $_PARAM['sys_del_flag'] == 'Y'){
				$_sql .= "AND _employee.sys_del_flag = '{$_PARAM["sys_del_flag"]}' ";
			} else if($_PARAM['sys_del_flag'] == 'A'){

			} else {
				$_sql .= "AND _employee.sys_del_flag = 'N' ";
			}
		}

		if($_PARAM['signout_flag']){
			$_sql .= "AND _employee.signout_flag = '{$_PARAM['signout_flag']}' ";
		}
		if($_PARAM['round_xtra_config']){
			$_sql .= "AND _employee.round_xtra_config = '{$_PARAM['round_xtra_config']}' ";
		}
		if($_PARAM['round_ot_config']){
			$_sql .= "AND _employee.round_ot_config = '{$_PARAM['round_ot_config']}' ";
		}
		if($_PARAM['round_worktime_config']){
			$_sql .= "AND _employee.round_worktime_config = '{$_PARAM['round_worktime_config']}' ";
		}

		if($GLOBALS['employeeLogin']['employee_id'] != ''){
			$auth = PageAuthorizeService::getAuthorizeByUserGroup(array("SAL", "SALINEX", "SALBU", "AUDIT", "HRBU"));
			if($_PARAM['only_in_position_line'] == true || $auth === false){
				$posEmpList = $this->getListEmployeeAuthorize($GLOBALS['employeeLogin']['employee_id']);
				$arrayEOH = array();
				$arrayEOH[] = $GLOBALS['employeeLogin']['employee_id'];
				for ($i = 0; $i < sizeof($posEmpList); $i++){
					$arrayEOH[] = $posEmpList[$i]['employee_id'];
				}
				$_sql .= "AND _employee.employee_id IN ('" . implode("','" , $arrayEOH) . "') ";
				// $posEmpList = $this->getListEmployeePositionLine($GLOBALS['employeeLogin']['employee_id']);
				// $arrayEOH = array();
				// for($i=0;$i<sizeof($posEmpList);$i++){
				// 	$arrayEOH[] = $posEmpList[$i]['employee_id'];
				// }
				// if(sizeof($arrayEOH)>0){
				// 	$_sql .= "AND _employee.employee_id IN ('".implode("','" , $arrayEOH)."') ";
				// }
			} else if ($auth == true) {
				// if($_REQUEST['_beta'] == "Y"){
					$tmp_supervisor = $this->filterSupervisorBeta();
					$supervisor_count = $tmp_supervisor['supervisor_count'];
					$arrayEOH = $tmp_supervisor['employee_list'];
				// }else{
				// 	$tmp_supervisor = $this->filterSupervisor();
				// 	$supervisor_count = $tmp_supervisor['supervisor_count'];
				// 	$arrayEOH = $tmp_supervisor['employee_list'];
				// }
				
				// if (sizeof($arrayEOH) > 0) {
					if($supervisor_count > 0){
						$_sql .= "AND _employee.employee_id IN ('" . implode("','" , $arrayEOH) . "') ";
					}
				// }
			}
		}
		$_sql .= "ORDER BY _company.company_code,_branch.branch_code,_department.department_code,_division.division_code,_section.section_code,_employee.employee_code ";
		// $channel = $GLOBALS['instanceServerChannelService']->getInstanceServerSpecificChannels($_REQUEST['server_id'], $_REQUEST['instance_server_id'],$_REQUEST['instance_server_channel_id']);
		// if($channel['max_user_limit']>0){
		// 	$_sql .= "LIMIT ".$channel['max_user_limit']; 
		// }
		// if($_REQUEST['_debug']=='Y'){
		//echo "$_sql<hr>";
		// }

		// echo "$_sql<hr>";
		//exit;

		$lists = $this->_sqllists($_sql);

		$_sql = "SELECT _cycle.*
						FROM comp_work_cycle _cycle
						WHERE _cycle.server_id = '{$_REQUEST['server_id']}'
						AND _cycle.instance_server_id = '{$_REQUEST['instance_server_id']}' 
						AND _cycle.instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}' 
						ORDER BY _cycle.work_cycle_code ";
		// echo "$_sql<br>";
		$cycleLists = $this->_sqllists($_sql);
		$labelCycle = array();
		for ($i = 0; $i < sizeof($cycleLists); $i++){
			$labelCycle[$cycleLists[$i]['work_cycle_id']] = $cycleLists[$i];
		}

		$_sql = "SELECT _appr.* ,
		_emp.employee_id AS approver_employee_id,
		_emp.employee_code AS approver_employee_code,
		_emp.employee_name AS approver_employee_name,
		_emp.employee_last_name AS approver_employee_last_name,
		_emp.employee_nickname AS approver_employee_nickname,
		_emp.employee_name_en AS approver_employee_name_en,
		_emp.employee_last_name_en AS approver_employee_last_name_en,
		_emp.employee_nickname_en AS approver_employee_nickname_en 
		FROM {$GLOBALS['instanceServer']['instance_server_dbn']}.payroll_employee_approver _appr 
		INNER JOIN hms_api.comp_employee _emp ON (_appr.approver_id = _emp.employee_id)
		WHERE _appr.server_id = '{$_REQUEST['server_id']}' 
		AND _appr.instance_server_id = '{$_REQUEST['instance_server_id']}'  
		AND _appr.instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}' ";

		$approver_list = $this->_sqllists($_sql);

		for ($i = 0; $i < sizeof($lists); $i++){
			$cycleListsEmployee = json_decode($lists[$i]['work_cycle_id_json'], true);
			$cycleKey = array_keys($cycleListsEmployee);
			for ($x = 0; $x < sizeof($cycleKey); $x++){
				$lists[$i]['work_cycle_lists'][$x][$cycleKey[$x]] = $labelCycle[$cycleListsEmployee[$cycleKey[$x]]];
			}

			$holidayListsEmployee = json_decode($lists[$i]['holiday_day_json'], true);
			$holidayKey = array_keys($holidayListsEmployee);
			for ($x = 0; $x < sizeof($holidayKey); $x++){
				$lists[$i]['holiday_lists'][$x][$holidayKey[$x]] = $holidayListsEmployee[$holidayKey[$x]];
			}

			$approver_step = array("first","second","third","fourth","fifth");
			for($app_idx = 0; $app_idx < sizeof($approver_list); $app_idx++){
				if($approver_list[$app_idx]['employee_id'] == $lists[$i]['employee_id']){
					$lists[$i]['auth_'.$approver_step[$approver_list[$app_idx]['approver_step']-1]] = $approver_list[$app_idx]['approver_employee_id'];
					$lists[$i]['auth_'.$approver_step[$approver_list[$app_idx]['approver_step']-1].'_id'] = $approver_list[$app_idx]['approver_employee_id'];
					$lists[$i]['auth_'.$approver_step[$approver_list[$app_idx]['approver_step']-1].'_code'] = $approver_list[$app_idx]['approver_employee_code'];
					$lists[$i]['auth_'.$approver_step[$approver_list[$app_idx]['approver_step']-1].'_name'] = $_REQUEST['language_code'] == 'TH' ? $approver_list[$app_idx]['approver_employee_name'] : $approver_list[$app_idx]['approver_employee_name_en'];
					$lists[$i]['auth_'.$approver_step[$approver_list[$app_idx]['approver_step']-1].'_last_name'] = $_REQUEST['language_code'] == 'TH' ? $approver_list[$app_idx]['approver_employee_last_name'] : $approver_list[$app_idx]['approver_employee_last_name_en'];
					$lists[$i]['auth_'.$approver_step[$approver_list[$app_idx]['approver_step']-1].'_nickname'] = $_REQUEST['language_code'] == 'TH' ? $approver_list[$app_idx]['approver_employee_nickname'] : $approver_list[$app_idx]['approver_employee_nickname_en'];
				}
			}
		}

		return $lists;
	}

	function getListEmployeeNotCreateUserWithFilter($_PARAM){

		$_sql = "SELECT _employee.*
						FROM comp_employee _employee
						INNER JOIN comp_company _company ON (_company.company_id=_employee.company_id) 
						INNER JOIN comp_branch _branch ON (_branch.branch_id=_employee.branch_id) 
						INNER JOIN comp_department _department ON (_department.department_id=_employee.department_id) 
						INNER JOIN comp_position _position ON (_position.position_id=_employee.position_id) 
						WHERE _employee.sys_del_flag = 'N'
						AND _employee.server_id = '{$_PARAM['server_id']}'
						AND _employee.instance_server_id = '{$_PARAM['instance_server_id']}'
						AND _employee.instance_server_channel_id = '{$_PARAM['instance_server_channel_id']}' 
						AND _employee.employee_id not in (
							SELECT employee_id FROM suso_user WHERE _employee.server_id = '{$_PARAM['server_id']}' 
							AND _employee.instance_server_id = '{$_PARAM['instance_server_id']}'
						) ";

		if(is_array($_PARAM["company_lists"]) && sizeof($_PARAM["company_lists"]) > 0){
			$companyIds = "";
			for ($i = 0; $i < sizeof($_PARAM["company_lists"]); $i++){
				if($i == 0)
					$companyIds = "'" . base64_decode($_PARAM["company_lists"][$i]["id"]) . "' ";
				else
					$companyIds .= ", '" . base64_decode($_PARAM["company_lists"][$i]["id"]) . "' ";
			}
			$_sql .= "AND _employee.company_id in ({$companyIds}) ";
		}

		if(is_array($_PARAM["branch_lists"]) && sizeof($_PARAM["branch_lists"]) > 0){
			$branchIds = "";
			for ($i = 0; $i < sizeof($_PARAM["branch_lists"]); $i++){
				if($i == 0)
					$branchIds = "'" . base64_decode($_PARAM["branch_lists"][$i]["id"]) . "' ";
				else
					$branchIds .= ", '" . base64_decode($_PARAM["branch_lists"][$i]["id"]) . "' ";
			}
			$_sql .= "AND _employee.branch_id in ({$branchIds}) ";
		}

		if(is_array($_PARAM["department_lists"]) && sizeof($_PARAM["department_lists"]) > 0){
			$departmentIds = "";
			for ($i = 0; $i < sizeof($_PARAM["department_lists"]); $i++){
				if($i == 0)
					$departmentIds = "'" . base64_decode($_PARAM["department_lists"][$i]["id"]) . "' ";
				else
					$departmentIds .= ", '" . base64_decode($_PARAM["department_lists"][$i]["id"]) . "' ";
			}
			$_sql .= "AND _employee.department_id in ({$departmentIds}) ";
		}

		if(is_array($_PARAM["position_lists"]) && sizeof($_PARAM["position_lists"]) > 0){
			$positionIds = "";
			for ($i = 0; $i < sizeof($_PARAM["position_lists"]); $i++){
				if($i == 0)
					$positionIds = "'" . base64_decode($_PARAM["position_lists"][$i]["id"]) . "' ";
				else
					$positionIds .= ", '" . base64_decode($_PARAM["position_lists"][$i]["id"]) . "' ";
			}
			$_sql .= "AND _employee.position_id in ({$positionIds}) ";
		}

		//echo "$_sql<hr>";
		$lists = $this->_sqllists($_sql);

		return $lists;
	}

	function getListEmployeeUserWithFilter($_PARAM){
		$listEmployee = $this->getListEmployeeWithFilter($_PARAM);
		$arrayEOH = array();
		for ($i = 0; $i < sizeof($listEmployee); $i++){
			$arrayEOH[] = $listEmployee[$i]['employee_id'];
		}

		$_sql = "SELECT _user.*,_employee.*  
						FROM comp_employee _employee
						INNER JOIN suso_user _user ON (_employee.employee_id=_user.employee_id) 
						INNER JOIN comp_company _company ON (_employee.company_id=_company.company_id) 
						INNER JOIN comp_branch _branch ON (_employee.branch_id=_branch.branch_id) 
						INNER JOIN comp_department _department ON (_employee.department_id=_department.department_id) 
						LEFT JOIN comp_division _division ON (_employee.division_id=_division.division_id) 
						LEFT JOIN comp_section _section ON (_employee.section_id=_section.section_id) 
						LEFT JOIN comp_section_lv01 _section_lv01 ON (_employee.section_lv01_id=_section_lv01.section_lv01_id) 
						LEFT JOIN comp_section_lv02 _section_lv02 ON (_employee.section_lv02_id=_section_lv02.section_lv02_id) 
						LEFT JOIN comp_section_lv03 _section_lv03 ON (_employee.section_lv03_id=_section_lv03.section_lv03_id) 
						LEFT JOIN comp_section_lv04 _section_lv04 ON (_employee.section_lv04_id=_section_lv04.section_lv04_id) 
						LEFT JOIN comp_section_lv05 _section_lv05 ON (_employee.section_lv05_id=_section_lv05.section_lv05_id) 
						WHERE _employee.sys_del_flag = 'N'
						AND _employee.server_id = '{$_PARAM['server_id']}'
						AND _employee.instance_server_id = '{$_PARAM['instance_server_id']}'
						AND _employee.instance_server_channel_id = '{$_PARAM['instance_server_channel_id']}' 
						AND _employee.employee_id IN ('" . implode("','" , $arrayEOH) . "') 
						ORDER BY _company.company_code,_branch.branch_code,_department.department_code,_division.division_code,_section.section_code,_section_lv01.section_lv01_code,_section_lv02.section_lv02_code,_section_lv03.section_lv03_code,_section_lv04.section_lv04_code,_section_lv05.section_lv05_code,_employee.employee_code ";
		//echo "$_sql<hr>";
		$lists = $this->_sqllists($_sql);

		return $lists;
	}

	function updateLineUserID($_employee_id, $_line_user_id){
		$_sql = "UPDATE comp_employee 
						SET line_user_id = NULL 
						WHERE line_user_id='{$_line_user_id}' 
						AND server_id = '{$_REQUEST['server_id']}' ";
		// echo $_sql."<BR>";
		$this->Internal_Execute_Query($_sql);

		$_sql = "UPDATE comp_employee 
						SET line_user_id='{$_line_user_id}' 
						WHERE employee_id='{$_employee_id}' 
						AND server_id = '{$_REQUEST['server_id']}' ";
		// echo $_sql."<BR>";
		$this->Internal_Execute_Query($_sql);
	}

	function unlinkLineUserID($_line_user_id){
		$_sql = "UPDATE comp_employee 
						SET line_user_id = NULL 
						WHERE line_user_id='{$_line_user_id}' 
						AND server_id = '{$_REQUEST['server_id']}' ";
		// echo $_sql."<BR>";
		$this->Internal_Execute_Query($_sql);
	}

	function updatePlayerID($_employee_id, $_player_id){
		$_sql = "UPDATE comp_employee  
						SET player_id = NULL 
						WHERE player_id='{$_player_id}' 
						AND server_id = '{$_REQUEST['server_id']}' ";
		//echo $_sql."<BR>";
		$this->Internal_Execute_Query($_sql);

		$_sql = "UPDATE comp_employee 
						SET player_id='{$_player_id}' 
						WHERE employee_id='{$_employee_id}' 
						AND server_id = '{$_REQUEST['server_id']}' ";
		//echo $_sql."<BR>";
		$this->Internal_Execute_Query($_sql);
	}
	function updateDeviceID($_employee_id,$_device_id){
		$_sql = "UPDATE hms_api.comp_employee
		SET device_id = NULL 
		WHERE device_id='{$_device_id}' 
		AND server_id = '{$_REQUEST['server_id']}' ";
		//echo $_sql."<BR>";
		$this->Internal_Execute_Query($_sql);

		$_sql = "UPDATE hms_api.comp_employee 
				SET device_id='{$_device_id}' 
				WHERE employee_id='{$_employee_id}' 
				AND server_id = '{$_REQUEST['server_id']}' ";
		// echo $_sql."<BR>";
		$this->Internal_Execute_Query($_sql);
	}
	function updateDeviceIDAndPlayerID($_employee_id, $_device_id, $_player_id){
		$_sql = "UPDATE comp_employee  
						SET player_id = NULL, device_id = NULL 
						WHERE player_id='{$_player_id}' 
						AND server_id = '{$_REQUEST['server_id']}' ";
		//echo $_sql."<BR>";
		$this->Internal_Execute_Query($_sql);

		$_sql = "UPDATE comp_employee 
						SET player_id='{$_player_id}', device_id='{$_device_id}'  
						WHERE employee_id='{$_employee_id}' 
						AND server_id = '{$_REQUEST['server_id']}' ";
		//echo $_sql."<BR>";
		$this->Internal_Execute_Query($_sql);
	}
	function updateAppleID($_employee_id, $_apple_id){
		$_sql = "UPDATE comp_employee 
						SET apple_id = NULL 
						WHERE apple_id='{$_apple_id}' 
						AND server_id = '{$_REQUEST['server_id']}' ";
		//echo $_sql."<BR>";
		$this->Internal_Execute_Query($_sql);

		$_sql = "UPDATE comp_employee 
						SET apple_id='{$_apple_id}' 
						WHERE employee_id='{$_employee_id}' 
						AND server_id = '{$_REQUEST['server_id']}' ";
		//echo $_sql."<BR>";
		$this->Internal_Execute_Query($_sql);
	}
	// ~ ezreal: sso ----------------------------------------------
	function updateMicrosoftEntraIDID($_employee_id, $_microsoft_entra_id_id){
		$_sql = "UPDATE comp_employee 
						SET microsoft_entra_id_id = NULL 
						WHERE microsoft_entra_id_id = '{$_microsoft_entra_id_id}' 
						AND server_id = '{$_REQUEST['server_id']}' ";
		//echo $_sql."<BR>";
		$this->Internal_Execute_Query($_sql);

		$_sql = "UPDATE comp_employee 
						SET microsoft_entra_id_id = '{$_microsoft_entra_id_id}' 
						WHERE employee_id ='{$_employee_id}' 
						AND server_id = '{$_REQUEST['server_id']}' ";
		//echo $_sql."<BR>";
		$this->Internal_Execute_Query($_sql);
	}
	function unsetMicrosoftEntraIDID($_employee_id){
		$_sql = "UPDATE comp_employee 
						SET microsoft_entra_id_id = NULL 
						WHERE employee_id = '{$_employee_id}'  
						AND server_id = '{$_REQUEST['server_id']}' ";
		// echo $_sql."<BR>";
		$this->Internal_Execute_Query($_sql);
	}
	// ~ ----------------------------------------------------------
	function updateFacebookID($_employee_id, $_facebook_id){
		$_sql = "UPDATE comp_employee 
						SET facebook_id = NULL 
						WHERE facebook_id='{$_facebook_id}' 
						AND server_id = '{$_REQUEST['server_id']}' ";
		//echo $_sql."<BR>";
		$this->Internal_Execute_Query($_sql);

		$_sql = "UPDATE comp_employee 
						SET facebook_id='{$_facebook_id}' 
						WHERE employee_id='{$_employee_id}' 
						AND server_id = '{$_REQUEST['server_id']}' ";
		//echo $_sql."<BR>";
		$this->Internal_Execute_Query($_sql);
	}

	function updateGoogleID($_employee_id, $_google_id){
		$_sql = "UPDATE comp_employee 
						SET google_id = NULL 
						WHERE google_id='{$_google_id}' 
						AND server_id = '{$_REQUEST['server_id']}' ";
		//echo $_sql."<BR>";
		$this->Internal_Execute_Query($_sql);

		$_sql = "UPDATE comp_employee 
						SET google_id='{$_google_id}' 
						WHERE employee_id='{$_employee_id}' 
						AND server_id = '{$_REQUEST['server_id']}' ";
		//echo $_sql."<BR>";
		$this->Internal_Execute_Query($_sql);
	}

	function unsetPlayerID($_employee_id){
		$_sql = "UPDATE comp_employee 
						SET player_id = NULL 
						WHERE employee_id = '{$_employee_id}'  
						AND server_id = '{$_REQUEST['server_id']}' ";
		//echo $_sql."<BR>";
		$this->Internal_Execute_Query($_sql);
	}

	function unsetDeviceID($_employee_id){
		$_sql = "UPDATE comp_employee 
						SET device_id = NULL 
						WHERE employee_id = '{$_employee_id}'  
						AND server_id = '{$_REQUEST['server_id']}' ";
		// echo $_sql."<BR>";
		$this->Internal_Execute_Query($_sql);
	}

	function unsetFacebookID($_employee_id){
		$_sql = "UPDATE comp_employee 
						SET facebook_id = NULL 
						WHERE employee_id = '{$_employee_id}'  
						AND server_id = '{$_REQUEST['server_id']}' ";
		// echo $_sql."<BR>";
		$this->Internal_Execute_Query($_sql);
	}

	function unsetGoogleID($_employee_id){
		$_sql = "UPDATE comp_employee 
						SET google_id = NULL 
						WHERE employee_id = '{$_employee_id}'  
						AND server_id = '{$_REQUEST['server_id']}' ";
		// echo $_sql."<BR>";
		$this->Internal_Execute_Query($_sql);
	}

	function unsetAppleID($_employee_id){
		$_sql = "UPDATE comp_employee 
						SET apple_id = NULL 
						WHERE employee_id = '{$_employee_id}'  
						AND server_id = '{$_REQUEST['server_id']}' ";
		// echo $_sql."<BR>";
		$this->Internal_Execute_Query($_sql);
	}

	function setPlayerIDSocial($_player_id, $_sub_sql){
		$_sql = "UPDATE comp_employee 
						SET player_id = '{$_player_id}' 
						WHERE {$_sub_sql}  
						AND server_id = '{$_REQUEST['server_id']}' ";
		// echo $_sql."<BR>";
		$this->Internal_Execute_Query($_sql);
	}
	function setDeviceIDSocial($_device_id, $_sub_sql){
		$_sql = "UPDATE comp_employee 
						SET device_id = '{$_device_id}' 
						WHERE {$_sub_sql}  
						AND server_id = '{$_REQUEST['server_id']}' ";
		// echo $_sql."<BR>";
		$this->Internal_Execute_Query($_sql);
	}
	function setPublish($_employee_id, $_publish_flag){
		$_sql = "UPDATE comp_employee 
						SET publish_flag = '{$_publish_flag}' 
						WHERE employee_id = '{$_employee_id}'  
						AND server_id = '{$_REQUEST['server_id']}' ";
		// echo $_sql."<BR>";
		$this->Internal_Execute_Query($_sql);
	}

	function getAuthorizeAllDoc($employee_id){
		$step_leave = $this->configApproveStep("Leave");
		$step_work = $this->configApproveStep("Work_Cycle");
		$step_ot = $this->configApproveStep("OT");
		$step_holiday = $this->configApproveStep("Holiday");
		$step_time = $this->configApproveStep("Time_Adjust");

		$tmpEmpList = $this->getListEmployeeAuthorize($employee_id);
		$_2step_EmpList = array();
		for($i=0;$i<sizeof($tmpEmpList);$i++){
			$_2step_EmpList[] = $tmpEmpList[$i]['employee_id'];
		}

		$tmpEmpList = $this->getListEmployeeAuthorizeFirst($employee_id);
		$_1step_EmpList = array();
		for($i=0;$i<sizeof($tmpEmpList);$i++){
			$_1step_EmpList[] = $tmpEmpList[$i]['employee_id'];
		}

		$emp_list = array();

		if($step_leave == '2'){
			$emp_list['Leave'] = $_2step_EmpList;
		}else if($step_leave == '1'){
			$emp_list['Leave'] = $_1step_EmpList;
		}else{
			$emp_list['Leave'] = array();
		}

		if($step_work == '2'){
			$emp_list['Work_Cycle'] = $_2step_EmpList;
		}else if($step_work == '1'){
			$emp_list['Work_Cycle'] = $_1step_EmpList;
		}else{
			$emp_list['Work_Cycle'] = array();
		}

		if($step_ot == '2'){
			$emp_list['OT'] = $_2step_EmpList;
		}else if($step_ot == '1'){
			$emp_list['OT'] = $_1step_EmpList;
		}else{
			$emp_list['OT'] = array();
		}

		if($step_holiday == '2'){
			$emp_list['Holiday'] = $_2step_EmpList;
		}else if($step_holiday == '1'){
			$emp_list['Holiday'] = $_1step_EmpList;
		}else{
			$emp_list['Holiday'] = array();
		}

		if($step_time == '2'){
			$emp_list['Time_Adjust'] = $_2step_EmpList;
		}else if($step_time == '1'){
			$emp_list['Time_Adjust'] = $_1step_EmpList;
		}else{
			$emp_list['Time_Adjust'] = array();
		}

		return $emp_list;
	}

	function getListEmployeeWithFilterCount($_PARAM)
	{
		// print_r($GLOBALS['employeeLogin']);
		$_sql = "SELECT COUNT(*) AS count
						FROM (select * from hms_api.comp_employee  where instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}') _employee 
						INNER JOIN (select company_id, company_code, company_name, company_name_en FROM hms_api.comp_company  where instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}') _company ON (_company.company_id=_employee.company_id) 
						INNER JOIN (select branch_id, branch_code, branch_name, branch_name_en FROM hms_api.comp_branch  where instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}') _branch ON (_branch.branch_id=_employee.branch_id) 
						INNER JOIN (select department_id, department_code, department_name, department_name_en 
						                       FROM hms_api.comp_department  where instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}') _department ON (_department.department_id=_employee.department_id)  
						INNER JOIN (select position_id, position_code, position_name, position_name_en 
												FROM hms_api.comp_position where instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}') _position ON (_position.position_id=_employee.position_id) 
						WHERE _employee.server_id = '{$_REQUEST['server_id']}'
						AND _employee.instance_server_id = '{$_REQUEST['instance_server_id']}'
						AND _employee.instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}' ";

		if($_PARAM["keyword"] != ''){
			$_sql .= "AND (
								_employee.employee_nickname LIKE '%{$_PARAM["keyword"]}%' OR
								_employee.employee_name LIKE '%{$_PARAM["keyword"]}%' OR
								_employee.employee_last_name LIKE '%{$_PARAM["keyword"]}%' OR
								_employee.employee_nickname_en LIKE '%{$_PARAM["keyword"]}%' OR
								_employee.employee_name_en LIKE '%{$_PARAM["keyword"]}%' OR
								_employee.employee_last_name_en LIKE '%{$_PARAM["keyword"]}%' OR
								_employee.fing_code LIKE '%{$_PARAM["keyword"]}%' OR
								_employee.employee_code LIKE '%{$_PARAM["keyword"]}%') ";
		}

		if(sizeof($_PARAM["hashtags"]) == 1){
			$_sql .= " AND _employee.hashtag_desc LIKE '%{$_PARAM["hashtags"][0]}%' ";
		} else if(sizeof($_PARAM["hashtags"]) > 1){
			$_sql .= " AND ( ";
			for ($i = 0; $i < sizeof($_PARAM["hashtags"]); $i++){
				if($i == 0)
					$_sql .= " _employee.hashtag_desc LIKE '%{$_PARAM["hashtags"][$i]}%' ";
				else
					$_sql .= " OR _employee.hashtag_desc LIKE '%{$_PARAM["hashtags"][$i]}%' ";
			}
			$_sql .= " ) ";
		}

		if(is_array($_PARAM["except"]) && sizeof($_PARAM["except"]) > 0){
			$excepIds = "";
			for ($i = 0; $i < sizeof($_PARAM["except"]); $i++){
				if($i == 0)
					$excepIds = "'" . base64_decode($_PARAM["except"][$i]["id"]) . "' ";
				else
					$excepIds .= ", '" . base64_decode($_PARAM["except"][$i]["id"]) . "' ";
			}
			$_sql .= "AND _employee.employee_id NOT IN ({$excepIds}) ";
		}

		// if($_PARAM['sys_del_flag']=='N'||$_PARAM['sys_del_flag']=='Y'){
		// 	$_sql .= "AND _employee.sys_del_flag = '{$_PARAM["sys_del_flag"]}' ";
		// }else if($_PARAM['sys_del_flag']=='A'){

		// }else{
		// 	$_sql .= "AND _employee.sys_del_flag = 'N' "; 
		// }

		if(is_array($_PARAM["company_lists"]) && sizeof($_PARAM["company_lists"]) > 0){
			$companyIds = "";
			for ($i = 0; $i < sizeof($_PARAM["company_lists"]); $i++){
				if($i == 0)
					$companyIds = "'" . base64_decode($_PARAM["company_lists"][$i]["id"]) . "' ";
				else
					$companyIds .= ", '" . base64_decode($_PARAM["company_lists"][$i]["id"]) . "' ";
			}
			$_sql .= "AND _employee.company_id IN ({$companyIds}) ";
		}

		if(is_array($_PARAM["branch_lists"]) && sizeof($_PARAM["branch_lists"]) > 0){
			$branchIds = "";
			for ($i = 0; $i < sizeof($_PARAM["branch_lists"]); $i++){
				if($i == 0)
					$branchIds = "'" . base64_decode($_PARAM["branch_lists"][$i]["id"]) . "' ";
				else
					$branchIds .= ", '" . base64_decode($_PARAM["branch_lists"][$i]["id"]) . "' ";
			}
			$_branchSql = " _employee.department_id IN (SELECT department_id FROM hms_api.comp_department WHERE branch_id IN ({$branchIds}) 
							AND instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}' AND sys_del_flag='N') ";
		}

		if(is_array($_PARAM["department_lists"]) && sizeof($_PARAM["department_lists"]) > 0){
			$departmentIds = "";
			for ($i = 0; $i < sizeof($_PARAM["department_lists"]); $i++){
				if($i == 0)
					$departmentIds = "'" . base64_decode($_PARAM["department_lists"][$i]["id"]) . "' ";
				else
					$departmentIds .= ", '" . base64_decode($_PARAM["department_lists"][$i]["id"]) . "' ";
			}
			$_departmentSql = "_employee.department_id IN ({$departmentIds}) ";
		}

		if(is_array($_PARAM["division_lists"]) && sizeof($_PARAM["division_lists"]) > 0){
			$divisionIds = "";
			for ($i = 0; $i < sizeof($_PARAM["division_lists"]); $i++){
				if($i == 0){
					$divisionIds = "'" . base64_decode($_PARAM["division_lists"][$i]["id"]) . "' ";
				} else {
					$divisionIds .= ", '" . base64_decode($_PARAM["division_lists"][$i]["id"]) . "' ";
				}
			}
			$_divisionSql = "_employee.division_id IN ({$divisionIds}) ";
		}

		if(is_array($_PARAM["section_lists"]) && sizeof($_PARAM["section_lists"]) > 0){
			$sectionIds = "";
			for ($i = 0; $i < sizeof($_PARAM["section_lists"]); $i++){
				if($i == 0){
					$sectionIds = "'" . base64_decode($_PARAM["section_lists"][$i]["id"]) . "' ";
				} else {
					$sectionIds .= ", '" . base64_decode($_PARAM["section_lists"][$i]["id"]) . "' ";
				}
			}
			$_sectionSql = "_employee.section_id IN ({$sectionIds}) ";
		}
		
		if(is_array($_REQUEST["section_lists_lv01"]) && sizeof($_REQUEST["section_lists_lv01"]) > 0){
			$sectionLv01Ids = "";
			for ($i = 0; $i < sizeof($_REQUEST["section_lists_lv01"]); $i++){
				if($i == 0)
					$sectionLv01Ids = "'" . base64_decode($_REQUEST["section_lists_lv01"][$i]["id"]) . "' ";
				 else 
					$sectionLv01Ids .= ", '" . base64_decode($_REQUEST["section_lists_lv01"][$i]["id"]) . "' ";
				
			}
			$_sectionLv01Sql = "_employee.section_lv01_id IN ({$sectionLv01Ids}) ";
		}
	
		if(is_array($_REQUEST["section_lists_lv02"]) && sizeof($_REQUEST["section_lists_lv02"]) > 0){
			$sectionLv02Ids = "";
			for ($i = 0; $i < sizeof($_REQUEST["section_lists_lv02"]); $i++){
				if($i == 0)
					$sectionLv02Ids = "'" . base64_decode($_REQUEST["section_lists_lv02"][$i]["id"]) . "' ";
				 else 
					$sectionLv02Ids .= ", '" . base64_decode($_REQUEST["section_lists_lv02"][$i]["id"]) . "' ";
				
			}
			$_sectionLv02Sql = "_employee.section_lv02_id IN ({$sectionLv02Ids}) ";
		}
	
		if(is_array($_REQUEST["section_lists_lv03"]) && sizeof($_REQUEST["section_lists_lv03"]) > 0){
			$sectionLv03Ids = "";
			for ($i = 0; $i < sizeof($_REQUEST["section_lists_lv03"]); $i++){
				if($i == 0)
					$sectionLv03Ids = "'" . base64_decode($_REQUEST["section_lists_lv03"][$i]["id"]) . "' ";
				 else 
					$sectionLv03Ids .= ", '" . base64_decode($_REQUEST["section_lists_lv03"][$i]["id"]) . "' ";
				
			}
			$_sectionLv03Sql = "_employee.section_lv03_id IN ({$sectionLv03Ids}) ";
		}
	
		if(is_array($_REQUEST["section_lists_lv04"]) && sizeof($_REQUEST["section_lists_lv04"]) > 0){
			$sectionLv04Ids = "";
			for ($i = 0; $i < sizeof($_REQUEST["section_lists_lv04"]); $i++){
				if($i == 0)
					$sectionLv04Ids = "'" . base64_decode($_REQUEST["section_lists_lv04"][$i]["id"]) . "' ";
				else 
					$sectionLv04Ids .= ", '" . base64_decode($_REQUEST["section_lists_lv04"][$i]["id"]) . "' ";
				
			}
			$_sectionLv04Sql = "_employee.section_lv04_id IN ({$sectionLv04Ids}) ";
		}
	
		if(is_array($_REQUEST["section_lists_lv05"]) && sizeof($_REQUEST["section_lists_lv05"]) > 0){
			$sectionLv05Ids = "";
			for ($i = 0; $i < sizeof($_REQUEST["section_lists_lv05"]); $i++){
				if($i == 0)
					$sectionLv05Ids = "'" . base64_decode($_REQUEST["section_lists_lv05"][$i]["id"]) . "' ";
				 else 
					$sectionLv05Ids .= ", '" . base64_decode($_REQUEST["section_lists_lv05"][$i]["id"]) . "' ";
				
			}
			$_sectionLv05Sql = "_employee.section_lv05_id IN ({$sectionLv05Ids}) ";
		}

		if(sizeof($_PARAM["branch_lists"]) > 0 && sizeof($_PARAM["department_lists"]) > 0){
			$_sql .= "AND {$_branchSql} AND {$_departmentSql} ";
		} else if(sizeof($_PARAM["branch_lists"]) > 0 && sizeof($_PARAM["department_lists"]) == 0){
			$_sql .= "AND {$_branchSql} ";
		} else if(sizeof($_PARAM["branch_lists"]) == 0 && sizeof($_PARAM["department_lists"]) > 0){
			$_sql .= "AND {$_departmentSql}	";
		}

		if(sizeof($_PARAM["division_lists"]) > 0){
			$_sql .= "AND {$_divisionSql} ";
		}

		if(sizeof($_PARAM["section_lists"]) > 0){
			$_sql .= "AND {$_sectionSql} ";
		}

		if(sizeof($_PARAM["section_lists_lv01"]) > 0){
			$_sql .= "AND {$_sectionLv01Sql} ";
		}

		if(sizeof($_PARAM["section_lists_lv02"]) > 0){
			$_sql .= "AND {$_sectionLv02Sql} ";
		}

		if(sizeof($_PARAM["section_lists_lv03"]) > 0){
			$_sql .= "AND {$_sectionLv03Sql} ";
		}

		if(sizeof($_PARAM["section_lists_lv04"]) > 0){
			$_sql .= "AND {$_sectionLv04Sql} ";
		}

		if(sizeof($_PARAM["section_lists_lv05"]) > 0){
			$_sql .= "AND {$_sectionLv05Sql} ";
		}

		/* if(!empty($_PARAM['filter_by_individual_structure'])){
			$companyIds = "";
			if(is_array($_PARAM['filter_by_individual_structure']["company_lists"]) && sizeof($_PARAM['filter_by_individual_structure']["company_lists"]) > 0){
				for ($i = 0; $i < sizeof($_PARAM['filter_by_individual_structure']["company_lists"]); $i++) {
					if ($i == 0)
						$companyIds = "'" . base64_decode($_PARAM['filter_by_individual_structure']["company_lists"][$i]["id"]) . "' ";
					else
						$companyIds .= ", '" . base64_decode($_PARAM['filter_by_individual_structure']["company_lists"][$i]["id"]) . "' ";
				}
			}else{
				$companyIds = "('')";
			}
			
			$branchIds = "";
			if(is_array($_PARAM['filter_by_individual_structure']["branch_lists"]) && sizeof($_PARAM['filter_by_individual_structure']["branch_lists"]) > 0){
				for ($i = 0; $i < sizeof($_PARAM['filter_by_individual_structure']["branch_lists"]); $i++) {
					if ($i == 0)
						$branchIds = "'" . base64_decode($_PARAM['filter_by_individual_structure']["branch_lists"][$i]["id"]) . "' ";
					else
						$branchIds .= ", '" . base64_decode($_PARAM['filter_by_individual_structure']["branch_lists"][$i]["id"]) . "' ";
				}
			}else{
				$branchIds = "('')";
			}
			
			$departmentIds = "";
			if(is_array($_PARAM['filter_by_individual_structure']["department_lists"]) && sizeof($_PARAM['filter_by_individual_structure']["department_lists"]) > 0){
				for ($i = 0; $i < sizeof($_PARAM['filter_by_individual_structure']["department_lists"]); $i++) {
					if ($i == 0)
						$departmentIds = "'" . base64_decode($_PARAM['filter_by_individual_structure']["department_lists"][$i]["id"]) . "' ";
					else
						$departmentIds .= ", '" . base64_decode($_PARAM['filter_by_individual_structure']["department_lists"][$i]["id"]) . "' ";
				}
			}else{
				$departmentIds = "('')";
			}
			
			$_sql .= " AND (_employee.company_id IN ({$companyIds}) OR _employee.branch_id IN ({$branchIds}) OR _employee.department_id IN ({$departmentIds})) ";
		} */

		if (is_array($_PARAM["position_lists"]) && sizeof($_PARAM["position_lists"]) > 0) {
			$positionIds = "";
			for ($i = 0; $i < sizeof($_PARAM["position_lists"]); $i++){
				if($i == 0)
					$positionIds = "'" . base64_decode($_PARAM["position_lists"][$i]["id"]) . "' ";
				else
					$positionIds .= ", '" . base64_decode($_PARAM["position_lists"][$i]["id"]) . "' ";
			}
			$_sql .= "AND _employee.position_id IN ({$positionIds}) ";
		}

		if(is_array($_PARAM["employee_lists"]) && sizeof($_PARAM["employee_lists"]) > 0){
			$employeeIds = "";
			for ($i = 0; $i < sizeof($_PARAM["employee_lists"]); $i++){
				if($i == 0)
					$employeeIds = "'" . base64_decode($_PARAM["employee_lists"][$i]["id"]) . "' ";
				else
					$employeeIds .= ", '" . base64_decode($_PARAM["employee_lists"][$i]["id"]) . "' ";
			}
			$_sql .= "AND _employee.employee_id IN ({$employeeIds}) ";

			if($_PARAM['sys_del_flag'] == 'N' || $_PARAM['sys_del_flag'] == 'Y'){
				$_sql .= "AND _employee.sys_del_flag = '{$_PARAM["sys_del_flag"]}' ";
			}
		} else {
			if($_PARAM['sys_del_flag'] == 'N' || $_PARAM['sys_del_flag'] == 'Y'){
				$_sql .= "AND _employee.sys_del_flag = '{$_PARAM["sys_del_flag"]}' ";
			} else if($_PARAM['sys_del_flag'] == 'A'){

			} else {
				$_sql .= "AND _employee.sys_del_flag = 'N' ";
			}
		}

		if($_PARAM['signout_flag']){
			$_sql .= "AND _employee.signout_flag = '{$_PARAM['signout_flag']}' ";
		}
		if($_PARAM['round_xtra_config']){
			$_sql .= "AND _employee.round_xtra_config = '{$_PARAM['round_xtra_config']}' ";
		}
		if($_PARAM['round_ot_config']){
			$_sql .= "AND _employee.round_ot_config = '{$_PARAM['round_ot_config']}' ";
		}
		if($_PARAM['round_worktime_config']){
			$_sql .= "AND _employee.round_worktime_config = '{$_PARAM['round_worktime_config']}' ";
		}

		if($GLOBALS['employeeLogin']['employee_id'] != ''){
			$auth = PageAuthorizeService::getAuthorizeByUserGroup(array("SAL", "SALINEX", "SALBU", "AUDIT", "HRBU"));
			if($_PARAM['only_in_position_line'] == true || $auth === false){
				$posEmpList = $this->getListEmployeeAuthorize($GLOBALS['employeeLogin']['employee_id']);
				$arrayEOH = array();
				if($_PARAM['not_include_employee_login'] === true){
					// not add employee login
				}else{
					$arrayEOH[] = $GLOBALS['employeeLogin']['employee_id'];

				}
				for ($i = 0; $i < sizeof($posEmpList); $i++){
					$arrayEOH[] = $posEmpList[$i]['employee_id'];
				}
				$_sql .= "AND _employee.employee_id IN ('" . implode("','" , $arrayEOH) . "') ";
				// $posEmpList = $this->getListEmployeePositionLine($GLOBALS['employeeLogin']['employee_id']);
				// $arrayEOH = array();
				// for($i=0;$i<sizeof($posEmpList);$i++){
				// 	$arrayEOH[] = $posEmpList[$i]['employee_id'];
				// }
				// if(sizeof($arrayEOH)>0){
				// 	$_sql .= "AND _employee.employee_id IN ('".implode("','" , $arrayEOH)."') ";
				// }
			} else if ($auth == true) {
				// echo "Test 	2";
				// if($_REQUEST['_beta'] == "Y"){
					$tmp_supervisor = $this->filterSupervisorBeta();
					$supervisor_count = $tmp_supervisor['supervisor_count'];
					$arrayEOH = $tmp_supervisor['employee_list'];
				// }else{
				// 	$tmp_supervisor = $this->filterSupervisor();
				// 	$supervisor_count = $tmp_supervisor['supervisor_count'];
				// 	$arrayEOH = $tmp_supervisor['employee_list'];
				// }
				
				// if (sizeof($arrayEOH) > 0) {
					if($supervisor_count > 0){
						$_sql .= "AND _employee.employee_id IN ('" . implode("','" , $arrayEOH) . "') ";
					}
				// }
			}
		}
		$_sql .= "ORDER BY _company.company_code,_branch.branch_code,_department.department_code,_employee.employee_code ";
		if(!empty($_PARAM['_PAGE']) && !empty($_PARAM['_NUMBER_PER_PAGE']) && $_PARAM['_PAGE'] > 0 && $_PARAM['_NUMBER_PER_PAGE'] > 0){
			$_LIMIT = $_PARAM['_NUMBER_PER_PAGE'];
			$_OFFSET = ($_PARAM['_PAGE'] - 1) * $_PARAM['_NUMBER_PER_PAGE'];
			$_sql .= "LIMIT {$_LIMIT} OFFSET {$_OFFSET}";
		}
		// $channel = $GLOBALS['instanceServerChannelService']->getInstanceServerSpecificChannels($_REQUEST['server_id'], $_REQUEST['instance_server_id'],$_REQUEST['instance_server_channel_id']);
		// if($channel['max_user_limit']>0){
		// 	$_sql .= "LIMIT ".$channel['max_user_limit']; 
		// }
		// if($_REQUEST['_debug']=='Y'){
		// echo "$_sql<br><hr>";
		// }

		if(!empty($_PARAM['sqlCondition'])){
			$_sql .= $_PARAM['sqlCondition'];
		}

		// echo "$_sql<hr>";
		// exit;

		$lists = $this->_sqlget($_sql);
		if ($_PARAM['check_count_of_employee'] === true && sizeof($lists) > $_PARAM['count_of_employee_limit']) {
			throw new Exception('employee-overlimit');
		}
		return $lists['count'];
	}

	function getApproverListCompany($_emp = '', $domain = false, $check_step = true) {
		if($GLOBALS['instanceServerChannel']['package_id'] == '1' || $GLOBALS['instanceServerChannel']['package_id'] == '2'
		||$GLOBALS['instanceServerChannel']['package_id'] == '3'  || $GLOBALS['instanceServerChannel']['package_id'] == '4' 
		|| $GLOBALS['instanceServerChannel']['package_id'] == '6' || $GLOBALS['instanceServerChannel']['package_id'] == '10' 
		|| $check_step === false){
			$_sql = "SELECT _appr.* ,
			_appr.approver_id AS approver_employee_id,
			IFNULL(_appr.approver_photograph , 'images/userPlaceHolder.png') AS approver_photograph, 
			_sys_instance_server_channel.instance_server_channel_code
			FROM {$GLOBALS['instanceServer']['instance_server_dbn']}.payroll_employee_approver _appr 
			LEFT JOIN hms_api.sys_instance_server_channel _sys_instance_server_channel ON (_appr.approver_channel_id = _sys_instance_server_channel.instance_server_channel_id AND  _appr.instance_server_id = _sys_instance_server_channel.instance_server_id AND _appr.server_id = _sys_instance_server_channel.server_id)
			WHERE _appr.server_id = '{$_REQUEST['server_id']}' 
			AND _appr.instance_server_id = '{$_REQUEST['instance_server_id']}' ";

			if($GLOBALS['instanceServerChannel']['package_id'] != '3' && $GLOBALS['instanceServerChannel']['package_id'] != '4' && $check_step === true){
				$_sql .= " AND _appr.approver_step <= '2' ";
			}
			if($domain === false){
				$_sql .= " AND _appr.instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}' ";
			}
			

			if(!empty($_emp)){
				$_sql .= " AND _appr.employee_id = '{$_emp}' ";
			}
			$_sql .= " ORDER BY _appr.approver_step ASC ";
			// echo $_sql;
			$approver_list = $this->_sqllists($_sql);
		}else{
			$approver_list = array();
		}
		

		return $approver_list;
	}

	
	function getListEmployeeWithFilterCompanyCode($_PARAM) {
		// print_r($GLOBALS['employeeLogin']);
		$_sql = "SELECT _employee.employee_id,
						_employee.employee_code,
						_employee.fing_code,
						_employee.employee_type_code,
						_employee.employee_type_group_id,
						_employee.employee_nickname,
						_employee.employee_nickname_en,
						_employee.employee_name,
						_employee.employee_last_name,
						_employee.employee_name_en,
						_employee.employee_last_name_en,
						_employee.employee_title_lv,
						_employee.employee_gender,
						_employee.employee_foreigner,
						_employee.employee_status,
						_employee.position_id,
						_employee.company_id,
						_employee.branch_id,
						_employee.department_id,
						_employee.division_id,
						_employee.section_id,
						_employee.section_lv01_id,
						_employee.section_lv02_id,
						_employee.section_lv03_id,
						_employee.section_lv04_id,
						_employee.section_lv05_id,
						_employee.mobilephone,
						_employee.emailaddress,
						_employee.salary,
						_employee.salary_law,
						_employee.salary_per_week_type_lv,
						_employee.salary_per_week,
						_employee.payment_method,
						_employee.social_insurance_method_lv,
						_employee.social_insurance_method_constant,
						_employee.social_insurance_deduct_lv,
						_employee.tax_method_lv,
						_employee.tax_method_constant,
						_employee.tax_method_rate,
						_employee.tax_deduct_lv,
						_employee.days_per_month,
						_employee.hours_per_day,
						_employee.birth_dt,
						_employee.id_no,
						_employee.sso_no,
						_employee.opt_code,
						_employee.person_id,
						_employee.line_user_id,
						_employee.player_id,
						_employee.apple_id,
						_employee.line_token_id,
						_employee.line_token_todolist_id,
						IFNULL(_employee.photograph , 'images/userPlaceHolder.png') AS photograph,
						_employee.bank_id,
						_employee.coa_account_group_id,
						_employee.company_payment_account_id,
						_employee.bank_branch_code,
						_employee.bank_account_code,
						_employee.work_cycle_id_json,
						_employee.work_cycle_format,
						_employee.holiday_day_json,
						_employee.holiday_format,
						_employee.clock_inout,
						_employee.trial_range,
						_employee.effective_dt,
						_employee.begin_dt,
						_employee.signout_flag,
						_employee.signout_request_dt,
						_employee.signout_dt,
						_employee.out_dt,
						_employee.sso_out_dt,
						_employee.signout_type_flag,
						_employee.signout_remark,
						_employee.round_month_config,
						_employee.round_xtra_config,
						_employee.round_ot_config,
						_employee.round_worktime_config,
						_employee.holiday_apply_config,
						_employee.import_log_id,
						_employee.personal_config,
						_employee.address,
						_employee.address1,
						_employee.address2,
						_employee.address3,
						_employee.address4,
						_employee.address5,
						_employee.address6,
						_employee.address7,
						_employee.address8,
						_employee.address9,
						_employee.country_id,
						_employee.country_code,
						_employee.state_code,
						_employee.district_code,
						_employee.subdistrict_code,
						_employee.post_code,
						_employee.current_address,
						_employee.current_address1,
						_employee.current_address2,
						_employee.current_address3,
						_employee.current_address4,
						_employee.current_address5,
						_employee.current_address6,
						_employee.current_address7,
						_employee.current_address8,
						_employee.current_address9,
						_employee.current_country_code,
						_employee.current_state_code,
						_employee.current_district_code,
						_employee.current_subdistrict_code,
						_employee.current_post_code,
						_employee.hashtag_desc,
						_employee.order_no,
						_employee.server_id,
						_employee.instance_server_id,
						_employee.instance_server_channel_id,
						_employee.sys_del_flag,
						_employee.reference_code_1,					
						_employee.reference_code_2,					
						_employee.reference_code_3,					
						_employee.reference_code_4,					
						_employee.reference_code_5,
						_company.company_code,
						_company.company_name,
						_company.company_name_en,
						_branch.branch_code,
						_branch.branch_name,
						_branch.branch_name_en,
						_department.department_code,
						_department.department_name,
						_department.department_name_en,
						_division.division_code,
						_division.division_name,
						_division.division_name_en,
						_section.section_code,
						_section.section_name,
						_section.section_name_en,
						_section_lv01.section_lv01_code,
						_section_lv01.section_lv01_name,
						_section_lv01.section_lv01_name_en,
						_section_lv02.section_lv02_code,
						_section_lv02.section_lv02_name,
						_section_lv02.section_lv02_name_en,
						_section_lv03.section_lv03_code,
						_section_lv03.section_lv03_name,
						_section_lv03.section_lv03_name_en,
						_section_lv04.section_lv04_code,
						_section_lv04.section_lv04_name,
						_section_lv04.section_lv04_name_en,
						_section_lv05.section_lv05_code,
						_section_lv05.section_lv05_name,
						_section_lv05.section_lv05_name_en,
						_position.position_code,
						_position.position_name,
						_position.position_name_en,
						_taxperson.person_tax_transac_id AS person_tax_id,
						 _employee.publish_flag  ,
						 _typegroup.tax_type ,
						 _typegroup.employee_type_group_id,
						 _typegroup.employee_type_group_code,
						 _typegroup.employee_type_group_name,
						 _typegroup.employee_type_group_name_en 
						FROM (select * from hms_api.comp_employee  where instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}') _employee 
						INNER JOIN (select company_id, company_code, company_name, company_name_en FROM hms_api.comp_company  where instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}') _company ON (_company.company_id=_employee.company_id) 
						INNER JOIN (select branch_id, branch_code, branch_name, branch_name_en FROM hms_api.comp_branch  where instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}') _branch ON (_branch.branch_id=_employee.branch_id) 
						INNER JOIN (select department_id, department_code, department_name, department_name_en 
						                       FROM hms_api.comp_department  where instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}') _department ON (_department.department_id=_employee.department_id) 
						LEFT JOIN (select division_id, division_code, division_name, division_name_en 
						                       FROM hms_api.comp_division where instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}') _division ON (_division.division_id=_employee.division_id) 
						LEFT JOIN (select section_id, section_code, section_name, section_name_en 
						                       FROM hms_api.comp_section where instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}') _section ON (_section.section_id=_employee.section_id) 
						LEFT JOIN (select section_lv01_id, section_lv01_code, section_lv01_name, section_lv01_name_en 
						                       FROM hms_api.comp_section_lv01 where instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}') _section_lv01 ON (_section_lv01.section_lv01_id=_employee.section_lv01_id) 
						LEFT JOIN (select section_lv02_id, section_lv02_code, section_lv02_name, section_lv02_name_en 
						                       FROM hms_api.comp_section_lv02 where instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}') _section_lv02 ON (_section_lv02.section_lv02_id=_employee.section_lv02_id) 
						LEFT JOIN (select section_lv03_id, section_lv03_code, section_lv03_name, section_lv03_name_en 
						                       FROM hms_api.comp_section_lv03 where instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}') _section_lv03 ON (_section_lv03.section_lv03_id=_employee.section_lv03_id) 
						LEFT JOIN (select section_lv04_id, section_lv04_code, section_lv04_name, section_lv04_name_en 
						                       FROM hms_api.comp_section_lv04 where instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}') _section_lv04 ON (_section_lv04.section_lv04_id=_employee.section_lv04_id) 
						LEFT JOIN (select section_lv05_id, section_lv05_code, section_lv05_name, section_lv05_name_en 
						                       FROM hms_api.comp_section_lv05 where instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}') _section_lv05 ON (_section_lv05.section_lv05_id=_employee.section_lv05_id) 
						INNER JOIN (select position_id, position_code, position_name, position_name_en 
												FROM hms_api.comp_position where instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}') _position ON (_position.position_id=_employee.position_id) 
						LEFT JOIN (
							SELECT person_tax_transac_id, tax_year_code, tax_month_code, tax_category_id, employee_id 
							FROM {$GLOBALS['instanceServer']['instance_server_dbn']}.payroll_person_tax_transac 
							WHERE instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}' 
							AND tax_year_code = '" . date('Y') . "' 
							AND tax_month_code = '12' 
							AND tax_category_id = '60'
						) _taxperson ON (_taxperson.employee_id = _employee.employee_id) 
						LEFT JOIN hms_api.comp_employee_type_group _typegroup ON (_typegroup.employee_type_group_id = _employee.employee_type_group_id) 
						WHERE _employee.server_id = '{$_REQUEST['server_id']}'
						AND _employee.instance_server_id = '{$_REQUEST['instance_server_id']}'
						AND _employee.instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}' ";

		if($_PARAM["keyword"] != ''){
			$_sql .= "AND (
								_employee.employee_nickname LIKE '%{$_PARAM["keyword"]}%' OR
								_employee.employee_name LIKE '%{$_PARAM["keyword"]}%' OR
								_employee.employee_last_name LIKE '%{$_PARAM["keyword"]}%' OR
								_employee.employee_nickname_en LIKE '%{$_PARAM["keyword"]}%' OR
								_employee.employee_name_en LIKE '%{$_PARAM["keyword"]}%' OR
								_employee.employee_last_name_en LIKE '%{$_PARAM["keyword"]}%' OR
								_employee.fing_code LIKE '%{$_PARAM["keyword"]}%' OR
								_employee.employee_code LIKE '%{$_PARAM["keyword"]}%') ";
		}

		if(sizeof($_PARAM["hashtags"]) == 1){
			$_sql .= " AND _employee.hashtag_desc LIKE '%{$_PARAM["hashtags"][0]}%' ";
		} else if(sizeof($_PARAM["hashtags"]) > 1){
			$_sql .= " AND ( ";
			for ($i = 0; $i < sizeof($_PARAM["hashtags"]); $i++){
				if($i == 0)
					$_sql .= " _employee.hashtag_desc LIKE '%{$_PARAM["hashtags"][$i]}%' ";
				else
					$_sql .= " OR _employee.hashtag_desc LIKE '%{$_PARAM["hashtags"][$i]}%' ";
			}
			$_sql .= " ) ";
		}

		if(is_array($_PARAM["except"]) && sizeof($_PARAM["except"]) > 0){
			$excepIds = "";
			for ($i = 0; $i < sizeof($_PARAM["except"]); $i++){
				if($i == 0)
					$excepIds = "'" . base64_decode($_PARAM["except"][$i]["id"]) . "' ";
				else
					$excepIds .= ", '" . base64_decode($_PARAM["except"][$i]["id"]) . "' ";
			}
			$_sql .= "AND _employee.employee_id NOT IN ({$excepIds}) ";
		}

		// if($_PARAM['sys_del_flag']=='N'||$_PARAM['sys_del_flag']=='Y'){
		// 	$_sql .= "AND _employee.sys_del_flag = '{$_PARAM["sys_del_flag"]}' ";
		// }else if($_PARAM['sys_del_flag']=='A'){

		// }else{
		// 	$_sql .= "AND _employee.sys_del_flag = 'N' "; 
		// }

		if(is_array($_PARAM["company_lists"]) && sizeof($_PARAM["company_lists"]) > 0){
			$companyIds = "";
			for ($i = 0; $i < sizeof($_PARAM["company_lists"]); $i++){
				if($i == 0)
					$companyIds = "'" . base64_decode($_PARAM["company_lists"][$i]["id"]) . "' ";
				else
					$companyIds .= ", '" . base64_decode($_PARAM["company_lists"][$i]["id"]) . "' ";
			}
			$_sql .= "AND _employee.company_id IN ({$companyIds}) ";
		}

		if(is_array($_PARAM["branch_lists"]) && sizeof($_PARAM["branch_lists"]) > 0){
			$branchIds = "";
			for ($i = 0; $i < sizeof($_PARAM["branch_lists"]); $i++){
				if($i == 0)
					$branchIds = "'" . base64_decode($_PARAM["branch_lists"][$i]["id"]) . "' ";
				else
					$branchIds .= ", '" . base64_decode($_PARAM["branch_lists"][$i]["id"]) . "' ";
			}
			$_branchSql = " _employee.department_id IN (SELECT department_id FROM hms_api.comp_department WHERE branch_id IN ({$branchIds}) 
							AND instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}' AND sys_del_flag='N') ";
		}

		if(is_array($_PARAM["department_lists"]) && sizeof($_PARAM["department_lists"]) > 0){
			$departmentIds = "";
			for ($i = 0; $i < sizeof($_PARAM["department_lists"]); $i++){
				if($i == 0)
					$departmentIds = "'" . base64_decode($_PARAM["department_lists"][$i]["id"]) . "' ";
				else
					$departmentIds .= ", '" . base64_decode($_PARAM["department_lists"][$i]["id"]) . "' ";
			}
			$_departmentSql = "_employee.department_id IN ({$departmentIds}) ";
		}

		if(is_array($_PARAM["division_lists"]) && sizeof($_PARAM["division_lists"]) > 0){
			$divisionIds = "";
			for ($i = 0; $i < sizeof($_PARAM["division_lists"]); $i++){
				if($i == 0){
					$divisionIds = "'" . base64_decode($_PARAM["division_lists"][$i]["id"]) . "' ";
				} else {
					$divisionIds .= ", '" . base64_decode($_PARAM["division_lists"][$i]["id"]) . "' ";
				}
			}
			$_divisionSql = "_employee.division_id IN ({$divisionIds}) ";
		}

		if(is_array($_PARAM["section_lists"]) && sizeof($_PARAM["section_lists"]) > 0){
			$sectionIds = "";
			for ($i = 0; $i < sizeof($_PARAM["section_lists"]); $i++){
				if($i == 0){
					$sectionIds = "'" . base64_decode($_PARAM["section_lists"][$i]["id"]) . "' ";
				} else {
					$sectionIds .= ", '" . base64_decode($_PARAM["section_lists"][$i]["id"]) . "' ";
				}
			}
			$_sectionSql = "_employee.section_id IN ({$sectionIds}) ";
		}

		if(is_array($_PARAM["section_lists_lv01"]) && sizeof($_PARAM["section_lists_lv01"]) > 0){
			$sectionLv01Ids = "";
			for ($i = 0; $i < sizeof($_PARAM["section_lists_lv01"]); $i++){
				if($i == 0){
					$sectionLv01Ids = "'" . base64_decode($_PARAM["section_lists_lv01"][$i]["id"]) . "' ";
				} else {
					$sectionLv01Ids .= ", '" . base64_decode($_PARAM["section_lists_lv01"][$i]["id"]) . "' ";
				}
			}
			$_sectionLv01Sql = "_employee.section_lv01_id IN ({$sectionLv01Ids}) ";
		}

		if(is_array($_PARAM["section_lists_lv02"]) && sizeof($_PARAM["section_lists_lv02"]) > 0){
			$sectionLv02Ids = "";
			for ($i = 0; $i < sizeof($_PARAM["section_lists_lv02"]); $i++){
				if($i == 0){
					$sectionLv02Ids = "'" . base64_decode($_PARAM["section_lists_lv02"][$i]["id"]) . "' ";
				} else {
					$sectionLv02Ids .= ", '" . base64_decode($_PARAM["section_lists_lv02"][$i]["id"]) . "' ";
				}
			}
			$_sectionLv02Sql = "_employee.section_lv02_id IN ({$sectionLv02Ids}) ";
		}

		if(is_array($_PARAM["section_lists_lv03"]) && sizeof($_PARAM["section_lists_lv03"]) > 0){
			$sectionLv03Ids = "";
			for ($i = 0; $i < sizeof($_PARAM["section_lists_lv03"]); $i++){
				if($i == 0){
					$sectionLv03Ids = "'" . base64_decode($_PARAM["section_lists_lv03"][$i]["id"]) . "' ";
				} else {
					$sectionLv03Ids .= ", '" . base64_decode($_PARAM["section_lists_lv03"][$i]["id"]) . "' ";
				}
			}
			$_sectionLv03Sql = "_employee.section_lv03_id IN ({$sectionLv03Ids}) ";
		}

		if(is_array($_PARAM["section_lists_lv04"]) && sizeof($_PARAM["section_lists_lv04"]) > 0){
			$sectionLv04Ids = "";
			for ($i = 0; $i < sizeof($_PARAM["section_lists_lv04"]); $i++){
				if($i == 0){
					$sectionLv04Ids = "'" . base64_decode($_PARAM["section_lists_lv04"][$i]["id"]) . "' ";
				} else {
					$sectionLv04Ids .= ", '" . base64_decode($_PARAM["section_lists_lv04"][$i]["id"]) . "' ";
				}
			}
			$_sectionLv04Sql = "_employee.section_lv04_id IN ({$sectionLv04Ids}) ";
		}

		if(is_array($_PARAM["section_lists_lv05"]) && sizeof($_PARAM["section_lists_lv05"]) > 0){
			$sectionLv05Ids = "";
			for ($i = 0; $i < sizeof($_PARAM["section_lists_lv05"]); $i++){
				if($i == 0){
					$sectionLv05Ids = "'" . base64_decode($_PARAM["section_lists_lv05"][$i]["id"]) . "' ";
				} else {
					$sectionLv05Ids .= ", '" . base64_decode($_PARAM["section_lists_lv05"][$i]["id"]) . "' ";
				}
			}
			$_sectionLv05Sql = "_employee.section_lv05_id IN ({$sectionLv05Ids}) ";
		}


		if(sizeof($_PARAM["branch_lists"]) > 0 && sizeof($_PARAM["department_lists"]) > 0){
			$_sql .= "AND {$_branchSql} AND {$_departmentSql} ";
		} else if(sizeof($_PARAM["branch_lists"]) > 0 && sizeof($_PARAM["department_lists"]) == 0){
			$_sql .= "AND {$_branchSql} ";
		} else if(sizeof($_PARAM["branch_lists"]) == 0 && sizeof($_PARAM["department_lists"]) > 0){
			$_sql .= "AND {$_departmentSql}	";
		}

		if(sizeof($_PARAM["division_lists"]) > 0){
			$_sql .= "AND {$_divisionSql} ";
		}

		if(sizeof($_PARAM["section_lists"]) > 0){
			$_sql .= "AND {$_sectionSql} ";
		}

		if(sizeof($_PARAM["section_lists_lv01"]) > 0){
			$_sql .= "AND {$_sectionLv01Sql} ";
		}

		if(sizeof($_PARAM["section_lists_lv02"]) > 0){
			$_sql .= "AND {$_sectionLv02Sql} ";
		}
		if(sizeof($_PARAM["section_lists_lv03"]) > 0){
			$_sql .= "AND {$_sectionLv03Sql} ";
		}
		if(sizeof($_PARAM["section_lists_lv04"]) > 0){
			$_sql .= "AND {$_sectionLv04Sql} ";
		}
		if(sizeof($_PARAM["section_lists_lv05"]) > 0){
			$_sql .= "AND {$_sectionLv05Sql} ";
		}
		/* if(!empty($_PARAM['filter_by_individual_structure'])){
			$companyIds = "";
			if(is_array($_PARAM['filter_by_individual_structure']["company_lists"]) && sizeof($_PARAM['filter_by_individual_structure']["company_lists"]) > 0){
				for ($i = 0; $i < sizeof($_PARAM['filter_by_individual_structure']["company_lists"]); $i++) {
					if ($i == 0)
						$companyIds = "'" . base64_decode($_PARAM['filter_by_individual_structure']["company_lists"][$i]["id"]) . "' ";
					else
						$companyIds .= ", '" . base64_decode($_PARAM['filter_by_individual_structure']["company_lists"][$i]["id"]) . "' ";
				}
			}else{
				$companyIds = "('')";
			}
			
			$branchIds = "";
			if(is_array($_PARAM['filter_by_individual_structure']["branch_lists"]) && sizeof($_PARAM['filter_by_individual_structure']["branch_lists"]) > 0){
				for ($i = 0; $i < sizeof($_PARAM['filter_by_individual_structure']["branch_lists"]); $i++) {
					if ($i == 0)
						$branchIds = "'" . base64_decode($_PARAM['filter_by_individual_structure']["branch_lists"][$i]["id"]) . "' ";
					else
						$branchIds .= ", '" . base64_decode($_PARAM['filter_by_individual_structure']["branch_lists"][$i]["id"]) . "' ";
				}
			}else{
				$branchIds = "('')";
			}
			
			$departmentIds = "";
			if(is_array($_PARAM['filter_by_individual_structure']["department_lists"]) && sizeof($_PARAM['filter_by_individual_structure']["department_lists"]) > 0){
				for ($i = 0; $i < sizeof($_PARAM['filter_by_individual_structure']["department_lists"]); $i++) {
					if ($i == 0)
						$departmentIds = "'" . base64_decode($_PARAM['filter_by_individual_structure']["department_lists"][$i]["id"]) . "' ";
					else
						$departmentIds .= ", '" . base64_decode($_PARAM['filter_by_individual_structure']["department_lists"][$i]["id"]) . "' ";
				}
			}else{
				$departmentIds = "('')";
			}
			
			$_sql .= " AND (_employee.company_id IN ({$companyIds}) OR _employee.branch_id IN ({$branchIds}) OR _employee.department_id IN ({$departmentIds})) ";
		} */

		if (is_array($_PARAM["position_lists"]) && sizeof($_PARAM["position_lists"]) > 0) {
			$positionIds = "";
			for ($i = 0; $i < sizeof($_PARAM["position_lists"]); $i++){
				if($i == 0)
					$positionIds = "'" . base64_decode($_PARAM["position_lists"][$i]["id"]) . "' ";
				else
					$positionIds .= ", '" . base64_decode($_PARAM["position_lists"][$i]["id"]) . "' ";
			}
			$_sql .= "AND _employee.position_id IN ({$positionIds}) ";
		}

		if(is_array($_PARAM["employee_lists"]) && sizeof($_PARAM["employee_lists"]) > 0){
			$employeeIds = "";
			for ($i = 0; $i < sizeof($_PARAM["employee_lists"]); $i++){
				if($i == 0)
					$employeeIds = "'" . base64_decode($_PARAM["employee_lists"][$i]["id"]) . "' ";
				else
					$employeeIds .= ", '" . base64_decode($_PARAM["employee_lists"][$i]["id"]) . "' ";
			}
			$_sql .= "AND _employee.employee_id IN ({$employeeIds}) ";

			if($_PARAM['sys_del_flag'] == 'N' || $_PARAM['sys_del_flag'] == 'Y'){
				$_sql .= "AND _employee.sys_del_flag = '{$_PARAM["sys_del_flag"]}' ";
			}
		} else {
			if($_PARAM['sys_del_flag'] == 'N' || $_PARAM['sys_del_flag'] == 'Y'){
				$_sql .= "AND _employee.sys_del_flag = '{$_PARAM["sys_del_flag"]}' ";
			} else if($_PARAM['sys_del_flag'] == 'A'){

			} else {
				$_sql .= "AND _employee.sys_del_flag = 'N' ";
			}
		}

		if($_PARAM['signout_flag']){
			$_sql .= "AND _employee.signout_flag = '{$_PARAM['signout_flag']}' ";
		}
		if($_PARAM['round_xtra_config']){
			$_sql .= "AND _employee.round_xtra_config = '{$_PARAM['round_xtra_config']}' ";
		}
		if($_PARAM['round_ot_config']){
			$_sql .= "AND _employee.round_ot_config = '{$_PARAM['round_ot_config']}' ";
		}
		if($_PARAM['round_worktime_config']){
			$_sql .= "AND _employee.round_worktime_config = '{$_PARAM['round_worktime_config']}' ";
		}

		if($GLOBALS['employeeLogin']['employee_id'] != ''){
			$auth = PageAuthorizeService::getAuthorizeByUserGroup(array("SAL", "SALINEX", "SALBU", "AUDIT", "HRBU"));
			if($_PARAM['only_in_position_line'] == true || $auth === false){
				$posEmpList = $this->getListEmployeeAuthorize($GLOBALS['employeeLogin']['employee_id']);
				$arrayEOH = array();
				if($_PARAM['not_include_employee_login'] === true){
					// not add employee login
				}else{
					$arrayEOH[] = $GLOBALS['employeeLogin']['employee_id'];

				}
				for ($i = 0; $i < sizeof($posEmpList); $i++){
					$arrayEOH[] = $posEmpList[$i]['employee_id'];
				}
				$_sql .= "AND _employee.employee_id IN ('" . implode("','" , $arrayEOH) . "') ";
				// $posEmpList = $this->getListEmployeePositionLine($GLOBALS['employeeLogin']['employee_id']);
				// $arrayEOH = array();
				// for($i=0;$i<sizeof($posEmpList);$i++){
				// 	$arrayEOH[] = $posEmpList[$i]['employee_id'];
				// }
				// if(sizeof($arrayEOH)>0){
				// 	$_sql .= "AND _employee.employee_id IN ('".implode("','" , $arrayEOH)."') ";
				// }
			} else if ($auth == true) {
				// echo "Test 	2";
				// if($_REQUEST['_beta'] == "Y"){
					$tmp_supervisor = $this->filterSupervisorBeta();
					$supervisor_count = $tmp_supervisor['supervisor_count'];
					$arrayEOH = $tmp_supervisor['employee_list'];
				// }else{
				// 	$tmp_supervisor = $this->filterSupervisor();
				// 	$supervisor_count = $tmp_supervisor['supervisor_count'];
				// 	$arrayEOH = $tmp_supervisor['employee_list'];
				// }
				
				// if (sizeof($arrayEOH) > 0) {
					if($supervisor_count > 0){
						$_sql .= "AND _employee.employee_id IN ('" . implode("','" , $arrayEOH) . "') ";
					}
				// }
			}
		}
		$_sql .= "ORDER BY _company.company_code,_branch.branch_code,_department.department_code,_division.division_code,_section.section_code,_section_lv01.section_lv01_code,_section_lv02.section_lv02_code,_section_lv03.section_lv03_code,_section_lv04.section_lv04_code,_section_lv05.section_lv05_code,_employee.employee_code ";
		// $channel = $GLOBALS['instanceServerChannelService']->getInstanceServerSpecificChannels($_REQUEST['server_id'], $_REQUEST['instance_server_id'],$_REQUEST['instance_server_channel_id']);
		// if($channel['max_user_limit']>0){
		// 	$_sql .= "LIMIT ".$channel['max_user_limit']; 
		// }
		// if($_REQUEST['_debug']=='Y'){
		// echo "$_sql<br><hr>";
		// }

		if(!empty($_PARAM['sqlCondition'])){
			$_sql .= $_PARAM['sqlCondition'];
		}

		// echo "$_sql<hr>";
		// exit;

		$lists = $this->_sqllists($_sql);

		$_sql = "SELECT _cycle.*
						FROM hms_api.comp_work_cycle _cycle
						WHERE _cycle.server_id = '{$_REQUEST['server_id']}'
						AND _cycle.instance_server_id = '{$_REQUEST['instance_server_id']}' 
						AND _cycle.instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}' 
						ORDER BY _cycle.work_cycle_code ";
		// echo "$_sql<br>";
		$cycleLists = $this->_sqllists($_sql);
		$labelCycle = array();
		for ($i = 0; $i < sizeof($cycleLists); $i++){
			$labelCycle[$cycleLists[$i]['work_cycle_id']] = $cycleLists[$i];
		}

		$_sql = "SELECT user_id AS identify_user_id, user_name, first_singin_flag, employee_id   
		FROM hms_api.suso_user 
		WHERE server_id = '{$_REQUEST['server_id']}' 
		AND instance_server_id = '{$_REQUEST['instance_server_id']}'  
		AND instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}' ";

		$user_tmp = $GLOBALS['userService']->_sqllists($_sql);

		$approver_list = $this->getApproverListCompany('', false, $_PARAM['check_step']);

		for ($i = 0; $i < sizeof($lists); $i++){
			$cycleListsEmployee = json_decode($lists[$i]['work_cycle_id_json'], true);
			$cycleKey = array_keys($cycleListsEmployee);
			for ($x = 0; $x < sizeof($cycleKey); $x++){
				$lists[$i]['work_cycle_lists'][$x][$cycleKey[$x]] = $labelCycle[$cycleListsEmployee[$cycleKey[$x]]];
			}

			$holidayListsEmployee = json_decode($lists[$i]['holiday_day_json'], true);
			$holidayKey = array_keys($holidayListsEmployee);
			for ($x = 0; $x < sizeof($holidayKey); $x++){
				$lists[$i]['holiday_lists'][$x][$holidayKey[$x]] = $holidayListsEmployee[$holidayKey[$x]];
			}

			$key_user = array_search($lists['employee_id'], array_column($user_tmp, 'employee'));
			if($key_user != false){
				$lists[$i] = array_unique(array_merge($lists[$i], $user_tmp[$key_user]));
			}else{
				$lists[$i]['identify_user_id'] = null;
				$lists[$i]['user_name'] = null;
				$lists[$i]['first_singin_flag'] = null;
			}

			$approver_step = array("first","second","third","fourth","fifth");
			for($app_idx = 0; $app_idx < sizeof($approver_list); $app_idx++){
				if($approver_list[$app_idx]['employee_id'] == $lists[$i]['employee_id']){
					$lists[$i]['auth_'.$approver_step[$approver_list[$app_idx]['approver_step']-1]] = $approver_list[$app_idx]['approver_employee_id'];
					$lists[$i]['auth_'.$approver_step[$approver_list[$app_idx]['approver_step']-1].'_id'] = $approver_list[$app_idx]['approver_employee_id'];
					$lists[$i]['auth_'.$approver_step[$approver_list[$app_idx]['approver_step']-1].'_code'] = $approver_list[$app_idx]['approver_employee_code'];
					// $lists[$i]['auth_'.$approver_step[$approver_list[$app_idx]['approver_step']-1].'_name'] = $_REQUEST['language_code'] == 'TH' ? $approver_list[$app_idx]['approver_employee_name'] : $approver_list[$app_idx]['approver_employee_name_en'];
					// $lists[$i]['auth_'.$approver_step[$approver_list[$app_idx]['approver_step']-1].'_last_name'] = $_REQUEST['language_code'] == 'TH' ? $approver_list[$app_idx]['approver_employee_last_name'] : $approver_list[$app_idx]['approver_employee_last_name_en'];
					// $lists[$i]['auth_'.$approver_step[$approver_list[$app_idx]['approver_step']-1].'_nickname'] = $_REQUEST['language_code'] == 'TH' ? $approver_list[$app_idx]['approver_employee_nickname'] : $approver_list[$app_idx]['approver_employee_nickname_en'];
					
					$lists[$i]['auth_'.$approver_step[$approver_list[$app_idx]['approver_step']-1].'_name'] = $approver_list[$app_idx]['approver_employee_name'];
					$lists[$i]['auth_'.$approver_step[$approver_list[$app_idx]['approver_step']-1].'_last_name'] = $approver_list[$app_idx]['approver_employee_last_name'];
					$lists[$i]['auth_'.$approver_step[$approver_list[$app_idx]['approver_step']-1].'_nickname'] = $approver_list[$app_idx]['approver_employee_nickname'];
					$lists[$i]['auth_'.$approver_step[$approver_list[$app_idx]['approver_step']-1].'_name_en'] = $approver_list[$app_idx]['approver_employee_name_en'];
					$lists[$i]['auth_'.$approver_step[$approver_list[$app_idx]['approver_step']-1].'_last_name_en'] = $approver_list[$app_idx]['approver_employee_last_name_en'];
					$lists[$i]['auth_'.$approver_step[$approver_list[$app_idx]['approver_step']-1].'_nickname_en'] = $approver_list[$app_idx]['approver_employee_nickname_en'];
					$lists[$i]['auth_'.$approver_step[$approver_list[$app_idx]['approver_step']-1].'_photograph'] = $approver_list[$app_idx]['approver_photograph'];
					$lists[$i]['auth_'.$approver_step[$approver_list[$app_idx]['approver_step']-1].'_channel_id'] = $approver_list[$app_idx]['approver_channel_id'];
					$lists[$i]['auth_'.$approver_step[$approver_list[$app_idx]['approver_step']-1].'_instance_server_channel_code'] = $approver_list[$app_idx]['instance_server_channel_code'];
				}
			}
		}

		return $lists;
	}

		function getCountEmpEffAndOutInRangeDiff($_start_date, $_end_date, $date_diff_start_date, $date_diff_end_date, $_filter) {
		$_sql = "WITH current_people_monthly AS (
				SELECT COUNT(*) AS count
				FROM hms_api.comp_employee _comp_emp
				WHERE  _comp_emp.server_id = '{$_REQUEST['server_id']}'
					AND _comp_emp.instance_server_id = '{$_REQUEST['instance_server_id']}'
					AND _comp_emp.instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}'
					AND (
							_comp_emp.effective_dt  BETWEEN '".$_start_date."' AND '".$_end_date."'
							OR
							_comp_emp.out_dt  BETWEEN '".$_start_date."' AND '".$_end_date."'
						)" ; 
		if ($_filter['company_id']) {
			$_sql .= " AND _comp_emp.company_id  = '".$_filter['company_id']."' " ;  
		}
		if ($_filter['branch_id']) {
			$_sql .= " AND _comp_emp.branch_id  = '".$_filter['branch_id']."' " ;  
		}
		if ($_filter['department_id']) {
			$_sql .= " AND _comp_emp.department_id  = '".$_filter['department_id']."' " ;  
		}
		if ($_filter['division_id']) {
			$_sql .= " AND _comp_emp.division_id  = '".$_filter['division_id']."' " ;  
		}
		$_sql .= "),
		" ;
		$_sql .= "target_people_month AS (
				SELECT COUNT(*) AS count
					FROM hms_api.comp_employee _comp_emp
					WHERE  _comp_emp.server_id = '{$_REQUEST['server_id']}'
				AND _comp_emp.instance_server_id = '{$_REQUEST['instance_server_id']}'
				AND _comp_emp.instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}'
				AND (
						_comp_emp.effective_dt  BETWEEN '".$date_diff_start_date."' AND '".$date_diff_end_date."'
						OR
						_comp_emp.out_dt  BETWEEN '".$date_diff_start_date."' AND '".$date_diff_end_date."'
					)" ; 
		if ($_filter['company_id']) {
			$_sql .= " AND _comp_emp.company_id  = '".$_filter['company_id']."' " ;  
		}
		if ($_filter['branch_id']) {
			$_sql .= " AND _comp_emp.branch_id  = '".$_filter['branch_id']."' " ;  
		}
		if ($_filter['department_id']) {
			$_sql .= " AND _comp_emp.department_id  = '".$_filter['department_id']."' " ;  
		}
		if ($_filter['division_id']) {
			$_sql .= " AND _comp_emp.division_id  = '".$_filter['division_id']."' " ;  
		}
		$_sql .= ")
		" ;

		$_sql .= "SELECT
				_curr_pp_monthly.count AS curr_count,
				_target_pp_month.count AS target_count,
				(_curr_pp_monthly.count - _target_pp_month.count) AS differance
			FROM
				current_people_monthly _curr_pp_monthly,
				target_people_month _target_pp_month ;" ;

		$lists = $this->_sqlget($_sql);

		return $lists;	
	}

	function getCountEmpEffAndOutRange($_start_date, $_end_date, $_filter) {
		$_sql = "SELECT COUNT(*) AS count
				FROM hms_api.comp_employee _comp_emp
				WHERE  _comp_emp.server_id = '{$_REQUEST['server_id']}'
					AND _comp_emp.instance_server_id = '{$_REQUEST['instance_server_id']}'
					AND _comp_emp.instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}'
					AND (
							_comp_emp.effective_dt  BETWEEN '".$_start_date."' AND '".$_end_date."'
							OR
							_comp_emp.out_dt  BETWEEN '".$_start_date."' AND '".$_end_date."'
						)" ; 
		if ($_filter['company_id']) {
			$_sql .= " AND _comp_emp.company_id  = '".$_filter['company_id']."' " ;  
		}
		if ($_filter['branch_id']) {
			$_sql .= " AND _comp_emp.branch_id  = '".$_filter['branch_id']."' " ;  
		}
		if ($_filter['department_id']) {
			$_sql .= " AND _comp_emp.department_id  = '".$_filter['department_id']."' " ;  
		}
		if ($_filter['division_id']) {
			$_sql .= " AND _comp_emp.division_id  = '".$_filter['division_id']."' " ;  
		}
		$lists = $this->_sqlget($_sql);

		return $lists;	
	}

	function getCountEmpEffAndOutInRangeDiffCCS($_start_date, $_end_date, $date_diff_start_date, $date_diff_end_date, $_filter) {
		$_sql = "WITH current_people_monthly AS (
				SELECT COUNT(*) AS count
				FROM hms_api.comp_employee _comp_emp
				WHERE  _comp_emp.server_id = '{$_REQUEST['server_id']}'
					AND _comp_emp.instance_server_id = '{$_REQUEST['instance_server_id']}'
					AND (
							_comp_emp.effective_dt  BETWEEN '".$_start_date."' AND '".$_end_date."'
							OR
							_comp_emp.out_dt  BETWEEN '".$_start_date."' AND '".$_end_date."'
						)" ; 
		if ($_filter['company_id']) {
			$_sql .= " AND _comp_emp.company_id  = '".$_filter['company_id']."' " ;  
		}
		if ($_filter['branch_id']) {
			$_sql .= " AND _comp_emp.branch_id  = '".$_filter['branch_id']."' " ;  
		}
		if ($_filter['department_id']) {
			$_sql .= " AND _comp_emp.department_id  = '".$_filter['department_id']."' " ;  
		}
		if ($_filter['division_id']) {
			$_sql .= " AND _comp_emp.division_id  = '".$_filter['division_id']."' " ;  
		}
		$_sql .= "),
		" ;
		$_sql .= "target_people_month AS (
				SELECT COUNT(*) AS count
					FROM hms_api.comp_employee _comp_emp
					WHERE  _comp_emp.server_id = '{$_REQUEST['server_id']}'
				AND _comp_emp.instance_server_id = '{$_REQUEST['instance_server_id']}'
				AND (
						_comp_emp.effective_dt  BETWEEN '".$date_diff_start_date."' AND '".$date_diff_end_date."'
						OR
						_comp_emp.out_dt  BETWEEN '".$date_diff_start_date."' AND '".$date_diff_end_date."'
					)" ; 
		if ($_filter['company_id']) {
			$_sql .= " AND _comp_emp.company_id  = '".$_filter['company_id']."' " ;  
		}
		if ($_filter['branch_id']) {
			$_sql .= " AND _comp_emp.branch_id  = '".$_filter['branch_id']."' " ;  
		}
		if ($_filter['department_id']) {
			$_sql .= " AND _comp_emp.department_id  = '".$_filter['department_id']."' " ;  
		}
		if ($_filter['division_id']) {
			$_sql .= " AND _comp_emp.division_id  = '".$_filter['division_id']."' " ;  
		}
		$_sql .= ")
		" ;

		$_sql .= "SELECT
				_curr_pp_monthly.count AS curr_count,
				_target_pp_month.count AS target_count,
				(_curr_pp_monthly.count - _target_pp_month.count) AS differance
			FROM
				current_people_monthly _curr_pp_monthly,
				target_people_month _target_pp_month ;" ;

		$lists = $this->_sqlget($_sql);

		return $lists;	
	}

	function getAllempSignout($_PARAM)
	{
        $_sql = "SELECT DISTINCT _emp.employee_id
        FROM hms_api.comp_employee _emp
        WHERE _emp.server_id = '{$_REQUEST['server_id']}' 
        AND _emp.instance_server_id = '{$_REQUEST['instance_server_id']}' 
        AND _emp.instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}' 
        AND _emp.sys_del_flag = 'Y'";		
		if ($_PARAM['year']) {
			$_sql .= " AND _emp.signout_dt BETWEEN '{$_PARAM['year']}-01-01' AND '{$_PARAM['year']}-12-31'";
		}
		// echo $_sql;
		$lists = $this->_sqllists($_sql);

		return $lists;
	}
	
	function getListEmployeeWithFilterModifiedCount($_PARAM)
	{
		$_sql = "SELECT COUNT(*) AS count
						FROM (select * from hms_api.comp_employee  where instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}') _employee 
						INNER JOIN (select company_id, company_code, company_name, company_name_en FROM hms_api.comp_company  where instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}') _company ON (_company.company_id=_employee.company_id) 
						INNER JOIN (select branch_id, branch_code, branch_name, branch_name_en FROM hms_api.comp_branch  where instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}') _branch ON (_branch.branch_id=_employee.branch_id) 
						INNER JOIN (select department_id, department_code, department_name, department_name_en 
						                       FROM hms_api.comp_department  where instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}') _department ON (_department.department_id=_employee.department_id)  
						INNER JOIN (select position_id, position_code, position_name, position_name_en 
												FROM hms_api.comp_position where instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}') _position ON (_position.position_id=_employee.position_id) 
						WHERE _employee.server_id = '{$_REQUEST['server_id']}'
						AND _employee.instance_server_id = '{$_REQUEST['instance_server_id']}'
						AND _employee.instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}' ";

		if($_PARAM["keyword"] != ''){
			$_sql .= "AND (
								_employee.employee_nickname LIKE '%{$_PARAM["keyword"]}%' OR
								_employee.employee_name LIKE '%{$_PARAM["keyword"]}%' OR
								_employee.employee_last_name LIKE '%{$_PARAM["keyword"]}%' OR
								_employee.employee_nickname_en LIKE '%{$_PARAM["keyword"]}%' OR
								_employee.employee_name_en LIKE '%{$_PARAM["keyword"]}%' OR
								_employee.employee_last_name_en LIKE '%{$_PARAM["keyword"]}%' OR
								_employee.fing_code LIKE '%{$_PARAM["keyword"]}%' OR
								_employee.employee_code LIKE '%{$_PARAM["keyword"]}%') ";
		}

		if(sizeof($_PARAM["hashtags"]) == 1){
			$_sql .= " AND _employee.hashtag_desc LIKE '%{$_PARAM["hashtags"][0]}%' ";
		} else if(sizeof($_PARAM["hashtags"]) > 1){
			$_sql .= " AND ( ";
			for ($i = 0; $i < sizeof($_PARAM["hashtags"]); $i++){
				if($i == 0)
					$_sql .= " _employee.hashtag_desc LIKE '%{$_PARAM["hashtags"][$i]}%' ";
				else
					$_sql .= " OR _employee.hashtag_desc LIKE '%{$_PARAM["hashtags"][$i]}%' ";
			}
			$_sql .= " ) ";
		}

		if(is_array($_PARAM["except"]) && sizeof($_PARAM["except"]) > 0){
			$excepIds = "";
			for ($i = 0; $i < sizeof($_PARAM["except"]); $i++){
				if($i == 0)
					$excepIds = "'" . base64_decode($_PARAM["except"][$i]["id"]) . "' ";
				else
					$excepIds .= ", '" . base64_decode($_PARAM["except"][$i]["id"]) . "' ";
			}
			$_sql .= "AND _employee.employee_id NOT IN ({$excepIds}) ";
		}

		// if($_PARAM['sys_del_flag']=='N'||$_PARAM['sys_del_flag']=='Y'){
		// 	$_sql .= "AND _employee.sys_del_flag = '{$_PARAM["sys_del_flag"]}' ";
		// }else if($_PARAM['sys_del_flag']=='A'){

		// }else{
		// 	$_sql .= "AND _employee.sys_del_flag = 'N' "; 
		// }

		if(is_array($_PARAM["company_lists"]) && sizeof($_PARAM["company_lists"]) > 0){
			$companyIds = "";
			for ($i = 0; $i < sizeof($_PARAM["company_lists"]); $i++){
				if($i == 0)
					$companyIds = "'" . base64_decode($_PARAM["company_lists"][$i]["id"]) . "' ";
				else
					$companyIds .= ", '" . base64_decode($_PARAM["company_lists"][$i]["id"]) . "' ";
			}
			$_sql .= "AND _employee.company_id IN ({$companyIds}) ";
		}

		if(is_array($_PARAM["branch_lists"]) && sizeof($_PARAM["branch_lists"]) > 0){
			$branchIds = "";
			for ($i = 0; $i < sizeof($_PARAM["branch_lists"]); $i++){
				if($i == 0)
					$branchIds = "'" . base64_decode($_PARAM["branch_lists"][$i]["id"]) . "' ";
				else
					$branchIds .= ", '" . base64_decode($_PARAM["branch_lists"][$i]["id"]) . "' ";
			}
			$_branchSql = " _employee.department_id IN (SELECT department_id FROM hms_api.comp_department WHERE branch_id IN ({$branchIds}) 
							AND instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}' AND sys_del_flag='N') ";
		}

		if(is_array($_PARAM["department_lists"]) && sizeof($_PARAM["department_lists"]) > 0){
			$departmentIds = "";
			for ($i = 0; $i < sizeof($_PARAM["department_lists"]); $i++){
				if($i == 0)
					$departmentIds = "'" . base64_decode($_PARAM["department_lists"][$i]["id"]) . "' ";
				else
					$departmentIds .= ", '" . base64_decode($_PARAM["department_lists"][$i]["id"]) . "' ";
			}
			$_departmentSql = "_employee.department_id IN ({$departmentIds}) ";
		}

		if(is_array($_PARAM["division_lists"]) && sizeof($_PARAM["division_lists"]) > 0){
			$divisionIds = "";
			for ($i = 0; $i < sizeof($_PARAM["division_lists"]); $i++){
				if($i == 0){
					$divisionIds = "'" . base64_decode($_PARAM["division_lists"][$i]["id"]) . "' ";
				} else {
					$divisionIds .= ", '" . base64_decode($_PARAM["division_lists"][$i]["id"]) . "' ";
				}
			}
			$_divisionSql = "_employee.division_id IN ({$divisionIds}) ";
		}

		if(is_array($_PARAM["section_lists"]) && sizeof($_PARAM["section_lists"]) > 0){
			$sectionIds = "";
			for ($i = 0; $i < sizeof($_PARAM["section_lists"]); $i++){
				if($i == 0){
					$sectionIds = "'" . base64_decode($_PARAM["section_lists"][$i]["id"]) . "' ";
				} else {
					$sectionIds .= ", '" . base64_decode($_PARAM["section_lists"][$i]["id"]) . "' ";
				}
			}
			$_sectionSql = "_employee.section_id IN ({$sectionIds}) ";
		}
		
		if(is_array($_REQUEST["section_lists_lv01"]) && sizeof($_REQUEST["section_lists_lv01"]) > 0){
			$sectionLv01Ids = "";
			for ($i = 0; $i < sizeof($_REQUEST["section_lists_lv01"]); $i++){
				if($i == 0)
					$sectionLv01Ids = "'" . base64_decode($_REQUEST["section_lists_lv01"][$i]["id"]) . "' ";
				 else 
					$sectionLv01Ids .= ", '" . base64_decode($_REQUEST["section_lists_lv01"][$i]["id"]) . "' ";
				
			}
			$_sectionLv01Sql = "_employee.section_lv01_id IN ({$sectionLv01Ids}) ";
		}
	
		if(is_array($_REQUEST["section_lists_lv02"]) && sizeof($_REQUEST["section_lists_lv02"]) > 0){
			$sectionLv02Ids = "";
			for ($i = 0; $i < sizeof($_REQUEST["section_lists_lv02"]); $i++){
				if($i == 0)
					$sectionLv02Ids = "'" . base64_decode($_REQUEST["section_lists_lv02"][$i]["id"]) . "' ";
				 else 
					$sectionLv02Ids .= ", '" . base64_decode($_REQUEST["section_lists_lv02"][$i]["id"]) . "' ";
				
			}
			$_sectionLv02Sql = "_employee.section_lv02_id IN ({$sectionLv02Ids}) ";
		}
	
		if(is_array($_REQUEST["section_lists_lv03"]) && sizeof($_REQUEST["section_lists_lv03"]) > 0){
			$sectionLv03Ids = "";
			for ($i = 0; $i < sizeof($_REQUEST["section_lists_lv03"]); $i++){
				if($i == 0)
					$sectionLv03Ids = "'" . base64_decode($_REQUEST["section_lists_lv03"][$i]["id"]) . "' ";
				 else 
					$sectionLv03Ids .= ", '" . base64_decode($_REQUEST["section_lists_lv03"][$i]["id"]) . "' ";
				
			}
			$_sectionLv03Sql = "_employee.section_lv03_id IN ({$sectionLv03Ids}) ";
		}
	
		if(is_array($_REQUEST["section_lists_lv04"]) && sizeof($_REQUEST["section_lists_lv04"]) > 0){
			$sectionLv04Ids = "";
			for ($i = 0; $i < sizeof($_REQUEST["section_lists_lv04"]); $i++){
				if($i == 0)
					$sectionLv04Ids = "'" . base64_decode($_REQUEST["section_lists_lv04"][$i]["id"]) . "' ";
				else 
					$sectionLv04Ids .= ", '" . base64_decode($_REQUEST["section_lists_lv04"][$i]["id"]) . "' ";
				
			}
			$_sectionLv04Sql = "_employee.section_lv04_id IN ({$sectionLv04Ids}) ";
		}
	
		if(is_array($_REQUEST["section_lists_lv05"]) && sizeof($_REQUEST["section_lists_lv05"]) > 0){
			$sectionLv05Ids = "";
			for ($i = 0; $i < sizeof($_REQUEST["section_lists_lv05"]); $i++){
				if($i == 0)
					$sectionLv05Ids = "'" . base64_decode($_REQUEST["section_lists_lv05"][$i]["id"]) . "' ";
				 else 
					$sectionLv05Ids .= ", '" . base64_decode($_REQUEST["section_lists_lv05"][$i]["id"]) . "' ";
				
			}
			$_sectionLv05Sql = "_employee.section_lv05_id IN ({$sectionLv05Ids}) ";
		}

		$operator = "";
		$_sql_org = "";
		
    	if(sizeof($_REQUEST["branch_lists"])>0 && sizeof($_REQUEST["department_lists"])>0 ){
    	        $_sql_org .= " {$operator} ({$_branchSql} OR {$_departmentSql})	";
    	        $operator = "OR";
    	}else if(sizeof($_REQUEST["branch_lists"]) >0 && sizeof($_REQUEST["department_lists"])==0 ){
    	        $_sql_org .= " {$operator} {$_branchSql} ";
    	        $operator = "OR";
    	}else if(sizeof($_REQUEST["branch_lists"])==0 && sizeof($_REQUEST["department_lists"])>0){
    	        $_sql_org .= " {$operator} {$_departmentSql}	";
    	        $operator = "OR";
    	}

    	if(sizeof($_REQUEST["division_lists"])>0){
    	    $_sql_org .= " {$operator} {$_divisionSql} ";
    	    $operator = "OR";
    	}

    	if(sizeof($_REQUEST["section_lists"])>0){
    	    $_sql_org .= " {$operator} {$_sectionSql} ";
    	    $operator = "OR";
    	}

    	if(sizeof($_REQUEST["section_lists_lv01"]) > 0){
			 $_sql_org .= " {$operator} {$_sectionLv01Sql} ";
    	     $operator = "OR";
		}

		if(sizeof($_REQUEST["section_lists_lv02"]) > 0){
			 $_sql_org .= " {$operator} {$_sectionLv02Sql} ";
    	     $operator = "OR";
		}

		if(sizeof($_REQUEST["section_lists_lv03"]) > 0){
			 $_sql_org .= " {$operator} {$_sectionLv03Sql} ";
    	     $operator = "OR";
		}

		if(sizeof($_REQUEST["section_lists_lv04"]) > 0){
			 $_sql_org .= " {$operator} {$_sectionLv04Sql} ";
    	     $operator = "OR";
		}

		if(sizeof($_REQUEST["section_lists_lv05"]) > 0){
			 $_sql_org .= " {$operator} {$_sectionLv05Sql} ";
    	     $operator = "OR";
		}

    	if($_sql_org != '') {
    	    $_sql .= " AND ({$_sql_org}) ";
    	}

		if (is_array($_PARAM["position_lists"]) && sizeof($_PARAM["position_lists"]) > 0) {
			$positionIds = "";
			for ($i = 0; $i < sizeof($_PARAM["position_lists"]); $i++){
				if($i == 0)
					$positionIds = "'" . base64_decode($_PARAM["position_lists"][$i]["id"]) . "' ";
				else
					$positionIds .= ", '" . base64_decode($_PARAM["position_lists"][$i]["id"]) . "' ";
			}
			$_sql .= "AND _employee.position_id IN ({$positionIds}) ";
		}

		if(is_array($_PARAM["employee_lists"]) && sizeof($_PARAM["employee_lists"]) > 0){
			$employeeIds = "";
			for ($i = 0; $i < sizeof($_PARAM["employee_lists"]); $i++){
				if($i == 0)
					$employeeIds = "'" . base64_decode($_PARAM["employee_lists"][$i]["id"]) . "' ";
				else
					$employeeIds .= ", '" . base64_decode($_PARAM["employee_lists"][$i]["id"]) . "' ";
			}
			$_sql .= "AND _employee.employee_id IN ({$employeeIds}) ";

			if($_PARAM['sys_del_flag'] == 'N' || $_PARAM['sys_del_flag'] == 'Y'){
				$_sql .= "AND _employee.sys_del_flag = '{$_PARAM["sys_del_flag"]}' ";
			}
		} else {
			if($_PARAM['sys_del_flag'] == 'N' || $_PARAM['sys_del_flag'] == 'Y'){
				$_sql .= "AND _employee.sys_del_flag = '{$_PARAM["sys_del_flag"]}' ";
			} else if($_PARAM['sys_del_flag'] == 'A'){

			} else {
				$_sql .= "AND _employee.sys_del_flag = 'N' ";
			}
		}

		if($_PARAM['signout_flag']){
			$_sql .= "AND _employee.signout_flag = '{$_PARAM['signout_flag']}' ";
		}
		if($_PARAM['round_xtra_config']){
			$_sql .= "AND _employee.round_xtra_config = '{$_PARAM['round_xtra_config']}' ";
		}
		if($_PARAM['round_ot_config']){
			$_sql .= "AND _employee.round_ot_config = '{$_PARAM['round_ot_config']}' ";
		}
		if($_PARAM['round_worktime_config']){
			$_sql .= "AND _employee.round_worktime_config = '{$_PARAM['round_worktime_config']}' ";
		}

		if($GLOBALS['employeeLogin']['employee_id'] != ''){
			$auth = PageAuthorizeService::getAuthorizeByUserGroup(array("SAL", "SALINEX", "SALBU", "AUDIT", "HRBU"));
			if($_PARAM['only_in_position_line'] == true || $auth === false){
				$posEmpList = $this->getListEmployeeAuthorize($GLOBALS['employeeLogin']['employee_id']);
				$arrayEOH = array();
				if($_PARAM['not_include_employee_login'] === true){

				}else{
					$arrayEOH[] = $GLOBALS['employeeLogin']['employee_id'];

				}
				for ($i = 0; $i < sizeof($posEmpList); $i++){
					$arrayEOH[] = $posEmpList[$i]['employee_id'];
				}
				$_sql .= "AND _employee.employee_id IN ('" . implode("','" , $arrayEOH) . "') ";

			} else if ($auth == true) {

					$tmp_supervisor = $this->filterSupervisorBeta();
					$supervisor_count = $tmp_supervisor['supervisor_count'];
					$arrayEOH = $tmp_supervisor['employee_list'];

					if($supervisor_count > 0){
						$_sql .= "AND _employee.employee_id IN ('" . implode("','" , $arrayEOH) . "') ";
					}
			}
		}
		$_sql .= "ORDER BY _company.company_code,_branch.branch_code,_department.department_code,_employee.employee_code ";
		if(!empty($_PARAM['_PAGE']) && !empty($_PARAM['_NUMBER_PER_PAGE']) && $_PARAM['_PAGE'] > 0 && $_PARAM['_NUMBER_PER_PAGE'] > 0){
			$_LIMIT = $_PARAM['_NUMBER_PER_PAGE'];
			$_OFFSET = ($_PARAM['_PAGE'] - 1) * $_PARAM['_NUMBER_PER_PAGE'];
			$_sql .= "LIMIT {$_LIMIT} OFFSET {$_OFFSET}";
		}


		if(!empty($_PARAM['sqlCondition'])){
			$_sql .= $_PARAM['sqlCondition'];
		}

		// echo "$_sql<hr>";
		// exit;

		$lists = $this->_sqlget($_sql);
		if ($_PARAM['check_count_of_employee'] === true && sizeof($lists) > $_PARAM['count_of_employee_limit']) {
			throw new Exception('employee-overlimit');
		}
		return $lists['count'];
	}

	function getListEmployeeWithFilterModifiedCount2($_PARAM)
	{
		// print_r($GLOBALS['employeeLogin']);
		$_sql = "SELECT _employee.employee_id,
						_employee.employee_code,
						_employee.fing_code,
						_employee.employee_type_code,
						_employee.employee_type_group_id,
						_employee.employee_nickname,
						_employee.employee_nickname_en,
						_employee.employee_name,
						_employee.employee_last_name,
						_employee.employee_name_en,
						_employee.employee_last_name_en,
						_employee.employee_title_lv,
						_employee.employee_gender,
						_employee.employee_foreigner,
						_employee.employee_status,
						_employee.position_id,
						_employee.company_id,
						_employee.branch_id,
						_employee.department_id,
						_employee.division_id,
						_employee.section_id,
						_employee.section_lv01_id,
						_employee.section_lv02_id,
                        _employee.section_lv03_id,
                        _employee.section_lv04_id,
                        _employee.section_lv05_id,
						_employee.mobilephone,
						_employee.emailaddress,
						_employee.salary,
						_employee.salary_law,
						_employee.salary_per_week_type_lv,
						_employee.salary_per_week,
						_employee.payment_method,
						_employee.social_insurance_method_lv,
						_employee.social_insurance_method_constant,
						_employee.social_insurance_deduct_lv,
						_employee.tax_method_lv,
						_employee.tax_method_constant,
						_employee.tax_method_rate,
						_employee.tax_deduct_lv,
						_employee.days_per_month,
						_employee.hours_per_day,
						_employee.birth_dt,
						_employee.id_no,
						_employee.sso_no,
						_employee.opt_code,
						_employee.person_id,
						_employee.line_user_id,
						_employee.player_id,
						_employee.apple_id,
						_employee.line_token_id,
						_employee.line_token_todolist_id,
						IFNULL(_employee.photograph , 'images/userPlaceHolder.png') AS photograph,
						_employee.bank_id,
						_employee.coa_account_group_id,
						_employee.company_payment_account_id,
						_employee.bank_branch_code,
						_employee.bank_account_code,
						_employee.work_cycle_id_json,
						_employee.work_cycle_format,
						_employee.holiday_day_json,
						_employee.holiday_format,
						_employee.clock_inout,
						_employee.trial_range,
						_employee.effective_dt,
						_employee.begin_dt,
						_employee.signout_flag,
						_employee.signout_request_dt,
						_employee.signout_dt,
						_employee.out_dt,
						_employee.sso_out_dt,
						_employee.signout_type_flag,
						_employee.signout_remark,
						_employee.round_month_config,
						_employee.round_xtra_config,
						_employee.round_ot_config,
						_employee.round_worktime_config,
						_employee.holiday_apply_config,
						_employee.import_log_id,
						_employee.personal_config,
						_employee.address,
						_employee.address1,
						_employee.address2,
						_employee.address3,
						_employee.address4,
						_employee.address5,
						_employee.address6,
						_employee.address7,
						_employee.address8,
						_employee.address9,
						_employee.country_id,
						_employee.country_code,
						_employee.state_code,
						_employee.district_code,
						_employee.subdistrict_code,
						_employee.post_code,
						_employee.current_address,
						_employee.current_address1,
						_employee.current_address2,
						_employee.current_address3,
						_employee.current_address4,
						_employee.current_address5,
						_employee.current_address6,
						_employee.current_address7,
						_employee.current_address8,
						_employee.current_address9,
						_employee.current_country_code,
						_employee.current_state_code,
						_employee.current_district_code,
						_employee.current_subdistrict_code,
						_employee.current_post_code,
						_employee.hashtag_desc,
						_employee.order_no,
						_employee.server_id,
						_employee.instance_server_id,
						_employee.instance_server_channel_id,
						_employee.sys_del_flag,
						_employee.reference_code_1,					
						_employee.reference_code_2,					
						_employee.reference_code_3,					
						_employee.reference_code_4,					
						_employee.reference_code_5,
						 _employee.publish_flag  
						FROM hms_api.comp_employee_lookup _employee
						WHERE _employee.server_id = '{$_REQUEST['server_id']}'
						AND _employee.instance_server_id = '{$_REQUEST['instance_server_id']}'
						AND _employee.instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}' ";

		if($_PARAM["keyword"] != ''){
			$_sql .= "AND (
								_employee.employee_nickname LIKE '%{$_PARAM["keyword"]}%' OR
								_employee.employee_name LIKE '%{$_PARAM["keyword"]}%' OR
								_employee.employee_last_name LIKE '%{$_PARAM["keyword"]}%' OR
								_employee.employee_nickname_en LIKE '%{$_PARAM["keyword"]}%' OR
								_employee.employee_name_en LIKE '%{$_PARAM["keyword"]}%' OR
								_employee.employee_last_name_en LIKE '%{$_PARAM["keyword"]}%' OR
								_employee.fing_code LIKE '%{$_PARAM["keyword"]}%' OR
								_employee.employee_code LIKE '%{$_PARAM["keyword"]}%') ";
		}

		if(sizeof($_PARAM["hashtags"]) == 1){
			$_sql .= " AND _employee.hashtag_desc LIKE '%{$_PARAM["hashtags"][0]}%' ";
		} else if(sizeof($_PARAM["hashtags"]) > 1){
			$_sql .= " AND ( ";
			for ($i = 0; $i < sizeof($_PARAM["hashtags"]); $i++){
				if($i == 0)
					$_sql .= " _employee.hashtag_desc LIKE '%{$_PARAM["hashtags"][$i]}%' ";
				else
					$_sql .= " OR _employee.hashtag_desc LIKE '%{$_PARAM["hashtags"][$i]}%' ";
			}
			$_sql .= " ) ";
		}

		if(is_array($_PARAM["except"]) && sizeof($_PARAM["except"]) > 0){
			$excepIds = "";
			for ($i = 0; $i < sizeof($_PARAM["except"]); $i++){
				if($i == 0)
					$excepIds = "'" . base64_decode($_PARAM["except"][$i]["id"]) . "' ";
				else
					$excepIds .= ", '" . base64_decode($_PARAM["except"][$i]["id"]) . "' ";
			}
			$_sql .= "AND _employee.employee_id NOT IN ({$excepIds}) ";
		}

		if(is_array($_PARAM["company_lists"]) && sizeof($_PARAM["company_lists"]) > 0){
			$companyIds = "";
			for ($i = 0; $i < sizeof($_PARAM["company_lists"]); $i++){
				if($i == 0)
					$companyIds = "'" . base64_decode($_PARAM["company_lists"][$i]["id"]) . "' ";
				else
					$companyIds .= ", '" . base64_decode($_PARAM["company_lists"][$i]["id"]) . "' ";
			}
			$_sql .= "AND _employee.company_id IN ({$companyIds}) ";
		}

		if(is_array($_PARAM["branch_lists"]) && sizeof($_PARAM["branch_lists"]) > 0){
			$branchIds = "";
			for ($i = 0; $i < sizeof($_PARAM["branch_lists"]); $i++){
				if($i == 0)
					$branchIds = "'" . base64_decode($_PARAM["branch_lists"][$i]["id"]) . "' ";
				else
					$branchIds .= ", '" . base64_decode($_PARAM["branch_lists"][$i]["id"]) . "' ";
			}
			$_branchSql = " _employee.department_id IN (SELECT department_id FROM hms_api.comp_department WHERE branch_id IN ({$branchIds}) 
							AND instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}' AND sys_del_flag='N') ";
		}

		if(is_array($_PARAM["department_lists"]) && sizeof($_PARAM["department_lists"]) > 0){
			$departmentIds = "";
			for ($i = 0; $i < sizeof($_PARAM["department_lists"]); $i++){
				if($i == 0)
					$departmentIds = "'" . base64_decode($_PARAM["department_lists"][$i]["id"]) . "' ";
				else
					$departmentIds .= ", '" . base64_decode($_PARAM["department_lists"][$i]["id"]) . "' ";
			}
			$_departmentSql = "_employee.department_id IN ({$departmentIds}) ";
		}

		if(is_array($_PARAM["division_lists"]) && sizeof($_PARAM["division_lists"]) > 0){
			$divisionIds = "";
			for ($i = 0; $i < sizeof($_PARAM["division_lists"]); $i++){
				if($i == 0){
					$divisionIds = "'" . base64_decode($_PARAM["division_lists"][$i]["id"]) . "' ";
				} else {
					$divisionIds .= ", '" . base64_decode($_PARAM["division_lists"][$i]["id"]) . "' ";
				}
			}
			$_divisionSql = "_employee.division_id IN ({$divisionIds}) ";
		}

		if(is_array($_PARAM["section_lists"]) && sizeof($_PARAM["section_lists"]) > 0){
			$sectionIds = "";
			for ($i = 0; $i < sizeof($_PARAM["section_lists"]); $i++){
				if($i == 0){
					$sectionIds = "'" . base64_decode($_PARAM["section_lists"][$i]["id"]) . "' ";
				} else {
					$sectionIds .= ", '" . base64_decode($_PARAM["section_lists"][$i]["id"]) . "' ";
				}
			}
			$_sectionSql = "_employee.section_id IN ({$sectionIds}) ";
		}

		if(is_array($_PARAM["section_lists_lv01"]) && sizeof($_PARAM["section_lists_lv01"]) > 0){
			$sectionLv01Ids = "";
			for ($i = 0; $i < sizeof($_PARAM["section_lists_lv01"]); $i++){
				if($i == 0){
					$sectionLv01Ids = "'" . base64_decode($_PARAM["section_lists_lv01"][$i]["id"]) . "' ";
				} else {
					$sectionLv01Ids .= ", '" . base64_decode($_PARAM["section_lists_lv01"][$i]["id"]) . "' ";
				}
			}
			$_sectionLv01Sql = "_employee.section_lv01_id IN ({$sectionLv01Ids}) ";
		}

		if(is_array($_PARAM["section_lists_lv02"]) && sizeof($_PARAM["section_lists_lv02"]) > 0){
			$sectionLv02Ids = "";
			for ($i = 0; $i < sizeof($_PARAM["section_lists_lv02"]); $i++){
				if($i == 0){
					$sectionLv02Ids = "'" . base64_decode($_PARAM["section_lists_lv02"][$i]["id"]) . "' ";
				} else {
					$sectionLv02Ids .= ", '" . base64_decode($_PARAM["section_lists_lv02"][$i]["id"]) . "' ";
				}
			}
			$_sectionLv02Sql = "_employee.section_lv02_id IN ({$sectionLv02Ids}) ";
		}

		if(is_array($_PARAM["section_lists_lv03"]) && sizeof($_PARAM["section_lists_lv03"]) > 0){
			$sectionLv03Ids = "";
			for ($i = 0; $i < sizeof($_PARAM["section_lists_lv03"]); $i++){
				if($i == 0){
					$sectionLv03Ids = "'" . base64_decode($_PARAM["section_lists_lv03"][$i]["id"]) . "' ";
				} else {
					$sectionLv03Ids .= ", '" . base64_decode($_PARAM["section_lists_lv03"][$i]["id"]) . "' ";
				}
			}
			$_sectionLv03Sql = "_employee.section_lv03_id IN ({$sectionLv03Ids}) ";
		}

		if(is_array($_PARAM["section_lists_lv04"]) && sizeof($_PARAM["section_lists_lv04"]) > 0){
			$sectionLv04Ids = "";
			for ($i = 0; $i < sizeof($_PARAM["section_lists_lv04"]); $i++){
				if($i == 0){
					$sectionLv04Ids = "'" . base64_decode($_PARAM["section_lists_lv04"][$i]["id"]) . "' ";
				} else {
					$sectionLv04Ids .= ", '" . base64_decode($_PARAM["section_lists_lv04"][$i]["id"]) . "' ";
				}
			}
			$_sectionLv04Sql = "_employee.section_lv04_id IN ({$sectionLv04Ids}) ";
		}

		if(is_array($_PARAM["section_lists_lv05"]) && sizeof($_PARAM["section_lists_lv05"]) > 0){
			$sectionLv05Ids = "";
			for ($i = 0; $i < sizeof($_PARAM["section_lists_lv05"]); $i++){
				if($i == 0){
					$sectionLv05Ids = "'" . base64_decode($_PARAM["section_lists_lv05"][$i]["id"]) . "' ";
				} else {
					$sectionLv05Ids .= ", '" . base64_decode($_PARAM["section_lists_lv05"][$i]["id"]) . "' ";
				}
			}
			$_sectionLv05Sql = "_employee.section_lv05_id IN ({$sectionLv05Ids}) ";
		}


		$operator = "";
		$_sql_org = "";
		
	    if(sizeof($_REQUEST["branch_lists"])>0 && sizeof($_REQUEST["department_lists"])>0 ){
	            $_sql_org .= " {$operator} ({$_branchSql} OR {$_departmentSql})	";
	            $operator = "OR";
	    }else if(sizeof($_REQUEST["branch_lists"]) >0 && sizeof($_REQUEST["department_lists"])==0 ){
	            $_sql_org .= " {$operator} {$_branchSql} ";
	            $operator = "OR";
	    }else if(sizeof($_REQUEST["branch_lists"])==0 && sizeof($_REQUEST["department_lists"])>0){
	            $_sql_org .= " {$operator} {$_departmentSql}	";
	            $operator = "OR";
	    }

	    if(sizeof($_REQUEST["division_lists"])>0){
	        $_sql_org .= " {$operator} {$_divisionSql} ";
	        $operator = "OR";
	    }

	    if(sizeof($_REQUEST["section_lists"])>0){
	        $_sql_org .= " {$operator} {$_sectionSql} ";
	        $operator = "OR";
	    }

	    if(sizeof($_REQUEST["section_lists_lv01"]) > 0){
			 $_sql_org .= " {$operator} {$_sectionLv01Sql} ";
	         $operator = "OR";
		}

		if(sizeof($_REQUEST["section_lists_lv02"]) > 0){
			 $_sql_org .= " {$operator} {$_sectionLv02Sql} ";
	         $operator = "OR";
		}

		if(sizeof($_REQUEST["section_lists_lv03"]) > 0){
			 $_sql_org .= " {$operator} {$_sectionLv03Sql} ";
	         $operator = "OR";
		}

		if(sizeof($_REQUEST["section_lists_lv04"]) > 0){
			 $_sql_org .= " {$operator} {$_sectionLv04Sql} ";
	         $operator = "OR";
		}

		if(sizeof($_REQUEST["section_lists_lv05"]) > 0){
			 $_sql_org .= " {$operator} {$_sectionLv05Sql} ";
	         $operator = "OR";
		}

	    if($_sql_org != '') {
	        $_sql .= " AND ({$_sql_org}) ";
	    }

		if (is_array($_PARAM["position_lists"]) && sizeof($_PARAM["position_lists"]) > 0) {
			$positionIds = "";
			for ($i = 0; $i < sizeof($_PARAM["position_lists"]); $i++){
				if($i == 0)
					$positionIds = "'" . base64_decode($_PARAM["position_lists"][$i]["id"]) . "' ";
				else
					$positionIds .= ", '" . base64_decode($_PARAM["position_lists"][$i]["id"]) . "' ";
			}
			$_sql .= "AND _employee.position_id IN ({$positionIds}) ";
		}

		if(is_array($_PARAM["employee_lists"]) && sizeof($_PARAM["employee_lists"]) > 0){
			$employeeIds = "";
			for ($i = 0; $i < sizeof($_PARAM["employee_lists"]); $i++){
				if($i == 0)
					$employeeIds = "'" . base64_decode($_PARAM["employee_lists"][$i]["id"]) . "' ";
				else
					$employeeIds .= ", '" . base64_decode($_PARAM["employee_lists"][$i]["id"]) . "' ";
			}
			$_sql .= "AND _employee.employee_id IN ({$employeeIds}) ";

			if($_PARAM['sys_del_flag'] == 'N' || $_PARAM['sys_del_flag'] == 'Y'){
				$_sql .= "AND _employee.sys_del_flag = '{$_PARAM["sys_del_flag"]}' ";
			}
		} else {
			if($_PARAM['sys_del_flag'] == 'N' || $_PARAM['sys_del_flag'] == 'Y'){
				$_sql .= "AND _employee.sys_del_flag = '{$_PARAM["sys_del_flag"]}' ";
			} else if($_PARAM['sys_del_flag'] == 'A'){

			} else {
				$_sql .= "AND _employee.sys_del_flag = 'N' ";
			}
		}

		if($_PARAM['signout_flag']){
			$_sql .= "AND _employee.signout_flag = '{$_PARAM['signout_flag']}' ";
		}
		if($_PARAM['round_xtra_config']){
			$_sql .= "AND _employee.round_xtra_config = '{$_PARAM['round_xtra_config']}' ";
		}
		if($_PARAM['round_ot_config']){
			$_sql .= "AND _employee.round_ot_config = '{$_PARAM['round_ot_config']}' ";
		}
		if($_PARAM['round_worktime_config']){
			$_sql .= "AND _employee.round_worktime_config = '{$_PARAM['round_worktime_config']}' ";
		}

		if($GLOBALS['employeeLogin']['employee_id'] != ''){
			$auth = PageAuthorizeService::getAuthorizeByUserGroup(array("SAL", "SALINEX", "SALBU", "AUDIT", "HRBU"));
			if(!empty($_PARAM['sub_menu_approve_employee']) && $_PARAM['sub_menu_approve_employee']) {
				$auth = true;
			}

			if($_PARAM['only_in_position_line'] == true || $auth === false){
				$posEmpList = $this->getListEmployeeAuthorize($GLOBALS['employeeLogin']['employee_id']);
				$arrayEOH = array();
				if($_PARAM['not_include_employee_login'] === true){
					// not add employee login
				}else{
					$arrayEOH[] = $GLOBALS['employeeLogin']['employee_id'];

				}
				for ($i = 0; $i < sizeof($posEmpList); $i++){
					$arrayEOH[] = $posEmpList[$i]['employee_id'];
				}
				$_sql .= "AND _employee.employee_id IN ('" . implode("','" , $arrayEOH) . "') ";
				// $posEmpList = $this->getListEmployeePositionLine($GLOBALS['employeeLogin']['employee_id']);
				// $arrayEOH = array();
				// for($i=0;$i<sizeof($posEmpList);$i++){
				// 	$arrayEOH[] = $posEmpList[$i]['employee_id'];
				// }
				// if(sizeof($arrayEOH)>0){
				// 	$_sql .= "AND _employee.employee_id IN ('".implode("','" , $arrayEOH)."') ";
				// }
			} else if ($auth == true) {
					$tmp_supervisor = $this->filterSupervisorBeta();
					$supervisor_count = $tmp_supervisor['supervisor_count'];
					$arrayEOH = $tmp_supervisor['employee_list'];
					if($supervisor_count > 0){
						$_sql .= "AND _employee.employee_id IN ('" . implode("','" , $arrayEOH) . "') ";
					}
			}
		}
		$_sql .= "ORDER BY _employee.employee_code ";
		if(!empty($_PARAM['_PAGE']) && !empty($_PARAM['_NUMBER_PER_PAGE']) && $_PARAM['_PAGE'] > 0 && $_PARAM['_NUMBER_PER_PAGE'] > 0){
			$_LIMIT = $_PARAM['_NUMBER_PER_PAGE'];
			$_OFFSET = ($_PARAM['_PAGE'] - 1) * $_PARAM['_NUMBER_PER_PAGE'];
			$_sql .= "LIMIT {$_LIMIT} OFFSET {$_OFFSET}";
		}

		$lists = $this->_sqllists($_sql);

		if ($_PARAM['check_count_of_employee'] === true && sizeof($lists) > $_PARAM['count_of_employee_limit']) {
			throw new Exception('employee-overlimit');
		}

		return $lists;
	}

	function getListEmployeeWithFilterModidiedCompanyCode($_PARAM) {
		// print_r($GLOBALS['employeeLogin']);
		$_sql = "SELECT _employee.employee_id,
						_employee.employee_code,
						_employee.fing_code,
						_employee.employee_type_code,
						_employee.employee_type_group_id,
						_employee.employee_nickname,
						_employee.employee_nickname_en,
						_employee.employee_name,
						_employee.employee_last_name,
						_employee.employee_name_en,
						_employee.employee_last_name_en,
						_employee.employee_title_lv,
						_employee.employee_gender,
						_employee.employee_foreigner,
						_employee.employee_status,
						_employee.position_id,
						_employee.company_id,
						_employee.branch_id,
						_employee.department_id,
						_employee.division_id,
						_employee.section_id,
						_employee.section_lv01_id,
						_employee.section_lv02_id,
						_employee.section_lv03_id,
						_employee.section_lv04_id,
						_employee.section_lv05_id,
						_employee.mobilephone,
						_employee.emailaddress,
						_employee.salary,
						_employee.salary_law,
						_employee.salary_per_week_type_lv,
						_employee.salary_per_week,
						_employee.payment_method,
						_employee.social_insurance_method_lv,
						_employee.social_insurance_method_constant,
						_employee.social_insurance_deduct_lv,
						_employee.tax_method_lv,
						_employee.tax_method_constant,
						_employee.tax_method_rate,
						_employee.tax_deduct_lv,
						_employee.days_per_month,
						_employee.hours_per_day,
						_employee.birth_dt,
						_employee.id_no,
						_employee.sso_no,
						_employee.opt_code,
						_employee.person_id,
						_employee.line_user_id,
						_employee.player_id,
						_employee.apple_id,
						_employee.line_token_id,
						_employee.line_token_todolist_id,
						IFNULL(_employee.photograph , 'images/userPlaceHolder.png') AS photograph,
						_employee.bank_id,
						_employee.coa_account_group_id,
						_employee.company_payment_account_id,
						_employee.bank_branch_code,
						_employee.bank_account_code,
						_employee.work_cycle_id_json,
						_employee.work_cycle_format,
						_employee.holiday_day_json,
						_employee.holiday_format,
						_employee.clock_inout,
						_employee.trial_range,
						_employee.effective_dt,
						_employee.begin_dt,
						_employee.signout_flag,
						_employee.signout_request_dt,
						_employee.signout_dt,
						_employee.out_dt,
						_employee.sso_out_dt,
						_employee.signout_type_flag,
						_employee.signout_remark,
						_employee.round_month_config,
						_employee.round_xtra_config,
						_employee.round_ot_config,
						_employee.round_worktime_config,
						_employee.holiday_apply_config,
						_employee.import_log_id,
						_employee.personal_config,
						_employee.address,
						_employee.address1,
						_employee.address2,
						_employee.address3,
						_employee.address4,
						_employee.address5,
						_employee.address6,
						_employee.address7,
						_employee.address8,
						_employee.address9,
						_employee.country_id,
						_employee.country_code,
						_employee.state_code,
						_employee.district_code,
						_employee.subdistrict_code,
						_employee.post_code,
						_employee.current_address,
						_employee.current_address1,
						_employee.current_address2,
						_employee.current_address3,
						_employee.current_address4,
						_employee.current_address5,
						_employee.current_address6,
						_employee.current_address7,
						_employee.current_address8,
						_employee.current_address9,
						_employee.current_country_code,
						_employee.current_state_code,
						_employee.current_district_code,
						_employee.current_subdistrict_code,
						_employee.current_post_code,
						_employee.hashtag_desc,
						_employee.order_no,
						_employee.server_id,
						_employee.instance_server_id,
						_employee.instance_server_channel_id,
						_employee.sys_del_flag,
						_employee.reference_code_1,					
						_employee.reference_code_2,					
						_employee.reference_code_3,					
						_employee.reference_code_4,					
						_employee.reference_code_5,
						_company.company_code,
						_company.company_name,
						_company.company_name_en,
						_branch.branch_code,
						_branch.branch_name,
						_branch.branch_name_en,
						_department.department_code,
						_department.department_name,
						_department.department_name_en,
						_division.division_code,
						_division.division_name,
						_division.division_name_en,
						_section.section_code,
						_section.section_name,
						_section.section_name_en,
						_section_lv01.section_lv01_code,
						_section_lv01.section_lv01_name,
						_section_lv01.section_lv01_name_en,
						_section_lv02.section_lv02_code,
						_section_lv02.section_lv02_name,
						_section_lv02.section_lv02_name_en,
						_section_lv03.section_lv03_code,
						_section_lv03.section_lv03_name,
						_section_lv03.section_lv03_name_en,
						_section_lv04.section_lv04_code,
						_section_lv04.section_lv04_name,
						_section_lv04.section_lv04_name_en,
						_section_lv05.section_lv05_code,
						_section_lv05.section_lv05_name,
						_section_lv05.section_lv05_name_en,
						_position.position_code,
						_position.position_name,
						_position.position_name_en,
						_taxperson.person_tax_transac_id AS person_tax_id,
						 _employee.publish_flag  ,
						 _typegroup.tax_type ,
						 _typegroup.employee_type_group_id,
						 _typegroup.employee_type_group_code,
						 _typegroup.employee_type_group_name,
						 _typegroup.employee_type_group_name_en 
						FROM (select * from hms_api.comp_employee  where instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}') _employee 
						INNER JOIN (select company_id, company_code, company_name, company_name_en FROM hms_api.comp_company  where instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}') _company ON (_company.company_id=_employee.company_id) 
						INNER JOIN (select branch_id, branch_code, branch_name, branch_name_en FROM hms_api.comp_branch  where instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}') _branch ON (_branch.branch_id=_employee.branch_id) 
						INNER JOIN (select department_id, department_code, department_name, department_name_en 
						                       FROM hms_api.comp_department  where instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}') _department ON (_department.department_id=_employee.department_id) 
						LEFT JOIN (select division_id, division_code, division_name, division_name_en 
						                       FROM hms_api.comp_division where instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}') _division ON (_division.division_id=_employee.division_id) 
						LEFT JOIN (select section_id, section_code, section_name, section_name_en 
						                       FROM hms_api.comp_section where instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}') _section ON (_section.section_id=_employee.section_id) 
						LEFT JOIN (select section_lv01_id, section_lv01_code, section_lv01_name, section_lv01_name_en 
						                       FROM hms_api.comp_section_lv01 where instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}') _section_lv01 ON (_section_lv01.section_lv01_id=_employee.section_lv01_id) 
						LEFT JOIN (select section_lv02_id, section_lv02_code, section_lv02_name, section_lv02_name_en 
						                       FROM hms_api.comp_section_lv02 where instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}') _section_lv02 ON (_section_lv02.section_lv02_id=_employee.section_lv02_id) 
						LEFT JOIN (select section_lv03_id, section_lv03_code, section_lv03_name, section_lv03_name_en 
						                       FROM hms_api.comp_section_lv03 where instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}') _section_lv03 ON (_section_lv03.section_lv03_id=_employee.section_lv03_id) 
						LEFT JOIN (select section_lv04_id, section_lv04_code, section_lv04_name, section_lv04_name_en 
						                       FROM hms_api.comp_section_lv04 where instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}') _section_lv04 ON (_section_lv04.section_lv04_id=_employee.section_lv04_id) 
						LEFT JOIN (select section_lv05_id, section_lv05_code, section_lv05_name, section_lv05_name_en 
						                       FROM hms_api.comp_section_lv05 where instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}') _section_lv05 ON (_section_lv05.section_lv05_id=_employee.section_lv05_id) 
						INNER JOIN (select position_id, position_code, position_name, position_name_en 
												FROM hms_api.comp_position where instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}') _position ON (_position.position_id=_employee.position_id) 
						LEFT JOIN (
							SELECT person_tax_transac_id, tax_year_code, tax_month_code, tax_category_id, employee_id 
							FROM {$GLOBALS['instanceServer']['instance_server_dbn']}.payroll_person_tax_transac 
							WHERE instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}' 
							AND tax_year_code = '" . date('Y') . "' 
							AND tax_month_code = '12' 
							AND tax_category_id = '60'
						) _taxperson ON (_taxperson.employee_id = _employee.employee_id) 
						LEFT JOIN hms_api.comp_employee_type_group _typegroup ON (_typegroup.employee_type_group_id = _employee.employee_type_group_id) 
						WHERE _employee.server_id = '{$_REQUEST['server_id']}'
						AND _employee.instance_server_id = '{$_REQUEST['instance_server_id']}'
						AND _employee.instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}' ";

		if($_PARAM["keyword"] != ''){
			$_sql .= "AND (
				_employee.employee_nickname LIKE '%{$_PARAM["keyword"]}%' OR
				_employee.employee_name LIKE '%{$_PARAM["keyword"]}%' OR
				_employee.employee_last_name LIKE '%{$_PARAM["keyword"]}%' OR
				_employee.employee_nickname_en LIKE '%{$_PARAM["keyword"]}%' OR
				_employee.employee_name_en LIKE '%{$_PARAM["keyword"]}%' OR
				_employee.employee_last_name_en LIKE '%{$_PARAM["keyword"]}%' OR
				_employee.fing_code LIKE '%{$_PARAM["keyword"]}%' OR
				_employee.employee_code LIKE '%{$_PARAM["keyword"]}%') ";
		}

		if(sizeof($_PARAM["hashtags"]) == 1){
			$_sql .= " AND _employee.hashtag_desc LIKE '%{$_PARAM["hashtags"][0]}%' ";
		} else if(sizeof($_PARAM["hashtags"]) > 1){
			$_sql .= " AND ( ";
			for ($i = 0; $i < sizeof($_PARAM["hashtags"]); $i++){
				if($i == 0)
					$_sql .= " _employee.hashtag_desc LIKE '%{$_PARAM["hashtags"][$i]}%' ";
				else
					$_sql .= " OR _employee.hashtag_desc LIKE '%{$_PARAM["hashtags"][$i]}%' ";
			}
			$_sql .= " ) ";
		}

		if(is_array($_PARAM["except"]) && sizeof($_PARAM["except"]) > 0){
			$excepIds = "";
			for ($i = 0; $i < sizeof($_PARAM["except"]); $i++){
				if($i == 0)
					$excepIds = "'" . base64_decode($_PARAM["except"][$i]["id"]) . "' ";
				else
					$excepIds .= ", '" . base64_decode($_PARAM["except"][$i]["id"]) . "' ";
			}
			$_sql .= "AND _employee.employee_id NOT IN ({$excepIds}) ";
		}

		if(is_array($_PARAM["company_lists"]) && sizeof($_PARAM["company_lists"]) > 0){
			$companyIds = "";
			for ($i = 0; $i < sizeof($_PARAM["company_lists"]); $i++){
				if($i == 0)
					$companyIds = "'" . base64_decode($_PARAM["company_lists"][$i]["id"]) . "' ";
				else
					$companyIds .= ", '" . base64_decode($_PARAM["company_lists"][$i]["id"]) . "' ";
			}
			$_sql .= "AND _employee.company_id IN ({$companyIds}) ";
		}

		if(is_array($_PARAM["branch_lists"]) && sizeof($_PARAM["branch_lists"]) > 0){
			$branchIds = "";
			for ($i = 0; $i < sizeof($_PARAM["branch_lists"]); $i++){
				if($i == 0)
					$branchIds = "'" . base64_decode($_PARAM["branch_lists"][$i]["id"]) . "' ";
				else
					$branchIds .= ", '" . base64_decode($_PARAM["branch_lists"][$i]["id"]) . "' ";
			}
			$_branchSql = " _employee.department_id IN (SELECT department_id FROM hms_api.comp_department WHERE branch_id IN ({$branchIds}) 
							AND instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}' AND sys_del_flag='N') ";
		}

		if(is_array($_PARAM["department_lists"]) && sizeof($_PARAM["department_lists"]) > 0){
			$departmentIds = "";
			for ($i = 0; $i < sizeof($_PARAM["department_lists"]); $i++){
				if($i == 0)
					$departmentIds = "'" . base64_decode($_PARAM["department_lists"][$i]["id"]) . "' ";
				else
					$departmentIds .= ", '" . base64_decode($_PARAM["department_lists"][$i]["id"]) . "' ";
			}
			$_departmentSql = "_employee.department_id IN ({$departmentIds}) ";
		}

		if(is_array($_PARAM["division_lists"]) && sizeof($_PARAM["division_lists"]) > 0){
			$divisionIds = "";
			for ($i = 0; $i < sizeof($_PARAM["division_lists"]); $i++){
				if($i == 0){
					$divisionIds = "'" . base64_decode($_PARAM["division_lists"][$i]["id"]) . "' ";
				} else {
					$divisionIds .= ", '" . base64_decode($_PARAM["division_lists"][$i]["id"]) . "' ";
				}
			}
			$_divisionSql = "_employee.division_id IN ({$divisionIds}) ";
		}

		if(is_array($_PARAM["section_lists"]) && sizeof($_PARAM["section_lists"]) > 0){
			$sectionIds = "";
			for ($i = 0; $i < sizeof($_PARAM["section_lists"]); $i++){
				if($i == 0){
					$sectionIds = "'" . base64_decode($_PARAM["section_lists"][$i]["id"]) . "' ";
				} else {
					$sectionIds .= ", '" . base64_decode($_PARAM["section_lists"][$i]["id"]) . "' ";
				}
			}
			$_sectionSql = "_employee.section_id IN ({$sectionIds}) ";
		}

		if(is_array($_PARAM["section_lists_lv01"]) && sizeof($_PARAM["section_lists_lv01"]) > 0){
			$sectionLv01Ids = "";
			for ($i = 0; $i < sizeof($_PARAM["section_lists_lv01"]); $i++){
				if($i == 0){
					$sectionLv01Ids = "'" . base64_decode($_PARAM["section_lists_lv01"][$i]["id"]) . "' ";
				} else {
					$sectionLv01Ids .= ", '" . base64_decode($_PARAM["section_lists_lv01"][$i]["id"]) . "' ";
				}
			}
			$_sectionLv01Sql = "_employee.section_lv01_id IN ({$sectionLv01Ids}) ";
		}

		if(is_array($_PARAM["section_lists_lv02"]) && sizeof($_PARAM["section_lists_lv02"]) > 0){
			$sectionLv02Ids = "";
			for ($i = 0; $i < sizeof($_PARAM["section_lists_lv02"]); $i++){
				if($i == 0){
					$sectionLv02Ids = "'" . base64_decode($_PARAM["section_lists_lv02"][$i]["id"]) . "' ";
				} else {
					$sectionLv02Ids .= ", '" . base64_decode($_PARAM["section_lists_lv02"][$i]["id"]) . "' ";
				}
			}
			$_sectionLv02Sql = "_employee.section_lv02_id IN ({$sectionLv02Ids}) ";
		}

		if(is_array($_PARAM["section_lists_lv03"]) && sizeof($_PARAM["section_lists_lv03"]) > 0){
			$sectionLv03Ids = "";
			for ($i = 0; $i < sizeof($_PARAM["section_lists_lv03"]); $i++){
				if($i == 0){
					$sectionLv03Ids = "'" . base64_decode($_PARAM["section_lists_lv03"][$i]["id"]) . "' ";
				} else {
					$sectionLv03Ids .= ", '" . base64_decode($_PARAM["section_lists_lv03"][$i]["id"]) . "' ";
				}
			}
			$_sectionLv03Sql = "_employee.section_lv03_id IN ({$sectionLv03Ids}) ";
		}

		if(is_array($_PARAM["section_lists_lv04"]) && sizeof($_PARAM["section_lists_lv04"]) > 0){
			$sectionLv04Ids = "";
			for ($i = 0; $i < sizeof($_PARAM["section_lists_lv04"]); $i++){
				if($i == 0){
					$sectionLv04Ids = "'" . base64_decode($_PARAM["section_lists_lv04"][$i]["id"]) . "' ";
				} else {
					$sectionLv04Ids .= ", '" . base64_decode($_PARAM["section_lists_lv04"][$i]["id"]) . "' ";
				}
			}
			$_sectionLv04Sql = "_employee.section_lv04_id IN ({$sectionLv04Ids}) ";
		}

		if(is_array($_PARAM["section_lists_lv05"]) && sizeof($_PARAM["section_lists_lv05"]) > 0){
			$sectionLv05Ids = "";
			for ($i = 0; $i < sizeof($_PARAM["section_lists_lv05"]); $i++){
				if($i == 0){
					$sectionLv05Ids = "'" . base64_decode($_PARAM["section_lists_lv05"][$i]["id"]) . "' ";
				} else {
					$sectionLv05Ids .= ", '" . base64_decode($_PARAM["section_lists_lv05"][$i]["id"]) . "' ";
				}
			}
			$_sectionLv05Sql = "_employee.section_lv05_id IN ({$sectionLv05Ids}) ";
		}

		$operator = "";
		$_sql_org = "";
		
	    if(sizeof($_REQUEST["branch_lists"])>0 && sizeof($_REQUEST["department_lists"])>0 ){
	            $_sql_org .= " {$operator} ({$_branchSql} OR {$_departmentSql})	";
	            $operator = "OR";
	    }else if(sizeof($_REQUEST["branch_lists"]) >0 && sizeof($_REQUEST["department_lists"])==0 ){
	            $_sql_org .= " {$operator} {$_branchSql} ";
	            $operator = "OR";
	    }else if(sizeof($_REQUEST["branch_lists"])==0 && sizeof($_REQUEST["department_lists"])>0){
	            $_sql_org .= " {$operator} {$_departmentSql}	";
	            $operator = "OR";
	    }

	    if(sizeof($_REQUEST["division_lists"])>0){
	        $_sql_org .= " {$operator} {$_divisionSql} ";
	        $operator = "OR";
	    }

	    if(sizeof($_REQUEST["section_lists"])>0){
	        $_sql_org .= " {$operator} {$_sectionSql} ";
	        $operator = "OR";
	    }

	    if(sizeof($_REQUEST["section_lists_lv01"]) > 0){
			 $_sql_org .= " {$operator} {$_sectionLv01Sql} ";
	         $operator = "OR";
		}

		if(sizeof($_REQUEST["section_lists_lv02"]) > 0){
			 $_sql_org .= " {$operator} {$_sectionLv02Sql} ";
	         $operator = "OR";
		}

		if(sizeof($_REQUEST["section_lists_lv03"]) > 0){
			 $_sql_org .= " {$operator} {$_sectionLv03Sql} ";
	         $operator = "OR";
		}

		if(sizeof($_REQUEST["section_lists_lv04"]) > 0){
			 $_sql_org .= " {$operator} {$_sectionLv04Sql} ";
	         $operator = "OR";
		}

		if(sizeof($_REQUEST["section_lists_lv05"]) > 0){
			 $_sql_org .= " {$operator} {$_sectionLv05Sql} ";
	         $operator = "OR";
		}

	    if($_sql_org != '') {
	        $_sql .= " AND ({$_sql_org}) ";
	    }

		if (is_array($_PARAM["position_lists"]) && sizeof($_PARAM["position_lists"]) > 0) {
			$positionIds = "";
			for ($i = 0; $i < sizeof($_PARAM["position_lists"]); $i++){
				if($i == 0)
					$positionIds = "'" . base64_decode($_PARAM["position_lists"][$i]["id"]) . "' ";
				else
					$positionIds .= ", '" . base64_decode($_PARAM["position_lists"][$i]["id"]) . "' ";
			}
			$_sql .= "AND _employee.position_id IN ({$positionIds}) ";
		}

		if(is_array($_PARAM["employee_lists"]) && sizeof($_PARAM["employee_lists"]) > 0){
			$employeeIds = "";
			for ($i = 0; $i < sizeof($_PARAM["employee_lists"]); $i++){
				if($i == 0)
					$employeeIds = "'" . base64_decode($_PARAM["employee_lists"][$i]["id"]) . "' ";
				else
					$employeeIds .= ", '" . base64_decode($_PARAM["employee_lists"][$i]["id"]) . "' ";
			}
			$_sql .= "AND _employee.employee_id IN ({$employeeIds}) ";

			if($_PARAM['sys_del_flag'] == 'N' || $_PARAM['sys_del_flag'] == 'Y'){
				$_sql .= "AND _employee.sys_del_flag = '{$_PARAM["sys_del_flag"]}' ";
			}
		} else {
			if($_PARAM['sys_del_flag'] == 'N' || $_PARAM['sys_del_flag'] == 'Y'){
				$_sql .= "AND _employee.sys_del_flag = '{$_PARAM["sys_del_flag"]}' ";
			} else if($_PARAM['sys_del_flag'] == 'A'){

			} else {
				$_sql .= "AND _employee.sys_del_flag = 'N' ";
			}
		}

		if($_PARAM['signout_flag']){
			$_sql .= "AND _employee.signout_flag = '{$_PARAM['signout_flag']}' ";
		}
		if($_PARAM['round_xtra_config']){
			$_sql .= "AND _employee.round_xtra_config = '{$_PARAM['round_xtra_config']}' ";
		}
		if($_PARAM['round_ot_config']){
			$_sql .= "AND _employee.round_ot_config = '{$_PARAM['round_ot_config']}' ";
		}
		if($_PARAM['round_worktime_config']){
			$_sql .= "AND _employee.round_worktime_config = '{$_PARAM['round_worktime_config']}' ";
		}

		if($GLOBALS['employeeLogin']['employee_id'] != ''){
			$auth = PageAuthorizeService::getAuthorizeByUserGroup(array("SAL", "SALINEX", "SALBU", "AUDIT", "HRBU"));
			if($_PARAM['only_in_position_line'] == true || $auth === false){
				$posEmpList = $this->getListEmployeeAuthorize($GLOBALS['employeeLogin']['employee_id']);
				$arrayEOH = array();
				if($_PARAM['not_include_employee_login'] === true){
					// not add employee login
				}else{
					$arrayEOH[] = $GLOBALS['employeeLogin']['employee_id'];

				}
				for ($i = 0; $i < sizeof($posEmpList); $i++){
					$arrayEOH[] = $posEmpList[$i]['employee_id'];
				}
				$_sql .= "AND _employee.employee_id IN ('" . implode("','" , $arrayEOH) . "') ";

			} else if ($auth == true) {

				$tmp_supervisor = $this->filterSupervisorBeta();
				$supervisor_count = $tmp_supervisor['supervisor_count'];
				$arrayEOH = $tmp_supervisor['employee_list'];

				if($supervisor_count > 0){
					$_sql .= "AND _employee.employee_id IN ('" . implode("','" , $arrayEOH) . "') ";
				}
			}
		}
		$_sql .= "ORDER BY _company.company_code,_branch.branch_code,_department.department_code,_division.division_code,_section.section_code,_section_lv01.section_lv01_code,_section_lv02.section_lv02_code,_section_lv03.section_lv03_code,_section_lv04.section_lv04_code,_section_lv05.section_lv05_code,_employee.employee_code ";


		if(!empty($_PARAM['sqlCondition'])){
			$_sql .= $_PARAM['sqlCondition'];
		}

		// echo "$_sql<hr>";
		// exit;

		$lists = $this->_sqllists($_sql);

		$_sql = "SELECT _cycle.*
						FROM hms_api.comp_work_cycle _cycle
						WHERE _cycle.server_id = '{$_REQUEST['server_id']}'
						AND _cycle.instance_server_id = '{$_REQUEST['instance_server_id']}' 
						AND _cycle.instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}' 
						ORDER BY _cycle.work_cycle_code ";
		// echo "$_sql<br>";
		$cycleLists = $this->_sqllists($_sql);
		$labelCycle = array();
		for ($i = 0; $i < sizeof($cycleLists); $i++){
			$labelCycle[$cycleLists[$i]['work_cycle_id']] = $cycleLists[$i];
		}

		$_sql = "SELECT user_id AS identify_user_id, user_name, first_singin_flag, employee_id   
		FROM hms_api.suso_user 
		WHERE server_id = '{$_REQUEST['server_id']}' 
		AND instance_server_id = '{$_REQUEST['instance_server_id']}'  
		AND instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}' ";

		$user_tmp = $GLOBALS['userService']->_sqllists($_sql);

		$approver_list = $this->getApproverListCompany('', false, $_PARAM['check_step']);

		for ($i = 0; $i < sizeof($lists); $i++){
			$cycleListsEmployee = json_decode($lists[$i]['work_cycle_id_json'], true);
			$cycleKey = array_keys($cycleListsEmployee);
			for ($x = 0; $x < sizeof($cycleKey); $x++){
				$lists[$i]['work_cycle_lists'][$x][$cycleKey[$x]] = $labelCycle[$cycleListsEmployee[$cycleKey[$x]]];
			}

			$holidayListsEmployee = json_decode($lists[$i]['holiday_day_json'], true);
			$holidayKey = array_keys($holidayListsEmployee);
			for ($x = 0; $x < sizeof($holidayKey); $x++){
				$lists[$i]['holiday_lists'][$x][$holidayKey[$x]] = $holidayListsEmployee[$holidayKey[$x]];
			}

			$key_user = array_search($lists['employee_id'], array_column($user_tmp, 'employee'));
			if($key_user != false){
				$lists[$i] = array_unique(array_merge($lists[$i], $user_tmp[$key_user]));
			}else{
				$lists[$i]['identify_user_id'] = null;
				$lists[$i]['user_name'] = null;
				$lists[$i]['first_singin_flag'] = null;
			}

			$approver_step = array("first","second","third","fourth","fifth");
			for($app_idx = 0; $app_idx < sizeof($approver_list); $app_idx++){
				if($approver_list[$app_idx]['employee_id'] == $lists[$i]['employee_id']){
					$lists[$i]['auth_'.$approver_step[$approver_list[$app_idx]['approver_step']-1]] = $approver_list[$app_idx]['approver_employee_id'];
					$lists[$i]['auth_'.$approver_step[$approver_list[$app_idx]['approver_step']-1].'_id'] = $approver_list[$app_idx]['approver_employee_id'];
					$lists[$i]['auth_'.$approver_step[$approver_list[$app_idx]['approver_step']-1].'_code'] = $approver_list[$app_idx]['approver_employee_code'];

					$lists[$i]['auth_'.$approver_step[$approver_list[$app_idx]['approver_step']-1].'_name'] = $approver_list[$app_idx]['approver_employee_name'];
					$lists[$i]['auth_'.$approver_step[$approver_list[$app_idx]['approver_step']-1].'_last_name'] = $approver_list[$app_idx]['approver_employee_last_name'];
					$lists[$i]['auth_'.$approver_step[$approver_list[$app_idx]['approver_step']-1].'_nickname'] = $approver_list[$app_idx]['approver_employee_nickname'];
					$lists[$i]['auth_'.$approver_step[$approver_list[$app_idx]['approver_step']-1].'_name_en'] = $approver_list[$app_idx]['approver_employee_name_en'];
					$lists[$i]['auth_'.$approver_step[$approver_list[$app_idx]['approver_step']-1].'_last_name_en'] = $approver_list[$app_idx]['approver_employee_last_name_en'];
					$lists[$i]['auth_'.$approver_step[$approver_list[$app_idx]['approver_step']-1].'_nickname_en'] = $approver_list[$app_idx]['approver_employee_nickname_en'];
					$lists[$i]['auth_'.$approver_step[$approver_list[$app_idx]['approver_step']-1].'_photograph'] = $approver_list[$app_idx]['approver_photograph'];
					$lists[$i]['auth_'.$approver_step[$approver_list[$app_idx]['approver_step']-1].'_channel_id'] = $approver_list[$app_idx]['approver_channel_id'];
					$lists[$i]['auth_'.$approver_step[$approver_list[$app_idx]['approver_step']-1].'_instance_server_channel_code'] = $approver_list[$app_idx]['instance_server_channel_code'];
				}
			}
		}

		return $lists;
	}

	// อัพเดทสถานะตารางเวลาการทำงานจากวันที่ลาออกหรือวันที่เริ่มงาน (ยังไม่ได้เริ่มงาน/ลาออก)
	/**
	 * The function `updateWorkNotAvailableWithFilter` updates the `work_not_available` field in two
	 * different database tables based on specified parameters and conditions.
	 * 
	 * @param _PARAM The function `updateWorkNotAvailableWithFilter` is designed to update the
	 * `work_not_available` field in two different tables based on certain conditions and filters. Here's
	 * a breakdown of the parameters used in the function:
	 */
	function updateWorkNotAvailableWithFilter($_PARAM) {
		 $_sql=" UPDATE {$GLOBALS['instanceServer']['instance_server_dbn']}.payroll_time_attendance_group_transac _group 
		 		INNER JOIN {$GLOBALS['instanceServer']['instance_server_dbn']}.payroll_master_salary_report _report ON ( _report.master_salary_report_id = _group.master_salary_report_id AND _report.read_only_flag = 'N')
				SET _group.work_not_available = {$_PARAM['status']}
				WHERE _group.employee_id = '{$_REQUEST['employee_id']}' 
				AND _group.server_id = '{$_REQUEST['server_id']}' 
				AND _group.instance_server_id = '{$_REQUEST['instance_server_id']}' 
				AND _group.instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}' ";

				if ($_PARAM['master_salary_report_id']) {
					$_sql .= " AND _group.master_salary_report_id = '{$_PARAM['master_salary_report_id']}' ";	
				}

				// ถ้ามีวันลาออก
				if ($_PARAM['signout_dt']) {
					$_sql .= " AND _group.work_date >= '{$_PARAM['signout_dt']}' ";
				}

				// ถ้ามีการแก้ไขวันที่เริ่มงาน
				if ($_PARAM['tmp_effective_dt'] && $_PARAM['effective_dt']) {
					$_sql .= " AND _group.work_date BETWEEN '" . $_PARAM['tmp_effective_dt'] . "' AND '" . $_PARAM['effective_dt'] . "' ";
				}

				// echo json_encode($_PARAM); exit;
				// echo "$_sql<BR>"; exit;
				$this->Execute_Query($_sql);

		$_sql="	UPDATE {$GLOBALS['instanceServer']['instance_server_dbn']}.payroll_time_attendance_group_transac_merge _group 
				INNER JOIN {$GLOBALS['instanceServer']['instance_server_dbn']}.payroll_master_salary_report _report ON ( _report.master_salary_report_id = _group.master_salary_report_id AND _report.read_only_flag = 'N')
				SET _group.work_not_available = {$_PARAM['status']}
				WHERE _group.employee_id ='{$_REQUEST['employee_id']}' 
				AND _group.server_id = '{$_REQUEST['server_id']}' 
				AND _group.instance_server_id = '{$_REQUEST['instance_server_id']}' 
				AND _group.instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}' "; 

				if ($_PARAM['master_salary_report_id']) {
					$_sql .= " AND _group.master_salary_report_id = '{$_PARAM['master_salary_report_id']}' ";	
				}

				// ถ้ามีวันลาออก
				if ($_PARAM['signout_dt']) {
					$_sql .= " AND _group.work_date >= '{$_PARAM['signout_dt']}' ";
				}

				// ถ้ามีการแก้ไขวันที่เริ่มงาน
				if ($_PARAM['tmp_effective_dt'] && $_PARAM['effective_dt']) {
					$_sql .= " AND _group.work_date BETWEEN '" . $_PARAM['tmp_effective_dt'] . "' AND '" . $_PARAM['effective_dt'] . "' ";
				}

				// echo "$_sql<BR>";
				$this->Execute_Query($_sql);
	}

	// อัพเดทสถานะตารางเวลาการทำงานจาก Re-active ข้อมูลพนักงานเดิม (ยังไม่ได้เริ่มงาน/ลาออก)
	/**
	 * The function `updateWorkNotAvailableWithReactive` updates work availability status based on
	 * employee signout date.
	 */
	function updateWorkNotAvailableWithReactive() {
	 	$employee = $this->getEmployeeByID($_REQUEST['employee_id']);
		$month = $GLOBALS['masterSalaryReportService']->getMasterReportByDate($employee['signout_dt']);

		$data = [
			'status' => 0,
			'master_salary_report_id' => $month['master_salary_report_id'],
			'signout_dt' => $employee['signout_dt'],
		];

		$this->updateWorkNotAvailableWithFilter($data);

		// echo " month --> ";
		// echo json_encode($month); exit;
	}
	function getListEmployeeForReportMultipleMonthFund($_PARAM) {
		try {
			// Validate required parameters
			if (empty($_PARAM['start_month']) || empty($_PARAM['end_month'])) {
				throw new Exception('Missing required parameters: start_month and end_month');
			}
	
			if (empty($_PARAM['fund_ids'])) {
				throw new Exception('Missing required parameter: fund_ids');
			}
	
			if (!is_array($_PARAM['fund_ids'])) {
				throw new Exception('Invalid parameter: fund_ids must be an array');
			}
	
			if (empty($_PARAM['server_id']) || empty($_PARAM['instance_server_id']) || empty($_PARAM['instance_server_channel_id'])) {
				throw new Exception('Missing required server parameters');
			}
	
			// Validate date format and range
			if (!preg_match('/^\d{4}-\d{2}$/', $_PARAM['start_month']) || 
				!preg_match('/^\d{4}-\d{2}$/', $_PARAM['end_month'])) {
				throw new Exception('Invalid date format. Expected format: YYYY-MM');
			}
	
			$startDate = new DateTime($_PARAM['start_month'] . '-01');
			$endDate = new DateTime($_PARAM['end_month'] . '-01');
	
			if ($startDate > $endDate) {
				throw new Exception('Start month cannot be after end month');
			}
	
			$interval = $startDate->diff($endDate);
			$monthsDiff = ($interval->y * 12) + $interval->m;
	
			if ($monthsDiff > 12) {
				throw new Exception('Date range cannot exceed 12 months');
			}
	
			// Get employee list with company, branch, and department information
			$_sql_employee_list = "SELECT 
				t1.employee_id, 
				t1.employee_code, 
				t1.employee_name, 
				t1.employee_last_name,
				t1.company_id,
				t1.branch_id,
				t1.department_id,
				t2.company_name, 
				t3.branch_name, 
				t4.department_name
			FROM comp_employee t1 
			LEFT JOIN comp_company t2 
				ON (t1.company_id = t2.company_id 
					AND t1.server_id = t2.server_id 
					AND t1.instance_server_id = t2.instance_server_id 
					AND t1.instance_server_channel_id = t2.instance_server_channel_id)
			LEFT JOIN comp_branch t3 
				ON (t1.branch_id = t3.branch_id 
					AND t1.server_id = t3.server_id 
					AND t1.instance_server_id = t3.instance_server_id 
					AND t1.instance_server_channel_id = t3.instance_server_channel_id)
			LEFT JOIN comp_department t4 
				ON (t1.department_id = t4.department_id 
					AND t1.server_id = t4.server_id 
					AND t1.instance_server_id = t4.instance_server_id 
					AND t1.instance_server_channel_id = t4.instance_server_channel_id)
			WHERE t1.server_id = '{$_PARAM['server_id']}'
				AND t1.instance_server_id = '{$_PARAM['instance_server_id']}'
				AND t1.instance_server_channel_id = '{$_PARAM['instance_server_channel_id']}'";
	
			// Add company filter if provided
			if (!empty($_PARAM['company_lists'])) {
				$companyIds = array_map(function($company) { 
					return isset($company['id']) ? base64_decode($company['id']) : null; 
				}, $_PARAM['company_lists']);
				$companyIds = array_filter($companyIds);
				if (!empty($companyIds)) {
					$_sql_employee_list .= " AND t1.company_id IN ('" . implode("','", $companyIds) . "')";
				}
			}
	
			// Add branch filter if provided
			if (!empty($_PARAM['branch_lists'])) {
				$branchIds = array_map(function($branch) { 
					return isset($branch['id']) ? base64_decode($branch['id']) : null; 
				}, $_PARAM['branch_lists']);
				$branchIds = array_filter($branchIds);
				if (!empty($branchIds)) {
					$_sql_employee_list .= " AND t1.branch_id IN ('" . implode("','", $branchIds) . "')";
				}
			}
	
			// Add department filter if provided
			if (!empty($_PARAM['department_lists'])) {
				$departmentIds = array_map(function($department) { 
					return isset($department['id']) ? base64_decode($department['id']) : null;
				}, $_PARAM['department_lists']);
				$departmentIds = array_filter($departmentIds);
				if (!empty($departmentIds)) {
					$_sql_employee_list .= " AND t1.department_id IN ('" . implode("','", $departmentIds) . "')";
				}
			}
	
			// Add sys_del_flag filter if provided
			if (isset($_PARAM['sys_del_flag']) && $_PARAM['sys_del_flag'] !== 'A') {
				$_sql_employee_list .= " AND t1.sys_del_flag = '{$_PARAM['sys_del_flag']}'";
			}
	
			$listEmployee = $this->_sqllists($_sql_employee_list);
	
			if (empty($listEmployee)) {
				throw new Exception("No employees found for the report based on current filters.");
			}
	
			// Get employee IDs for fund data
			$arrayEOH = array();
			foreach($listEmployee as $emp) {
				if (isset($emp['employee_id'])) {
					$arrayEOH[] = $emp['employee_id'];
				}
			}
	
			// Get fund data for each month
			$employeeData = array();
			$periodStartDate = new DateTime($_PARAM['start_month'] . '-01');
			$periodEndDate = new DateTime($_PARAM['end_month'] . '-01');
			$periodEndDate->modify('+1 month');
	
			$interval = new DateInterval('P1M');
			$period = new DatePeriod($periodStartDate, $interval, $periodEndDate);
	
			foreach ($period as $date) {
				$monthKey = $date->format('Y-m');
				
				// Get employee fund data
				$_sql_fund_data = "SELECT
						t1.employee_id,
						t1.employee_code,
						t1.employee_name,
						t8.fund_employee_date,
						t8.fund_employee_no,
						t5.fund_id,
						t5.log_balance AS employee_contribution,
						t8.fund_employee_balance AS company_contribution,
						t5.master_salary_month
					FROM
						comp_employee t1
					INNER JOIN
						{$GLOBALS['instanceServer']['instance_server_dbn']}.payroll_fund_employee_log t5 ON (
							t1.employee_id = t5.employee_id AND t1.server_id = t5.server_id AND t1.instance_server_id = t5.instance_server_id AND t1.instance_server_channel_id = t5.instance_server_channel_id
						)
					INNER JOIN
						comp_fund_employee t8 ON (
							t5.employee_id = t8.employee_id AND t5.fund_id = t8.fund_id AND t5.server_id = t8.server_id AND t5.instance_server_id = t8.instance_server_id AND t5.instance_server_channel_id = t8.instance_server_channel_id
						)
					WHERE
						t1.server_id = '{$_PARAM['server_id']}' AND t1.instance_server_id = '{$_PARAM['instance_server_id']}' AND t1.instance_server_channel_id = '{$_PARAM['instance_server_channel_id']}' AND t1.employee_id IN ('" . implode("','", $arrayEOH) . "') AND t5.fund_id IN ('" . implode("','", $_PARAM['fund_ids']) . "') AND t5.master_salary_month = '{$monthKey}'";
	
				$employeeFundDataResults = $this->_sqllists($_sql_fund_data);
	
				if (is_array($employeeFundDataResults)) {
					foreach ($employeeFundDataResults as $dataRow) {
						$employeeId = $dataRow['employee_id'];
						$fundId = $dataRow['fund_id'];
	
						if (!isset($employeeData[$fundId])) {
							$employeeData[$fundId] = array();
						}
						if (!isset($employeeData[$fundId][$employeeId])) {
							$employeeData[$fundId][$employeeId] = array();
						}
	
						$employeeData[$fundId][$employeeId] = array(
							'employee_code' => $dataRow['employee_code'],
							'employee_name' => $dataRow['employee_name'],
							'date_register' => $dataRow['date_register'],
							'date' => $dataRow['fund_employee_date'],
							'employee_contribution' => $dataRow['employee_contribution'],
							'company_contribution' => $dataRow['company_contribution']
						);
					}
				}
			}
	
			return array(
				'list_employee' => $listEmployee,
				'employee_data' => $employeeData
			);
	
		} catch (Exception $e) {
			error_log("Error in getListEmployeeForReportMultipleMonthFund: " . $e->getMessage());
			throw $e;
		}
	}


}

$employee_lists = array();
$employeeService = new EmployeeService();

?>