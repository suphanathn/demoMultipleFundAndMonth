<?php
    // ไฟล์นี้จะถูก include โดย newFund.php
    // ตัวแปร $months, $fund_Lists, $employee_data, $report_array_structure จะถูกกำหนดค่ามาจาก newFund.php

    // --- ส่วนของการสร้าง Header/Column Name (อาจจะต้องปรับปรุงตาม UI ที่ต้องการ) ---
    $columnHeaders = [];
    $headerRow1 = [];
    $headerRow2 = [];

    $headerRow1[] = ["data" => $translate['employee_code'] ?? 'รหัสพนักงาน', "align" => "center", "colspan" => "1", "rowspan" => "2"];
    $headerRow1[] = ["data" => $translate['employee_name'] ?? 'ชื่อ-นามสกุล', "align" => "center", "colspan" => "1", "rowspan" => "2"];

    $fundColspan = 2; // วันที่ลงทะเบียน, เลขที่บัญชี (ถ้ามี) + จำนวนเดือนที่แสดง
    $totalColspanForEmployeeInfo = 2;

    $monthsToDisplay = [];
    foreach ($months as $monthInfo) {
        $monthsToDisplay[$monthInfo['master_salary_month']] = month_year($monthInfo['master_salary_month']); // "ม.ค. 68"
    }

    foreach ($fund_Lists as $fund) {
        $fundNameDisplay = $_REQUEST['language_code'] == 'TH' ? $fund['salary_type_name'] : $fund['salary_type_name_en'];
        // แต่ละกองทุนจะมีคอลัมน์ย่อยสำหรับแต่ละเดือนที่มีข้อมูล
        $headerRow1[] = ["data" => $fundNameDisplay, "align" => "center", "colspan" => count($monthsToDisplay) * 2]; // *2 เพราะมี หักพนักงาน และ บริษัทสมทบ

        foreach ($monthsToDisplay as $monthKey => $monthLabel) {
             $headerRow2[] = ["data" => $monthLabel . " (" .($translate['deduction_of_employees'] ?? 'หัก พนง.'). ")", "align" => "center", "colspan" => "1"];
             $headerRow2[] = ["data" => $monthLabel . " (" .($translate['deduction_of_company'] ?? 'บริษัทสมทบ'). ")", "align" => "center", "colspan" => "1"];
        }
    }
    $columnHeaders[] = $headerRow1;
    if (!empty($headerRow2)) { // เพิ่ม headerRow2 ถ้ามีข้อมูล
       $columnHeaders[] = $headerRow2;
    }


    // --- ส่วนของการสร้าง Data Rows ---
    $dataRows = [];
    $summaryData = []; // สำหรับเก็บผลรวมต่างๆ

    // Function to safely get nested array value
    function get_nested_value($array, $keys, $default = 0) {
        foreach ($keys as $key) {
            if (!isset($array[$key])) {
                return $default;
            }
            $array = $array[$key];
        }
        return $array;
    }


    // วนลูปตามโครงสร้างองค์กรที่ได้จาก $report_array_structure
    $grandTotalEmployees = 0;
    $grandTotalEmployeeContribution = array_fill_keys(array_column($fund_Lists, 'fund_id'), array_fill_keys(array_keys($monthsToDisplay), 0));
    $grandTotalCompanyContribution = array_fill_keys(array_column($fund_Lists, 'fund_id'), array_fill_keys(array_keys($monthsToDisplay), 0));


    foreach ($report_array_structure as $companyId => $company) {
        $dataRows[] = [["data" => $company['name'], "align" => "left", "colspan" => $totalColspanForEmployeeInfo + (count($fund_Lists) * count($monthsToDisplay) * 2) ]]; // ปรับ colspan ให้ถูก
        $companyTotalEmployees = 0;
        $companyTotalEmployeeContribution = array_fill_keys(array_column($fund_Lists, 'fund_id'), array_fill_keys(array_keys($monthsToDisplay), 0));
        $companyTotalCompanyContribution = array_fill_keys(array_column($fund_Lists, 'fund_id'), array_fill_keys(array_keys($monthsToDisplay), 0));

        foreach ($company['branches'] as $branchId => $branch) {
            $dataRows[] = [["data" => "  " . $branch['name'], "align" => "left", "colspan" => $totalColspanForEmployeeInfo + (count($fund_Lists) * count($monthsToDisplay) * 2) ]];
            $branchTotalEmployees = 0;
            $branchTotalEmployeeContribution = array_fill_keys(array_column($fund_Lists, 'fund_id'), array_fill_keys(array_keys($monthsToDisplay), 0));
            $branchTotalCompanyContribution = array_fill_keys(array_column($fund_Lists, 'fund_id'), array_fill_keys(array_keys($monthsToDisplay), 0));

            foreach ($branch['departments'] as $departmentId => $department) {
                $dataRows[] = [["data" => "    " . $department['name'], "align" => "left", "colspan" => $totalColspanForEmployeeInfo + (count($fund_Lists) * count($monthsToDisplay) * 2) ]];
                $departmentTotalEmployees = 0;
                $departmentTotalEmployeeContribution = array_fill_keys(array_column($fund_Lists, 'fund_id'), array_fill_keys(array_keys($monthsToDisplay), 0));
                $departmentTotalCompanyContribution = array_fill_keys(array_column($fund_Lists, 'fund_id'), array_fill_keys(array_keys($monthsToDisplay), 0));
                $departmentHasEmployeeWithFund = false;


                foreach ($department['employees'] as $employeeId => $employee) {
                    $rowData = [];
                    $employeeHasFundDataThisRow = false; // ตรวจสอบว่าพนักงานนี้มีข้อมูลกองทุนแสดงในแถวนี้หรือไม่

                    $rowData[] = ["data" => $employee['employee_code'], "align" => "center", "colspan" => "1"];
                    $fullName = $employee['employee_name'] . " " . $employee['employee_last_name'];
                    $rowData[] = ["data" => $fullName, "align" => "left", "colspan" => "1"];

                    foreach ($fund_Lists as $fund) {
                        $currentFundId = $fund['fund_id'];
                        $employeeFundInfo = $employee_data[$currentFundId][$employeeId] ?? null;

                        foreach ($monthsToDisplay as $monthKey => $monthLabel) {
                            if ($employeeFundInfo && isset($employeeFundInfo['contributions_by_month'][$monthKey])) {
                                $contributions = $employeeFundInfo['contributions_by_month'][$monthKey];
                                $rowData[] = ["data" => $contributions['employee_contribution'], "align" => "right", "colspan" => "1", "setint" => "Y"];
                                $rowData[] = ["data" => $contributions['company_contribution'], "align" => "right", "colspan" => "1", "setint" => "Y"];

                                $departmentTotalEmployeeContribution[$currentFundId][$monthKey] += $contributions['employee_contribution'];
                                $departmentTotalCompanyContribution[$currentFundId][$monthKey] += $contributions['company_contribution'];
                                $employeeHasFundDataThisRow = true;
                            } else {
                                $rowData[] = ["data" => "-", "align" => "right", "colspan" => "1"];
                                $rowData[] = ["data" => "-", "align" => "right", "colspan" => "1"];
                            }
                        }
                    }
                    if ($employeeHasFundDataThisRow) {
                        $dataRows[] = $rowData;
                        $departmentTotalEmployees++;
                        $departmentHasEmployeeWithFund = true;
                    }
                }

                if ($departmentHasEmployeeWithFund) {
                    $summaryRow = [];
                    $summaryRow[] = ["data" => ($translate['total_number_in_department'] ?? 'รวมแผนก') . " " . $departmentTotalEmployees . " " . ($translate['employees'] ?? 'คน'), "align" => "right", "colspan" => "2"];
                    foreach ($fund_Lists as $fund) {
                        $currentFundId = $fund['fund_id'];
                        foreach ($monthsToDisplay as $monthKey => $monthLabel) {
                             $summaryRow[] = ["data" => $departmentTotalEmployeeContribution[$currentFundId][$monthKey], "align" => "right", "colspan" => "1", "setint" => "Y", "is_summary" => true];
                             $summaryRow[] = ["data" => $departmentTotalCompanyContribution[$currentFundId][$monthKey], "align" => "right", "colspan" => "1", "setint" => "Y", "is_summary" => true];
                             $branchTotalEmployeeContribution[$currentFundId][$monthKey] += $departmentTotalEmployeeContribution[$currentFundId][$monthKey];
                             $branchTotalCompanyContribution[$currentFundId][$monthKey] += $departmentTotalCompanyContribution[$currentFundId][$monthKey];
                        }
                    }
                    $dataRows[] = $summaryRow;
                    $branchTotalEmployees += $departmentTotalEmployees;
                }
            }
            // สรุปของ Branch (ถ้า Branch นั้นมีพนักงานที่มีข้อมูลกองทุน)
            if ($branchTotalEmployees > 0) {
                $summaryRowBranch = [];
                $summaryRowBranch[] = ["data" => ($translate['total_number_in_branch'] ?? 'รวมสาขา') . " " . $branchTotalEmployees . " " . ($translate['employees'] ?? 'คน'), "align" => "right", "colspan" => "2"];
                 foreach ($fund_Lists as $fund) {
                    $currentFundId = $fund['fund_id'];
                    foreach ($monthsToDisplay as $monthKey => $monthLabel) {
                        $summaryRowBranch[] = ["data" => $branchTotalEmployeeContribution[$currentFundId][$monthKey], "align" => "right", "colspan" => "1", "setint" => "Y", "is_summary" => true];
                        $summaryRowBranch[] = ["data" => $branchTotalCompanyContribution[$currentFundId][$monthKey], "align" => "right", "colspan" => "1", "setint" => "Y", "is_summary" => true];
                        $companyTotalEmployeeContribution[$currentFundId][$monthKey] += $branchTotalEmployeeContribution[$currentFundId][$monthKey];
                        $companyTotalCompanyContribution[$currentFundId][$monthKey] += $branchTotalCompanyContribution[$currentFundId][$monthKey];
                    }
                }
                $dataRows[] = $summaryRowBranch;
                $companyTotalEmployees += $branchTotalEmployees;
            }
        }
         // สรุปของ Company (ถ้า Company นั้นมีพนักงานที่มีข้อมูลกองทุน)
        if ($companyTotalEmployees > 0) {
            $summaryRowCompany = [];
            $summaryRowCompany[] = ["data" => ($translate['total_number_in_company'] ?? 'รวมบริษัท') . " " . $companyTotalEmployees . " " . ($translate['employees'] ?? 'คน'), "align" => "right", "colspan" => "2"];
            foreach ($fund_Lists as $fund) {
                $currentFundId = $fund['fund_id'];
                foreach ($monthsToDisplay as $monthKey => $monthLabel) {
                    $summaryRowCompany[] = ["data" => $companyTotalEmployeeContribution[$currentFundId][$monthKey], "align" => "right", "colspan" => "1", "setint" => "Y", "is_summary" => true];
                    $summaryRowCompany[] = ["data" => $companyTotalCompanyContribution[$currentFundId][$monthKey], "align" => "right", "colspan" => "1", "setint" => "Y", "is_summary" => true];
                    $grandTotalEmployeeContribution[$currentFundId][$monthKey] += $companyTotalEmployeeContribution[$currentFundId][$monthKey];
                    $grandTotalCompanyContribution[$currentFundId][$monthKey] += $companyTotalCompanyContribution[$currentFundId][$monthKey];
                }
            }
            $dataRows[] = $summaryRowCompany;
            $grandTotalEmployees += $companyTotalEmployees;
        }
    }

    // สรุป Grand Total (ถ้ามีพนักงานแสดงผล)
    if ($grandTotalEmployees > 0) {
        $summaryRowGrand = [];
        $summaryRowGrand[] = ["data" => ($translate['total_grand'] ?? 'รวมทั้งหมด') . " " . $grandTotalEmployees . " " . ($translate['employees'] ?? 'คน'), "align" => "right", "colspan" => "2"];
        foreach ($fund_Lists as $fund) {
            $currentFundId = $fund['fund_id'];
            foreach ($monthsToDisplay as $monthKey => $monthLabel) {
                $summaryRowGrand[] = ["data" => $grandTotalEmployeeContribution[$currentFundId][$monthKey], "align" => "right", "colspan" => "1", "setint" => "Y", "is_summary" => true, "is_grand_total" => true];
                $summaryRowGrand[] = ["data" => $grandTotalCompanyContribution[$currentFundId][$monthKey], "align" => "right", "colspan" => "1", "setint" => "Y", "is_summary" => true, "is_grand_total" => true];
            }
        }
        $dataRows[] = $summaryRowGrand;
    }


    // --- ผลลัพธ์ JSON ---
    $payload = [
        "column" => $columnHeaders,
        "row" => $dataRows,
        // "summary_overall" => [ // อาจจะเพิ่มผลรวมทั้งหมดแยกต่างหากถ้าต้องการ
        //     "total_employees" => $grandTotalEmployees,
        //     "contributions" => $grandTotalEmployeeContribution, // [fund_id][month_key]
        //     "company_contributions" => $grandTotalCompanyContribution // [fund_id][month_key]
        // ],
        "months_header_info" => $monthsToDisplay, // ข้อมูลเดือนสำหรับ Frontend ใช้สร้าง Header แบบไดนามิก
        "funds_info" => $fund_Lists // ข้อมูลกองทุนสำหรับ Frontend
    ];

    $jsonResponse = [
        "header_jwt_verify" => [ // จำลอง header, ควรมาจากระบบจริง
            "api_id" => "NEW_FUND_REPORT_API_ID",
            "jwt_verify" => "N" // หรือ Y ถ้ามีการ verify
        ],
        "code" => "200",
        "message" => "สำเร็จ",
        "payload" => $payload,
        "MEMORY_USAGE" => round(memory_get_usage() / (1024 * 1024), 6) . "MB" // ตัวอย่างการแสดง Memory Usage
    ];

    if (!headers_sent()) {
        header('Content-Type: application/json; charset=utf-8');
    }
    echo json_encode($jsonResponse, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
?>