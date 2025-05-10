<?php

// Include Services (ตรวจสอบ Path ให้ถูกต้อง)
include_once("_classes/MasterSalaryReportService.class.php");
include_once("_classes/FundService.class.php");



try {
    // 1. ตรวจสอบสิทธิ์ (ถ้ามี)
    $salaryPermission = PageAuthorizeService::getUserSalaryPermission();
    if (!$salaryPermission) {
        throw new Exception("Access Denied: Insufficient salary permission.", 403);
    }

    // 2. ตรวจสอบและ Validate Request Parameters
    if (empty($_REQUEST['start_month']) || empty($_REQUEST['end_month']) || empty($_REQUEST['fund_ids']) || !is_array($_REQUEST['fund_ids'])) {
        throw new Exception("Missing or invalid required parameters (start_month, end_month, fund_ids).", 400);
    }
    // เพิ่มการ Validate อื่นๆ เช่น รูปแบบวันที่, จำนวนเดือนสูงสุด, Server ID

    // 3. ดึงข้อมูลรอบเดือน (จาก MasterSalaryReportService)
    $monthsInRange = $masterSalaryReportService->getMasterReportByMonthLists($_REQUEST['start_month'], $_REQUEST['end_month']);
    if (empty($monthsInRange)) {
        throw new Exception("No salary report months found for the specified range.", 404);
    }
    $months = array_map(function($m) {
        return [
            'master_salary_month' => $m['master_salary_month'],
            'salary_report_start_dt' => $m['salary_report_start_dt'],
            'salary_report_end_dt' => $m['salary_report_end_dt'],
        ];
    }, $monthsInRange);


    // 4. ดึงข้อมูลกองทุน (จาก FundService)
    $fund_Lists = [];
    foreach ($_REQUEST['fund_ids'] as $encodedFundId) {
        $fundId = base64_decode($encodedFundId);
        if ($fundId) {
            $fundDetail = $fundService->getSpecificFund($fundId); // สมมติว่า getSpecificFund รับ decoded ID
            if ($fundDetail) {
                $fund_Lists[] = $fundDetail;
            }
        }
    }
    if (empty($fund_Lists)) {
        throw new Exception("No valid funds found for the specified IDs.", 404);
    }

    // 5. ดึงข้อมูลพนักงานและข้อมูลกองทุนของพนักงาน (จาก EmployeeService)
    // เตรียมพารามิเตอร์สำหรับ getListEmployeeForReportMultipleMonthFund
    $employeeServiceParams = [
        'start_month' => $_REQUEST['start_month'],
        'end_month' => $_REQUEST['end_month'],
        'fund_ids' => array_map(function($f) { return $f['fund_id']; }, $fund_Lists), // ส่ง Decoded IDs
        'server_id' => $_REQUEST['server_id'],
        'instance_server_id' => $_REQUEST['instance_server_id'],
        'instance_server_channel_id' => $_REQUEST['instance_server_channel_id'],
        'company_lists' => $_REQUEST['company_lists'] ?? [],
        'branch_lists' => $_REQUEST['branch_lists'] ?? [],
        'department_lists' => $_REQUEST['department_lists'] ?? [],
        'sys_del_flag' => $_REQUEST['sys_del_flag'] ?? 'A', // Default to All if not specified
        // เพิ่มพารามิเตอร์อื่นๆ ที่ EmployeeService ต้องการ
    ];

    $employeeReportData = $employeeService->getListEmployeeForReportMultipleMonthFund($employeeServiceParams);
    $listEmployee = $employeeReportData['list_employee'];
    $rawEmployeeFundData = $employeeReportData['employee_data']; // employee_data จาก service

    if (empty($listEmployee)) {
         throw new Exception("No employees found matching the report criteria.", 404);
    }

    // 6. ประมวลผลข้อมูลกองทุนของพนักงาน:
    //    - ตรวจสอบว่าพนักงานมีกองทุนนี้หรือไม่
    //    - กรองเดือนที่ไม่มีข้อมูลกองทุนออก
    $employee_data = []; // โครงสร้างใหม่สำหรับเก็บข้อมูลที่ผ่านการกรอง
    $report_array_structure = []; // โครงสร้างสำหรับแสดงผลตามองค์กร

    // สร้าง lookup สำหรับข้อมูลพนักงานเพื่อลดการวนซ้ำ
    $employeeLookup = [];
    foreach ($listEmployee as $emp) {
        $employeeLookup[$emp['employee_id']] = $emp;
        // เตรียมโครงสร้าง report_array
        $companyId = $emp['company_id'];
        $branchId = $emp['branch_id'] ?? 'N/A';
        $departmentId = $emp['department_id'] ?? 'N/A';

        if (!isset($report_array_structure[$companyId])) {
            $report_array_structure[$companyId] = [
                'name' => $emp['company_name'] ?? 'Unknown Company',
                'branches' => []
            ];
        }
        if (!isset($report_array_structure[$companyId]['branches'][$branchId])) {
            $report_array_structure[$companyId]['branches'][$branchId] = [
                'name' => $emp['branch_name'] ?? 'Unknown Branch',
                'departments' => []
            ];
        }
        if (!isset($report_array_structure[$companyId]['branches'][$branchId]['departments'][$departmentId])) {
            $report_array_structure[$companyId]['branches'][$branchId]['departments'][$departmentId] = [
                'name' => $emp['department_name'] ?? 'Unknown Department',
                'employees' => []
            ];
        }
        $report_array_structure[$companyId]['branches'][$branchId]['departments'][$departmentId]['employees'][$emp['employee_id']] = [
            'employee_id' => $emp['employee_id'],
            'employee_code' => $emp['employee_code'],
            'employee_name' => $emp['employee_name'],
            'employee_last_name' => $emp['employee_last_name']
        ];
    }


    foreach ($rawEmployeeFundData as $fundId => $employeesInFund) {
        if (!isset($employee_data[$fundId])) {
            $employee_data[$fundId] = [];
        }
        foreach ($employeesInFund as $employeeId => $fundDataByMonth) {
             // ตรวจสอบว่าพนักงานคนนี้อยู่ใน listEmployee ที่เราสนใจหรือไม่
            if (!isset($employeeLookup[$employeeId])) {
                continue; // ข้ามพนักงานคนนี้ถ้าไม่ได้อยู่ในกลุ่มที่ต้องการแสดงผล
            }

            if (!isset($employee_data[$fundId][$employeeId])) {
                $employee_data[$fundId][$employeeId] = [
                    'employee_code' => $employeeLookup[$employeeId]['employee_code'],
                    'employee_name' => $employeeLookup[$employeeId]['employee_name'],
                    'employee_last_name' => $employeeLookup[$employeeId]['employee_last_name'],
                    'contributions_by_month' => [] // จะเก็บข้อมูลกองทุนแยกตามเดือน
                ];
            }

            // $fundDataByMonth น่าจะเป็น array ของข้อมูลกองทุนสำหรับพนักงานนั้นๆ ในแต่ละเดือนอยู่แล้ว
            // หรือถ้า $rawEmployeeFundData มีโครงสร้าง [fund_id][employee_id][month_key]
            // ก็ต้องปรับการวนลูปให้สอดคล้อง
            // สมมติว่า $fundDataByMonth คือ array ที่ key เป็น master_salary_month

            foreach ($months as $monthInfo) { // วนลูปตามเดือนที่เราสนใจ
                $currentMonthKey = $monthInfo['master_salary_month'];
                // ตรวจสอบว่าพนักงานมีข้อมูลกองทุนในเดือนนี้หรือไม่
                // โครงสร้างของ $rawEmployeeFundData จาก Service เดิมคือ:
                // $employeeData[$fundId][$employeeId] = [ 'date' => ..., 'employee_contribution' => ...]
                // เราต้องปรับ Service `getListEmployeeForReportMultipleMonthFund` ให้คืนข้อมูลแบบรายเดือน
                // หรือ ทำการ query ใหม่ในแต่ละเดือน (ซึ่งไม่ดีต่อ performance)

                // *** ปรับปรุงส่วนนี้ตามโครงสร้างข้อมูลที่ EmployeeService คืนค่ามาจริงๆ ***
                // สมมติว่า EmployeeService คืนข้อมูลมาในรูปแบบ
                // $rawEmployeeFundData[fund_id][employee_id][month_key] = { contributions }
                if (isset($fundDataByMonth[$currentMonthKey]) &&
                    (isset($fundDataByMonth[$currentMonthKey]['employee_contribution']) && $fundDataByMonth[$currentMonthKey]['employee_contribution'] > 0) ||
                    (isset($fundDataByMonth[$currentMonthKey]['company_contribution']) && $fundDataByMonth[$currentMonthKey]['company_contribution'] > 0)
                ) {
                    $employee_data[$fundId][$employeeId]['contributions_by_month'][$currentMonthKey] = [
                        'date' => $fundDataByMonth[$currentMonthKey]['date'] ?? null, // วันที่ทำรายการ/ลงทะเบียน
                        'account_number' => $fundDataByMonth[$currentMonthKey]['account_number'] ?? null, // เลขที่บัญชี (ถ้ามี)
                        'employee_contribution' => $fundDataByMonth[$currentMonthKey]['employee_contribution'] ?? 0,
                        'company_contribution' => $fundDataByMonth[$currentMonthKey]['company_contribution'] ?? 0,
                    ];
                }
            }
            // ถ้าหลังจากวนทุกเดือนแล้วพนักงานคนนี้ไม่มีข้อมูลกองทุนเลย ก็อาจจะลบออกจาก $employee_data[$fundId]
            if (empty($employee_data[$fundId][$employeeId]['contributions_by_month'])) {
                unset($employee_data[$fundId][$employeeId]);
            }
        }
        // ถ้าหลังจากวนทุกพนักงานแล้วกองทุนนี้ไม่มีข้อมูลเลย ก็อาจจะลบออกจาก $employee_data
        if (empty($employee_data[$fundId])) {
            unset($employee_data[$fundId]);
        }
    }


    // 7. ส่งข้อมูลไปยัง newJson.php
    // ตัวแปรที่ newJson.php ต้องการ: $months, $fund_Lists, $employee_data, $report_array_structure
    // สร้าง $jsonResponse ใน newJson.php

    // echo "<pre>Months:\n"; print_r($months); echo "</pre>";
    // echo "<pre>Fund Lists:\n"; print_r($fund_Lists); echo "</pre>";
    // echo "<pre>Employee Data (Processed):\n"; print_r($employee_data); echo "</pre>";
    // echo "<pre>Report Array Structure:\n"; print_r($report_array_structure); echo "</pre>";

    // Include the JSON formatting script
    // include_once(__DIR__ . "/newJson.php");


} catch (Exception $e) {
    $errorCode = $e->getCode() == 0 ? 500 : $e->getCode(); // Default to 500 if code is 0
    http_response_code($errorCode); // Set HTTP status code
    $jsonResponse = [
        "code" => (string)$errorCode,
        "message" => $e->getMessage(),
        "payload" => null,
        // "trace" => $e->getTraceAsString() // For debugging only, remove in production
    ];
    echo json_encode($jsonResponse);
    exit;
}

?>