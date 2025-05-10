<? 
	class AbstractFund extends AbstractBaseService{
		var $tableFields = array('fund_id'=>'string'					
, 'salary_type_id'=>'string'					
, 'fund_code'=>'string'					
, 'fund_name'=>'string'					
, 'fund_desc'=>'blob'					
, 'fund_balance'=>'real'		
, 'bank_id'=>'string'				
, 'order_no'=>'int'					
, 'server_id'=>'string'					
, 'instance_server_id'=>'string'					
, 'instance_server_channel_id'=>'string'					
, 'publish_flag'=>'string'					
, 'approve_flag'=>'string'					
, 'approve_remark'=>'blob'					
, 'global_flag'=>'string'					
, 'read_only_flag'=>'string'					
, 'sys_del_flag'=>'string'					
, 'remote_ip'=>'string'					
, 'language_code'=>'string'					
, 'created_by'=>'string'					
, 'created'=>'datetime'					
, 'last_upd_by'=>'string'					
, 'last_upd'=>'datetime');
		var $tablePrimaryKey = "fund_id";
		var $tableName = "comp_fund";
	function AbstractFund(){
				$this->tablesField[$this->tableName] = $this->tableFields;
				$this->tablePrimaryKey = $this->tablePrimaryKey;
				$this->tableName = $this->tableName;
				$this->databaseName = $GLOBALS["__MYSQLDB"]["DB_NAME"];
	}
	}
?>