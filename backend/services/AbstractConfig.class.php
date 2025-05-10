<? 
	class AbstractConfig extends AbstractBaseService{
		var $tableFields = array('config_id'=>'string'					
, 'config_code'=>'string'					
, 'config_group'=>'string'					
, 'config_name'=>'string'					
, 'config_name_en'=>'string'					
, 'config_status'=>'string'					
, 'config_key_1'=>'string'					
, 'config_key_2'=>'string'					
, 'config_key_3'=>'string'					
, 'config_key_4'=>'string'					
, 'config_key_5'=>'string'					
, 'config_key_6'=>'string'					
, 'config_key_7'=>'string'					
, 'config_key_8'=>'string'					
, 'config_key_9'=>'string'					
, 'config_key_10'=>'string'					
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
, 'last_upd'=>'timestamp');
		var $tablePrimaryKey = "config_id";
		var $tableName = "comp_config";
	function AbstractConfig(){
				$this->tablesField[$this->tableName] = $this->tableFields;
				$this->tablePrimaryKey = $this->tablePrimaryKey;
				$this->tableName = $this->tableName;
				$this->databaseName = $GLOBALS["__MYSQLDB"]["DB_NAME"];
	}
	}
?>