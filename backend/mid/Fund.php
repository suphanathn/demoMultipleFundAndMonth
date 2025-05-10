<?php

    include("_classes/MasterSalaryReportService.class.php");
    include("_classes/FundService.class.php");



    try {
        // Initialize services
        if (!isset($masterSalaryReportService)) {
            $masterSalaryReportService = new MasterSalaryReportService();
        }
        if (!isset($fundService)) {
            $fundService = new FundService();
        }
        if (!isset($employeeService)) {
            // Assuming EmployeeService class exists and is needed.
            // If not, this line (and its include) can be removed if $employeeService is not used elsewhere.
            $employeeService = new EmployeeService();
        }
        if (!isset($userService)) {
            $userService = new UserService();
        }

        // Check available methods - This block seems for debugging and can be kept as is.
        try {
            if (isset($userService) && class_exists('UserService') && method_exists($userService, 'get_class_methods')) { // Defensive check
                $methods = get_class_methods($userService);
                error_log("Available methods in UserService: " . implode(", ", $methods));
            }
            
            // Define a helper function to use the correct method
            // Ensure the function is defined only once, if this script can be included multiple times.
            if (!function_exists('executeQuery')) {
                function executeQuery($service, $query, $isServerQuery = false) {
                    // Ensure $_REQUEST variables are set before accessing them to avoid notices
                    $serverId = isset($_REQUEST['server_id']) ? $_REQUEST['server_id'] : null;
                    $instanceServerId = isset($_REQUEST['instance_server_id']) ? $_REQUEST['instance_server_id'] : null;
                    $instanceServerChannelId = isset($_REQUEST['instance_server_channel_id']) ? $_REQUEST['instance_server_channel_id'] : null;

                    if ($isServerQuery && (!$serverId || !$instanceServerId || !$instanceServerChannelId)) {
                        error_log("Missing server parameters for server query.");
                        // Decide how to handle this: throw exception or return error/false
                        // For now, let's log and potentially let it fail in the service call if it relies on them.
                    }

                    try {
                        if ($isServerQuery) {
                            $serverParams = array(
                                'server_id' => $serverId,
                                'instance_server_id' => $instanceServerId,
                                'instance_server_channel_id' => $instanceServerChannelId
                            );
                            
                            error_log("Server parameters: " . print_r($serverParams, true));
                            error_log("Original query: " . $query);
                            
                            $hasT1Alias = stripos($query, "t1.") !== false;
                            $tablePrefix = $hasT1Alias ? "t1." : "";
                            
                            // Ensure serverParams values are properly escaped if directly injecting into SQL,
                            // though ideally, parameterized queries should be used by the service methods.
                            // For this example, assuming service methods handle sanitization/escaping.
                            $serverConditions = "{$tablePrefix}server_id = '{$serverParams['server_id']}' 
                                AND {$tablePrefix}instance_server_id = '{$serverParams['instance_server_id']}' 
                                AND {$tablePrefix}instance_server_channel_id = '{$serverParams['instance_server_channel_id']}'";
                                
                            if (stripos($query, "WHERE") !== false) {
                                // This replacement logic for "WHERE 1=1 AND" might be too specific.
                                // A more robust way would be to parse the query or ensure a consistent query structure.
                                $query = preg_replace("/WHERE\s+1\s*=\s*1\s*AND/i", "WHERE", $query, 1); // Limit to 1 replacement
                                $query = preg_replace("/WHERE/i", "WHERE " . $serverConditions . " AND ", $query, 1);
                            } else {
                                $query .= " WHERE " . $serverConditions;
                            }
                            
                            error_log("Modified query with server params: " . $query);
                            // Ensure $service is an object and the method exists
                            if (is_object($service) && method_exists($service, '_serversqllists')) {
                                return $service->_serversqllists($query);
                            } else {
                                error_log("Service object or _serversqllists method not available.");
                                return false; // Or throw exception
                            }
                        } else {
                             // For non-server queries, if server_id is still mandatory by _sqllists
                            if ($serverId && stripos($query, "server_id") === false) {
                                // This logic might also be too simplistic for adding server_id.
                                // It assumes WHERE exists or can be appended.
                                if (stripos($query, "WHERE") !== false) {
                                    $query = preg_replace("/WHERE/i", "WHERE server_id = '{$serverId}' AND ", $query, 1);
                                } else {
                                     // Check if query has GROUP BY, ORDER BY, LIMIT to append WHERE correctly
                                    $query .= " WHERE server_id = '{$serverId}'";
                                }
                            }
                            // Ensure $service is an object and the method exists
                            if (is_object($service) && method_exists($service, '_sqllists')) {
                                return $service->_sqllists($query);
                            } else {
                                error_log("Service object or _sqllists method not available.");
                                return false; // Or throw exception
                            }
                        }
                    } catch (Exception $e) {
                        error_log("Query execution error: " . $e->getMessage());
                        error_log("Query: " . $query);
                        error_log("Is server query: " . ($isServerQuery ? "Yes" : "No"));
                        throw $e;
                    }
                }
            }
            
            // Test connection with server parameters
            try {
                // Ensure $_REQUEST parameters are set before this test
                if (isset($_REQUEST['server_id']) && isset($_REQUEST['instance_server_id']) && isset($_REQUEST['instance_server_channel_id'])) {
                    $checkQuery = "SELECT 1 as test FROM comp_employee t1"; // t1 alias implies server query context
                    $testRs = executeQuery($userService, $checkQuery, true);
                    error_log("Database connection test with server parameters: " . ($testRs ? "Success" : "Failed"));
                } else {
                    error_log("Skipping DB connection test: Missing server parameters in \$_REQUEST.");
                }
            } catch (Exception $e) {
                error_log("DB Connection Test/Method check error: " . $e->getMessage());
            }
        } catch (Exception $e) {
            error_log("Outer Method check error: " . $e->getMessage());
        }

        // Validate required parameters
        // Ensure the function is defined only once
        if (!function_exists('validateRequest')) {
            function validateRequest() {
                if (empty($_REQUEST['start_month']) || empty($_REQUEST['end_month'])) {
                    throw new Exception('Missing required parameters: start_month and end_month');
                }

                if (empty($_REQUEST['fund_ids'])) {
                    throw new Exception('Missing required parameter: fund_ids');
                }
                // Ensure fund_ids is an array
                if (!is_array($_REQUEST['fund_ids'])) {
                     throw new Exception('Invalid parameter: fund_ids must be an array.');
                }


                if (empty($_REQUEST['server_id']) || empty($_REQUEST['instance_server_id']) || empty($_REQUEST['instance_server_channel_id'])) {
                    throw new Exception('Missing required server parameters');
                }

                if (!preg_match('/^\d{4}-\d{2}$/', $_REQUEST['start_month']) || 
                    !preg_match('/^\d{4}-\d{2}$/', $_REQUEST['end_month'])) {
                    throw new Exception('Invalid date format. Expected format: YYYY-MM');
                }

                $startDate = new DateTime($_REQUEST['start_month'] . '-01');
                $endDate = new DateTime($_REQUEST['end_month'] . '-01');
                
                // To include the end_month in the period, the endDate for DatePeriod should be one day after the target end month's first day
                // Or, adjust the DatePeriod to include the end month explicitly.
                // For month difference, this is fine.

                if ($startDate > $endDate) {
                    throw new Exception('Start month cannot be after end month');
                }
                
                $interval = $startDate->diff($endDate);
                $monthsDiff = ($interval->y * 12) + $interval->m;

                if ($monthsDiff > 12) { // Max 13 months if start=Jan, end=Jan next year (0-12)
                    throw new Exception('Date range cannot exceed 12 months difference (e.g., Jan to Dec is 11 months diff, Jan to Jan next year is 12 months diff)');
                }
            }
        }
        
        validateRequest(); // Call validation

        // Log request data for debugging
        error_log("Processing report for months: " . $_REQUEST['start_month'] . " to " . $_REQUEST['end_month']);
        error_log("Processing report for funds: " . (is_array($_REQUEST['fund_ids']) ? implode(", ", $_REQUEST['fund_ids']) : 'Invalid fund_ids'));
        error_log("Server parameters: " . print_r([
            'server_id' => $_REQUEST['server_id'],
            'instance_server_id' => $_REQUEST['instance_server_id'],
            'instance_server_channel_id' => $_REQUEST['instance_server_channel_id']
        ], true));

        // Decode and log company ID
        if (!empty($_REQUEST['company_lists'])) {
            if (!is_array($_REQUEST['company_lists'])) {
                 throw new Exception('Invalid parameter: company_lists must be an array.');
            }
            foreach ($_REQUEST['company_lists'] as $company) {
                if (!isset($company['id'])) {
                    error_log("Warning: Company entry missing 'id'. Entry: " . print_r($company, true));
                    continue; // Skip this entry
                }
                error_log("Company ID (encoded): " . $company['id']);
                $decodedCompanyId = base64_decode($company['id']);
                error_log("Company ID (decoded): " . $decodedCompanyId);
                
                $companyCheckQuery = "SELECT company_id FROM comp_company 
                    WHERE server_id = '{$_REQUEST['server_id']}'
                    AND instance_server_id = '{$_REQUEST['instance_server_id']}'
                    AND instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}'
                    AND company_id = '{$decodedCompanyId}'";
                // Using $userService, assuming it has the generic query execution methods
                $companyCheck = executeQuery($userService, $companyCheckQuery, false); // Server params are in the query itself
                error_log("Company check result for {$decodedCompanyId}: " . print_r($companyCheck, true));

                if (empty($companyCheck)) {
                    throw new Exception("Company not found or not accessible: " . $decodedCompanyId);
                }
            }
        }

        $salaryPermission = null;
        if (class_exists('PageAuthorizeService')) {
            $salaryPermission = PageAuthorizeService::getUserSalaryPermission();
            error_log("Salary permission: " . print_r($salaryPermission, true));
        } else {
            error_log("PageAuthorizeService class not found.");
        }
    
        // Get month data with retry mechanism
        $months = array();
        // Adjust endDate for DatePeriod to include the end_month itself.
        $periodStartDate = new DateTime($_REQUEST['start_month'] . '-01');
        $periodEndDate = new DateTime($_REQUEST['end_month'] . '-01');
        $periodEndDate->modify('+1 month'); // Iterate up to, but not including, the month after end_month

        $interval = new DateInterval('P1M');
        $period = new DatePeriod($periodStartDate, $interval, $periodEndDate);

        error_log("Starting month data retrieval process for period: " . $_REQUEST['start_month'] . " to " . $_REQUEST['end_month']);

        foreach ($period as $date) {
            $monthKey = $date->format('Y-m');
            $maxRetries = 3;
            $retryCount = 0;
            $monthData = null;
        
            while ($retryCount < $maxRetries && $monthData === null) {
                try {
                    error_log("Attempt " . ($retryCount + 1) . " - Retrieving month data for: " . $monthKey);
                    $currentMonthData = $masterSalaryReportService->getMasterReportByMonth($monthKey);
        
                    error_log("Attempt " . ($retryCount + 1) . " - Month raw data for {$monthKey}: " . print_r($currentMonthData, true));
        
                    if (!empty($currentMonthData) && isset($currentMonthData['master_salary_month'])) {
                        error_log("Month data structure for {$monthKey}: " . print_r(array_keys($currentMonthData), true));
                        //  Refactored:  Only pick the fields we need
                        $months[] = array(
                            'master_salary_month' => $currentMonthData['master_salary_month'],
                            'salary_report_start_dt' => $currentMonthData['salary_report_start_dt'],
                            'salary_report_end_dt' => $currentMonthData['salary_report_end_dt']
                        );
                        $monthData = $currentMonthData;
                    } else {
                        error_log("Month data for {$monthKey} is empty or invalid after attempt " . ($retryCount + 1));
                        $retryCount++;
                        if ($retryCount < $maxRetries) {
                            sleep(1);
                        }
                    }
                } catch (Exception $e) {
                    error_log("Error retrieving month data for {$monthKey} on attempt " . ($retryCount + 1) . ": " . $e->getMessage());
                    error_log("Error trace: " . $e->getTraceAsString());
                    $retryCount++;
                    if ($retryCount < $maxRetries) {
                        sleep(1);
                    }
                }
            }
            if ($monthData === null) {
                error_log("Failed to retrieve month data for {$monthKey} after {$maxRetries} attempts.");
            }
        }


        if (empty($months)) {
            // This check is after attempting to fetch all months in the range.
            throw new Exception("No valid months data found in the selected range after retries.");
        }

        error_log("Month data retrieval completed.");
        error_log("Final collected months data state (" . count($months) . " months):");
        // error_log("Months: " . print_r($months, true)); // Can be very verbose

        // Define $startMonth and $endMonth from the successfully fetched $months array
        $startMonthMeta = null;
        $endMonthMeta = null;

        if (!empty($months)) {
            $startMonthMeta = $months[0]; // First successfully fetched month in the range
            $endMonthMeta = end($months);   // Last successfully fetched month in the range
            // Reset array pointer if end() was used and $months is iterated later
            reset($months);
        }
        
        // Check if $startMonthMeta and $endMonthMeta are populated and have the required key
        if(isset($startMonthMeta['master_salary_month']) && $startMonthMeta['master_salary_month'] !='' && 
           isset($endMonthMeta['master_salary_month']) && $endMonthMeta['master_salary_month'] !='' && 
           !empty($_REQUEST['fund_ids'])) {
            
            $reportConfig = isset($GLOBALS['configService']) ? $GLOBALS['configService']->getSpecConfig('report_data') : null;
            if ($reportConfig) {
                error_log("Report config: " . print_r($reportConfig, true));
            } else {
                error_log("Warning: Global configService not available or report_data config missing.");
                $reportConfig = ['config_key_1' => 'Y']; // Default behavior if config is missing
            }


            $elogin = isset($employeeLogin) ? $employeeLogin : null; // Ensure $employeeLogin is defined
            if ($elogin && isset($reportConfig['config_key_1']) && $reportConfig['config_key_1'] == 'N'){
                unset($employeeLogin['employee_id']); // $employeeLogin needs to be the actual variable
            }

            error_log("Employee login data (after potential unset): " . print_r(isset($employeeLogin) ? $employeeLogin : null, true));

            // Use $startMonthMeta and $endMonthMeta for these values
            $_REQUEST['salary_report_start_dt'] = $startMonthMeta['salary_report_start_dt'] ?? null;
            $_REQUEST['salary_report_end_dt'] = $endMonthMeta['salary_report_end_dt'] ?? null;
            $_REQUEST['sys_del_flag'] = $_REQUEST['sys_del_flag'] ?? 'A'; // Use existing if set, else default to 'A'   
            $_REQUEST['count_of_employee_limit'] = !empty($_REQUEST['count_of_employee_limit']) ? (int)$_REQUEST['count_of_employee_limit'] : 5000;
            $_REQUEST['check_count_of_employee'] =  isset($_REQUEST['check_count_of_employee']) ? $_REQUEST['check_count_of_employee'] : true;                                
            
            error_log("Request parameters for employee list (updated): " . print_r($_REQUEST, true));
            
            if (!empty($_REQUEST['company_lists'])) {
                 if (!is_array($_REQUEST['company_lists'])) { // Redundant check, already done in validateRequest
                     throw new Exception('Invalid parameter: company_lists must be an array.');
                 }
                foreach ($_REQUEST['company_lists'] as $company) {
                    if (!isset($company['id'])) continue;
                    $decodedCompanyId = base64_decode($company['id']);
                    $employeeCheckQuery = "SELECT COUNT(*) as count 
                        FROM comp_employee 
                        WHERE server_id = '{$_REQUEST['server_id']}'
                        AND instance_server_id = '{$_REQUEST['instance_server_id']}'
                        AND instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}'
                        AND company_id = '{$decodedCompanyId}'";
                     // Using $userService, assuming it has the generic query execution methods
                    $employeeCheck = executeQuery($userService, $employeeCheckQuery, false); // Server params are in the query
                    error_log("Employee count in company {$decodedCompanyId}: " . print_r($employeeCheck, true));

                    if (empty($employeeCheck) || !isset($employeeCheck[0]['count']) || $employeeCheck[0]['count'] == 0) {
                        throw new Exception("No employees found in company: " . $decodedCompanyId);
                    }
                }
            }
            
            // This try-catch is for the main data fetching logic for employees and funds
            try {
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
                WHERE t1.server_id = '{$_REQUEST['server_id']}'
                    AND t1.instance_server_id = '{$_REQUEST['instance_server_id']}'
                    AND t1.instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}'";

                if (!empty($_REQUEST['company_lists'])) {
                    $companyIds = array_map(function($company) { 
                        return isset($company['id']) ? base64_decode($company['id']) : null; 
                    }, $_REQUEST['company_lists']);
                    $companyIds = array_filter($companyIds); // Remove nulls
                    if (!empty($companyIds)) {
                        $_sql_employee_list .= " AND t1.company_id IN ('" . implode("','", $companyIds) . "')";
                    }
                }

                if (!empty($_REQUEST['branch_lists'])) {
                     if (!is_array($_REQUEST['branch_lists'])) throw new Exception('branch_lists must be an array');
                    $branchIds = array_map(function($branch) { 
                        return isset($branch['id']) ? base64_decode($branch['id']) : null; 
                    }, $_REQUEST['branch_lists']);
                    $branchIds = array_filter($branchIds);
                     if (!empty($branchIds)) {
                        $_sql_employee_list .= " AND t1.branch_id IN ('" . implode("','", $branchIds) . "')";
                    }
                }

                if (!empty($_REQUEST['department_lists'])) {
                    if (!is_array($_REQUEST['department_lists'])) throw new Exception('department_lists must be an array');
                    $departmentIds = array_map(function($department) { 
                        return isset($department['id']) ? base64_decode($department['id']) : null;
                    }, $_REQUEST['department_lists']);
                    $departmentIds = array_filter($departmentIds);
                    if (!empty($departmentIds)) {
                        $_sql_employee_list .= " AND t1.department_id IN ('" . implode("','", $departmentIds) . "')";
                    }
                }

                if (isset($_REQUEST['sys_del_flag']) && $_REQUEST['sys_del_flag'] !== 'A') { // 'A' might mean all/any
                    $_sql_employee_list .= " AND t1.sys_del_flag = '{$_REQUEST['sys_del_flag']}'";
                }
                // Add ORDER BY for consistent results, if necessary
                // $_sql_employee_list .= " ORDER BY t1.company_id, t1.branch_id, t1.department_id, t1.employee_code";


                error_log("Employee list query: " . $_sql_employee_list);
                // Using $employeeService or $userService for this query. Let's assume $userService for consistency with executeQuery
                $listEmployee = executeQuery($userService, $_sql_employee_list, false); // Server params are in the query
                error_log("Employee list count: " . (is_array($listEmployee) ? count($listEmployee) : 0));

                if (empty($listEmployee)) {
                    error_log("No employees found with the given criteria");
                    // Depending on requirements, this might not be an exception if it's a valid outcome.
                    // For now, matching original logic:
                    throw new Exception("No employees found for the report based on current filters.");
                }

                $arrayEOH = array(); // Employee IDs for subsequent queries
                if (is_array($listEmployee)) {
                    foreach($listEmployee as $emp) {
                        if (isset($emp['employee_id'])) {
                            $arrayEOH[] = $emp['employee_id'];
                        }
                    }
                }
                error_log("Employee IDs for fund data (" . count($arrayEOH) . "): " . implode(", ", array_slice($arrayEOH, 0, 10)) . (count($arrayEOH) > 10 ? "..." : ""));


                if(isset($employeeLogin) && $elogin) { // Restore $employeeLogin if it was modified
                    $employeeLogin = $elogin;
                }

                if(!empty($arrayEOH)){ // Check if there are employee IDs to process
                    $fund_Lists = array();
                    if (is_array($_REQUEST['fund_ids'])) {
                        foreach($_REQUEST['fund_ids'] as $fund_id_encoded) {
                            try {
                                $decodedFundId = base64_decode($fund_id_encoded);
                                if (!$decodedFundId) {
                                    error_log("Warning: Failed to decode fund_id: " . $fund_id_encoded);
                                    continue;
                                }
                                
                                $fund = $fundService->getSpecificFund($decodedFundId); 
                                if ($fund && isset($fund['fund_id'])) {
                                    $fund_Lists[] = array(
                                        'fund_id' => $fund['fund_id'],
                                        'fund_name' => $fund['fund_name'],
                                        'salary_type_name' => $fund['salary_type_name'],
                                        'salary_type_name_en' => $fund['salary_type_name_en']
                                    );
                                } else {
                                    error_log("Warning: Fund not found or invalid: " . $decodedFundId . " (encoded: " . $fund_id_encoded . ")");
                                }
                            } catch (Exception $e) {
                                error_log("Error processing fund_id " . $fund_id_encoded . ": " . $e->getMessage());
                                continue;
                            }
                        }
                    }
                    error_log("Fund list count: " . count($fund_Lists));

                    if (empty($fund_Lists)) {
                        throw new Exception("No valid funds selected or found.");
                    }
                    
                    $decodedFundIdsForQuery = array_map(function($f) {
                        return $f['fund_id']; // Assuming $fund objects have 'fund_id'
                    }, $fund_Lists);


                    $employeeData = array();
                    // Iterate over the successfully fetched $months (which now contains actual month data objects/arrays)
                    foreach ($months as $monthDetail) { 
                        $monthKey = $monthDetail['master_salary_month']; // Use the key from the fetched month data
                        
                        // Ensure $GLOBALS['instanceServer']['instance_server_dbn'] is set
                        $dbName = $GLOBALS['instanceServer']['instance_server_dbn'] ?? 'default_db_name';
                        if ($dbName === 'default_db_name') {
                            error_log("Warning: \$GLOBALS['instanceServer']['instance_server_dbn'] is not set!");
                        }

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
                                {$dbName}.payroll_fund_employee_log t5 ON (
                                    t1.employee_id = t5.employee_id AND t1.server_id = t5.server_id AND t1.instance_server_id = t5.instance_server_id AND t1.instance_server_channel_id = t5.instance_server_channel_id
                                )
                            INNER JOIN
                                comp_fund_employee t8 ON (
                                    t5.employee_id = t8.employee_id AND t5.fund_id = t8.fund_id AND t5.server_id = t8.server_id AND t5.instance_server_id = t8.instance_server_id AND t5.instance_server_channel_id = t8.instance_server_channel_id
                                )
                            WHERE
                                t1.server_id = '{$_REQUEST['server_id']}' AND t1.instance_server_id = '{$_REQUEST['instance_server_id']}' AND t1.instance_server_channel_id = '{$_REQUEST['instance_server_channel_id']}' AND t1.employee_id IN ('" . implode("','", $arrayEOH) . "') AND t5.fund_id IN ('" . implode("','", $decodedFundIdsForQuery) . "') AND t5.master_salary_month = '{$monthKey}'";
                                                    
                        // error_log("Employee Fund Data Query for month {$monthKey}: " . $_sql_fund_data);
                        $employeeFundDataResults = executeQuery($userService, $_sql_fund_data, false); // Server params in query

                        // Refactored data structuring:
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
                        error_log("Processed fund data for month {$monthKey}. Found " . (is_array($employeeFundDataResults) ? count($employeeFundDataResults) : 0) . " records.");
                    }

                    $reportArray = array();
                    foreach ($listEmployee as $employee) {
                        $companyId = $employee['company_id'];
                        $branchId = $employee['branch_id'] ?? 'N/A';
                        $departmentId = $employee['department_id'] ?? 'N/A';

                        if (!isset($reportArray[$companyId])) {
                            $reportArray[$companyId] = array(
                                'name' => $employee['company_name'] ?? 'Unknown Company',
                                'branches' => array()
                            );
                        }
                        if (!isset($reportArray[$companyId]['branches'][$branchId])) {
                            $reportArray[$companyId]['branches'][$branchId] = array(
                                'name' => $employee['branch_name'] ?? 'Unknown Branch',
                                'departments' => array()
                            );
                        }
                        if (!isset($reportArray[$companyId]['branches'][$branchId]['departments'][$departmentId])) {
                            $reportArray[$companyId]['branches'][$branchId]['departments'][$departmentId] = array(
                                'name' => $employee['department_name'] ?? 'Unknown Department',
                                'employees' => array()
                            );
                        }

                        // เก็บเฉพาะข้อมูลที่จำเป็นของพนักงาน
                        $reportArray[$companyId]['branches'][$branchId]['departments'][$departmentId]['employees'][$employee['employee_id']] = array(
                            'employee_id' => $employee['employee_id'],
                            'employee_code' => $employee['employee_code'],
                            'employee_name' => $employee['employee_name'],
                            'employee_last_name' => $employee['employee_last_name']
                        );
                    }



                    $jsonResponse = array(
                        'code' => '200',
                        'message' => 'Report data successfully generated.', // Added a success message
                        'payload' => array(
                            'months' => $months, // Array of successfully fetched month details
                            'fund_Lists' => $fund_Lists, // Array of fund details
                            'employee_data' => $employeeData, // Per employee, per month, per fund contribution data
                            'report_array' => $reportArray, // Hierarchical employee list for report structure
                            // 'list_employee' => $listEmployee // Optionally, send the flat list if needed by client
                        )
                    );
                } else { // No employees in $arrayEOH
                    $jsonResponse = array(
                        'code' => '400', // Or 200 with an informative message if this is a valid state
                        'message' => 'No employees found matching criteria to process for funds.'
                    );
                }
            } catch (Exception $e) { // Catch for the main data fetching logic
                error_log("Error in report generation (main data fetching): " . $e->getMessage());
                error_log("Trace: " . $e->getTraceAsString());
                $jsonResponse = array(
                    'code' => '500',
                    'message' => "Error during report generation: " . $e->getMessage()
                );
            }
        } else { // Condition for $startMonthMeta, $endMonthMeta, fund_ids failed
            $missingParamsMsg = "Invalid request parameters for proceeding with report generation. ";
            if (!isset($startMonthMeta['master_salary_month']) || $startMonthMeta['master_salary_month'] == '') {
                $missingParamsMsg .= "Start month data is missing or invalid. ";
            }
            if (!isset($endMonthMeta['master_salary_month']) || $endMonthMeta['master_salary_month'] == '') {
                $missingParamsMsg .= "End month data is missing or invalid. ";
            }
            if (empty($_REQUEST['fund_ids'])) {
                $missingParamsMsg .= "Fund IDs are missing. ";
            }
            error_log($missingParamsMsg);
            $jsonResponse = array(
                'code' => '400',
                'message' => $missingParamsMsg
            );
        }
    } catch (Throwable $e) { // Catch for validateRequest or other early exceptions
        error_log("Fund Report Critical Error: " . $e->getMessage());
        error_log("Trace: " . $e->getTraceAsString());
        // Initialize $jsonResponse if not already set
        if (!isset($jsonResponse)) {
            $jsonResponse = array();
        }

        if ($e->getMessage() == 'employee-overlimit') { // Specific error message check
            if (!headers_sent()) { // Check if headers already sent
                 header("HTTP/1.0 413 Payload Too Large");
            }
            $jsonResponse["code"] = '413';
            $jsonResponse["message"] = "Payload Too Large: Employee count exceeds limit."; // More descriptive
            $jsonResponse["payload"] = "Payload Too Large"; // Keep original payload if needed
        } else {
            $jsonResponse["code"] = '500';
            $jsonResponse["message"] = "Critical Error: " . $e->getMessage();
            // $jsonResponse["payload"] = $e->getMessage(); // Redundant if message is set
        }
    }
    
    // Ensure $jsonResponse is always set before outputting
    if (!isset($jsonResponse)) {
        error_log("jsonResponse was not set. Defaulting to a generic error.");
        $jsonResponse = array('code' => '500', 'message' => 'An unexpected error occurred and no specific response was generated.');
    }

    // Output JSON response
    if (!headers_sent()) {
        header('Content-Type: application/json');
    }
    echo json_encode($jsonResponse);
    exit; // Terminate script after sending JSON response

?>
