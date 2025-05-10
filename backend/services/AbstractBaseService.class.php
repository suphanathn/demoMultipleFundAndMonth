<? 
	
	class AbstractBaseService{
			             
			var $tableFields = array();
			var $tablesField = array();
			var $tablePrimaryKey = "";
			var $tableName = "";
			
			var $masterDatabaseName = "";
			var $dwhDatabaseName = "";
			var $trxDatabaseName = "";
			
			var $search_not_array = array("created_by", "last_upd_by");

			function setMasterDatabaseName($masterDatabaseName){
				$this->masterDatabaseName = $masterDatabaseName;
			}
			function getMasterDatabaseName(){
				return $this->masterDatabaseName;
			}
			
			function setWarehouseDatabaseName($dwhDatabaseName){
				$this->dwhDatabaseName = $dwhDatabaseName;
			}
			function getWarehouseDatabaseName(){
				return $this->dwhDatabaseName;
			}

			function setTransactionDatabaseName($trxDatabaseName){
				$this->trxDatabaseName = $trxDatabaseName;
			}
			function getTransactionDatabaseName(){
				return $this->trxDatabaseName;
			}

			function getPrimaryKey($lenght = 12) {
				// uniqid gives 13 chars, but you could adjust it to your needs.
				if (function_exists("random_bytes")) {
					$bytes = random_bytes(ceil($lenght / 2));
				} elseif (function_exists("openssl_random_pseudo_bytes")) {
					$bytes = openssl_random_pseudo_bytes(ceil($lenght / 2));
				} else {
					throw new Exception("no cryptographically secure random function available");
				}
				return date("Ymd").strtoupper(substr(bin2hex($bytes), 0, $lenght));
			}
			
			function jwtVerify($_compgrp, $_component, $_action){
				$_sql = "  select api_id, jwt_verify
								from sys_api api
								where api.api_compgrp = '{$_compgrp}' AND api.api_comp = '{$_component}' AND api.api_action = '{$_action}' AND api.server_id = '10101010' ";
				//echo "$_sql";
				return $this->_sqlget($_sql);   
			} 
                                    
			function validateApiParameters($_compgrp, $_component, $_action, $_params){
				
				$_sql = "select api.api_id, api.api_code, apiParam.api_parameter_name, apiParam.api_parameter_require, apiParam.api_parameter_type
								from sys_api api, sys_api_parameter apiParam
								where api.api_id = apiParam.api_id
								AND apiParam.api_parameter_require = 'required'
								AND api.api_compgrp = '{$_compgrp}' AND api.api_comp = '{$_component}' AND api.api_action = '{$_action}' AND apiParam.server_id > 0 ";
				$keys = $this->_sqllists($_sql); 
				if($GLOBALS["debugApi"]){
					echo "(".sizeof($keys).")$_sql<br>";
				}
				$requireFields = array();
				
				if(empty($_params["_compgrp"]))
						$requireFields = array_merge($requireFields, array("_compgrp"=>"string"));
				if(empty($_params["_comp"]))
						$requireFields = array_merge($requireFields, array("_comp"=>"string"));
				if(empty($_params["_action"]))
						$requireFields = array_merge($requireFields, array("_action"=>"string"));
				if(empty($_params["server_id"]))
						$requireFields = array_merge($requireFields, array("server_id"=>"string"));
				
				for($i=0;$i<sizeof($keys);$i++){
					
					if(empty($_params[$keys[$i]["api_parameter_name"]])){
						//echo "required {$keys[$i]["api_parameter_name"]}<BR> ";
						$requireFields = array_merge($requireFields, array($keys[$i]["api_parameter_name"]=>$keys[$i]["api_parameter_type"]));
					}
				}
				if(sizeof($requireFields)>0){ 

					$requireFields = array_merge($requireFields, array("api_id"=>$keys[0]["api_id"]));
					$requireFields = array_merge($requireFields, array("api_code"=>$keys[0]["api_code"]));
					 
					return array("verify_flag"=>"false", "REQUIRED_FIELDS"=>$requireFields);
				}           
				return array("verify_flag"=>"true");
			}
			
			function Api_Search($pageNavigator, $_PARAM, $_sql){  
					$keys = array_keys($_PARAM); 
					for($i=0;$i<sizeof($keys);$i++){ 
						if($this->tableFields[$keys[$i]]){ 
							if(!empty($_PARAM[$keys[$i]])){
								if(substr($keys[$i], -3)=="_id"){    
									$_sql .= " AND _master.{$keys[$i]} = '{$_PARAM[$keys[$i]]}' ";
								}else if(substr($keys[$i], -3)=="_dt"){    
									$_sql .= " AND _master.{$keys[$i]} = '{$_PARAM[$keys[$i]]}' ";
								}else if($keys[$i]=='language_code'){
									
								}else{
									$_sql .= " AND _master.{$keys[$i]} like '%{$_PARAM[$keys[$i]]}%' ";
								}
							}
						}
					}
					$pageNavigator->setRecordCount(sizeof($this->_sqllists($_sql)));
					$_sql .= " limit  {$pageNavigator->currentPageFirstRecordNo}, {$pageNavigator->recordPerPage} ";
					// echo "$_sql<br>";
					$pageNavigator->setPageResultLists($this->_sqllists($_sql));  
					return $pageNavigator;
			}
			
			function validateUniqueValues($_PARAM, $keys, $runOnDb){
					if(empty($runOnDb)) 
								return "Invalid Database Name Target"; 
					if(empty($this->tableName)) 
								return NULL;
					$_sql=" SELECT * FROM {$runOnDb}.".$this->tableName."  ";	
					$_sql.=" WHERE  sys_del_flag='N' AND server_id = '{$_PARAM['server_id']}'  ";
					for($i=0;$i<sizeof($keys);$i++){
						$_sql.="  AND {$keys[$i]} = '{$_PARAM[$keys[$i]]}'  ";
					}
					if($_REQUEST['_debug']=="Y")
							echo $_sql;
					return $this->_sqlget($_sql);

			}
			function trx_servicecreate($_PARAM){      
							//print_r($_PARAM); 
							if(empty($_PARAM[$this->tablePrimaryKey])){
								$_PARAM[$this->tablePrimaryKey] = $this->getPrimaryKey(12);
							}
							$_sql = $this->trxSqlCreateGenerator($_PARAM); 
							if($_REQUEST['_debug']=="Y")
								echo "$_sql<br>"; 
							if(empty($_sql))
								return NULL;			              
							
							$retrows = $this->Execute_Query($_sql); 

							//echo "\$mysqli_insert_id >>[{$retrows}]$_sql<br>";
							//if(!$retrows){ throw new Exception("MySQL [".$GLOBALS["__MYSQLDB"]["DB_NAME"]."] Error ");	}  
							//echo "ID >> $mysqli_insert_id<br>"; 
							if(empty($retrows))
								return NULL;
							return $_PARAM;         
			}           
			function trxSqlCreateGenerator($_PARAM){  
				if($this->tableName=="")
					return NULL;  
				$_jump_fields = array("sys_del_flag","server_id", "instance_server_id", "instance_server_channel_id",  "remote_ip", "created", "created_by", "last_upd", "last_upd_by");
				$_fields = "{$this->tablePrimaryKey}, sys_del_flag,server_id, instance_server_id, instance_server_channel_id, remote_ip,created, created_by, last_upd, last_upd_by ";
				$_values = "'{$_PARAM[$this->tablePrimaryKey]}', 'N', '{$_REQUEST['server_id']}', '{$_REQUEST['instance_server_id']}', '{$_REQUEST['instance_server_channel_id']}', '{$_SERVER['REMOTE_ADDR']}', NOW(), '{$_REQUEST['identify_user_id']}', NOW(), '{$_REQUEST['identify_user_id']}'";
				//echo "\$this->tableName >>[".$this->tableName."]<br>";
				//print_r($this->tablesField);
				//echo "<br>";
				$_keys = array_keys($this->tablesField[$this->tableName]);
				$tableFields = $this->tablesField[$this->tableName];
				if(empty($_keys) || empty($tableFields))
					return NULL;
				////print_r($tableFields);
				$append_index = 0;			
				for($keys=0;$keys<sizeof($_keys);$keys++){               
					if($_keys[$keys]!=$this->tablePrimaryKey){ 
		 					//echo "??????? [{$_keys[$keys]}] value is [{$_PARAM[$_keys[$keys]]}] <br>"; 
							$_reqvalue = "";
							$_reqkey = $_keys[$keys];
							if(!in_array($_reqkey, $_jump_fields) && strpos($_reqkey, "_id")){
								//echo "[$_reqkey] case 1<BR>";
								$_reqvalue = trim($_PARAM[$_reqkey]);
							}else if(!in_array($_reqkey, $_jump_fields) && strpos($_reqkey, "_psw")){
								$_reqvalue = md5(trim($_PARAM[$_reqkey]));
							}else if(!in_array($_reqkey, $_jump_fields)){
								$_reqvalue = trim($_PARAM[$_reqkey]);
							}
							
							if(!in_array($_reqkey, $_jump_fields) && $_PARAM[$_keys[$keys]]!=NULL && $_PARAM[$_keys[$keys]]!=""){
								//echo "append value+field type [{$keys}] [{$_keys[$keys]}] [{$tableFields[$_keys[$keys]]}]<br>";
								$_fields .= ", ".trim($_keys[$keys]);
								if($tableFields[$_keys[$keys]]=='int' || $tableFields[$_keys[$keys]]=='real'){
										if(strpos($_keys[$keys], "_id")){
											$_values .= ", ".($_reqvalue)*1;
											//echo "decode [$_values]<BR>";
										}else{		
											$_values .= ", ".$_reqvalue*1;
										}
								}else if($tableFields[$_keys[$keys]]=='string' || $tableFields[$_keys[$keys]]=='blob'){ 
									$_values .= ", '".trim($_reqvalue)."'	";
								}else if($tableFields[$_keys[$keys]]=='datetime'){ 
										if($_reqvalue=="NOW()"){
											$_values .= ", $_reqvalue";
										}else{
												$_values .= ", '".date('Y-m-d H:i:s',strtotime($_reqvalue))."'";                                                                                                       
										}
								}else  if($tableFields[$_keys[$keys]]=='date'){ 
									if($_reqvalue=="NOW()"){
										$_values .= ", $_reqvalue";
									}else{
										$_values .= ", '".date('Y-m-d',strtotime($_reqvalue))."'";     
									}
								}else  if($tableFields[$_keys[$keys]]=='time'){ 
										$_values .= ",'".$_reqvalue."'";
								}
								$append_index++;
							}
                                             
					}

				}		 

				$_sql = "INSERT INTO {$GLOBALS["instanceServer"]["instance_server_dbn"]}.".$this->tableName." ($_fields) VALUES ($_values);	";
				return $_sql;

			}
			function odb_servicecreate($_PARAM){      
							//print_r($_PARAM); 
							if(empty($_PARAM[$this->tablePrimaryKey])){
								$_PARAM[$this->tablePrimaryKey] = $this->getPrimaryKey(12);
							}
							$_sql = $this->odbSqlCreateGenerator($_PARAM); 

							if($_REQUEST['_debug']=="Y")
								echo "$_sql<br>"; 
							if(empty($_sql))
								return NULL;			              
							
							$retrows = $this->Execute_Query($_sql); 

							//echo "\$mysqli_insert_id >>[{$retrows}]$_sql<br>";
							//if(!$retrows){ throw new Exception("MySQL [".$GLOBALS["__MYSQLDB"]["DB_NAME"]."] Error ");	}  
							//echo "ID >> $mysqli_insert_id<br>"; 
							if(empty($retrows))
								return NULL;
							return $_PARAM;         
			}           
			function odbSqlCreateGenerator($_PARAM){  
				if($this->tableName=="")
					return NULL;  
				$_jump_fields = array("sys_del_flag","server_id", "instance_server_id", "instance_server_channel_id",  "remote_ip", "created", "created_by", "last_upd", "last_upd_by");
				$_fields = "{$this->tablePrimaryKey}, sys_del_flag,server_id, instance_server_id, instance_server_channel_id, remote_ip,created, created_by, last_upd, last_upd_by ";
				$_values = "'{$_PARAM[$this->tablePrimaryKey]}', 'N', '{$_REQUEST['server_id']}', '{$_REQUEST['instance_server_id']}', '{$_REQUEST['instance_server_channel_id']}', '{$_SERVER['REMOTE_ADDR']}', NOW(), '{$_REQUEST['identify_user_id']}', NOW(), '{$_REQUEST['identify_user_id']}'";
				//echo "\$this->tableName >>[".$this->tableName."]<br>";
				//print_r($this->tablesField);
				//echo "<br>";
				$_keys = array_keys($this->tablesField[$this->tableName]);
				$tableFields = $this->tablesField[$this->tableName];
				if(empty($_keys) || empty($tableFields))
					return NULL;
				////print_r($tableFields);
				$append_index = 0;			
				for($keys=0;$keys<sizeof($_keys);$keys++){               
					if($_keys[$keys]!=$this->tablePrimaryKey){ 
		 					//echo "??????? [{$_keys[$keys]}] value is [{$_PARAM[$_keys[$keys]]}] <br>"; 
							$_reqvalue = "";
							$_reqkey = $_keys[$keys];
							if(!in_array($_reqkey, $_jump_fields) && strpos($_reqkey, "_id")){
								//echo "[$_reqkey] case 1<BR>";
								$_reqvalue = trim($_PARAM[$_reqkey]);
							}else if(!in_array($_reqkey, $_jump_fields) && strpos($_reqkey, "_psw")){
								$_reqvalue = md5(trim($_PARAM[$_reqkey]));
							}else if(!in_array($_reqkey, $_jump_fields)){
								$_reqvalue = trim($_PARAM[$_reqkey]);
							}
							
							if(!in_array($_reqkey, $_jump_fields) && $_PARAM[$_keys[$keys]]!=NULL && $_PARAM[$_keys[$keys]]!=""){
								//echo "append value+field type [{$keys}] [{$_keys[$keys]}] [{$tableFields[$_keys[$keys]]}]<br>";
								$_fields .= ", ".trim($_keys[$keys]);
								if($tableFields[$_keys[$keys]]=='int' || $tableFields[$_keys[$keys]]=='real'){
										if(strpos($_keys[$keys], "_id")){
											$_values .= ", ".($_reqvalue)*1;
											//echo "decode [$_values]<BR>";
										}else{		
											$_values .= ", ".$_reqvalue*1;
										}
								}else if($tableFields[$_keys[$keys]]=='string' || $tableFields[$_keys[$keys]]=='blob'){ 
									$_values .= ", '".trim($_reqvalue)."'	";
								}else if($tableFields[$_keys[$keys]]=='datetime'){ 
										if($_reqvalue=="NOW()"){
											$_values .= ", $_reqvalue";
										}else{
												$_values .= ", '".date('Y-m-d H:i:s',strtotime($_reqvalue))."'";                                                                                                       
										}
								}else  if($tableFields[$_keys[$keys]]=='date'){ 
									if($_reqvalue=="NOW()"){
										$_values .= ", $_reqvalue";
									}else{
										$_values .= ", '".date('Y-m-d',strtotime($_reqvalue))."'";     
									}
								}else  if($tableFields[$_keys[$keys]]=='time'){ 
										$_values .= ",'".$_reqvalue."'";
								}
								$append_index++;
							}
                                             
					}

				}		 

				$_sql = "INSERT INTO {$this->databaseName}.".$this->tableName." ($_fields) VALUES ($_values);	";
				return $_sql;

			}
			function trx_servicecreates($_PARAM, $_size){     			
							//echo "servicecreates ($_size)<BR>";
							$_sql = $this->trx_sqlCreatesGenerator($_PARAM, $_size); 
					 		if(empty($_sql))
								return NULL;
						    
							$_sqls = explode(";", $_sql);
							for($i=0;$i<sizeof($_sqls);$i++){
								if(!empty($_sqls[$i]) && trim($_sqls[$i])!=""){ 
									$retrows = $this->Execute_Query($_sqls[$i]);
									//echo "($retrows)[$i]{$_sqls[$i]}<br>";
									$mysqli_insert_id = $GLOBALS["_connection"]->insert_id;
									if(!$retrows){ 
										$message = "SQL Error!!";
										if($_SERVER['SERVER_NAME']=='localhost'){
											$message = "MySQL [".$GLOBALS["__MYSQLDB"]["DB_NAME"]."] Error [{$_sqls[$i]}]";
										}
										throw new Exception($message);	
									}           
									$_PARAM["UPDATE_RETURN"][$i] = $mysqli_insert_id;
								} 	 	                    			    
							}

							return $_PARAM;          		
							
			}  
			
			function trx_sqlCreatesGenerator($_PARAM, $_size){
				//echo "create [{$service_insert_tables[0]}] sqlGenerator<br>";
				//echo "create [{$service_insert_tables[0]}] sqlGenerator<br>";
				//echo "tablesField >>".$this->tablesField[$service_insert_tables[0]]."<BR>";		 
				if(empty($_size))
					return;
				$tableFields = $this->tablesField[$this->tableName];
				$_keys = array_keys($tableFields);
				if(empty($_keys) || empty($tableFields) && $_size<1)
					return NULL;
				$append_index = 0;
				
				for($l=0;$l<$_size;$l++){

					$_jump_fields = array("sys_del_flag","server_id", "instance_server_id", "instance_server_channel_id","remote_ip", "created", "created_by", "last_upd", "last_upd_by");
					$_fields = "sys_del_flag,server_id, instance_server_id, instance_server_channel_id, remote_ip,created, created_by, last_upd, last_upd_by ";
					$_values = "'N','{$_REQUEST['server_id']}', '{$_REQUEST['instance_server_id']}', '{$_REQUEST['instance_server_channel_id']}', '{$_SERVER['REMOTE_ADDR']}', NOW(), '{$_REQUEST['identify_user_id']}', NOW(), '{$_REQUEST['identify_user_id']}' ";
					
					for($keys=0; $keys<sizeof($_keys);$keys++){       
					//echo "??????? Field >>".$_keys[$keys]."<br>";    
					//	if($_PARAM[$_keys[$keys]][$l]!=NULL && $_PARAM[$_keys[$keys]][$l]!=""){
						$_reqkey = $_keys[$keys];
						if(!empty($_PARAM[$_keys[$keys]])){							
							if(!in_array($_reqkey,$_jump_fields) && is_array($_PARAM[$_keys[$keys]])){
								//echo "array __PARAM[".$_keys[$keys]."] case  array 1.<br>";
								$_reqvalue = $_PARAM[$_keys[$keys]][$l]; 
								//echo $_keys[$keys]."[$l] = [$_reqvalue]<br>";
							}else{
								//echo "not array __PARAM[".$_keys[$keys]."] case not array 2.<br>"; 
								$_reqvalue = $_PARAM[$_keys[$keys]];
							}
							
							if(!in_array($_reqkey,$_jump_fields)  && strpos($_keys[$keys], "_id")){
								$_reqvalue = (trim($_reqvalue));
							}if(!in_array($_reqkey,$_jump_fields)  && strpos($_keys[$keys], "_psw")){
								$_reqvalue = md5(trim($_reqvalue));
							}else{
								$_reqvalue = trim($_reqvalue); 
							}
							if(!in_array($_reqkey,$_jump_fields)){
							$_fields .= ", ".trim($_keys[$keys]);
							if($tableFields[$_keys[$keys]]=='int' || $tableFields[$_keys[$keys]]=='real'){
								$_values .= ", ".trim($_reqvalue)*1;
							}else if($tableFields[$_keys[$keys]]=='string' || $tableFields[$_keys[$keys]]=='blob'){
								if(strpos($_keys[$keys], "_psw")){
									$_values .= ", '".md5(trim($_reqvalue))."'";
								}else{
									$_values .= ", '".trim($_reqvalue)."'";
								}
							}else if($tableFields[$_keys[$keys]]=='datetime'){
										if($_reqvalue=="NOW()"){
											$_values .= ", $_reqvalue";
										}else{
												$_values .= ", '".anyDate('Y-m-d H:i:s',$_reqvalue)."'";                                                                                                       
										}
							}else  if($tableFields[$_keys[$keys]]=='date'){ 
									if($_reqvalue=="NOW()"){
										$_values .= ", $_reqvalue";
									}else{
										$_values .= ", '".anyDate('Y-m-d',$_reqvalue)."'";     
									}
							}else  if($tableFields[$_keys[$keys]]=='time'){ 
									if($_reqvalue=="NOW()"){
										$_values .= ", $_reqvalue";
									}else{
										$_values .= ",'".$_reqvalue."'";		
									}
							}else{
								echo "!!! invalid type [".$tableFields[$_keys[$keys]]."]<br>";
							}   
							
							$append_index++;
							
						} 
						}
					}
					
					$_sql .= "INSERT INTO {$GLOBALS["instanceServer"]["instance_server_dbn"]}.".$this->tableName." ($_fields) VALUES ($_values);\n";
					
				}  
				
				return $_sql;
				
			}

			function trx_serviceupdate($_PARAM){
				//print_r($_PARAM);
				$_sql = $this->trx_sqlUpdateGenerator($_PARAM);      
				// echo $_sql."<BR><hr>";
				
				if(empty($_sql)) 
					return NULL;
				 $retrows = $this->Execute_Query($_sql);
				if($retrows<0){ 
					$message = "SQL Error!!";
					if($_SERVER['SERVER_NAME']=='localhost'){
						$message = "MySQL [".$GLOBALS["__MYSQLDB"]["DB_NAME"]."] Error [{$_sql}]";
					}
					throw new Exception($message);	
				}          
				return $retrows;
			}

			function trx_serviceupdateTest($_PARAM){
				//print_r($_PARAM);
				$_sql = $this->trx_sqlUpdateGenerator($_PARAM);      
				//echo $_sql."<BR>";
				$jsonResponse['sql'] = $_sql;
				if(empty($_sql)) 
					return NULL;
				 $retrows = $this->Execute_Query($_sql);
				if($retrows<0){ 
					$message = "SQL Error!!";
					if($_SERVER['SERVER_NAME']=='localhost'){
						$message = "MySQL [".$GLOBALS["__MYSQLDB"]["DB_NAME"]."] Error [{$_sql}]";
					}
					throw new Exception($message);	
				}          
				return $_sql;
			}
			
			function trx_sqlUpdateGenerator($_PARAM){
				
				//echo "<hr>";
				//print_r($_PARAM);
				//echo "<hr>";
				
				$_fields = "";
				$_values = "";
				$_keys = array_keys($this->tablesField[$this->tableName]);
				$tableFields = $this->tablesField[$this->tableName];
				if(empty($_keys) || empty($tableFields))
					return NULL;
				
				////print_r($tableFields);
				$append_index = 0;
				
				//echo "usergroup_desc >>(".$_PARAM['usergroup_desc'].")<br>";
				
				$_req_keys = array_keys($_PARAM);
				for($p=0;$p<sizeof($_req_keys);$p++){
					$_reqkeys[$_req_keys[$p]] = $_req_keys[$p];
				}
				
				for($keys=0;$keys<sizeof($_keys);$keys++){
					
					//echo "ทดสอบ [{$_keys[$keys]}] value is [{$_PARAM[$_keys[$keys]]}] <br>";
					//if($_PARAM[$_keys[$keys]]!=NULL && $_PARAM[$_keys[$keys]]!="" && $_keys[$keys]!=$this->tablePrimaryKey){
					//echo $_keys[$keys].">>".$_reqkeys[$_keys[$keys]]."<BR>";
							
					if($_reqkeys[$_keys[$keys]]!="" && $_keys[$keys]!=$this->tablePrimaryKey && isset($_PARAM[$_keys[$keys]])){
							
						//echo "++ append value+field type [{$keys}] [{$_keys[$keys]}] [{$tableFields[$_keys[$keys]]}]<br>";
							
						if($tableFields[$_keys[$keys]]=='int' || $tableFields[$_keys[$keys]]=='real'){
							
							if(strpos($_keys[$keys], "_id")){
								if($_fields=="")
									$_fields .= "".$_keys[$keys]." = '".(trim($_PARAM[$_keys[$keys]]))."'";
								else
									$_fields .= ", ".$_keys[$keys]." = '".(trim($_PARAM[$_keys[$keys]]))."'";
							}else{
								if($_fields=="")
									$_fields .= "".$_keys[$keys]." = ".trim($_PARAM[$_keys[$keys]])*1;
								else
									$_fields .= ", ".$_keys[$keys]." = ".trim($_PARAM[$_keys[$keys]])*1;
							}
							
						}else if($tableFields[$_keys[$keys]]=='string' || $tableFields[$_keys[$keys]]=='blob'){
							if(strpos($_keys[$keys], "_psw")){
								if($_fields=="")
									$_fields .= " ".$_keys[$keys]." = '".md5(trim($_PARAM[$_keys[$keys]]))."'";
								else
									$_fields .= ", ".$_keys[$keys]." = '".md5(trim($_PARAM[$_keys[$keys]]))."'";
							}else{
								if($_fields=="")
									$_fields .= " ".$_keys[$keys]." = '".trim($_PARAM[$_keys[$keys]])."'";
								else
									$_fields .= ", ".$_keys[$keys]." = '".trim($_PARAM[$_keys[$keys]])."'";
							}
						}else if($tableFields[$_keys[$keys]]=='datetime'){ 
									if($_PARAM[$_keys[$keys]]==""){ $_PARAM[$_keys[$keys]]="NULL"; }
									if($_PARAM[$_keys[$keys]]=='NOW()'||$_PARAM[$_keys[$keys]]=='NULL'){
										if($_fields=="")
												$_fields .= " ".$_keys[$keys]." = ".$_PARAM[$_keys[$keys]];
										else 
											$_fields .= ", ".$_keys[$keys]." = ".$_PARAM[$_keys[$keys]];
									}else{
										if($_fields=="")
												$_fields .= " ".$_keys[$keys]." = '".anyDate('Y-m-d H:i:s',$_PARAM[$_keys[$keys]])."' ";
										else 
											$_fields .= ", ".$_keys[$keys]." = '".anyDate('Y-m-d H:i:s',$_PARAM[$_keys[$keys]])."' ";
									}
						}else if($tableFields[$_keys[$keys]]=='date'){ 
									if($_PARAM[$_keys[$keys]]==""){ $_PARAM[$_keys[$keys]]="NULL"; }
									if($_PARAM[$_keys[$keys]]=='NOW()'||$_PARAM[$_keys[$keys]]=='NULL'){
										if($_fields=="")
												$_fields .= " ".$_keys[$keys]." = ".$_PARAM[$_keys[$keys]];
										else 
											$_fields .= ", ".$_keys[$keys]." = ".$_PARAM[$_keys[$keys]];
									}else{
										if($_fields=="")
												$_fields .= " ".$_keys[$keys]." = '".anyDate('Y-m-d',$_PARAM[$_keys[$keys]])."' ";
										else 
											$_fields .= ", ".$_keys[$keys]." = '".anyDate('Y-m-d',$_PARAM[$_keys[$keys]])."' ";
									}	
						}else if($tableFields[$_keys[$keys]]=='time'){ 
									if($_PARAM[$_keys[$keys]]==""){ $_PARAM[$_keys[$keys]]="NULL"; }
									if($_PARAM[$_keys[$keys]]=='NOW()'||$_PARAM[$_keys[$keys]]=='NULL'){
										if($_fields=="")
											$_fields .= " ".$_keys[$keys]." = ".$_PARAM[$_keys[$keys]];
										else 
											$_fields .= ", ".$_keys[$keys]." = ".$_PARAM[$_keys[$keys]];
									}else{
										if($_fields=="")
											$_fields .= " ".$_keys[$keys]." = '".$_PARAM[$_keys[$keys]]."'";
										else 
											$_fields .= ", ".$_keys[$keys]." = '".$_PARAM[$_keys[$keys]]."'";
									}
						}
						$append_index++;
					}
				}
				
				$tableFields = $this->tablesField[$this->tableName];
				//echo "\$tableFields >>".$tableFields[$this->tablePrimaryKey]."<BR>";
				
				$_sql = " UPDATE  {$GLOBALS["instanceServer"]["instance_server_dbn"]}.".$this->tableName."  set $_fields , last_upd_by = '{$_REQUEST['identify_user_id']}' , last_upd = NOW() ";
								 
				if($tableFields[$this->tablePrimaryKey]=="string")
						$_sql .= "	   where ".$this->tablePrimaryKey." = '".($_PARAM[$this->tablePrimaryKey])."' 
						AND server_id = '{$_REQUEST['server_id']}' 
						AND instance_server_id = '{$_REQUEST['instance_server_id']}'
						AND instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}'  ";
				else if($tableFields[$this->tablePrimaryKey]=="int")
						$_sql .= "	   where ".$this->tablePrimaryKey." = '".($_PARAM[$this->tablePrimaryKey])."' 
						AND server_id = '{$_REQUEST['server_id']}' 
						AND instance_server_id = '{$_REQUEST['instance_server_id']}'
						AND instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}'  ";
				return $_sql;
			}	  
                          
			function trx_serviceget($_PARAM){      
				if(empty($_PARAM[$this->tablePrimaryKey]))
					throw new Exception("NULL PARAMETER[".$this->tablePrimaryKey."]values(".$_PARAM[$this->tablePrimaryKey].") ++ ");
				//echo "tablePrimaryKey >>[".$this->tablePrimaryKey."]<br>";
			    
				$tableFields = $this->tablesField[$this->tableName];
				//echo "\$tableFields >>".$tableFields[$this->tablePrimaryKey]."<BR>";
				
			    $_sql =" SELECT * FROM {$GLOBALS["instanceServer"]["instance_server_dbn"]}.".$this->tableName." _table  ";
				$_sql .= "  WHERE  _table.".$this->tablePrimaryKey." = '".($_PARAM[$this->tablePrimaryKey])."' 
				AND _table.server_id = '{$_REQUEST['server_id']}' 
				AND _table.instance_server_id = '{$_REQUEST['instance_server_id']}'
				AND _table.instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}' ";	
				//echo "$_sql<br>";
  

				$retArray = $this->_sqlget($_sql);       
				
				if(is_array($retArray) && is_array($_PARAM)){
					$_PARAM = array_merge($_PARAM, $retArray); 
					return $_PARAM;
				}
 
				return NULL;
			}
			
			function trx_deleted($_PARAM){     
				$_sql=" UPDATE {$GLOBALS["instanceServer"]["instance_server_dbn"]}.".$this->tableName."  SET sys_del_flag = 'Y'  WHERE  ".$this->tablePrimaryKey." = '{$_PARAM[$this->tablePrimaryKey]}' 
				AND server_id = '{$_REQUEST['server_id']}' 
				AND instance_server_id = '{$_REQUEST['instance_server_id']}'
				AND instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}' ";	
				//echo "$_sql<br>";  
				//$this->Execute_Query("SET character_set_results=utf8");
				$_sql_rets = $this->Execute_Query($_sql);				 
				if(isset($_sql_rets) && $_sql_rets!=0){         
						return true;            
				}                                
				return false;       
		}		

		
		function listChilds($_PARAM, $keys, $runOnDb){
					if(empty($runOnDb)) 
								return "Invalid Database Name Target"; 
					if(empty($this->tableName)) 
								return NULL;
					$_sql=" SELECT * FROM {$runOnDb}.".$this->tableName."  ";	
					$_sql.=" WHERE  sys_del_flag='N' AND server_id = '{$_PARAM['server_id']}'  ";
					for($i=0;$i<sizeof($keys);$i++){
						$_sql.="  AND {$keys[$i]} = '{$_PARAM[$keys[$i]]}'  ";
					}
					//echo $_sql;
					return $this->_sqllists($_sql); 

			}


			function getByUniqueFields($_PARAM, $keys){       
			    $_sql =" SELECT * FROM ".$this->tableName." _table  ";
				$_sql .= "  WHERE  _table.server_id = '{$_REQUEST['server_id']}'  ";	  
				$_sql .= "  AND  _table.instance_server_id = '{$_REQUEST['instance_server_id']}'  ";	  
				$_sql .= "  AND  _table.instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}'  ";	  
				$_sql .= "  AND  _table.sys_del_flag = 'N'  ";	    
				$keyCode = "";
				for($i=0;$i<sizeof($keys);$i++){
						if($i==0)
							$keyCode = trim($_PARAM[$keys[$i]]);
						else
							$keyCode .="-".trim($_PARAM[$keys[$i]]);
						$_sql.="  AND _table.{$keys[$i]} = '".trim($_PARAM[$keys[$i]])."'  ";
				}     
				$_sql .= "  ORDER BY order_no  ";	
				//echo "$_sql<br>";
				$obj = $this->_sqlget($_sql); 
				if(!empty($obj)){
					$retObj[$keyCode] = $obj;
					return $retObj;
				}
				return null;
			}
		     
			function getAllUniqueFields($_PARAM, $key, $wKeys){       
			    $_sql =" SELECT * FROM ".$this->tableName." _table  ";
				$_sql .= "  WHERE  _table.server_id = '{$_REQUEST['server_id']}'  ";	  
				$_sql .= "  AND  _table.instance_server_id = '{$_REQUEST['instance_server_id']}'  ";	  
				$_sql .= "  AND  _table.instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}'  ";	   
				$_sql .= "  AND  _table.sys_del_flag = 'N'  ";	
				if(!empty($wKeys)){
					$keys = array_keys($wKeys);
					for($i=0;$i<sizeof($keys);$i++){
						$_sql .= "  AND  _table.{$keys[$i]} = '{$wKeys[$keys[$i]]}'  ";	   
					} 
				}
				$_sql .= "  ORDER BY order_no  ";	
				//echo "$_sql<br>";
				$lists = $this->_sqllists($_sql); 
				$retLists = array();
				if(!empty($lists)){
					for($i=0;$i<sizeof($lists);$i++){
						$retLists[$lists[$i][$key]] = $lists[$i];
					}
				}
				return $retLists;
			}
				
 

		 
  
			
			function servicecreate($_PARAM){     
					if($_REQUEST['_debug']=="Y"){
						print_r($_PARAM); 
						echo "<hr>";
					}
					if(empty($_PARAM[$this->tablePrimaryKey])){
						$_PARAM[$this->tablePrimaryKey] = $this->getPrimaryKey(12);
					}
					$_sql = $this->sqlCreateGenerator($_PARAM); 
					if($_REQUEST['_debug']=="Y"){
						echo "$_sql<br>";
						echo "<hr>";
					} 
					if(empty($_sql))
						return NULL;			              
						
					$retrows = $this->Execute_Query($_sql); 
					//echo "\$mysqli_insert_id >>[{$retrows}]$_sql<br>";        
					if(!$retrows || $retrows ==-1){ 
						$message = "SQL Error!!";
						if($_SERVER['SERVER_NAME']=='localhost'){
							$message = "MySQL [".$GLOBALS["__MYSQLDB"]["DB_NAME"]."] Error [{$_sql}]";
						}
						throw new Exception($message);
					}  
					//echo "ID >> $mysqli_insert_id<br>"; 
					if(empty($retrows))
						return NULL;
					return $_PARAM;         
			}           
			
			function sqlCreateGenerator($_PARAM){  
				if($this->tableName=="")
					return NULL;  
				$_jump_fields = array("sys_del_flag","server_id","instance_server_id","instance_server_channel_id",  "remote_ip", "created", "created_by", "last_upd", "last_upd_by");
				$_fields = "{$this->tablePrimaryKey}, sys_del_flag,server_id,instance_server_id,instance_server_channel_id, remote_ip,created, created_by, last_upd, last_upd_by ";
				$_values = "'{$_PARAM[$this->tablePrimaryKey]}', 'N', '{$_REQUEST['server_id']}', '{$_REQUEST['instance_server_id']}', '{$_REQUEST['instance_server_channel_id']}', '{$_SERVER['REMOTE_ADDR']}', NOW(), '{$_REQUEST['identify_user_id']}', NOW(), '{$_REQUEST['identify_user_id']}'";
				//echo "\$this->tableName >>[".$this->tableName."]<br>";
				//print_r($this->tablesField);
				//echo "<br>";
				$_keys = array_keys($this->tablesField[$this->tableName]);
				$tableFields = $this->tablesField[$this->tableName];
				if(empty($_keys) || empty($tableFields))
					return NULL;
				////print_r($tableFields);
				$append_index = 0;			
				for($keys=0;$keys<sizeof($_keys);$keys++){               
					if($_keys[$keys]!=$this->tablePrimaryKey){ 
		 					//echo "??????? [{$_keys[$keys]}] value is [{$_PARAM[$_keys[$keys]]}] <br>"; 
							$_reqvalue = "";
							$_reqkey = $_keys[$keys];
							if(!in_array($_reqkey, $_jump_fields) && strpos($_reqkey, "_id")){
								//echo "[$_reqkey] case 1<BR>";
								$_reqvalue = trim($_PARAM[$_reqkey]);
							}else if(!in_array($_reqkey, $_jump_fields) && strpos($_reqkey, "_psw")){
								$_reqvalue = md5(trim($_PARAM[$_reqkey]));
							}else if(!in_array($_reqkey, $_jump_fields)){
								$_reqvalue = trim($_PARAM[$_reqkey]);
							}
							
							if(!in_array($_reqkey, $_jump_fields) && $_PARAM[$_keys[$keys]]!=NULL && $_PARAM[$_keys[$keys]]!=""){
								//echo "append value+field type [{$keys}] [{$_keys[$keys]}] [{$tableFields[$_keys[$keys]]}]<br>";
								$_fields .= ", `".trim($_keys[$keys])."`";
								if($tableFields[$_keys[$keys]]=='int' || $tableFields[$_keys[$keys]]=='real'){
										if(strpos($_keys[$keys], "_id")){
											$_values .= ", ".($_reqvalue)*1;
											//echo "decode [$_values]<BR>";
										}else{		
											$_values .= ", ".$_reqvalue*1;
										}
								}else if($tableFields[$_keys[$keys]]=='string' || $tableFields[$_keys[$keys]]=='blob'){ 
									$_values .= ", '".trim($_reqvalue)."'	";
								}else if($tableFields[$_keys[$keys]]=='datetime'){ 
										if($_reqvalue=="NOW()"){
											$_values .= ", $_reqvalue";
										}else{
												$_values .= ", '".anyDate('Y-m-d H:i:s',$_reqvalue)."'";                                                                                                       
										}
								}else  if($tableFields[$_keys[$keys]]=='date'){ 
									if($_reqvalue=="NOW()"){
										$_values .= ", $_reqvalue";
									}else{
										$_values .= ", '".anyDate('Y-m-d',$_reqvalue)."'";     
									}
								}else  if($tableFields[$_keys[$keys]]=='time'){ 
										$_values .= ",'".$_reqvalue."'";
								}
								$append_index++;
							}
                                         
					}

				}		 

				$_sql = "INSERT INTO ".$this->tableName." ($_fields) VALUES ($_values);	";
				return $_sql;

			}

			function servicecreatetest($_PARAM){     
				if($_REQUEST['_debug']=="Y"){
					print_r($_PARAM); 
					echo "<hr>";
				}
				if(empty($_PARAM[$this->tablePrimaryKey])){
					$_PARAM[$this->tablePrimaryKey] = $this->getPrimaryKey(12);
				}
				$_sql = $this->sqlCreateGeneratortest($_PARAM); 
				if($_REQUEST['_debug']=="Y"){
					echo "$_sql<br>";
					echo "<hr>";
				} 
				if(empty($_sql))
					return NULL;			              
					
				$retrows = $this->Execute_Query($_sql); 
				//echo "\$mysqli_insert_id >>[{$retrows}]$_sql<br>";        
				if(!$retrows || $retrows ==-1){ 
					$message = "SQL Error!!";
					if($_SERVER['SERVER_NAME']=='localhost'){
						$message = "MySQL [".$GLOBALS["__MYSQLDB"]["DB_NAME"]."] Error [{$_sql}]";
					}
					throw new Exception($message);
				}  
				//echo "ID >> $mysqli_insert_id<br>"; 
				if(empty($retrows))
					return NULL;
				return $_PARAM;         
		}           
		
		function sqlCreateGeneratortest($_PARAM){  
			if($this->tableName=="")
				return NULL;  
			$_jump_fields = array("sys_del_flag","server_id","instance_server_id","instance_server_channel_id",  "remote_ip", "created", "created_by", "last_upd", "last_upd_by");
			$_fields = "{$this->tablePrimaryKey}, sys_del_flag,server_id,instance_server_id,instance_server_channel_id, remote_ip,created, created_by, last_upd, last_upd_by ";
			$_values = "'{$_PARAM[$this->tablePrimaryKey]}', '{$_PARAM['sys_del_flag']}', '{$_REQUEST['server_id']}', '{$_PARAM['instance_server_id']}', '{$_PARAM['instance_server_channel_id']}', '{$_SERVER['REMOTE_ADDR']}', NOW(), '{$_REQUEST['identify_user_id']}', NOW(), '{$_REQUEST['identify_user_id']}'";
			//echo "\$this->tableName >>[".$this->tableName."]<br>";
			//print_r($this->tablesField);
			//echo "<br>";
			$_keys = array_keys($this->tablesField[$this->tableName]);
			$tableFields = $this->tablesField[$this->tableName];
			if(empty($_keys) || empty($tableFields))
				return NULL;
			////print_r($tableFields);
			$append_index = 0;			
			for($keys=0;$keys<sizeof($_keys);$keys++){               
				if($_keys[$keys]!=$this->tablePrimaryKey){ 
						 //echo "??????? [{$_keys[$keys]}] value is [{$_PARAM[$_keys[$keys]]}] <br>"; 
						$_reqvalue = "";
						$_reqkey = $_keys[$keys];
						if(!in_array($_reqkey, $_jump_fields) && strpos($_reqkey, "_id")){
							//echo "[$_reqkey] case 1<BR>";
							$_reqvalue = trim($_PARAM[$_reqkey]);
						}else if(!in_array($_reqkey, $_jump_fields) && strpos($_reqkey, "_psw")){
							$_reqvalue = md5(trim($_PARAM[$_reqkey]));
						}else if(!in_array($_reqkey, $_jump_fields)){
							$_reqvalue = trim($_PARAM[$_reqkey]);
						}
						
						if(!in_array($_reqkey, $_jump_fields) && $_PARAM[$_keys[$keys]]!=NULL && $_PARAM[$_keys[$keys]]!=""){
							//echo "append value+field type [{$keys}] [{$_keys[$keys]}] [{$tableFields[$_keys[$keys]]}]<br>";
							$_fields .= ", ".trim($_keys[$keys]);
							if($tableFields[$_keys[$keys]]=='int' || $tableFields[$_keys[$keys]]=='real'){
									if(strpos($_keys[$keys], "_id")){
										$_values .= ", ".($_reqvalue)*1;
										//echo "decode [$_values]<BR>";
									}else{		
										$_values .= ", ".$_reqvalue*1;
									}
							}else if($tableFields[$_keys[$keys]]=='string' || $tableFields[$_keys[$keys]]=='blob'){ 
								$_values .= ", '".trim($_reqvalue)."'	";
							}else if($tableFields[$_keys[$keys]]=='datetime'){ 
									if($_reqvalue=="NOW()"){
										$_values .= ", $_reqvalue";
									}else{
											$_values .= ", '".anyDate('Y-m-d H:i:s',$_reqvalue)."'";                                                                                                       
									}
							}else  if($tableFields[$_keys[$keys]]=='date'){ 
								if($_reqvalue=="NOW()"){
									$_values .= ", $_reqvalue";
								}else{
									$_values .= ", '".anyDate('Y-m-d',$_reqvalue)."'";     
								}
							}else  if($tableFields[$_keys[$keys]]=='time'){ 
									$_values .= ",'".$_reqvalue."'";
							}
							$append_index++;
						}
									 
				}

			}		 

			$_sql = "INSERT INTO ".$this->tableName." ($_fields) VALUES ($_values);	";
			return $_sql;

		}
			
			

			function servicecreates($_PARAM, $_size){     			
							//echo "servicecreates ($_size)<BR>";
							$_sql = $this->sqlCreatesGenerator($_PARAM, $_size); 
					 		if(empty($_sql))
								return NULL;
						     
							$_sqls = explode(";", $_sql);
							for($i=0;$i<sizeof($_sqls);$i++){
								if(!empty($_sqls[$i]) && trim($_sqls[$i])!=""){ 
									$retrows = $this->Execute_Query($_sqls[$i]);
									//echo "($retrows)[$i]{$_sqls[$i]}<br>";
									$mysqli_insert_id = $GLOBALS["_connection"]->insert_id;
									if(!$retrows){ 
										$message = "SQL Error!!";
										if($_SERVER['SERVER_NAME']=='localhost'){
											$message = "MySQL [".$GLOBALS["__MYSQLDB"]["DB_NAME"]."] Error [{$_sqls[$i]}]";
										}
										throw new Exception($message);
									}     

									$_PARAM["UPDATE_RETURN"][$i] = $mysqli_insert_id;
								} 	 	                    			    
							}

							return $_PARAM;          		
							
			}  
			
			function sqlCreatesGenerator($_PARAM, $_size){
				
				if(empty($_size))
					return;
				$tableFields = $this->tablesField[$this->tableName];
				$_keys = array_keys($tableFields);
				if(empty($_keys) || empty($tableFields) && $_size<1)
					return NULL;
				$append_index = 0;
				
				for($l=0;$l<$_size;$l++){
					
					if(empty($_PARAM[$this->tablePrimaryKey])){
								$_PARAM[$l][$this->tablePrimaryKey] = $this->getPrimaryKey(12);
					} 

					$_jump_fields = array("sys_del_flag","server_id", "instance_server_id", "instance_server_channel_id", "remote_ip", "created", "created_by", "last_upd", "last_upd_by");
					$_fields = "{$this->tablePrimaryKey}, sys_del_flag,server_id, instance_server_id, instance_server_channel_id, remote_ip,created, created_by, last_upd, last_upd_by ";
					$_values = "'{$_PARAM[$l][$this->tablePrimaryKey]}', 'N','{$_REQUEST['server_id']}', '{$_REQUEST['instance_server_id']}', '{$_REQUEST['instance_server_channel_id']}', '{$_SERVER['REMOTE_ADDR']}', NOW(), '{$_REQUEST['identify_user_id']}', NOW(), '{$_REQUEST['identify_user_id']}' ";
					
					for($keys=0; $keys<sizeof($_keys);$keys++){       
						// echo "Get  Value From Field >>".$_keys[$keys]."<br>";    
						//	if($_PARAM[$_keys[$keys]][$l]!=NULL && $_PARAM[$_keys[$keys]][$l]!=""){
						$_reqkey = $_keys[$keys];
						if(!empty($_PARAM[$_keys[$keys]])){							
							if(!in_array($_reqkey,$_jump_fields) && is_array($_PARAM[$_keys[$keys]])){
								//echo "array __PARAM[".$_keys[$keys]."] case  array 1.<br>";
								$_reqvalue = $_PARAM[$_keys[$keys]][$l];
								//echo $_keys[$keys]."[$l] = [$_reqvalue]<br>";
							}else{
								//echo "not array __PARAM[".$_keys[$keys]."] case not array 2.<br>"; 
								$_reqvalue = $_PARAM[$_keys[$keys]];
							}
							
							if(!in_array($_reqkey,$_jump_fields)  && strpos($_keys[$keys], "_id")){
								$_reqvalue = (trim($_reqvalue));
							}if(!in_array($_reqkey,$_jump_fields)  && strpos($_keys[$keys], "_psw")){
								$_reqvalue = md5(trim($_reqvalue));
							}else{
								$_reqvalue = trim($_reqvalue); 
							}
							if(!in_array($_reqkey,$_jump_fields)){
							$_fields .= ", ".trim($_keys[$keys]);
							if($tableFields[$_keys[$keys]]=='int' || $tableFields[$_keys[$keys]]=='real'){
								$_values .= ", ".trim($_reqvalue)*1;
							}else if($tableFields[$_keys[$keys]]=='string' || $tableFields[$_keys[$keys]]=='blob'){
								if(strpos($_keys[$keys], "_psw")){
									$_values .= ", '".md5(trim($_reqvalue))."'";
								}else{
									$_values .= ", '".trim($_reqvalue)."'";
								}
							}else if($tableFields[$_keys[$keys]]=='datetime'){
										if($_reqvalue=="NOW()"){
											$_values .= ", $_reqvalue";
										}else{
												$_values .= ", '".anyDate('Y-m-d H:i:s',$_reqvalue)."'";                                                                                                       
										}
							}else  if($tableFields[$_keys[$keys]]=='date'){ 
									if($_reqvalue=="NOW()"){
										$_values .= ", $_reqvalue";
									}else{
										$_values .= ", '".anyDate('Y-m-d',$_reqvalue)."'";     
									}
							}else  if($tableFields[$_keys[$keys]]=='time'){ 
									if($_reqvalue=="NOW()"){
										$_values .= ", $_reqvalue";
									}else{
										$_values .= ",'".$_reqvalue."'";		
									}
							}else{
								echo "!!! invalid type [".$tableFields[$_keys[$keys]]."]<br>";
							}   
							
							$append_index++;
							
						} 
						}
					}
					
					$_sql .= "INSERT INTO ".$this->tableName." ($_fields) VALUES ($_values);\n";
					
				}  
				
				return $_sql;
				
			}
			
			function server_servicecreate($_PARAM){    
				//print_r($_PARAM); 
				if(empty($_PARAM[$this->tablePrimaryKey])){
					$_PARAM[$this->tablePrimaryKey] = $this->getPrimaryKey(12);
				}  
				//print_r($_PARAM);
				$_sql = $this->sqlServerCreateGenerator($_PARAM); 
				// echo "$_sql<br>";
				if($_REQUEST['_debug']=="Y")
					echo "$_sql<br>";
				if(empty($_sql))
					return NULL;			              
					
				$GLOBALS["_connection"]->query($_sql);
				$retrows = $GLOBALS["_connection"]->affected_rows; 
				//echo "\$retrows >>[$retrows]$_sql<br>";
				if(!$retrows){ 
					$message = "SQL Error!!";
					if($_SERVER['SERVER_NAME']=='localhost'){
						$message = "MySQL [".$GLOBALS["__MYSQLDB"]["DB_NAME"]."] Error [{$_sql}]";
					}
					throw new Exception($message);
				}    
				//echo "ID >> $mysqli_insert_id<br>"; 
				return $_PARAM;         
			}           
			
			function sqlServerCreateGenerator($_PARAM){
				
				if($this->tableName=="")
					return NULL;
				$_jump_fields = array("sys_del_flag","server_id","remote_ip", "created", "created_by", "last_upd", "last_upd_by");
				$_fields = "{$this->tablePrimaryKey},sys_del_flag,server_id, remote_ip,created, created_by, last_upd, last_upd_by ";
				$_values = "'{$_PARAM[$this->tablePrimaryKey]}','N','{$_REQUEST['server_id']}', '{$_SERVER['REMOTE_ADDR']}', NOW(), '{$_REQUEST['identify_user_id']}', NOW(), '{$_REQUEST['identify_user_id']}' ";
				//echo "\$this->tableName >>[".$this->tableName."]<br>";
				//print_r($this->tablesField);
				//echo "<br>";
				$_keys = array_keys($this->tablesField[$this->tableName]);
				$tableFields = $this->tablesField[$this->tableName];
				if(empty($_keys) || empty($tableFields))
					return NULL;
				////print_r($tableFields);
				$append_index = 0;			
				for($keys=0;$keys<sizeof($_keys);$keys++){               
					if($_keys[$keys]!=$this->tablePrimaryKey){
						    
		 					//echo "??????? [{$_keys[$keys]}] value is [{$_PARAM[$_keys[$keys]]}] <br>";
							
							$_reqvalue = "";
							$_reqkey = $_keys[$keys];
							if(!in_array($_reqkey, $_jump_fields) && strpos($_reqkey, "_id")){
								//echo "[$_reqkey] case 1<BR>";
								$_reqvalue = trim($_PARAM[$_reqkey]);
							}else if(!in_array($_reqkey, $_jump_fields) && strpos($_reqkey, "_psw")){
								$_reqvalue = md5(trim($_PARAM[$_reqkey]));
							}else if(!in_array($_reqkey, $_jump_fields)){
								$_reqvalue = trim($_PARAM[$_reqkey]);
							}
							
							if(!in_array($_reqkey, $_jump_fields) && $_PARAM[$_keys[$keys]]!=NULL && $_PARAM[$_keys[$keys]]!=""){
								//echo "append value+field type [{$keys}] [{$_keys[$keys]}] [{$tableFields[$_keys[$keys]]}]<br>";
								$_fields .= ", ".trim($_keys[$keys]);
								if($tableFields[$_keys[$keys]]=='int' || $tableFields[$_keys[$keys]]=='real'){
										if(strpos($_keys[$keys], "_id")){
											$_values .= ", ".($_reqvalue)*1;
											//echo "decode [$_values]<BR>";
										}else{		
											$_values .= ", ".$_reqvalue*1;
										}
								}else if($tableFields[$_keys[$keys]]=='string' || $tableFields[$_keys[$keys]]=='blob'){ 
									$_values .= ", '".trim($_reqvalue)."'	";
								}else if($tableFields[$_keys[$keys]]=='datetime'){ 
										if($_reqvalue=="NOW()"){
											$_values .= ", $_reqvalue";
										}else{
												$_values .= ", '".anyDate('Y-m-d H:i:s',$_reqvalue)."'";                                                                                                       
										}
								}else  if($tableFields[$_keys[$keys]]=='date'){ 
									if($_reqvalue=="NOW()"){
										$_values .= ", $_reqvalue";
									}else{
										$_values .= ", '".anyDate('Y-m-d',$_reqvalue)."'";     
									}
								}else  if($tableFields[$_keys[$keys]]=='time'){ 
										$_values .= ",'".$_reqvalue."'";
								}
								$append_index++;
							}
                                         
					 }
				}		 

				$_sql = "INSERT INTO ".$this->tableName." ($_fields) VALUES ($_values);	";
				return $_sql;

			}

			function master_servicecreate($_PARAM){
				//print_r($_PARAM); 
				if(empty($_PARAM[$this->tablePrimaryKey])){
					$_PARAM[$this->tablePrimaryKey] = $this->getPrimaryKey(12);
				}  
				//print_r($_PARAM);
				$_sql = $this->sqlMasterCreateGenerator($_PARAM); 
				// echo "$_sql<br>";
				// echo $_sql;exit;
				if($_REQUEST['_debug']=="Y")
					echo "$_sql<br>";
				if(empty($_sql))
					return NULL;			              
					
				$GLOBALS["_connection"]->query($_sql);
				$retrows = $GLOBALS["_connection"]->affected_rows; 
				//echo "\$retrows >>[$retrows]$_sql<br>";
				if(!$retrows){ 
					$message = "SQL Error!!";
					if($_SERVER['SERVER_NAME']=='localhost'){
						$message = "MySQL [".$GLOBALS["__MYSQLDB"]["DB_NAME"]."] Error [{$_sql}]";
					}
					throw new Exception($message);
				}    
				//echo "ID >> $mysqli_insert_id<br>"; 
				return $_PARAM;         
			}
			
			function sqlMasterCreateGenerator($_PARAM){
				
				if($this->tableName=="")
					return NULL;
				$_jump_fields = array("server_id","remote_ip", "created", "created_by", "last_upd", "last_upd_by");
				$_fields = "{$this->tablePrimaryKey},server_id, remote_ip,created, created_by, last_upd, last_upd_by ";
				$_values = "'{$_PARAM[$this->tablePrimaryKey]}','{$_REQUEST['server_id']}', '{$_SERVER['REMOTE_ADDR']}', NOW(), '{$_REQUEST['identify_user_id']}', NOW(), '{$_REQUEST['identify_user_id']}' ";
				//echo "\$this->tableName >>[".$this->tableName."]<br>";
				//print_r($this->tablesField);
				//echo "<br>";
				$_keys = array_keys($this->tablesField[$this->tableName]);
				$tableFields = $this->tablesField[$this->tableName];
				if(empty($_keys) || empty($tableFields))
					return NULL;
				////print_r($tableFields);
				$append_index = 0;			
				for($keys=0;$keys<sizeof($_keys);$keys++){               
					if($_keys[$keys]!=$this->tablePrimaryKey){
						    
		 					//echo "??????? [{$_keys[$keys]}] value is [{$_PARAM[$_keys[$keys]]}] <br>";
							
							$_reqvalue = "";
							$_reqkey = $_keys[$keys];
							if(!in_array($_reqkey, $_jump_fields) && strpos($_reqkey, "_id")){
								//echo "[$_reqkey] case 1<BR>";
								$_reqvalue = trim($_PARAM[$_reqkey]);
							}else if(!in_array($_reqkey, $_jump_fields) && strpos($_reqkey, "_psw")){
								$_reqvalue = md5(trim($_PARAM[$_reqkey]));
							}else if(!in_array($_reqkey, $_jump_fields)){
								$_reqvalue = trim($_PARAM[$_reqkey]);
							}
							
							if(!in_array($_reqkey, $_jump_fields) && $_PARAM[$_keys[$keys]]!=NULL && $_PARAM[$_keys[$keys]]!=""){
								//echo "append value+field type [{$keys}] [{$_keys[$keys]}] [{$tableFields[$_keys[$keys]]}]<br>";
								$_fields .= ", ".trim($_keys[$keys]);
								if($tableFields[$_keys[$keys]]=='int' || $tableFields[$_keys[$keys]]=='real'){
										if(strpos($_keys[$keys], "_id")){
											$_values .= ", ".($_reqvalue)*1;
											//echo "decode [$_values]<BR>";
										}else{		
											$_values .= ", ".$_reqvalue*1;
										}
								}else if($tableFields[$_keys[$keys]]=='string' || $tableFields[$_keys[$keys]]=='blob'){ 
									$_values .= ", '".addslashes(trim($_reqvalue))."'	";
								}else if($tableFields[$_keys[$keys]]=='datetime'){ 
										if($_reqvalue=="NOW()"){
											$_values .= ", $_reqvalue";
										}else{
												$_values .= ", '".anyDate('Y-m-d H:i:s',$_reqvalue)."'";                                                                                                       
										}
								}else  if($tableFields[$_keys[$keys]]=='date'){ 
									if($_reqvalue=="NOW()"){
										$_values .= ", $_reqvalue";
									}else{
										$_values .= ", '".anyDate('Y-m-d',$_reqvalue)."'";     
									}
								}else  if($tableFields[$_keys[$keys]]=='time'){ 
										$_values .= ",'".$_reqvalue."'";
								}
								$append_index++;
							}
                                         
					 }
				}

				$_sql = "INSERT INTO ".$this->tableName." ($_fields) VALUES ($_values);	";
				return $_sql;
			}

			function server_servicecreates($_PARAM, $_size){     			
							//echo "servicecreates ($_size)<BR>";
							$_sql = $this->sqlServerCreatesGenerator($_PARAM, $_size); 
					 		if(empty($_sql))
								return NULL;
						    
							$_sqls = explode(";", $_sql);
							for($i=0;$i<sizeof($_sqls);$i++){
								if(!empty($_sqls[$i]) && trim($_sqls[$i])!=""){ 
									//echo "<hr>".$_sqls[$i]."<hr>";
									$retrows = $GLOBALS["_connection"]->query($_sqls[$i]); 
									$mysqli_insert_id = $GLOBALS["_connection"]->insert_id;
									if(!$retrows){ 
										$message = "SQL Error!!";
										if($_SERVER['SERVER_NAME']=='localhost'){
											$message = "MySQL [".$GLOBALS["__MYSQLDB"]["DB_NAME"]."] Error [{$_sqls[$i]}]";
										}
										throw new Exception($message);
									}    
									$_PARAM["UPDATE_RETURN"][$i] = $mysqli_insert_id;
								} 	 	                    			    
							}

							return $_PARAM;          		
							
			}  
			
			function sqlServerCreatesGenerator($_PARAM, $_size){
				//echo "create [{$service_insert_tables[0]}] sqlGenerator<br>";
				//echo "create [{$service_insert_tables[0]}] sqlGenerator<br>";
				//echo "tablesField >>".$this->tablesField[$service_insert_tables[0]]."<BR>";		
				

				if(empty($_size))
					return;
				$tableFields = $this->tablesField[$this->tableName];
				$_keys = array_keys($tableFields);
				if(empty($_keys) || empty($tableFields) && $_size<1)
					return NULL;
				$append_index = 0;
				
				for($l=0;$l<$_size;$l++){
					$_jump_fields = array("sys_del_flag","server_id","instance_server_id", "instance_server_channel_id","remote_ip", "created", "created_by", "last_upd", "last_upd_by");
					$_fields = "sys_del_flag,server_id, remote_ip,created, created_by, last_upd, last_upd_by ";
					$_values = "'N','{$_REQUEST['server_id']}', '{$_SERVER['REMOTE_ADDR']}', NOW(), '{$_REQUEST['identify_user_id']}', NOW(), '{$_REQUEST['identify_user_id']}' ";
					
					for($keys=0; $keys<sizeof($_keys);$keys++){       
					//echo "??????? Field >>".$_keys[$keys]."<br>";    
					//	if($_PARAM[$_keys[$keys]][$l]!=NULL && $_PARAM[$_keys[$keys]][$l]!=""){
						$_reqkey = $_keys[$keys];
						if(!empty($_PARAM[$_keys[$keys]])){							
							if(!in_array($_reqkey,$_jump_fields) && is_array($_PARAM[$_keys[$keys]])){
								//echo "array __PARAM[".$_keys[$keys]."] case  array 1.<br>";
								$_reqvalue = $_PARAM[$_keys[$keys]][$l];
								//echo $_keys[$keys]."[$l] = [$_reqvalue]<br>";
							}else{
								//echo "not array __PARAM[".$_keys[$keys]."] case not array 2.<br>"; 
								$_reqvalue = $_PARAM[$_keys[$keys]];
							}
							
							if(!in_array($_reqkey,$_jump_fields)  && strpos($_keys[$keys], "_id")){
								$_reqvalue = (trim($_reqvalue));
							}if(!in_array($_reqkey,$_jump_fields)  && strpos($_keys[$keys], "_psw")){
								$_reqvalue = md5(trim($_reqvalue));
							}else{
								$_reqvalue = trim($_reqvalue); 
							}
							if(!in_array($_reqkey,$_jump_fields)){
							$_fields .= ", ".trim($_keys[$keys]);
							if($tableFields[$_keys[$keys]]=='int' || $tableFields[$_keys[$keys]]=='real'){
								$_values .= ", ".trim($_reqvalue)*1;
							}else if($tableFields[$_keys[$keys]]=='string' || $tableFields[$_keys[$keys]]=='blob'){
								if(strpos($_keys[$keys], "_psw")){
									$_values .= ", '".md5(trim($_reqvalue))."'";
								}else{
									$_values .= ", '".trim($_reqvalue)."'";
								}
							}else if($tableFields[$_keys[$keys]]=='datetime'){
										if($_reqvalue==""){ $_reqvalue="NULL"; }
										if($_reqvalue=="NOW()"||$_reqvalue=="NULL"){
											$_values .= ", $_reqvalue";
										}else{
												$_values .= ", '".anyDate('Y-m-d H:i:s',$_reqvalue)."'";                                                                                                       
										}
							}else  if($tableFields[$_keys[$keys]]=='date'){ 
									if($_reqvalue==""){ $_reqvalue="NULL"; }
									if($_reqvalue=="NOW()"||$_reqvalue=="NULL"){
										$_values .= ", $_reqvalue";
									}else{
										$_values .= ", '".anyDate('Y-m-d',$_reqvalue)."'";     
									}
							}else  if($tableFields[$_keys[$keys]]=='time'){ 
									if($_reqvalue==""){ $_reqvalue="NULL"; }
									if($_reqvalue=="NOW()"||$_reqvalue=="NULL"){
										$_values .= ", $_reqvalue";
									}else{
										$_values .= ",'".$_reqvalue."'";		
									}
							}else{
								echo "!!! invalid type [".$tableFields[$_keys[$keys]]."]<br>";
							}   
							
							$append_index++;
							
						} 
						}
					}
					
					$_sql .= "INSERT INTO ".$this->tableName." ($_fields) VALUES ($_values);\n";
					
				}  
				
				return $_sql;
				
			}
			
			function server_serviceupdate($_PARAM){
				//print_r($_PARAM);
				//$_PARAM["server_id"] = "10101010";
				$_sql = $this->sqlServerUpdateGenerator($_PARAM);      
				if($_REQUEST['_debug']=="Y")
					echo $_sql."<BR>"; 
				if(empty($_sql)) 
					return NULL;
		 		$retrows = $GLOBALS['_connection']->query($_sql);
				if($retrows<0){ 
					$message = "SQL Error!!";
					if($_SERVER['SERVER_NAME']=='localhost'){
						$message = "MySQL [".$GLOBALS["__MYSQLDB"]["DB_NAME"]."] Error [{$_sql}]";
					}
					throw new Exception($message);
				}          
				return $retrows;
			}
			
			function sqlServerUpdateGenerator($_PARAM){
				
				//echo "<hr>";
				//print_r($_PARAM);
				//echo "<hr>";
				
				$_fields = "";
				$_values = "";
				$_keys = array_keys($this->tablesField[$this->tableName]);
				$tableFields = $this->tablesField[$this->tableName];
				if(empty($_keys) || empty($tableFields))
					return NULL;
				
				////print_r($tableFields);
				$append_index = 0;
				
				//echo "usergroup_desc >>(".$_PARAM['usergroup_desc'].")<br>";
				
				$_req_keys = array_keys($_PARAM);
				for($p=0;$p<sizeof($_req_keys);$p++){
					$_reqkeys[$_req_keys[$p]] = $_req_keys[$p];
				}
				
				for($keys=0;$keys<sizeof($_keys);$keys++){
					
					//echo "ทดสอบ [{$_keys[$keys]}] value is [{$_PARAM[$_keys[$keys]]}] <br>";
					//if($_PARAM[$_keys[$keys]]!=NULL && $_PARAM[$_keys[$keys]]!="" && $_keys[$keys]!=$this->tablePrimaryKey){
					//echo $_keys[$keys].">>".$_reqkeys[$_keys[$keys]]."<BR>";
							
					if($_reqkeys[$_keys[$keys]]!="" && $_keys[$keys]!=$this->tablePrimaryKey && isset($_PARAM[$_keys[$keys]])){
							
						//echo "++ append value+field type [{$keys}] [{$_keys[$keys]}] [{$tableFields[$_keys[$keys]]}]<br>";
							
						if($tableFields[$_keys[$keys]]=='int' || $tableFields[$_keys[$keys]]=='real'){
							
							if(strpos($_keys[$keys], "_id")){
								if($_fields=="")
									$_fields .= "".$_keys[$keys]." = '".(trim($_PARAM[$_keys[$keys]]))."'";
								else
									$_fields .= ", ".$_keys[$keys]." = '".(trim($_PARAM[$_keys[$keys]]))."'";
							}else{
								if($_fields=="")
									$_fields .= "".$_keys[$keys]." = ".trim($_PARAM[$_keys[$keys]])*1;
								else
									$_fields .= ", ".$_keys[$keys]." = ".trim($_PARAM[$_keys[$keys]])*1;
							}
							
						}else if($tableFields[$_keys[$keys]]=='string' || $tableFields[$_keys[$keys]]=='blob'){
							if(strpos($_keys[$keys], "_psw")){
								if($_fields=="")
									$_fields .= " ".$_keys[$keys]." = '".md5(trim($_PARAM[$_keys[$keys]]))."'";
								else
									$_fields .= ", ".$_keys[$keys]." = '".md5(trim($_PARAM[$_keys[$keys]]))."'";
							}else{
								if($_fields=="")
									$_fields .= " ".$_keys[$keys]." = '".trim($_PARAM[$_keys[$keys]])."'";
								else
									$_fields .= ", ".$_keys[$keys]." = '".trim($_PARAM[$_keys[$keys]])."'";
							}
						}else if($_keys[$keys]!="created" && $tableFields[$_keys[$keys]]=='datetime'){ 
									if($_PARAM[$_keys[$keys]]==""){ $_PARAM[$_keys[$keys]]="NULL"; }
									if($_PARAM[$_keys[$keys]]=='NOW()'||$_PARAM[$_keys[$keys]]=='NULL'){
										if($_fields=="")
												$_fields .= " ".$_keys[$keys]." = ".$_PARAM[$_keys[$keys]];
										else 
											$_fields .= ", ".$_keys[$keys]." = ".$_PARAM[$_keys[$keys]];
									}else{
										if($_fields=="")
												$_fields .= " ".$_keys[$keys]." = '".anyDate('Y-m-d H:i:s',$_PARAM[$_keys[$keys]])."' ";
										else 
											$_fields .= ", ".$_keys[$keys]." = '".anyDate('Y-m-d H:i:s',$_PARAM[$_keys[$keys]])."' ";
									}
						}else if($tableFields[$_keys[$keys]]=='date'){ 
									if($_PARAM[$_keys[$keys]]==""){ $_PARAM[$_keys[$keys]]="NULL"; }
									if($_PARAM[$_keys[$keys]]=='NOW()'||$_PARAM[$_keys[$keys]]=='NULL'){
										if($_fields=="")
												$_fields .= " ".$_keys[$keys]." = ".$_PARAM[$_keys[$keys]];
										else 
											$_fields .= ", ".$_keys[$keys]." = ".$_PARAM[$_keys[$keys]];
									}else{
										if($_fields=="")
												$_fields .= " ".$_keys[$keys]." = '".anyDate('Y-m-d',$_PARAM[$_keys[$keys]])."' ";
										else 
											$_fields .= ", ".$_keys[$keys]." = '".anyDate('Y-m-d',$_PARAM[$_keys[$keys]])."' ";
									}	
						}else if($tableFields[$_keys[$keys]]=='time'){ 
									if($_PARAM[$_keys[$keys]]==""){ $_PARAM[$_keys[$keys]]="NULL"; }
									if($_PARAM[$_keys[$keys]]=='NOW()'||$_PARAM[$_keys[$keys]]=='NULL'){
										if($_fields=="")
											$_fields .= " ".$_keys[$keys]." = ".$_PARAM[$_keys[$keys]];
										else 
											$_fields .= ", ".$_keys[$keys]." = ".$_PARAM[$_keys[$keys]];
									}else{
										if($_fields=="")
											$_fields .= " ".$_keys[$keys]." = '".$_PARAM[$_keys[$keys]]."'";
										else 
											$_fields .= ", ".$_keys[$keys]." = '".$_PARAM[$_keys[$keys]]."'";
									}
						}
						$append_index++;
					}
				}
				
				$tableFields = $this->tablesField[$this->tableName];
				//echo "\$tableFields >>".$tableFields[$this->tablePrimaryKey]."<BR>";
				
				$_sql = " UPDATE  ".$this->tableName."  set $_fields , last_upd_by = '{$_REQUEST['identify_user_id']}' , last_upd = NOW() ";
				$_sql .= "WHERE ".$this->tablePrimaryKey." = '".($_PARAM[$this->tablePrimaryKey])."'  "; 
				return $_sql;

			}	  
			
			 

			function serviceupdate($_PARAM){
				//print_r($_PARAM);
				$_sql = $this->sqlUpdateGenerator($_PARAM);      
				if($_REQUEST['_debug']=="Y")
						echo $_sql."<BR>";
				
				if(empty($_sql)) 
					return NULL;
				 $retrows = $this->Execute_Query($_sql); 
				 //echo $retrow;
				//  echo "\$mysqli_insert_id >>[{$retrows}]$_sql<br>"; 
				if($retrows<0){ 
					$message = "SQL Error!!";
					if($_SERVER['SERVER_NAME']=='localhost'){
						$message = "MySQL [".$GLOBALS["__MYSQLDB"]["DB_NAME"]."] Error [{$_sql}]";
					}
					throw new Exception($message);	
				}          
				return $retrows;
			}

		 
			
			function sqlUpdateGenerator($_PARAM){
				
				//echo "<hr>";
				//print_r($_PARAM);
				//echo "<hr>";
				
				$_fields = "";
				$_values = "";
				$_keys = array_keys($this->tablesField[$this->tableName]);
				$tableFields = $this->tablesField[$this->tableName];
				if(empty($_keys) || empty($tableFields))
					return NULL;
				
				////print_r($tableFields);
				$append_index = 0;
				
				//echo "usergroup_desc >>(".$_PARAM['usergroup_desc'].")<br>";
				
				$_req_keys = array_keys($_PARAM);
				for($p=0;$p<sizeof($_req_keys);$p++){
					$_reqkeys[$_req_keys[$p]] = $_req_keys[$p];
				}
				
				for($keys=0;$keys<sizeof($_keys);$keys++){
					
					//echo "ทดสอบ [{$_keys[$keys]}] value is [{$_PARAM[$_keys[$keys]]}] <br>";
					//if($_PARAM[$_keys[$keys]]!=NULL && $_PARAM[$_keys[$keys]]!="" && $_keys[$keys]!=$this->tablePrimaryKey){
					//echo $_keys[$keys].">>".$_reqkeys[$_keys[$keys]]."<BR>";
							
					if($_reqkeys[$_keys[$keys]]!="" && $_keys[$keys]!=$this->tablePrimaryKey && isset($_PARAM[$_keys[$keys]])){
							
						//echo "++ append value+field type [{$keys}] [{$_keys[$keys]}] [{$tableFields[$_keys[$keys]]}]<br>";
							
						if($tableFields[$_keys[$keys]]=='int' || $tableFields[$_keys[$keys]]=='real'){
							
							if(strpos($_keys[$keys], "_id")){
								if($_fields=="")
									$_fields .= "".$_keys[$keys]." = '".(trim($_PARAM[$_keys[$keys]]))."'";
								else
									$_fields .= ", ".$_keys[$keys]." = '".(trim($_PARAM[$_keys[$keys]]))."'";
							}else{
								if($_fields=="")
									$_fields .= "".$_keys[$keys]." = ".trim($_PARAM[$_keys[$keys]])*1;
								else
									$_fields .= ", ".$_keys[$keys]." = ".trim($_PARAM[$_keys[$keys]])*1;
							}
							
						}else if($tableFields[$_keys[$keys]]=='string' || $tableFields[$_keys[$keys]]=='blob'){
							if(strpos($_keys[$keys], "_psw")){
								if($_fields=="")
									$_fields .= " ".$_keys[$keys]." = '".md5(trim($_PARAM[$_keys[$keys]]))."'";
								else
									$_fields .= ", ".$_keys[$keys]." = '".md5(trim($_PARAM[$_keys[$keys]]))."'";
							}else{
								if($_fields=="")
									$_fields .= " ".$_keys[$keys]." = '".trim($_PARAM[$_keys[$keys]])."'";
								else
									$_fields .= ", ".$_keys[$keys]." = '".trim($_PARAM[$_keys[$keys]])."'";
							}
						}else if($_keys[$keys]!="created" && $tableFields[$_keys[$keys]]=='datetime'){ 
									if($_PARAM[$_keys[$keys]]==""){ $_PARAM[$_keys[$keys]]="NULL"; }
									if($_PARAM[$_keys[$keys]]=='NOW()'||$_PARAM[$_keys[$keys]]=='NULL'){
										if($_fields=="")
												$_fields .= " ".$_keys[$keys]." = ".$_PARAM[$_keys[$keys]];
										else 
											$_fields .= ", ".$_keys[$keys]." = ".$_PARAM[$_keys[$keys]];
									}else{
										if($_fields=="")
												$_fields .= " ".$_keys[$keys]." = '".anyDate('Y-m-d H:i:s',$_PARAM[$_keys[$keys]])."' ";
										else 
											$_fields .= ", ".$_keys[$keys]." = '".anyDate('Y-m-d H:i:s',$_PARAM[$_keys[$keys]])."' ";
									}
						}else if($tableFields[$_keys[$keys]]=='date'){ 
									if($_PARAM[$_keys[$keys]]==""){ $_PARAM[$_keys[$keys]]="NULL"; }
									if($_PARAM[$_keys[$keys]]=='NOW()'||$_PARAM[$_keys[$keys]]=='NULL'){
										if($_fields=="")
												$_fields .= " ".$_keys[$keys]." = ".$_PARAM[$_keys[$keys]];
										else 
											$_fields .= ", ".$_keys[$keys]." = ".$_PARAM[$_keys[$keys]];
									}else{
										if($_fields=="")
												$_fields .= " ".$_keys[$keys]." = '".anyDate('Y-m-d',$_PARAM[$_keys[$keys]])."' ";
										else 
											$_fields .= ", ".$_keys[$keys]." = '".anyDate('Y-m-d',$_PARAM[$_keys[$keys]])."' ";
									}	
						}else if($tableFields[$_keys[$keys]]=='time'){ 
									if($_PARAM[$_keys[$keys]]==""){ $_PARAM[$_keys[$keys]]="NULL"; }
									if($_PARAM[$_keys[$keys]]=='NOW()'||$_PARAM[$_keys[$keys]]=='NULL'){
										if($_fields=="")
											$_fields .= " ".$_keys[$keys]." = ".$_PARAM[$_keys[$keys]];
										else 
											$_fields .= ", ".$_keys[$keys]." = ".$_PARAM[$_keys[$keys]];
									}else{
										if($_fields=="")
											$_fields .= " ".$_keys[$keys]." = '".$_PARAM[$_keys[$keys]]."'";
										else 
											$_fields .= ", ".$_keys[$keys]." = '".$_PARAM[$_keys[$keys]]."'";
									}
						}
						$append_index++;
					}
				}
				
				$tableFields = $this->tablesField[$this->tableName];
				//echo "\$tableFields >>".$tableFields[$this->tablePrimaryKey]."<BR>";
				
				$_sql = " UPDATE  ".$this->tableName."  set $_fields , last_upd_by = '{$_REQUEST['identify_user_id']}' , last_upd = NOW() ";
				 $_sql .= "	   where ".$this->tablePrimaryKey." = '".($_PARAM[$this->tablePrimaryKey])."' 
				 AND server_id = '{$_REQUEST['server_id']}' 
				 AND instance_server_id = '{$_REQUEST['instance_server_id']}' 
				 AND instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}' "; 
				return $_sql;
			}	  
			function serviceupdatetest($_PARAM){
				//print_r($_PARAM);
				$_sql = $this->sqlUpdateGeneratortest($_PARAM);      
				if($_REQUEST['_debug']=="Y")
						echo $_sql."<BR>";
				
				if(empty($_sql)) 
					return NULL;
				 $retrows = $this->Execute_Query($_sql); 
				 //echo $retrow;
				//  echo "\$mysqli_insert_id >>[{$retrows}]$_sql<br>"; 
				if($retrows<0){ 
					$message = "SQL Error!!";
					if($_SERVER['SERVER_NAME']=='localhost'){
						$message = "MySQL [".$GLOBALS["__MYSQLDB"]["DB_NAME"]."] Error [{$_sql}]";
					}
					throw new Exception($message);	
				}          
				return $retrows;
			}

		 
			
			function sqlUpdateGeneratortest($_PARAM){
				
				//echo "<hr>";
				//print_r($_PARAM);
				//echo "<hr>";
				
				$_fields = "";
				$_values = "";
				$_keys = array_keys($this->tablesField[$this->tableName]);
				$tableFields = $this->tablesField[$this->tableName];
				if(empty($_keys) || empty($tableFields))
					return NULL;
				
				////print_r($tableFields);
				$append_index = 0;
				
				//echo "usergroup_desc >>(".$_PARAM['usergroup_desc'].")<br>";
				
				$_req_keys = array_keys($_PARAM);
				for($p=0;$p<sizeof($_req_keys);$p++){
					$_reqkeys[$_req_keys[$p]] = $_req_keys[$p];
				}
				
				for($keys=0;$keys<sizeof($_keys);$keys++){
					
					//echo "ทดสอบ [{$_keys[$keys]}] value is [{$_PARAM[$_keys[$keys]]}] <br>";
					//if($_PARAM[$_keys[$keys]]!=NULL && $_PARAM[$_keys[$keys]]!="" && $_keys[$keys]!=$this->tablePrimaryKey){
					//echo $_keys[$keys].">>".$_reqkeys[$_keys[$keys]]."<BR>";
							
					if($_reqkeys[$_keys[$keys]]!="" && $_keys[$keys]!=$this->tablePrimaryKey && isset($_PARAM[$_keys[$keys]])){
							
						//echo "++ append value+field type [{$keys}] [{$_keys[$keys]}] [{$tableFields[$_keys[$keys]]}]<br>";
							
						if($tableFields[$_keys[$keys]]=='int' || $tableFields[$_keys[$keys]]=='real'){
							
							if(strpos($_keys[$keys], "_id")){
								if($_fields=="")
									$_fields .= "".$_keys[$keys]." = '".(trim($_PARAM[$_keys[$keys]]))."'";
								else
									$_fields .= ", ".$_keys[$keys]." = '".(trim($_PARAM[$_keys[$keys]]))."'";
							}else{
								if($_fields=="")
									$_fields .= "".$_keys[$keys]." = ".trim($_PARAM[$_keys[$keys]])*1;
								else
									$_fields .= ", ".$_keys[$keys]." = ".trim($_PARAM[$_keys[$keys]])*1;
							}
							
						}else if($tableFields[$_keys[$keys]]=='string' || $tableFields[$_keys[$keys]]=='blob'){
							if(strpos($_keys[$keys], "_psw")){
								if($_fields=="")
									$_fields .= " ".$_keys[$keys]." = '".md5(trim($_PARAM[$_keys[$keys]]))."'";
								else
									$_fields .= ", ".$_keys[$keys]." = '".md5(trim($_PARAM[$_keys[$keys]]))."'";
							}else{
								if($_fields=="")
									$_fields .= " ".$_keys[$keys]." = '".trim($_PARAM[$_keys[$keys]])."'";
								else
									$_fields .= ", ".$_keys[$keys]." = '".trim($_PARAM[$_keys[$keys]])."'";
							}
						}else if($_keys[$keys]!="created" && $tableFields[$_keys[$keys]]=='datetime'){ 
									if($_PARAM[$_keys[$keys]]==""){ $_PARAM[$_keys[$keys]]="NULL"; }
									if($_PARAM[$_keys[$keys]]=='NOW()'||$_PARAM[$_keys[$keys]]=='NULL'){
										if($_fields=="")
												$_fields .= " ".$_keys[$keys]." = ".$_PARAM[$_keys[$keys]];
										else 
											$_fields .= ", ".$_keys[$keys]." = ".$_PARAM[$_keys[$keys]];
									}else{
										if($_fields=="")
												$_fields .= " ".$_keys[$keys]." = '".anyDate('Y-m-d H:i:s',$_PARAM[$_keys[$keys]])."' ";
										else 
											$_fields .= ", ".$_keys[$keys]." = '".anyDate('Y-m-d H:i:s',$_PARAM[$_keys[$keys]])."' ";
									}
						}else if($tableFields[$_keys[$keys]]=='date'){ 
									if($_PARAM[$_keys[$keys]]==""){ $_PARAM[$_keys[$keys]]="NULL"; }
									if($_PARAM[$_keys[$keys]]=='NOW()'||$_PARAM[$_keys[$keys]]=='NULL'){
										if($_fields=="")
												$_fields .= " ".$_keys[$keys]." = ".$_PARAM[$_keys[$keys]];
										else 
											$_fields .= ", ".$_keys[$keys]." = ".$_PARAM[$_keys[$keys]];
									}else{
										if($_fields=="")
												$_fields .= " ".$_keys[$keys]." = '".anyDate('Y-m-d',$_PARAM[$_keys[$keys]])."' ";
										else 
											$_fields .= ", ".$_keys[$keys]." = '".anyDate('Y-m-d',$_PARAM[$_keys[$keys]])."' ";
									}	
						}else if($tableFields[$_keys[$keys]]=='time'){ 
									if($_PARAM[$_keys[$keys]]==""){ $_PARAM[$_keys[$keys]]="NULL"; }
									if($_PARAM[$_keys[$keys]]=='NOW()'||$_PARAM[$_keys[$keys]]=='NULL'){
										if($_fields=="")
											$_fields .= " ".$_keys[$keys]." = ".$_PARAM[$_keys[$keys]];
										else 
											$_fields .= ", ".$_keys[$keys]." = ".$_PARAM[$_keys[$keys]];
									}else{
										if($_fields=="")
											$_fields .= " ".$_keys[$keys]." = '".$_PARAM[$_keys[$keys]]."'";
										else 
											$_fields .= ", ".$_keys[$keys]." = '".$_PARAM[$_keys[$keys]]."'";
									}
						}
						$append_index++;
					}
				}
				
				$tableFields = $this->tablesField[$this->tableName];
				//echo "\$tableFields >>".$tableFields[$this->tablePrimaryKey]."<BR>";
				
				$_sql = " UPDATE  ".$this->tableName."  set $_fields , last_upd_by = '{$_REQUEST['identify_user_id']}' , last_upd = NOW() ";
				 $_sql .= "	   where ".$this->tablePrimaryKey." = '".($_PARAM[$this->tablePrimaryKey])."' 
				 AND server_id = '{$_REQUEST['server_id']}' 
				 AND instance_server_id = '{$_PARAM['instance_server_id']}' 
				 AND instance_server_channel_id = '{$_PARAM['instance_server_channel_id']}' "; 
				return $_sql;
			}	  
			

			function server_serviceget($_PARAM){     
								
				if(empty($_PARAM[$this->tablePrimaryKey]))
					throw new Exception("NULL PARAMETER[".$this->tablePrimaryKey."]values(".$_PARAM[$this->tablePrimaryKey].") ++ ");
				//echo "tablePrimaryKey >>[".($_PARAM[$this->tablePrimaryKey])."]<br>";
				// if(!is_numeric(($_PARAM[$this->tablePrimaryKey])))
				// 	throw new Exception("NULL PARAMETER"); 
				  
				$tableFields = $this->tablesField[$this->tableName];
				//echo "\$tableFields >>".$tableFields[$this->tablePrimaryKey]."<BR>";
				
			    $_sql =" SELECT * FROM ".$this->tableName." _table  ";
				$_sql .= " WHERE  _table.".$this->tablePrimaryKey." = '".($_PARAM[$this->tablePrimaryKey])."' AND _table.server_id IS NOT NULL  ";	
				if($_REQUEST['_debug']=="Y")
					echo "$_sql<br>";
				//exit;

				$retArray = $this->_sqlget($_sql);       
				
				if(is_array($retArray) && is_array($_PARAM)){
					$_PARAM = array_merge($_PARAM, $retArray); 
					return $_PARAM;
				}
 
				return NULL;
			}
			
			function getPropertie($_PARAM){      
				
				if(empty($_PARAM[$this->tablePrimaryKey]))
					throw new Exception("NULL PARAMETER[".$this->tablePrimaryKey."]values(".$_PARAM[$this->tablePrimaryKey].") ++ ");
				//echo "tablePrimaryKey >>[".$this->tablePrimaryKey."]<br>";
			    
				$tableFields = $this->tablesField[$this->tableName];
				//echo "\$tableFields >>".$tableFields[$this->tablePrimaryKey]."<BR>";
				
			    $_sql =" SELECT * FROM hms_api.".$this->tableName." _table  ";
				$_sql .= "  WHERE  _table.".$this->tablePrimaryKey." = '".($_PARAM[$this->tablePrimaryKey])."' AND _table.server_id is not null  "; 
				// echo "$_sql<br>";
  

				$retArray = $this->_sqlget($_sql);       
				//echo "<hr>";
				// print_r($retArray);
				//echo "<hr>";
									
				if(is_array($retArray) && is_array($_PARAM)){
					$_PARAM = array_merge($_PARAM, $retArray); 
					// print_r($_PARAM);
					return $_PARAM;
				}
 
				return NULL;
			}

			function serviceget($_PARAM){      
				if(empty($_PARAM[$this->tablePrimaryKey]))
					throw new Exception("NULL PARAMETER[".$this->tablePrimaryKey."]values(".$_PARAM[$this->tablePrimaryKey].") ++ ");
				//echo "tablePrimaryKey >>[".$this->tablePrimaryKey."]<br>";
			    
				$tableFields = $this->tablesField[$this->tableName];
				//echo "\$tableFields >>".$tableFields[$this->tablePrimaryKey]."<BR>";
				
			    $_sql =" SELECT * FROM hms_api.".$this->tableName." _table  ";
				$_sql .= "  WHERE  _table.".$this->tablePrimaryKey." = '".($_PARAM[$this->tablePrimaryKey])."' 
				AND _table.server_id = '{$_REQUEST['server_id']}'  
				AND _table.sys_del_flag = 'N'  
				AND _table.instance_server_id = '{$_REQUEST['instance_server_id']}' 
				AND _table.instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}' "; 
				if($_REQUEST['_debug']=="Y")	
					echo "$_sql<br>";
                                     

				$retArray = $this->_sqlget($_sql);       
				//echo "<hr>";
				//print_r($retArray);
				//echo "<hr>";
									
				if(is_array($retArray) && is_array($_PARAM)){
					$_PARAM = array_merge($_PARAM, $retArray); 
					return $_PARAM;
				}
 
				return NULL;
			}
			
		 
			function serviceget_fk($_PARAM, $_fk ){     
								
				if(sizeof($_fk)==0 || empty($_fk))
					throw new Exception("NULL PARAMETER is NULL++ ");
				//echo "tablePrimaryKey >>[".($_PARAM[$this->tablePrimaryKey])."]<br>"; 
				$_sql =" SELECT * FROM ".$this->tableName." where sys_del_flag = 'N' 
				AND server_id = '{$_REQUEST['server_id']}' 
				AND instance_server_id = '{$_REQUEST['instance_server_id']}' 
				AND instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}' ";  
				$_keys = array_keys($_fk);
				for($i=0;$i<sizeof($_keys);$i++){
						$_sql .= "	AND ".$_keys[$i]." = '".($_fk[$_keys[$i]])."'	";		 
				} 
				return array_merge($_PARAM, $this->_sqlget($_sql));

			}

			function trx_serviceget_fk($_PARAM, $_fk ){     
								
				if(sizeof($_fk)==0 || empty($_fk))
					throw new Exception("NULL PARAMETER is NULL++ ");
				//echo "tablePrimaryKey >>[".($_PARAM[$this->tablePrimaryKey])."]<br>"; 
				$_sql =" SELECT * FROM {$GLOBALS["instanceServer"]["instance_server_dbn"]}.".$this->tableName." 
				WHERE sys_del_flag = 'N' 
				AND server_id = '{$_REQUEST['server_id']}' 
				AND instance_server_id = '{$_REQUEST['instance_server_id']}' 
				AND instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}' ";  
				$_keys = array_keys($_fk);
				for($i=0;$i<sizeof($_keys);$i++){
						$_sql .= "	AND ".$_keys[$i]." = '".($_fk[$_keys[$i]])."'	";		 
				} 
				return array_merge($_PARAM, $this->_sqlget($_sql));

			}
			  
			function servicedeletes($_PARAM, $_pkparams){        
				if(empty($_pkparams))
					return;
				$_sql = "	 update ".$this->tableName." _table set  _table.sys_del_flag='Y' 
				where  _table.server_id = '{$_REQUEST['server_id']}' 
				AND instance_server_id = '{$_REQUEST['instance_server_id']}' 
				AND instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}' ";   		
				$keys = array_keys($_pkparams);  
				                                                                  
				for($i=0; $i<sizeof($keys); $i++){
						$_sql .="	AND _table.".$keys[$i]." = '".($_pkparams[$keys[$i]])."' ";	
				} 
				    

				//$this->Execute_Query("SET character_set_results=utf8") or die("Invalid query: [{$GLOBALS["__MYSQLDB"]["DB_NAME"]}]" . mysqli_error($GLOBALS["_connection"]));
				$_sql_rets = $this->Execute_Query($_sql);
				if($_sql_rets!=0)
					return true;
				return false;
			}  
					 
	
	   		
		function get($_PARAM){       
				$_sql="SELECT * FROM  ".$this->tableName." WHERE  ".$this->tablePrimaryKey." = '{$_PARAM[$this->tablePrimaryKey]}' 
				AND server_id = '{$_REQUEST['server_id']}' 
				AND instance_server_id = '{$_REQUEST['instance_server_id']}' 
				AND instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}' ";
				//echo "$_sql<BR>";
				return $this->_sqlget($_sql);	
		}
		function getLists($_PARAM, $_keys, $_order){     
							$_sql=" SELECT * FROM hms_api.".$this->tableName." WHERE  sys_del_flag = 'N' 
							AND server_id = '{$_REQUEST['server_id']}' 
							AND instance_server_id = '{$_REQUEST['instance_server_id']}' 
							AND instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}' ";
							if(is_array($_keys) && sizeof($_keys)>0){
								$_arrkeys = array_keys($_keys);
								for($i=0;$i<sizeof($_arrkeys);$i++){
									if(!empty($_keys[$_arrkeys[$i]])){
										//$_sql.="  AND ".$_arrkeys[$i]." = '".$_keys[$_arrkeys[$i]]."' ";
										if(substr($_arrkeys[$i], -9)=="_datetime"){
												//echo "[".$_keys[$m]."]case 1++<br>";
												//$_allRet[$_cnt_item][$_fields[$a]] = thai_short_date($row[$_fields[$a]]);
												$_sql.="  AND ".$_arrkeys[$i]." = ".$_keys[$_arrkeys[$i]]." ";
										}else if(substr($_arrkeys[$i], -3)=="_dt"){
												//echo "[".$_keys[$m]."]case 2++<br>";
												//$_allRet[$_cnt_item][$_fields[$a]] = display_MySQL_Date($row[$_fields[$a]]);
												$_sql.="  AND ".$_arrkeys[$i]." = ".$_keys[$_arrkeys[$i]]." ";
										}else{						  
											$_sql.="  AND ".$_arrkeys[$i]." = '".$_keys[$_arrkeys[$i]]."' ";
										}
												
									}
								}  
							}
							if(!empty($_order))
								$_sql .=" ORDER BY ".$this->tableName.".".$_order." ";						
							else
								$_sql .=" ORDER BY ".$this->tableName.".order_no ASC ";				 
							//echo "$_sql<BR>"; 

							return $this->_sqllists($_sql);

		}
		
		function trx_getLists($_PARAM, $_keys, $_order){     
							$_sql=" SELECT * FROM {$GLOBALS["instanceServer"]["instance_server_dbn"]}.".$this->tableName."  WHERE  sys_del_flag = 'N' 
							AND server_id = '{$_REQUEST['server_id']}' 
							AND instance_server_id = '{$_REQUEST['instance_server_id']}' 
							AND instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}' ";
							if(is_array($_keys) && sizeof($_keys)>0){
								$_arrkeys = array_keys($_keys);
								for($i=0;$i<sizeof($_arrkeys);$i++){
									if(!empty($_keys[$_arrkeys[$i]])){
										//$_sql.="  AND ".$_arrkeys[$i]." = '".$_keys[$_arrkeys[$i]]."' ";
										if(substr($_arrkeys[$i], -9)=="_datetime"){
												//echo "[".$_keys[$m]."]case 1++<br>";
												//$_allRet[$_cnt_item][$_fields[$a]] = thai_short_date($row[$_fields[$a]]);
												$_sql.="  AND ".$_arrkeys[$i]." = ".$_keys[$_arrkeys[$i]]." ";
										}else if(substr($_arrkeys[$i], -3)=="_dt"){
												//echo "[".$_keys[$m]."]case 2++<br>";
												//$_allRet[$_cnt_item][$_fields[$a]] = display_MySQL_Date($row[$_fields[$a]]);
												$_sql.="  AND ".$_arrkeys[$i]." = ".$_keys[$_arrkeys[$i]]." ";
										}else{						  
											$_sql.="  AND ".$_arrkeys[$i]." = '".$_keys[$_arrkeys[$i]]."' ";
										}
												
									}
								}  
							}
							if(!empty($_order))
								$_sql .=" ORDER BY ".$this->tableName.".".$_order." ";						
							else
								$_sql .=" ORDER BY ".$this->tableName.".order_no ASC ";				 
							// echo "$_sql<BR>"; exit;

							return $this->_sqllists($_sql);

		}

	 

		function getServerLists($_keys){     
							$_sql=" SELECT * FROM hms_api.".$this->tableName."  WHERE  sys_del_flag = 'N' AND server_id = '10101010'  ";
							if(is_array($_keys) && sizeof($_keys)>0){
								$_arrkeys = array_keys($_keys);
								for($i=0;$i<sizeof($_arrkeys);$i++){
									if(!empty($_keys[$_arrkeys[$i]])){
										//$_sql.="  AND ".$_arrkeys[$i]." = '".$_keys[$_arrkeys[$i]]."' ";
										if(substr($_arrkeys[$i], -9)=="_datetime"){
												//echo "[".$_keys[$m]."]case 1++<br>";
												//$_allRet[$_cnt_item][$_fields[$a]] = thai_short_date($row[$_fields[$a]]);
												$_sql.="  AND ".$_arrkeys[$i]." = ".InsertMysqlDate($_keys[$_arrkeys[$i]])." ";
										}else if(substr($_arrkeys[$i], -3)=="_dt"){
												//echo "[".$_keys[$m]."]case 2++<br>";
												//$_allRet[$_cnt_item][$_fields[$a]] = display_MySQL_Date($row[$_fields[$a]]);
												$_sql.="  AND ".$_arrkeys[$i]." = ".InsertMysqlShotDate($_keys[$_arrkeys[$i]])." ";
										}else{						  
											$_sql.="  AND ".$_arrkeys[$i]." = '".$_keys[$_arrkeys[$i]]."' ";
										}
												
									}
								}  
							}
							// $_sql .=" AND ".$this->tablePrimaryKey."  > 0 ";			
							$_sql .=" ORDER BY ".$this->tableName.".order_no ";						
							// echo "$_sql<BR>"; 
							return $this->_sqllists($_sql);

		}
		
	 
		   
 

		function deleteByForengkey($_PARAM, $_keys){       
							$_sql="UPDATE ".$this->tableName."  SET  sys_del_flag = 'Y' 
							WHERE server_id = '{$_REQUEST['server_id']}' 
							AND instance_server_id = '{$_REQUEST['instance_server_id']}' 
							AND instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}' ";
							if(sizeof($_keys)>0 && is_array($_keys)){
								$_arrkeys = array_keys($_keys);
								for($i=0;$i<sizeof($_arrkeys);$i++){
									if(!empty($_keys[$_arrkeys[$i]])){
										//$_sql.="  AND ".$_arrkeys[$i]." = '".$_keys[$_arrkeys[$i]]."' ";
										if(substr($_arrkeys[$i], -9)=="_datetime"){
												//echo "[".$_keys[$m]."]case 1++<br>";
												//$_allRet[$_cnt_item][$_fields[$a]] = thai_short_date($row[$_fields[$a]]);
												$_sql.="  AND ".$_arrkeys[$i]." = ".InsertMysqlDate($_keys[$_arrkeys[$i]])." ";
										}else if(substr($_arrkeys[$i], -3)=="_dt"){
												//echo "[".$_keys[$m]."]case 2++<br>";
												//$_allRet[$_cnt_item][$_fields[$a]] = display_MySQL_Date($row[$_fields[$a]]);
												$_sql.="  AND ".$_arrkeys[$i]." = ".InsertMysqlShotDate($_keys[$_arrkeys[$i]])." ";
										}else{						  
											$_sql.="  AND ".$_arrkeys[$i]." = '".$_keys[$_arrkeys[$i]]."' ";
										}
												
									}
								}  
							} 				
							//echo "$_sql<BR>";
				 return $this->Execute_Query($_sql);		

		}

		function deleted($_PARAM){     
				$_sql=" UPDATE ".$this->tableName."  SET sys_del_flag = 'Y' 
				WHERE  ".$this->tablePrimaryKey." = '{$_PARAM[$this->tablePrimaryKey]}' 
				AND server_id = '{$_REQUEST['server_id']}'
				AND instance_server_id = '{$_REQUEST['instance_server_id']}' 
				AND instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}' ";
				if($_REQUEST['_debug']=="Y")
					echo "$_sql<br>";  
				//$this->Execute_Query("SET character_set_results=utf8");
				$_sql_rets = $this->Execute_Query($_sql);				  
				if(isset($_sql_rets) && $_sql_rets!=0){         
						return true;             
				}                                  
				return false;       
		}		

		function server_deleted($_PARAM){     
			$_sql=" UPDATE ".$this->tableName."  SET sys_del_flag = 'Y' 
			WHERE  ".$this->tablePrimaryKey." = '{$_PARAM[$this->tablePrimaryKey]}' 
			AND server_id = '{$_REQUEST['server_id']}' ";
			if($_REQUEST['_debug']=="Y")
				echo "$_sql<br>";  
			//$this->Execute_Query("SET character_set_results=utf8");
			$_sql_rets = $this->Internal_Execute_Query($_sql);				  
			if(isset($_sql_rets) && $_sql_rets!=0){         
					return true;             
			}                                  
			return false;       
		}	
 
	 
 

		function server_real_deleted($_PARAM){     
			$_sql=" DELETE  FROM ".$this->tableName." 
			WHERE  ".$this->tablePrimaryKey." = '".$_PARAM[$this->tablePrimaryKey]."' 
			AND server_id = '{$_REQUEST['server_id']}' ";
			if($_REQUEST['_debug']=='Y')
				echo "$_sql<br>"; 
			//$this->Execute_Query("SET character_set_results=utf8");
			$_sql_rets = $this->Internal_Execute_Query($_sql);				 
			if(isset($_sql_rets) && $_sql_rets!=0){         
					return true;            
			}                                 
			return false;        
		}

	 
			 
		function real_deleted($_PARAM){     
				$_sql=" DELETE  FROM ".$this->tableName." WHERE  ".$this->tablePrimaryKey." = '".$_PARAM[$this->tablePrimaryKey]."' AND server_id = '{$_REQUEST['server_id']}' 
				AND instance_server_id = '{$_REQUEST['instance_server_id']}' 
				AND instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}' ";
				if($_REQUEST['_debug']=='Y')
					echo "$_sql<br>"; 
				//$this->Execute_Query("SET character_set_results=utf8");
				$_sql_rets = $this->Execute_Query($_sql);				 
				if(isset($_sql_rets) && $_sql_rets!=0){         
						return true;            
				}                                 
				return false;        
		}

		function real_deletedbyKeys($_PARAM, $_keys){			
							$_sql=" DELETE FROM ".$this->tableName."  
							WHERE server_id = '{$_REQUEST['server_id']}' 
							AND instance_server_id = '{$_REQUEST['instance_server_id']}' 
							AND instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}' ";
							$_arrkeys = array_keys($_keys);
							for($i=0;$i<sizeof($_arrkeys);$i++){
								if(!empty($_keys[$_arrkeys[$i]]))
									$_sql.="  AND ".$_arrkeys[$i]." = '".$_keys[$_arrkeys[$i]]."' ";
							}
							//echo "$_sql<br>";
							//$this->Execute_Query("SET character_set_results=utf8") or die("Invalid query: ". mysqli_error($GLOBALS["_connection"]));
							$_sql_rets = $this->Execute_Query($_sql);				
							if(isset($_sql_rets) && $_sql_rets!=0){          
								return true;       
							}        
					return false;                              
		}

		function trx_real_deleted($_PARAM){     
				$_sql=" DELETE  FROM {$GLOBALS["instanceServer"]["instance_server_dbn"]}.".$this->tableName." 
				WHERE  ".$this->tablePrimaryKey." = '".$_PARAM[$this->tablePrimaryKey]."' 
				AND server_id = '{$_REQUEST['server_id']}' 
				AND instance_server_id = '{$_REQUEST['instance_server_id']}' 
				AND instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}' "; 
				//echo "$_sql<br>";
				//$this->Execute_Query("SET character_set_results=utf8");
				$_sql_rets = $this->Execute_Query($_sql);				 
				if(isset($_sql_rets) && $_sql_rets!=0){         
						return true;            
				}                                 
				return false;       
		}

		function trx_real_deletedbyKeys($_PARAM, $_keys){		
				$_sql=" DELETE FROM {$GLOBALS["instanceServer"]["instance_server_dbn"]}.".$this->tableName."  
				WHERE server_id = '{$_REQUEST['server_id']}' 
				AND instance_server_id = '{$_REQUEST['instance_server_id']}' 
				AND instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}' ";
				$_arrkeys = array_keys($_keys);
				for($i=0;$i<sizeof($_arrkeys);$i++){
					if(!empty($_keys[$_arrkeys[$i]]))
						$_sql.="  AND ".$_arrkeys[$i]." = '".$_keys[$_arrkeys[$i]]."' ";
				}
				// echo "$_sql<hr>";
				//$this->Execute_Query("SET character_set_results=utf8") or die("Invalid query: ". mysqli_error($GLOBALS["_connection"]));
				$_sql_rets = $this->Execute_Query($_sql);				
				if(isset($_sql_rets) && $_sql_rets!=0){          
					return true;       
				}        
				return false;                              
		}

		function _serversqlget($_sql){      
				 $result = $GLOBALS["_connection"]->query($_sql); 
				 if($obj = $result->fetch_array(MYSQLI_ASSOC)){
					 mysqli_free_result($result);
					mysqli_next_result($GLOBALS["_connection"]);  
					 return $this->aotoGetFromDB($obj);
				 }
		}

		function _serversqllists($_sql){    

				//echo "MySQL Error can't send server_id in sql ";
				//if (strpos($_sql, "server_id") === false)
				//	throw new Exception("MySQL Error can't send server_id in sql ");	;
				//echo "$_sql<hr>";
				$result = $GLOBALS["_connection"]->query($_sql); 
				$_allRet = array();
				$row = 0;
				while($obj = $result->fetch_array(MYSQLI_ASSOC)){
					 $_allRet[$row] = $this->aotoGetFromDB($obj);
					 $row++;
				 }
				mysqli_free_result($result);
					mysqli_next_result($GLOBALS["_connection"]);  
				return $_allRet; 
				
		}
				
		function _sqlget($_sql){       
				//echo "_sqlget"; 
				if (strpos($_sql, "server_id") === false)
					throw new Exception("MySQL Error can't send server_id in sql ");	;
				if($_REQUEST['_debug']=="Y")
					echo $_sql;
				 $result = $GLOBALS["_connection"]->query($_sql); 
				 if(!empty($result) && $obj = $result->fetch_array(MYSQLI_ASSOC)){ 
					mysqli_free_result($result);
					mysqli_next_result($GLOBALS["_connection"]);  
					 return $this->aotoGetFromDB($obj);
				 } 
				 return;
				
		}   

		  

		function _sqllists($_sql){   
				//echo "input $_sql\n";
				if (strpos($_sql, "server_id") === false)
					throw  new Exception("Exception BaseService SQL Error can't send server_id in sql ");
				//echo "step1\n";
				if($_REQUEST['_debug']=="Y")
					echo $_sql;
				$result = $GLOBALS["_connection"]->query($_sql);
				//echo "step2\n";
				if(empty($result)){
					if($_SERVER['SERVER_NAME']=='localhost'){
						throw  new Exception("Exception Invalid SQL Comman -: {$_sql}");
					}else{
						throw  new Exception("Exception Invalid SQL Comman -:-");
					}
						
				}
				//echo "step3\n";
				$_allRet = array();
				$row = 0;
				while($obj = $result->fetch_array(MYSQLI_ASSOC)){
					 $_allRet[$row] = $this->aotoGetFromDB($obj);
					 $row++;
				 }
				
				mysqli_free_result($result);
				mysqli_next_result($GLOBALS["_connection"]);
				return $_allRet;
				
		}
 
		function _InternalSqlLists($_sql){    
				$result = $GLOBALS["_connection"]->query($_sql);
				if(empty($result)){
						throw  new Exception("Invalid SQL Comman<br>$_sql<hr>");	;
				}

				$_allRet = array();
				$row = 0;
				while($obj = $result->fetch_array(MYSQLI_ASSOC)){
					 $_allRet[$row] = $this->aotoGetFromDB($obj);
					 $row++;
				 } 
				mysqli_free_result($result);
				mysqli_next_result($GLOBALS["_connection"]);   
				return $_allRet;
				
		}
		
		 

		function Execute_Query($_sql){   
			// echo json_encode(strpos($_sql, "server_id"))." -> ".json_encode(strpos($_sql, "instance_server_id"))." -> ".json_encode(strpos($_sql, "instance_server_channel_id"));
			if (strpos($_sql, "server_id") === false||strpos($_sql, "instance_server_id") === false||strpos($_sql, "instance_server_channel_id") === false)
					throw  new Exception("MySQL Error can't send server_id/instance_server_id/instance_server_channel_id in sql "); 
			$GLOBALS["_connection"]->query($_sql); 
			if ($GLOBALS["_connection"]->errno) {
				// throw new Exception($GLOBALS["_connection"]->error);
				$this->Log_Error($_sql, $GLOBALS["_connection"]->error, $GLOBALS["_connection"]->errno);
			}
			return  $GLOBALS["_connection"]->affected_rows; 
		} 

		function Internal_Execute_Query($_sql){   
			if (strpos($_sql, "server_id") === false)
					throw  new Exception("MySQL Error can't send server_id in sql "); 
			$GLOBALS["_connection"]->query($_sql); 
			if ($GLOBALS["_connection"]->errno) {
				$this->Log_Error($_sql, $GLOBALS["_connection"]->error, $GLOBALS["_connection"]->errno);
			}
			return  $GLOBALS["_connection"]->affected_rows; 
		} 

		function Master_Execute_Query($_sql){   
			$GLOBALS["_connection"]->query($_sql); 
			if ($GLOBALS["_connection"]->errno) {
				$this->Log_Error($_sql, $GLOBALS["_connection"]->error, $GLOBALS["_connection"]->errno);
			}
			return  $GLOBALS["_connection"]->affected_rows; 
		} 
		
		function aotoGetFromDB($objArr){
			$cols = array();
			if(is_array($objArr)){
				$keys = array_keys($objArr);
				for($i=0;$i<sizeof($keys);$i++){ 
					if(is_numeric($keys[$i])!=1){
						$_field_name = trim($keys[$i]);
						if(substr($_field_name, -3)=="_id"){    
							//$cols[$_field_name] = base64_encode($objArr[trim($_field_name)]);
							$cols[$_field_name] = ($objArr[trim($_field_name)]);
						}else if(substr($_field_name, -3)=="_dt"){
							//$cols[$_field_name.""] = ($objArr[trim($_field_name)]);    
							//$cols[$_field_name] = display_MySQL_Date($objArr[trim($_field_name)]);      
							$cols[$_field_name] = ($objArr[trim($_field_name)]);     
						}else if(substr($_field_name, -9)==""){ 
							$cols[$_field_name.""] = $objArr[trim($_field_name)];
							$cols[$_field_name] = thai_short_date($objArr[trim($_field_name)]);
						}else{
							$cols[$_field_name] = $objArr[trim($_field_name)];      
						} 
					}
				}
			}
			return $cols;
		}

		function sqlUpdateCheckNullGenerator($_PARAM){
				
			//echo "<hr>";
			//print_r($_PARAM);
			//echo "<hr>";
			
			$_fields = "";
			$_values = "";
			$_keys = array_keys($this->tablesField[$this->tableName]);
			$tableFields = $this->tablesField[$this->tableName];
			if(empty($_keys) || empty($tableFields))
				return NULL;
			
			////print_r($tableFields);
			$append_index = 0;
			
			//echo "usergroup_desc >>(".$_PARAM['usergroup_desc'].")<br>";
			
			$_req_keys = array_keys($_PARAM);
			for($p=0;$p<sizeof($_req_keys);$p++){
				$_reqkeys[$_req_keys[$p]] = $_req_keys[$p];
			}
			
			for($keys=0;$keys<sizeof($_keys);$keys++){
				
				// echo "ทดสอบ [{$_keys[$keys]}] value is [{$_PARAM[$_keys[$keys]]}] <br>";
				//if($_PARAM[$_keys[$keys]]!=NULL && $_PARAM[$_keys[$keys]]!="" && $_keys[$keys]!=$this->tablePrimaryKey){
				// echo $_keys[$keys].">>".$_reqkeys[$_keys[$keys]]."<BR>";
						
				if($_reqkeys[$_keys[$keys]]!="" && $_keys[$keys]!=$this->tablePrimaryKey && isset($_PARAM[$_keys[$keys]])){
						
					//echo "++ append value+field type [{$keys}] [{$_keys[$keys]}] [{$tableFields[$_keys[$keys]]}]<br>";
						
					if($tableFields[$_keys[$keys]]=='int' || $tableFields[$_keys[$keys]]=='real'){
						
						if(strpos($_keys[$keys], "_id")){
							if($_fields=="")
								$_fields .= "".$_keys[$keys]." = '".(trim($_PARAM[$_keys[$keys]]))."'";
							else
								$_fields .= ", ".$_keys[$keys]." = '".(trim($_PARAM[$_keys[$keys]]))."'";
						}else{
							if($_fields=="")
								if ($_PARAM[$_keys[$keys]]=="NULL") {
									$_fields .= "".$_keys[$keys]." = NULL";
								} else {
									$_fields .= "".$_keys[$keys]." = ".trim($_PARAM[$_keys[$keys]])*1;
								}
							else
								if ($_PARAM[$_keys[$keys]]=="NULL") {
									$_fields .= ", ".$_keys[$keys]." = NULL";
								} else {
									$_fields .= ", ".$_keys[$keys]." = ".trim($_PARAM[$_keys[$keys]])*1;
								}
						}
						
					}else if($tableFields[$_keys[$keys]]=='string' || $tableFields[$_keys[$keys]]=='blob'){
						if(strpos($_keys[$keys], "_psw")){
							if($_fields=="")
								$_fields .= " ".$_keys[$keys]." = '".md5(trim($_PARAM[$_keys[$keys]]))."'";
							else
								$_fields .= ", ".$_keys[$keys]." = '".md5(trim($_PARAM[$_keys[$keys]]))."'";
						}else{
							if($_fields=="") {
								if ($_PARAM[$_keys[$keys]]=="NULL") {
									$_fields .= " ".$_keys[$keys]." = NULL";
								} else {
									$_fields .= " ".$_keys[$keys]." = '".trim($_PARAM[$_keys[$keys]])."'";
								}
							}
							else {
								if ($_PARAM[$_keys[$keys]]=="NULL") {
									$_fields .= ", ".$_keys[$keys]." = NULL";
								} else {
									$_fields .= ", ".$_keys[$keys]." = '".trim($_PARAM[$_keys[$keys]])."'";
								}
							}
						}
					}else if($_keys[$keys]!="created" && $tableFields[$_keys[$keys]]=='datetime'){ 
								if($_PARAM[$_keys[$keys]]==""){ $_PARAM[$_keys[$keys]]="NULL"; }
								if($_PARAM[$_keys[$keys]]=='NOW()'||$_PARAM[$_keys[$keys]]=='NULL'){
									if($_fields=="")
										$_fields .= " ".$_keys[$keys]." = ".$_PARAM[$_keys[$keys]];
									else 
										$_fields .= ", ".$_keys[$keys]." = ".$_PARAM[$_keys[$keys]];
								}else{
									if($_fields=="")
										$_fields .= " ".$_keys[$keys]." = '".anyDate('Y-m-d H:i:s',$_PARAM[$_keys[$keys]])."' ";
									else 
										$_fields .= ", ".$_keys[$keys]." = '".anyDate('Y-m-d H:i:s',$_PARAM[$_keys[$keys]])."' ";
								}
					}else if($tableFields[$_keys[$keys]]=='date'){ 
								if($_PARAM[$_keys[$keys]]==""){ $_PARAM[$_keys[$keys]]="NULL"; }
								if($_PARAM[$_keys[$keys]]=='NOW()'||$_PARAM[$_keys[$keys]]=='NULL'){
									if($_fields=="")
											$_fields .= " ".$_keys[$keys]." = ".$_PARAM[$_keys[$keys]];
									else 
										$_fields .= ", ".$_keys[$keys]." = ".$_PARAM[$_keys[$keys]];
								}else{
									if($_fields=="")
											$_fields .= " ".$_keys[$keys]." = '".anyDate('Y-m-d',$_PARAM[$_keys[$keys]])."' ";
									else 
										$_fields .= ", ".$_keys[$keys]." = '".anyDate('Y-m-d',$_PARAM[$_keys[$keys]])."' ";
								}	
					}else if($tableFields[$_keys[$keys]]=='time'){ 
								if($_PARAM[$_keys[$keys]]==""){ $_PARAM[$_keys[$keys]]="NULL"; }
								if($_PARAM[$_keys[$keys]]=='NOW()'||$_PARAM[$_keys[$keys]]=='NULL'){
									if($_fields=="")
										$_fields .= " ".$_keys[$keys]." = ".$_PARAM[$_keys[$keys]];
									else 
										$_fields .= ", ".$_keys[$keys]." = ".$_PARAM[$_keys[$keys]];
								}else{
									if($_fields=="")
										$_fields .= " ".$_keys[$keys]." = '".$_PARAM[$_keys[$keys]]."'";
									else 
										$_fields .= ", ".$_keys[$keys]." = '".$_PARAM[$_keys[$keys]]."'";
								}
					}
					$append_index++;
				}
			}
			
			$tableFields = $this->tablesField[$this->tableName];
			//echo "\$tableFields >>".$tableFields[$this->tablePrimaryKey]."<BR>";
			
			$_sql = " UPDATE  ".$this->tableName."  set $_fields , last_upd_by = '{$_REQUEST['identify_user_id']}' , last_upd = NOW() ";
			 $_sql .= "	   where ".$this->tablePrimaryKey." = '".($_PARAM[$this->tablePrimaryKey])."' 
			 AND server_id = '{$_REQUEST['server_id']}' 
			 AND instance_server_id = '{$_REQUEST['instance_server_id']}' 
			 AND instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}' "; 
			return $_sql;
		}	 

		function serviceupdateCheckNull($_PARAM){
			//print_r($_PARAM);
			$_sql = $this->sqlUpdateCheckNullGenerator($_PARAM);      
			if($_REQUEST['_debug']=="Y")
					echo $_sql."<BR>";
			if(empty($_sql)) 
				return NULL;
			 $retrows = $this->Execute_Query($_sql); 
			 //echo $retrow;
			//  echo "\$mysqli_insert_id >>[{$retrows}]$_sql<br>"; 
			if($retrows<0){ 
				$message = "SQL Error!!";
				if($_SERVER['SERVER_NAME']=='localhost'){
					$message = "MySQL [".$GLOBALS["__MYSQLDB"]["DB_NAME"]."] Error [{$_sql}]";
				}
				throw new Exception($message);	
			}          
			return $retrows;
		}

		function Log_Error($_sql, $_ErrorMessage, $_ErrorCode){
			$compgrp = $GLOBALS['_REQUEST']['_compgrp'] ?? null;
			$comp = $GLOBALS['_REQUEST']['_comp'] ?? null;
			$action = $GLOBALS['_REQUEST']['_action'] ?? null;
			$request = $GLOBALS['_REQUEST'] ?? array();
			$server_id = $GLOBALS['_REQUEST']['server_id'] ?? null;
			$instance_server_id = $GLOBALS['_REQUEST']['instance_server_id'] ?? null;
			$instance_server_channel_id = $GLOBALS['_REQUEST']['instance_server_channel_id'] ?? null;
			$remote_ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
			
			$param = [
				$this->getPrimaryKey(12),
				$compgrp,
				$comp,
				$action,
				json_encode($request),
				$_sql,
				$_ErrorCode,
				$_ErrorMessage,
				$server_id,
				$instance_server_id,
				$instance_server_channel_id,
				$remote_ip,
				date('Y-m-d H:i:s'),
			];

			$query = "INSERT INTO hms_api.sys_error_log (
				error_log_id, _compgrp, _comp, _action, request, query, error_code, error_message, server_id, 
				instance_server_id, instance_server_channel_id, remote_ip, created
			) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?) ";
			
			// Prepare statement
			$stmt = $GLOBALS["_connection"]->prepare($query);
			if (!$stmt) {
				return ;
				// throw new Exception("Failed to prepare statement: " . $GLOBALS["_connection"]->error);
			}
		
			// Bind parameters
			$stmt->bind_param(
				"sssssssssssss",
				...$param
			);
		
			// Execute statement
			if (!$stmt->execute()) {
				return ;
				// throw new Exception("Failed to execute statement: " . $stmt->error);
			}
		}
		 
	}

	
?>