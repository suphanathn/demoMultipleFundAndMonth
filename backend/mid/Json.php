<?php
// Initialize required variables
$translate = array(
    'employee_code' => 'Employee Code',
    'employee_name' => 'Employee Name',
    'date_register' => 'Date Register',
    'account_number' => 'Account Number',
    'deduction_of_employees' => 'Deduction of Employees',
    'deduction_of_company' => 'Deduction of Company',
    'total_number_in_department' => 'Total in Department',
    'total_number_in_branch' => 'Total in Branch',
    'total_number_in_company' => 'Total in Company',
    'total_grand' => 'Grand Total',
    'employees' => 'Employees'
);

// Function to format date
function anyDate($format, $date, $lang = 'TH') {
    if (empty($date)) return '';
    $timestamp = strtotime($date);
    return date($format, $timestamp);
}

// Function to count months between two dates
function countMonths($start, $end) {
    $start_date = new DateTime($start . '-01');
    $end_date = new DateTime($end . '-01');
    $interval = $start_date->diff($end_date);
    return (($interval->y) * 12) + ($interval->m) + 1;
}

// Function to format month and year
function month_year($date) {
    $thai_month_arr = array(
        "01" => "ม.ค.",
        "02" => "ก.พ.",
        "03" => "มี.ค.",
        "04" => "เม.ย.",
        "05" => "พ.ค.",
        "06" => "มิ.ย.",
        "07" => "ก.ค.",
        "08" => "ส.ค.",
        "09" => "ก.ย.",
        "10" => "ต.ค.",
        "11" => "พ.ย.",
        "12" => "ธ.ค."
    );
    $month = substr($date, 5, 2);
    $year = substr($date, 0, 4) + 543;
    return $thai_month_arr[$month] . " " . substr($year, 2, 2);
}

/*---------------------  Begin Data  ---------------------*/
$result = array(
    'code' => '200',
    'message' => 'Report data successfully generated.',
    'payload' => array(
        'months' => array_map(function($month) {
            return array(
                'master_salary_month' => $month['master_salary_month'],
                'salary_report_start_dt' => $month['salary_report_start_dt'],
                'salary_report_end_dt' => $month['salary_report_end_dt']
            );
        }, $months),
        'fund_Lists' => array_map(function($fund) {
            return array(
                'fund_id' => $fund['fund_id'],
                'fund_name' => $fund['fund_name'],
                'salary_type_name' => $fund['salary_type_name'],
                'salary_type_name_en' => $fund['salary_type_name_en']
            );
        }, $fund_Lists),
        'employee_data' => array_map(function($employee) {
            return array(
                'date' => $employee['date'],
                'no' => $employee['no'],
                'employee' => $employee['employee'],
                'branch' => $employee['branch']
            );
        }, $employee_data),
        'report_array' => array_map(function($report) {
            return array(
                'employee_id' => $report['employee_id'],
                'employee_code' => $report['employee_code'],
                'employee_name' => $report['employee_name'],
                'employee_last_name' => $report['employee_last_name']
            );
        }, $report_array)
    )
);

$response = json_encode($result);
echo $response;
?>