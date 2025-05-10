<? 
	    include("AbstractFund.class.php");

	class FundService extends AbstractFund{

					function getNewFundCode(){     
						$_sql="	SELECT * FROM comp_fund _fund
						WHERE _fund.server_id='{$_REQUEST['server_id']}' 
						AND _fund.instance_server_id = '{$_REQUEST['instance_server_id']}' 
						AND _fund.instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}' ";
						//echo "$_sql";
						$lists = $this->_sqllists($_sql);	
						$seq = sizeof($lists)+1;	
						$nextSeq = str_pad($seq, 4, '0', STR_PAD_LEFT);
						$no = "FD".$nextSeq;
						return $no;		
					}
				
					function getFund(){     
						$_sql="SELECT * FROM comp_fund _fund 
						INNER JOIN comp_salary_type _stype ON (_fund.salary_type_id=_stype.salary_type_id) 
						WHERE _stype.sys_del_flag = 'N'  
						-- AND _stype.read_only_flag = 'N' 
						AND _fund.server_id = '{$_REQUEST['server_id']}' 
						AND _fund.instance_server_id = '{$_REQUEST['instance_server_id']}' 
						AND _fund.instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}' "; 
						// echo $_sql."<br>";
						$lists = $this->_sqllists($_sql);
						return $lists;
					}      

					function getFundBeforeSignout() {     
						$_sql="SELECT _fund.* 
						, _fcompany.* 
						, _femployee.* 
						, _stype.salary_type_name_en 
						FROM comp_fund _fund 
						INNER JOIN comp_salary_type _stype ON (_fund.salary_type_id = _stype.salary_type_id) 
						INNER JOIN comp_fund_employee _femployee ON (_fund.fund_id = _femployee.fund_id )
						LEFT JOIN (SELECT IFNULL(SUM(_flog.log_balance),0) as fund_company_balance, _flog.fund_id 
									FROM {$GLOBALS['instanceServer']['instance_server_dbn']}.payroll_fund_company_log _flog 
									WHERE _flog.server_id = '{$_REQUEST['server_id']}' 
									AND _flog.instance_server_id = '{$_REQUEST['instance_server_id']}' 
									AND _flog.instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}' 
									AND _flog.employee_id = '{$_REQUEST['employee_id']}' 
									AND _flog.sys_del_flag = 'N' 
									GROUP BY _flog.fund_id )
									_fcompany ON (_fund.fund_id = _fcompany.fund_id )
						WHERE _stype.sys_del_flag = 'N'  
						AND _femployee.employee_id = '{$_REQUEST['employee_id']}'
						AND _fund.server_id = '{$_REQUEST['server_id']}' 
						AND _fund.instance_server_id = '{$_REQUEST['instance_server_id']}' 
						AND _fund.instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}' "; 
						// echo $_sql."<br>";
						$lists = $this->_sqllists($_sql);
						return $lists;
					}

					function getProvident(){     
						$_sql="SELECT * FROM comp_fund _fund 
						-- INNER JOIN comp_salary_type _stype ON (_fund.salary_type_id=_stype.salary_type_id) 
						INNER JOIN {$GLOBALS['instanceServer']['instance_server_dbn']}.payroll_master_salary_type _mstype 
						ON _fund.salary_type_id = _mstype.salary_type_id 
						WHERE  _fund.sys_del_flag='N'  
						AND _fund.server_id = '{$_REQUEST['server_id']}' 
						AND _fund.instance_server_id = '{$_REQUEST['instance_server_id']}' 
						AND _fund.instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}'  ";
						if(!empty($_REQUEST['fund_code'])){
							$_sql .= " AND _mstype.salary_type_code = '{$_REQUEST['fund_code']}' ";
						}else{
							$_sql .= " AND _mstype.salary_type_code IN ('provident','provident2','provident3') ";
						}
						if (!empty($_REQUEST['year_month'])) {
							$_sql .= " AND _mstype.master_salary_month = '{$_REQUEST['year_month']}' ";
						}
						if($_REQUEST['bank_id'] == '0' ){
							$_sql .= " AND _fund.bank_id = '{$_REQUEST['bank_id']}' ";
						}else{
							if(!empty($_REQUEST['bank_id']))
							$_sql .= " AND _fund.bank_id = '{$_REQUEST['bank_id']}' ";
						}
						$_sql .= " GROUP BY _mstype.salary_type_code ";
						$_sql .= " ORDER BY _mstype.salary_type_code ";
						// echo $_sql; 
						
						$lists = $this->_sqllists($_sql);
						return $lists;
					}      

					function getSpecificFund($_fund_id){     
						$_sql="SELECT * FROM comp_fund _fund 
						INNER JOIN comp_salary_type _stype ON (_fund.salary_type_id=_stype.salary_type_id) 
						WHERE  _fund.sys_del_flag='N'  
						AND _fund.fund_id='{$_fund_id}' 
						AND _fund.server_id = '{$_REQUEST['server_id']}' 
						AND _fund.instance_server_id = '{$_REQUEST['instance_server_id']}' 
						AND _fund.instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}'  "; 
						$lists = $this->_sqlget($_sql);
						return $lists;
					}
					
					function getFundBySalaryTypeId($salary_type_id){     
						$_sql="SELECT * FROM comp_fund _fund 
						WHERE _fund.salary_type_id = '{$salary_type_id}' 
						AND _fund.server_id = '{$_REQUEST['server_id']}' 
						AND _fund.instance_server_id = '{$_REQUEST['instance_server_id']}' 
						AND _fund.instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}' "; 
						// echo $_sql."<br>";
						$lists = $this->_sqlget($_sql);
						return $lists;
					} 
				
					function deleteFundLog($_master_salary_report_id,$_employee_id){      
						$_sql = "DELETE _flog.* FROM {$GLOBALS['instanceServer']['instance_server_dbn']}.payroll_fund_log _flog 
						WHERE _flog.master_salary_report_id='{$_master_salary_report_id}'  
						AND _flog.employee_id= '{$_employee_id}' 
						AND _flog.server_id = '{$_REQUEST['server_id']}' 
						AND _flog.instance_server_id = '{$_REQUEST['instance_server_id']}' 
						AND _flog.instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}' ";
						//echo "$_sql";
						$this->Execute_Query($_sql);	
					
						$_sql = "DELETE _elog.* FROM {$GLOBALS['instanceServer']['instance_server_dbn']}.payroll_fund_employee_log _elog 
						WHERE _elog.master_salary_report_id='{$_master_salary_report_id}' 
						AND _elog.employee_id= '{$_employee_id}' 
						AND _elog.server_id = '{$_REQUEST['server_id']}' 
						AND _elog.instance_server_id = '{$_REQUEST['instance_server_id']}' 
						AND _elog.instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}' ";
						//echo "$_sql";
						$this->Execute_Query($_sql);	
						
						$_sql = "DELETE _blog.* FROM {$GLOBALS['instanceServer']['instance_server_dbn']}.payroll_fund_company_log _blog 
						WHERE _blog.master_salary_report_id='{$_master_salary_report_id}' 
						AND _blog.employee_id= '{$_employee_id}' 
						AND _blog.server_id = '{$_REQUEST['server_id']}' 
						AND _blog.instance_server_id = '{$_REQUEST['instance_server_id']}' 
						AND _blog.instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}' ";
						//echo "$_sql";
						$this->Execute_Query($_sql);	
					}

					function deleteAllFundLogInMonth($_master_salary_report_id){
						$_sql = "DELETE _flog.* FROM {$GLOBALS['instanceServer']['instance_server_dbn']}.payroll_fund_log _flog 
						WHERE _flog.master_salary_report_id='{$_master_salary_report_id}'   
						AND _flog.server_id = '{$_REQUEST['server_id']}' 
						AND _flog.instance_server_id = '{$_REQUEST['instance_server_id']}' 
						AND _flog.instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}' ";
						//echo "$_sql";
						$this->Execute_Query($_sql);	
					
						$_sql = "DELETE _elog.* FROM {$GLOBALS['instanceServer']['instance_server_dbn']}.payroll_fund_employee_log _elog 
						WHERE _elog.master_salary_report_id='{$_master_salary_report_id}' 
						AND _elog.server_id = '{$_REQUEST['server_id']}' 
						AND _elog.instance_server_id = '{$_REQUEST['instance_server_id']}' 
						AND _elog.instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}' ";
						//echo "$_sql";
						$this->Execute_Query($_sql);	
						
						$_sql = "DELETE _blog.* FROM {$GLOBALS['instanceServer']['instance_server_dbn']}.payroll_fund_company_log _blog 
						WHERE _blog.master_salary_report_id='{$_master_salary_report_id}' 
						AND _blog.server_id = '{$_REQUEST['server_id']}' 
						AND _blog.instance_server_id = '{$_REQUEST['instance_server_id']}' 
						AND _blog.instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}' ";
						//echo "$_sql";
						$this->Execute_Query($_sql);	
					}
					
					function updateSumBalance($_employee_id,$_company_id,$_fund_id){     
						$_sql = "UPDATE comp_fund_company _fcompany 
						SET _fcompany.fund_company_balance = (
							SELECT SUM(log_balance) as fund_company_balance 
							FROM {$GLOBALS['instanceServer']['instance_server_dbn']}.payroll_fund_company_log _blog 
							WHERE _blog.fund_id='".$_fund_id."' 
							AND _blog.company_id='".$_company_id."'
							AND _blog.sys_del_flag ='N' 
							AND _blog.server_id = '{$_REQUEST['server_id']}' 
							AND _blog.instance_server_id = '{$_REQUEST['instance_server_id']}' 
							AND _blog.instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}'
						) 
						WHERE _fcompany.fund_id='".$_fund_id."' 
						AND _fcompany.company_id='".$_company_id."' 
						AND _fcompany.server_id = '{$_REQUEST['server_id']}' 
						AND _fcompany.instance_server_id = '{$_REQUEST['instance_server_id']}' 
						AND _fcompany.instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}' ";
						//echo "$_sql";
						$this->Execute_Query($_sql);	
					
						$_sql = "UPDATE comp_fund_employee _femployee 
						SET _femployee.fund_employee_balance = (
							SELECT SUM(log_balance) as fund_employee_balance 
							FROM {$GLOBALS['instanceServer']['instance_server_dbn']}.payroll_fund_employee_log _elog 
							WHERE _elog.fund_id='".$_fund_id."' 
							AND _elog.employee_id='{$_employee_id}' 
							AND _elog.sys_del_flag ='N' 
							AND _elog.server_id = '{$_REQUEST['server_id']}' 
							AND _elog.instance_server_id = '{$_REQUEST['instance_server_id']}' 
							AND _elog.instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}'
						) 
						WHERE _femployee.fund_id='".$_fund_id."' 
						AND _femployee.employee_id='{$_employee_id}' 
						AND _femployee.server_id = '{$_REQUEST['server_id']}' 
						AND _femployee.instance_server_id = '{$_REQUEST['instance_server_id']}' 
						AND _femployee.instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}' ";
						// echo "$_sql";
						$this->Execute_Query($_sql);	
						
						$_sql = "UPDATE comp_fund _fund 
						SET _fund.fund_balance = (
							SELECT SUM(log_balance) as fund_balance 
							FROM {$GLOBALS['instanceServer']['instance_server_dbn']}.payroll_fund_log _flog 
							WHERE _flog.fund_id='".$_fund_id."' 
							AND _flog.server_id = '{$_REQUEST['server_id']}' 
							AND _flog.instance_server_id = '{$_REQUEST['instance_server_id']}' 
							AND _flog.instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}'
						) 
						WHERE _fund.fund_id='".$_fund_id."' 
						AND _fund.server_id = '{$_REQUEST['server_id']}' 
						AND _fund.instance_server_id = '{$_REQUEST['instance_server_id']}' 
						AND _fund.instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}' ";
						//echo "$_sql";
						$this->Execute_Query($_sql);	
					}








		}
			$fund_lists = array();
		$fundService = new FundService();

?>