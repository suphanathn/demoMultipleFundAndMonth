<? 
	class AbstractMasterSalaryReport extends AbstractBaseService{
		var $tableFields = array('master_salary_report_id'=>'string'					
, 'master_salary_month'=>'string'					
, 'salary_report_start_dt'=>'datetime'					
, 'salary_report_end_dt'=>'datetime'					
, 'day_in_month'=>'int'					
, 'master_salary_report_type_lv'=>'string'					
, 'salary_report_step'=>'int'					
, 'salary_paid_dt'=>'date'					
, 'tax_paid_dt'=>'date'					
, 'salary_split_flag'=>'string'					
, 'salary_split_round'=>'int'					
, 'sso_employee_rate'=>'real'					
, 'sso_company_rate'=>'real'					
, 'approve_flag'=>'string'					
, 'approve_remark'=>'blob'					
, 'remote_ip'=>'string'					
, 'order_no'=>'int'					
, 'server_id'=>'string'					
, 'instance_server_id'=>'string'					
, 'instance_server_channel_id'=>'string'					
, 'language_code'=>'string'					
, 'sys_del_flag'=>'string'					
, 'created_by'=>'string'					
, 'created'=>'datetime'					
, 'last_upd_by'=>'string'					
, 'last_upd'=>'datetime'					
, 'global_flag'=>'string'					
, 'read_only_flag'=>'string'					
, 'publish_flag'=>'string'					
, 'change_type_flag'=>'string'					
, 'change_sso_flag'=>'string'
, 'change_config_flag'=>'string'
, 'change_timeleave_flag'=>'string'
, 'change_branch_sso_flag' => 'string'
, 'Ref_1'=>'blob'
, 'Ref_2'=>'blob'
, 'Ref_3'=>'blob'
, 'Ref_4'=>'blob'
, 'Ref_5'=>'blob');
		var $tablePrimaryKey = "master_salary_report_id";
		var $tableName = "payroll_master_salary_report";
	function AbstractMasterSalaryReport(){
				$this->tablesField[$this->tableName] = $this->tableFields;
				$this->tablePrimaryKey = $this->tablePrimaryKey;
				$this->tableName = $this->tableName;
				$this->databaseName = $GLOBALS["__MYSQLDB"]["DB_NAME"];
	}
	}
?>