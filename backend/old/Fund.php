<?

    include("_classes/MasterSalaryReportService.class.php");
    include("_classes/FundService.class.php");

    try {
        $salaryPermission = PageAuthorizeService::getUserSalaryPermission();
    
        $month = $masterSalaryReportService->getMasterReportByMonth($_REQUEST['year_month']);

        if($month['master_salary_month']!=''&&$_REQUEST['fund_id']!=''){
            $reportConfig = $GLOBALS['configService']->getSpecConfig('report_data');

            $elogin = $employeeLogin;
            if($reportConfig['config_key_1'] == 'N'){
                unset($employeeLogin['employee_id']);
            }
            $_REQUEST['salary_report_start_dt'] = $month['salary_report_start_dt'];
            $_REQUEST['salary_report_end_dt'] = $month['salary_report_end_dt'];
            $_REQUEST['sys_del_flag'] = 'A';    
            $_REQUEST['count_of_employee_limit'] = !empty($_REQUEST['count_of_employee_limit']) ? (int)$_REQUEST['count_of_employee_limit'] : 5000;
            $_REQUEST['check_count_of_employee'] =  isset($_REQUEST['check_count_of_employee']) ? $_REQUEST['check_count_of_employee'] : true;                                
            $listEmployee = $employeeService->getListEmployeeForReportModified($_REQUEST);
            $arrayEOH = array();
            for($i=0;$i<sizeof($listEmployee);$i++){
                $arrayEOH[] = $listEmployee[$i]['employee_id'];
            }

            $employeeLogin = $elogin;
            if(sizeof($arrayEOH)>0){
                $fund_Lists[0] = $fundService->getSpecificFund($_REQUEST['fund_id']);
                $result = array();

                $_sql = "SELECT t1.employee_id,
                t8.fund_employee_date, 
                t8.fund_employee_no, 
                t5.fund_id AS employee_fund, 
                t5.log_balance AS employee_balance,
                t8.fund_employee_balance AS employee_acc 
                FROM comp_employee t1 
                INNER JOIN {$GLOBALS['instanceServer']['instance_server_dbn']}.payroll_fund_employee_log t5 ON (t1.employee_id=t5.employee_id AND t5.master_salary_month='{$month['master_salary_month']}') 
                INNER JOIN comp_fund_employee t8 ON (t5.employee_id=t8.employee_id AND t5.fund_id=t8.fund_id) 
                INNER JOIN {$GLOBALS['instanceServer']['instance_server_dbn']}.payroll_master_salary_type t7 ON (t5.salary_type_id=t7.salary_type_id AND t7.master_salary_month='{$month['master_salary_month']}') 
                WHERE t1.server_id = '{$_REQUEST['server_id']}' 
                AND t1.instance_server_id = '{$_REQUEST['instance_server_id']}' 
                AND t1.instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}' 
                AND t7.salary_type_id='{$fund_Lists[0]['salary_type_id']}' 
                AND t5.log_balance>0 
                AND t1.employee_id IN ('".implode("','" , $arrayEOH)."') ";
                //echo $_sql."<BR>";
                $list = $userService->_sqllists($_sql);    
                
                for($i=0;$i<sizeof($list);$i++){
                    $result[$list[$i]['employee_id']]['date'][$list[$i]['employee_fund']] = $list[$i]['fund_employee_date'];
                    $result[$list[$i]['employee_id']]['no'][$list[$i]['employee_fund']] = $list[$i]['fund_employee_no'];
                    $result[$list[$i]['employee_id']]['employee'][$list[$i]['employee_fund']] += $list[$i]['employee_balance'];
                    $result[$list[$i]['employee_id']]['employee_acc'][$list[$i]['employee_fund']] = $list[$i]['employee_acc'];
                }

                $_sql = "SELECT t1.employee_id,
                t7.salary_type_name,
                t7.salary_type_name_en,
                t6.fund_id AS branch_fund,
                t6.log_balance AS branch_balance,
                t9.fund_company_balance AS branch_acc  
                FROM comp_employee t1 
                INNER JOIN {$GLOBALS['instanceServer']['instance_server_dbn']}.payroll_fund_company_log t6 ON (t1.employee_id=t6.employee_id AND t6.master_salary_month='{$month['master_salary_month']}') 
                INNER JOIN comp_fund_company t9 ON (t6.company_id=t9.company_id AND t6.fund_id=t9.fund_id) 
                INNER JOIN {$GLOBALS['instanceServer']['instance_server_dbn']}.payroll_master_salary_type t7 ON (t6.salary_type_id=t7.salary_type_id AND t7.master_salary_month='{$month['master_salary_month']}') 
                WHERE t1.server_id = '{$_REQUEST['server_id']}' 
                AND t1.instance_server_id = '{$_REQUEST['instance_server_id']}' 
                AND t1.instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}' 
                AND t7.salary_type_id='{$fund_Lists[0]['salary_type_id']}' 
                AND t6.log_balance>0 
                AND t1.employee_id IN ('".implode("','" , $arrayEOH)."') ";
                //echo $_sql."<BR>";
                $list = $userService->_sqllists($_sql);    
                
                for($i=0;$i<sizeof($list);$i++){
                    $result[$list[$i]['employee_id']]['branch'][$list[$i]['branch_fund']] += $list[$i]['branch_balance'];
                    $result[$list[$i]['employee_id']]['branch_acc'][$list[$i]['employee_fund']] = $list[$i]['branch_acc'];
                }

                $_sql = "SELECT t1.* 
                FROM comp_employee t1 
                INNER JOIN {$GLOBALS['instanceServer']['instance_server_dbn']}.payroll_fund_employee_log t5 ON (t1.employee_id=t5.employee_id AND t5.master_salary_month='{$month['master_salary_month']}') 
                INNER JOIN comp_fund_employee t8 ON (t5.employee_id=t8.employee_id AND t5.fund_id=t8.fund_id) 
                INNER JOIN {$GLOBALS['instanceServer']['instance_server_dbn']}.payroll_master_salary_type t7 ON (t5.salary_type_id=t7.salary_type_id AND t7.master_salary_month='{$month['master_salary_month']}') 
                WHERE t1.server_id = '{$_REQUEST['server_id']}' 
                AND t1.instance_server_id = '{$_REQUEST['instance_server_id']}' 
                AND t1.instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}' 
                AND t7.salary_type_id='{$fund_Lists[0]['salary_type_id']}' 
                AND t5.log_balance>0 
                AND t1.employee_id IN ('".implode("','" , $arrayEOH)."') 
                GROUP BY t1.employee_id ";
                //echo $_sql."<BR>";
                $list = $userService->_sqllists($_sql);  

                // $list = $listEmployee; 

                $reportArray = array();
                for($i=0;$i<sizeof($list);$i++){  
                    if(!is_array($reportArray[$list[$i]["company_id"]])){
                        $reportArray[$list[$i]["company_id"]] = array();	
                    }
                                    
                    if(!is_array($reportArray[$list[$i]["company_id"]][$list[$i]["branch_id"]])){
                        $reportArray[$list[$i]["company_id"]][$list[$i]["branch_id"]] = array();
                    }
                    
                    if(!is_array($reportArray[$list[$i]["company_id"]][$list[$i]["branch_id"]][$list[$i]["department_id"]])){
                        $reportArray[$list[$i]["company_id"]][$list[$i]["branch_id"]][$list[$i]["department_id"]] = array();	
                    }

                    $reportArray[$list[$i]["company_id"]][$list[$i]["branch_id"]][$list[$i]["department_id"]][$list[$i]["employee_id"]] = $list[$i];
                }

                $topic = $_lang == 'TH' ? "รายงานกองทุน".$fund_Lists[0]['salary_type_name']." ".month_year($month['master_salary_month']) : "Fund-".$fund_Lists[0]['salary_type_name_en']." report ".month_year($month['master_salary_month']);

                include($_REQUEST['_action']."/".$_REQUEST['type'].".php");
            }
        }
    } catch (Throwable $e) {
        if ($e->getMessage() == 'employee-overlimit') {
            header("HTTP/1.0 413 Payload Too Large");
            $jsonResponse["code"] = '413';
            $jsonResponse["payload"] = "Payload Too Large";
        } else {
            $jsonResponse["code"] = '500';
            $jsonResponse["payload"] = "undefined";
        }
    }
    
?> 