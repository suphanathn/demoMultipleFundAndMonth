<?
    /*---------------------  Begin Data  ---------------------*/
        $columnName = array();
        $row = 0;
        $columnName[$row][] = array("data"=>$translate['employee_code'],"align"=>"center","colspan"=>"1", "rowspan"=>"2");
        $columnName[$row][] = array("data"=>$translate['employee_name'],"align"=>"center","colspan"=>"1", "rowspan"=>"2");
        for($fd=0;$fd<sizeof($fund_Lists);$fd++){
            $fund_Lists[$fd]['fund_name'] = $_lang == 'TH' ? $fund_Lists[$fd]['salary_type_name'] : $fund_Lists[$fd]['salary_type_name_en'];
            $columnName[$row][] = array("data"=>$fund_Lists[$fd]['fund_name'],"align"=>"center","colspan"=>"4");
        }
        $row++;
        // $columnName[$row][] = array("data"=>"","align"=>"center","colspan"=>"1");
        // $columnName[$row][] = array("data"=>"","align"=>"center","colspan"=>"1");
        for($fd=0;$fd<sizeof($fund_Lists);$fd++){
            $columnName[$row][] = array("data"=>$translate['date_register'],"align"=>"center","colspan"=>"1");
            $columnName[$row][] = array("data"=>$translate['account_number'],"align"=>"center","colspan"=>"1");
            $columnName[$row][] = array("data"=>$translate['deduction_of_employees'],"align"=>"center","colspan"=>"1");
            $columnName[$row][] = array("data"=>$translate['deduction_of_company'],"align"=>"center","colspan"=>"1");
            // $columnName[$row][] = array("data"=>"หักพนักงานสะสม","align"=>"center","colspan"=>"1");
            // $columnName[$row][] = array("data"=>"บริษัทสมทบสะสม","align"=>"center","colspan"=>"1");
        }

        $data = array();
        $row = 0;
        $sumAll = array(); 
        $company_Keys = array_keys($reportArray); 
        for($c=0;$c<sizeof($company_Keys);$c++){ 
            $data[$row][] = array("data"=>$labelCompanysCode[$company_Keys[$c]]." : ".$labelCompanys[$company_Keys[$c]],"align"=>"left","colspan"=>2);
            for($fd=0;$fd<sizeof($fund_Lists);$fd++){
                $data[$row][] = array("data"=>"","align"=>"center","colspan"=>"1");
                $data[$row][] = array("data"=>"","align"=>"center","colspan"=>"1");
                $data[$row][] = array("data"=>"","align"=>"center","colspan"=>"1");
                $data[$row][] = array("data"=>"","align"=>"center","colspan"=>"1");
                // $data[$row][] = array("data"=>"","align"=>"center","colspan"=>"1");
                // $data[$row][] = array("data"=>"","align"=>"center","colspan"=>"1");
            }
            $row++;
                $sumCompany = array();
                $branch_Keys = array_keys($reportArray[$company_Keys[$c]]);
                for($b=0;$b<sizeof($branch_Keys);$b++){
                    $data[$row][] = array("data"=>$labelBranchsCode[$branch_Keys[$b]]." : ".$labelBranchs[$branch_Keys[$b]],"align"=>"left","colspan"=>2);
                    for($fd=0;$fd<sizeof($fund_Lists);$fd++){
                        $data[$row][] = array("data"=>"","align"=>"center","colspan"=>"1");
                        $data[$row][] = array("data"=>"","align"=>"center","colspan"=>"1");
                        $data[$row][] = array("data"=>"","align"=>"center","colspan"=>"1");
                        $data[$row][] = array("data"=>"","align"=>"center","colspan"=>"1");
                        // $data[$row][] = array("data"=>"","align"=>"center","colspan"=>"1");
                        // $data[$row][] = array("data"=>"","align"=>"center","colspan"=>"1");
                    }
                    $row++;
                    $sumBranch = array();
                    $department_Keys = array_keys($reportArray[$company_Keys[$c]][$branch_Keys[$b]]);
                    for($d=0;$d<sizeof($department_Keys);$d++){
                        $data[$row][] = array("data"=>$labelDepartmentsCode[$department_Keys[$d]]." : ".$labelDepartments[$department_Keys[$d]],"align"=>"left","colspan"=>2);
                        for($fd=0;$fd<sizeof($fund_Lists);$fd++){
                            $data[$row][] = array("data"=>"","align"=>"center","colspan"=>"1");
                            $data[$row][] = array("data"=>"","align"=>"center","colspan"=>"1");
                            $data[$row][] = array("data"=>"","align"=>"center","colspan"=>"1");
                            $data[$row][] = array("data"=>"","align"=>"center","colspan"=>"1");
                            // $data[$row][] = array("data"=>"","align"=>"center","colspan"=>"1");
                            // $data[$row][] = array("data"=>"","align"=>"center","colspan"=>"1");
                        }
                        $row++;
                            $sumDepartment = array();
                            $employee_Keys = array_keys($reportArray[$company_Keys[$c]][$branch_Keys[$b]][$department_Keys[$d]]);
                            for($e=0;$e<sizeof($employee_Keys);$e++){
                                $empList = $reportArray[$company_Keys[$c]][$branch_Keys[$b]][$department_Keys[$d]][$employee_Keys[$e]];
                                $empList['employee_name'] = $_lang == 'TH' ? $empList['employee_name'] : $empList['employee_name_en'];
								$empList['employee_last_name'] = $_lang == 'TH' ? $empList['employee_last_name'] : $empList['employee_last_name_en'];
                                if($empList['sys_del_flag']=='Y'){ $color="FF0000"; }else{ $color="000000"; } 
                                $data[$row][] = array("data"=>$empList['employee_code'],"align"=>"center","colspan"=>"1","color"=>$color);
                                $data[$row][] = array("data"=>$empList['employee_name']." ".$empList['employee_last_name'],"align"=>"left","colspan"=>"1","color"=>$color);
                                for($fd=0;$fd<sizeof($fund_Lists);$fd++){
                                    $data[$row][] = array("data"=>anyDate('d/m/Y',$result[$empList['employee_id']]['date'][$fund_Lists[$fd]['fund_id']],'EN'),"align"=>"center","colspan"=>"1","setdate"=>"Y","color"=>$color);
                                    $data[$row][] = array("data"=>$result[$empList['employee_id']]['no'][$fund_Lists[$fd]['fund_id']],"align"=>"center","colspan"=>"1","color"=>$color);
                                    $data[$row][] = array("data"=>$result[$empList['employee_id']]['employee'][$fund_Lists[$fd]['fund_id']],"align"=>"right","colspan"=>"1","setint"=>"Y","color"=>$color);
                                    $data[$row][] = array("data"=>$result[$empList['employee_id']]['branch'][$fund_Lists[$fd]['fund_id']],"align"=>"right","colspan"=>"1","setint"=>"Y","color"=>$color);
                                    // $data[$row][] = array("data"=>$result[$empList['employee_id']]['employee_acc'][$fund_Lists[$fd]['fund_id']],"align"=>"right","colspan"=>"1","setint"=>"Y","color"=>$color);
                                    // $data[$row][] = array("data"=>$result[$empList['employee_id']]['branch_acc'][$fund_Lists[$fd]['fund_id']],"align"=>"right","colspan"=>"1","setint"=>"Y","color"=>$color);
                                    $sumDepartment['fund_employee'][$fund_Lists[$fd]['fund_id']] += $result[$empList['employee_id']]['employee'][$fund_Lists[$fd]['fund_id']];
                                    $sumDepartment['fund_branch'][$fund_Lists[$fd]['fund_id']] += $result[$empList['employee_id']]['branch'][$fund_Lists[$fd]['fund_id']];
                                    $sumDepartment['fund_employee_acc'][$fund_Lists[$fd]['fund_id']] += $result[$empList['employee_id']]['employee_acc'][$fund_Lists[$fd]['fund_id']];
                                    $sumDepartment['fund_branch_acc'][$fund_Lists[$fd]['fund_id']] += $result[$empList['employee_id']]['branch_acc'][$fund_Lists[$fd]['fund_id']];
                                }
                                $sumDepartment['count_employee']++;
                                $row++;
                            }
                        $data[$row][] = array("data"=>"{$translate['total_number_in_department']} ".$sumDepartment['count_employee']." {$translate['employees']}","align"=>"right","colspan"=>"2");
                        for($fd=0;$fd<sizeof($fund_Lists);$fd++){ 
                            $data[$row][] = array("data"=>"","align"=>"center","colspan"=>"1");
                            $data[$row][] = array("data"=>"","align"=>"center","colspan"=>"1");
                            $data[$row][] = array("data"=>$sumDepartment['fund_employee'][$fund_Lists[$fd]['fund_id']],"align"=>"right","colspan"=>"1","setint"=>"Y");
                            $data[$row][] = array("data"=>$sumDepartment['fund_branch'][$fund_Lists[$fd]['fund_id']],"align"=>"right","colspan"=>"1","setint"=>"Y");
                            // $data[$row][] = array("data"=>$sumDepartment['fund_employee_acc'][$fund_Lists[$fd]['fund_id']],"align"=>"right","colspan"=>"1","setint"=>"Y");
                            // $data[$row][] = array("data"=>$sumDepartment['fund_branch_acc'][$fund_Lists[$fd]['fund_id']],"align"=>"right","colspan"=>"1","setint"=>"Y");
                            $sumBranch['fund_employee'][$fund_Lists[$fd]['fund_id']] += $sumDepartment['fund_employee'][$fund_Lists[$fd]['fund_id']]; 
                            $sumBranch['fund_branch'][$fund_Lists[$fd]['fund_id']] += $sumDepartment['fund_branch'][$fund_Lists[$fd]['fund_id']]; 
                            $sumBranch['fund_employee_acc'][$fund_Lists[$fd]['fund_id']] += $sumDepartment['fund_employee_acc'][$fund_Lists[$fd]['fund_id']]; 
                            $sumBranch['fund_branch_acc'][$fund_Lists[$fd]['fund_id']] += $sumDepartment['fund_branch_acc'][$fund_Lists[$fd]['fund_id']]; 
                        } 
                        $sumBranch['count_employee'] +=$sumDepartment['count_employee'];
                        $row++;
                    }
                    $data[$row][] = array("data"=>"{$translate['total_number_in_branch']} ".$sumBranch['count_employee']." {$translate['employees']}","align"=>"right","colspan"=>"2");
                    for($fd=0;$fd<sizeof($fund_Lists);$fd++){ 
                        $data[$row][] = array("data"=>"","align"=>"center","colspan"=>"1");
                        $data[$row][] = array("data"=>"","align"=>"center","colspan"=>"1");
                        $data[$row][] = array("data"=>$sumBranch['fund_employee'][$fund_Lists[$fd]['fund_id']],"align"=>"right","colspan"=>"1","setint"=>"Y");
                        $data[$row][] = array("data"=>$sumBranch['fund_branch'][$fund_Lists[$fd]['fund_id']],"align"=>"right","colspan"=>"1","setint"=>"Y");
                        // $data[$row][] = array("data"=>$sumBranch['fund_employee_acc'][$fund_Lists[$fd]['fund_id']],"align"=>"right","colspan"=>"1","setint"=>"Y");
                        // $data[$row][] = array("data"=>$sumBranch['fund_branch_acc'][$fund_Lists[$fd]['fund_id']],"align"=>"right","colspan"=>"1","setint"=>"Y");
                        $sumCompany['fund_employee'][$fund_Lists[$fd]['fund_id']] += $sumBranch['fund_employee'][$fund_Lists[$fd]['fund_id']]; 
                        $sumCompany['fund_branch'][$fund_Lists[$fd]['fund_id']] += $sumBranch['fund_branch'][$fund_Lists[$fd]['fund_id']]; 
                        $sumCompany['fund_employee_acc'][$fund_Lists[$fd]['fund_id']] += $sumBranch['fund_employee_acc'][$fund_Lists[$fd]['fund_id']]; 
                        $sumCompany['fund_branch_acc'][$fund_Lists[$fd]['fund_id']] += $sumBranch['fund_branch_acc'][$fund_Lists[$fd]['fund_id']]; 
                    } 
                    $sumCompany['count_employee'] +=$sumBranch['count_employee'];
                    $row++;
                }
            $data[$row][] = array("data"=>"{$translate['total_number_in_company']} ".$sumCompany['count_employee']." {$translate['employees']}","align"=>"right","colspan"=>"2");
            for($fd=0;$fd<sizeof($fund_Lists);$fd++){ 
                $data[$row][] = array("data"=>"","align"=>"center","colspan"=>"1");
                $data[$row][] = array("data"=>"","align"=>"center","colspan"=>"1");
                $data[$row][] = array("data"=>$sumCompany['fund_employee'][$fund_Lists[$fd]['fund_id']],"align"=>"right","colspan"=>"1","setint"=>"Y");
                $data[$row][] = array("data"=>$sumCompany['fund_branch'][$fund_Lists[$fd]['fund_id']],"align"=>"right","colspan"=>"1","setint"=>"Y");
                // $data[$row][] = array("data"=>$sumCompany['fund_employee_acc'][$fund_Lists[$fd]['fund_id']],"align"=>"right","colspan"=>"1","setint"=>"Y");
                // $data[$row][] = array("data"=>$sumCompany['fund_branch_acc'][$fund_Lists[$fd]['fund_id']],"align"=>"right","colspan"=>"1","setint"=>"Y");
                $sumAll['fund_employee'][$fund_Lists[$fd]['fund_id']] += $sumCompany['fund_employee'][$fund_Lists[$fd]['fund_id']]; 
                $sumAll['fund_branch'][$fund_Lists[$fd]['fund_id']] += $sumCompany['fund_branch'][$fund_Lists[$fd]['fund_id']]; 
                $sumAll['fund_employee_acc'][$fund_Lists[$fd]['fund_id']] += $sumCompany['fund_employee_acc'][$fund_Lists[$fd]['fund_id']]; 
                $sumAll['fund_branch_acc'][$fund_Lists[$fd]['fund_id']] += $sumCompany['fund_branch_acc'][$fund_Lists[$fd]['fund_id']]; 
            } 
            $sumAll['count_employee'] +=$sumCompany['count_employee'];
            $row++;
        }
        $data[$row][] = array("data"=>"{$translate['total_grand']} ".$sumAll['count_employee']." {$translate['employees']}","align"=>"right","colspan"=>"2");
        for($fd=0;$fd<sizeof($fund_Lists);$fd++){ 
            $data[$row][] = array("data"=>"","align"=>"center","colspan"=>"1");
            $data[$row][] = array("data"=>"","align"=>"center","colspan"=>"1");
            $data[$row][] = array("data"=>$sumAll['fund_employee'][$fund_Lists[$fd]['fund_id']],"align"=>"right","colspan"=>"1","setint"=>"Y");
            $data[$row][] = array("data"=>$sumAll['fund_branch'][$fund_Lists[$fd]['fund_id']],"align"=>"right","colspan"=>"1","setint"=>"Y");
            // $data[$row][] = array("data"=>$sumAll['fund_employee_acc'][$fund_Lists[$fd]['fund_id']],"align"=>"right","colspan"=>"1","setint"=>"Y");
            // $data[$row][] = array("data"=>$sumAll['fund_branch_acc'][$fund_Lists[$fd]['fund_id']],"align"=>"right","colspan"=>"1","setint"=>"Y");
        } 
        $row++;
    /*---------------------  End Data  ---------------------*/
    $result = array();
    $result['column'] = $columnName;
    $result['row'] = $data;

    //$jsonResponse["message"] = "Fund Report (".$_REQUEST['year_month'].") Accept";
    $jsonResponse["payload"] = $result;
    
?>