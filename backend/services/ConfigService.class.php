<? 
	    include("AbstractConfig.class.php");

	class ConfigService extends AbstractConfig{
				
			function getSpecConfig($_code){    
				// $list_config = $this->getAllConfig();
				// $config_index = array_search($_code, array_column($list_config, 'config_code'));

				// if(!empty($list_config[$config_index])){
				// 	return $list_config[$config_index];
				// }
				// return array();
				
				// $_sql="	SELECT * FROM hms_api.comp_config _config  
				// WHERE _config.config_code='{$_code}' 
				// AND _config.server_id = '{$_REQUEST['server_id']}' 
				// AND _config.instance_server_id = '{$_REQUEST['instance_server_id']}' 
				// AND _config.instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}' "; 
				// //echo $_sql."<BR>";
				// $lists =  $this->_sqlget($_sql);     

				// return $lists;

				$config = getRedis("{$_REQUEST['instance_server_channel_id']}_CONFIG");
				/**
				 * Remove old wrong cache
				 */
				if(!empty($config[0]) || !empty($config[$_code][0])){
					$config = array();
				}
				
				// *************
				if(empty($config)){
					$config = $this->getAllConfig();
				}else{
					if(empty($config[$_code])){
						$config = $this->getConfigByCode($config, $_code);
					}
				}

				return $config[$_code];
			}

			function getMultipleConfig($_array){     
				// $list_config = $this->getAllConfig();

				// $lists = array();
				// for($i = 0; $i < sizeof($_array); $i++){
				// 	$config_index = array_search($_array[$i], array_column($list_config, 'config_code'));
				// 	if(!empty($list_config[$config_index])){
				// 		$lists[$_array[$i]] = $list_config[$config_index];
				// 	}
				// }

				// return $lists;

				// $_sql="	SELECT * FROM comp_config _config  
				// WHERE _config.config_code IN ('".implode("','" , $_array)."')  
				// AND _config.server_id = '{$_REQUEST['server_id']}' 
				// AND _config.instance_server_id = '{$_REQUEST['instance_server_id']}' 
				// AND _config.instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}' "; 
				// // echo $_sql."<BR>";
				// // exit;
				// $lists =  $this->_sqllists($_sql);     

				// $result = array();
				// for($i=0;$i<sizeof($lists);$i++){
				// 	$result[$lists[$i]['config_code']] = $lists[$i];
				// }
				// return $result;

				$config = getRedis("{$_REQUEST['instance_server_channel_id']}_CONFIG");
				// $list = array();
				if(empty($config)){
					$config = $this->getAllConfig();
				}
				
				$list = array();
				foreach($_array as $value){
					if(empty($config[$value])){
						$config = $this->getConfigByCode($config, $value);
					}

					$list[$value] = $config[$value];
				}
				return $list;
			}

			function getAllConfig(){
				// $_sql = "SELECT * FROM hms_api.comp_config_temp _config 
				// WHERE _config.server_id = '{$_REQUEST['server_id']}' 
				// AND _config.instance_server_id = '{$_REQUEST['instance_server_id']}' 
				// AND _config.instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}' ";
				// // echo "$_sql";
				// $lists_tmp =  $GLOBALS['configTempService']->_sqlget($_sql);   

				// if(empty($lists_tmp)){
				// 	$_sql="	SELECT _config.config_code, 
				// 	_config.config_group,  
				// 	_config.config_status, 
				// 	_config.config_key_1, 
				// 	_config.config_key_2, 
				// 	_config.config_key_3,  
				// 	_config.config_key_4, 
				// 	_config.config_key_5, 
				// 	_config.config_key_6  
				// 	FROM comp_config _config  
				// 	WHERE _config.server_id = '{$_REQUEST['server_id']}' 
				// 	AND _config.instance_server_id = '{$_REQUEST['instance_server_id']}' 
				// 	AND _config.instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}' "; 
				// 	// echo $_sql."<BR>";
				// 	$lists =  $this->_sqllists($_sql);  
					
				// 	$data = array();
				// 	$data['config_temp_json'] = json_encode($lists);
				// 	$data['server_id'] = $_REQUEST['server_id'];
				// 	$data['instance_server_id'] = $_REQUEST['instance_server_id'];
				// 	$data['instance_server_channel_id'] = $_REQUEST['instance_server_channel_id'];
				// 	$GLOBALS['configTempService']->server_servicecreate($data);
				// }else{
				// 	$lists = $lists_tmp['config_temp_json'];
				// }

				// $result = array();
				// for($i = 0; $i < sizeof($lists); $i++){
				// 	$result[$lists[$i]['config_code']] = $lists[$i];
				// }

				// return $result;

				$_sql="	SELECT * FROM comp_config _config  
				WHERE _config.server_id = '{$_REQUEST['server_id']}' 
				AND _config.instance_server_id = '{$_REQUEST['instance_server_id']}' 
				AND _config.instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}' "; 
				// echo $_sql."<BR>";
				$lists =  $this->_sqllists($_sql);     

				$result = array();
				for($i=0;$i<sizeof($lists);$i++){
					$result[$lists[$i]['config_code']] = $lists[$i];
				}

				setRedis("{$_REQUEST['instance_server_channel_id']}_CONFIG", $result);

				return $result;
			}

			function getConfigByCode($_config, $_code) {
				$_sql="	SELECT * FROM comp_config _config 
						WHERE _config.config_code = '{$_code}'  
						AND _config.server_id = '{$_REQUEST['server_id']}' 
						AND _config.instance_server_id = '{$_REQUEST['instance_server_id']}' 
						AND _config.instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}' "; 

				$lists =  $this->_sqlget($_sql);   
				$_config[$_code] = $lists;

				setRedis("{$_REQUEST['instance_server_channel_id']}_CONFIG", $_config);

				return $_config;
			}

			


		}
			$config_lists = array();
		$configService = new ConfigService();

?>