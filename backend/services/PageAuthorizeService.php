<? 
/*----------- HOW TO CALL THIS FUNCTION --------------*/
	 //echo "IN Group >>".PageAuthorizeService::getAuthorizeByUserGroup(array("SAL","IT","Config"))."<br>";
/*----------- HOW TO CALL THIS FUNCTION --------------*/
	
class PageAuthorizeService{ 
		
		public static function getAuthorizeByUserGroup($usergroup_code){
				$result = $GLOBALS['usergroupService']->IsUserInThisGroup($GLOBALS['userInfo']['user_id'], $usergroup_code); 
				$role = array("admincp", "super_admincp","admin-ccs","ccs","admin-acp","dealers","admin-dealers");
				if(in_array($GLOBALS['userInfo']['user_type'], $role)){
					return true;
				}else{
					if(sizeof($result)>0){
						return true;
					}else{
						return false;
					}
				}
		}

		public static function getAuthorizeByUserIDAndUserGroup($user_id,$usergroup_code){
			$result = $GLOBALS['usergroupService']->IsUserInThisGroup($user_id, $usergroup_code); 
			$role = array("admincp", "super_admincp","admin-ccs","ccs","admin-acp","dealers","admin-dealers");
			if(in_array($GLOBALS['userInfo']['user_type'], $role)){
				return true;
			}else{
				if(sizeof($result)>0){
					return true;
				}else{
					return false;
				}
			}
		}

		public static function getUserSalaryPermission(){
			if(empty($GLOBALS['userInfo']['user_secure_key'])){
				return true;
			}else{
				if($GLOBALS['userInfo']['user_secure_key'][0] == '1'){
					return true;
				}else {
					return false;
				}
			}
		}

		public static function getUserImportPermission(){
			if(empty($GLOBALS['userInfo']['user_secure_key'])){
				return true;
			}else{
				if($GLOBALS['userInfo']['user_secure_key'][2] == '1'){
					return true;
				}else {
					return false;
				}
			}
		}

	}
?>