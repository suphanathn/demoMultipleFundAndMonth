import { Injectable } from '@angular/core';
import { HttpService } from 'app/untils/http-service';
import { Functions } from 'app/untils/functions';

@Injectable({
  providedIn: 'root'
})
export class ReportService {
  userData = Functions.getLocalStorage('userData');
  slip_report_month = Functions.getLocalStorage('slip_report_month');

  constructor(private http: HttpService) { }

  private getUserData() {
    this.userData = Functions.getUserData();
  }

  excelReport(payload): Promise<any> {
    this.getUserData();
    let data = payload;
    data['_compgrp'] = 'hrs';
    data['_comp'] = 'report';
    data['identify_user_id'] = btoa(this.userData.identify_user_id);
    data['instance_server_id'] = btoa(this.userData.instance_server_id);
    data['instance_server_channel_id'] = btoa(this.userData.instance_server_channel_id);

    let name = payload._action.replace(/_/gi, " ");

    return this.http.httpPostServiceDownloadReport(data, payload.filename);
  }

  pdfReport(payload): Promise<any> {
    this.getUserData();
    let data = payload;
    data['_compgrp'] = 'hrs';
    data['_comp'] = 'report';
    data['identify_user_id'] = btoa(this.userData.identify_user_id);
    data['instance_server_id'] = btoa(this.userData.instance_server_id);
    data['instance_server_channel_id'] = btoa(this.userData.instance_server_channel_id);

    // return this.http.httpPostServiceOpen(data);
    return this.http.httpPostServiceGetFullPathReport(data);
  }

  taxReportJson(payload): Promise<any> {
    this.getUserData();
    let data = payload;
    delete data.month_no;
    data['_compgrp'] = 'hrs';
    data['_comp'] = 'report';
    data['_action'] = 'tax_month';
    data['identify_user_id'] = btoa(this.userData.identify_user_id);
    data['instance_server_id'] = btoa(this.userData.instance_server_id);
    data['instance_server_channel_id'] = btoa(this.userData.instance_server_channel_id);

    // data['employee_id'] = btoa(this.userData.employee_id);

    return this.http.httpPostServiceReport(data);
  }

  

  taxYearReportJson(payload): Promise<any> {
    this.getUserData();
    let data = payload;
    delete data.month_no;
    data['_compgrp'] = 'hrs';
    data['_comp'] = 'report';
    data['_action'] = 'tax_year';
    data['identify_user_id'] = btoa(this.userData.identify_user_id);
    data['instance_server_id'] = btoa(this.userData.instance_server_id);
    data['instance_server_channel_id'] = btoa(this.userData.instance_server_channel_id);

    // data['employee_id'] = btoa(this.userData.employee_id);

    return this.http.httpPostServiceReport(data);
  }

  taxAuditReportJson(payload): Promise<any> {
    this.getUserData();
    let data = payload;
    delete data.month_no;
    data['_compgrp'] = 'hrs';
    data['_comp'] = 'report';
    data['_action'] = 'tax_audit';
    data['identify_user_id'] = btoa(this.userData.identify_user_id);
    data['instance_server_id'] = btoa(this.userData.instance_server_id);
    data['instance_server_channel_id'] = btoa(this.userData.instance_server_channel_id);

    // data['employee_id'] = btoa(this.userData.employee_id);

    return this.http.httpPostServiceReport(data);
  }

  taxAuditReportFile(payload, type): Promise<any> {
    this.getUserData();
    let data = payload;
    delete data.month_no;
    data['_compgrp'] = 'hrs';
    data['_comp'] = 'report';
    data['_action'] = 'tax_audit';
    data['identify_user_id'] = btoa(this.userData.identify_user_id);
    data['instance_server_id'] = btoa(this.userData.instance_server_id);
    data['instance_server_channel_id'] = btoa(this.userData.instance_server_channel_id);

    if (type === 'pdf') {
      // return this.http.httpPostServiceOpen(data);
      return this.http.httpPostServiceGetFullPathReport(data);
    } else {
      return this.http.httpPostServiceDownloadReport(data, payload.filename);
    }
  }

  tax90ReportJson(payload): Promise<any> {
    this.getUserData();
    let data = payload;
    delete data.month_no;
    data['_compgrp'] = 'hrs';
    data['_comp'] = 'report';
    data['_action'] = 'tax_90';
    data['identify_user_id'] = btoa(this.userData.identify_user_id);
    data['instance_server_id'] = btoa(this.userData.instance_server_id);
    data['instance_server_channel_id'] = btoa(this.userData.instance_server_channel_id);

    return this.http.httpPostServiceReport(data);
  }
  tax90ReportFile(payload, type) {
    this.getUserData();
    let data = payload;
    delete data.month_no;
    data['_compgrp'] = 'hrs';
    data['_comp'] = 'report';
    data['_action'] = 'tax_90';
    data['identify_user_id'] = btoa(this.userData.identify_user_id);
    data['instance_server_id'] = btoa(this.userData.instance_server_id);
    data['instance_server_channel_id'] = btoa(this.userData.instance_server_channel_id);

    if (type === 'pdf') {
      return this.http.httpPostServiceOpen(data);
    } else {
      return this.http.httpPostServiceDownloadReport(data, payload.filename);
    }
  }
  tax91ReportJson(payload): Promise<any> {
    this.getUserData();
    let data = payload;
    delete data.month_no;
    data['_compgrp'] = 'hrs';
    data['_comp'] = 'report';
    data['_action'] = 'tax_91';
    data['identify_user_id'] = btoa(this.userData.identify_user_id);
    data['instance_server_id'] = btoa(this.userData.instance_server_id);
    data['instance_server_channel_id'] = btoa(this.userData.instance_server_channel_id);

    return this.http.httpPostServiceReport(data);
  }
  tax91ReportFile(payload, type) {
    this.getUserData();
    let data = payload;
    delete data.month_no;
    data['_compgrp'] = 'hrs';
    data['_comp'] = 'report';
    data['_action'] = 'tax_91';
    data['identify_user_id'] = btoa(this.userData.identify_user_id);
    data['instance_server_id'] = btoa(this.userData.instance_server_id);
    data['instance_server_channel_id'] = btoa(this.userData.instance_server_channel_id);

    if (type === 'pdf') {
      return this.http.httpPostServiceOpen(data);
    } else {
      return this.http.httpPostServiceDownloadReport(data, payload.filename);
    }
  }

  taxWithholdingReportJson(payload): Promise<any> {
    this.getUserData();
    let data = payload;
    delete data.month_no;
    data['_compgrp'] = 'hrs';
    data['_comp'] = 'report';
    data['_action'] = 'tax_withholding';
    data['identify_user_id'] = btoa(this.userData.identify_user_id);
    data['instance_server_id'] = btoa(this.userData.instance_server_id);
    data['instance_server_channel_id'] = btoa(this.userData.instance_server_channel_id);

    return this.http.httpPostServiceReport(data);
  }

  taxWithholdingReportFile(payload, type) {
    this.getUserData();
    let data = payload;
    delete data.month_no;
    data['_compgrp'] = 'hrs';
    data['_comp'] = 'report';
    data['_action'] = 'tax_withholding';
    data['identify_user_id'] = btoa(this.userData.identify_user_id);
    data['instance_server_id'] = btoa(this.userData.instance_server_id);
    data['instance_server_channel_id'] = btoa(this.userData.instance_server_channel_id);

    if (type === 'pdf') {
      return this.http.httpPostServiceOpen(data);
    } else {
      return this.http.httpPostServiceDownloadReport(data, payload.filename);
    }
  }

  nvatReportJson(payload) {
    this.getUserData();
    let data = payload;
    delete data.month_no;
    data['_compgrp'] = 'hrs';
    data['_comp'] = 'report';
    data['_action'] = 'nvat_month';
    data['identify_user_id'] = btoa(this.userData.identify_user_id);
    data['instance_server_id'] = btoa(this.userData.instance_server_id);
    data['instance_server_channel_id'] = btoa(this.userData.instance_server_channel_id);

    return this.http.httpPostServiceReport(data);
  }

  deletePersonTax(payload): Promise<any> {
    this.getUserData();
    let data = {...payload};
    data['_compgrp'] = 'hrs';
    data['_comp'] = 'person_tax';
    data['_action'] = 'delete_person_tax';
    data['identify_user_id'] = btoa(this.userData.identify_user_id);
    data['instance_server_id'] = btoa(this.userData.instance_server_id);
    data['instance_server_channel_id'] = btoa(this.userData.instance_server_channel_id);

    return this.http.httpPostService(data);
  }

  deletePersonNvat(payload): Promise<any> {
    this.getUserData();
    let data = {...payload};
    data['_compgrp'] = 'hrs';
    data['_comp'] = 'person_tax_nvat';
    data['_action'] = 'delete_person_tax_nvat';
    data['identify_user_id'] = btoa(this.userData.identify_user_id);
    data['instance_server_id'] = btoa(this.userData.instance_server_id);
    data['instance_server_channel_id'] = btoa(this.userData.instance_server_channel_id);

    return this.http.httpPostService(data);
  }

  nvatReportFile(payload, type) {
    this.getUserData();
    let data = payload;
    delete data.month_no;
    data['_compgrp'] = 'hrs';
    data['_comp'] = 'report';
    data['_action'] = 'nvat_month';
    data['identify_user_id'] = btoa(this.userData.identify_user_id);
    data['instance_server_id'] = btoa(this.userData.instance_server_id);
    data['instance_server_channel_id'] = btoa(this.userData.instance_server_channel_id);

    if (type === 'pdf') {
      // return this.http.httpPostServiceOpen(data);
      return this.http.httpPostServiceGetFullPathReport(data);
    } else {
      return this.http.httpPostServiceDownloadReport(data, payload.filename);
    }
  }

  taxReportFile(payload, type) {
    this.getUserData();
    let data = payload;
    delete data.month_no;
    data['_compgrp'] = 'hrs';
    data['_comp'] = 'report';
    data['_action'] = 'tax_month';
    data['identify_user_id'] = btoa(this.userData.identify_user_id);
    data['instance_server_id'] = btoa(this.userData.instance_server_id);
    data['instance_server_channel_id'] = btoa(this.userData.instance_server_channel_id);

    if (type === 'pdf') {
      // return this.http.httpPostServiceOpen(data);
      return this.http.httpPostServiceGetFullPathReport(data);
    } else {
      return this.http.httpPostServiceDownloadReport(data, payload.filename);
    }
  }
  taxYearReportFile(payload, type) {
    this.getUserData();
    let data = payload;
    delete data.month_no;
    data['_compgrp'] = 'hrs';
    data['_comp'] = 'report';
    data['_action'] = 'tax_year';
    data['identify_user_id'] = btoa(this.userData.identify_user_id);
    data['instance_server_id'] = btoa(this.userData.instance_server_id);
    data['instance_server_channel_id'] = btoa(this.userData.instance_server_channel_id);

    if (type === 'pdf') {
      // return this.http.httpPostServiceOpen(data);
      return this.http.httpPostServiceGetFullPathReport(data);
    } else {
      return this.http.httpPostServiceDownloadReport(data, payload.filename);
    }
  }
  // List
  dailyInexReportJson(payload): Promise<any> {
    this.getUserData();
    let data = payload;
    delete data.month_no;
    data['_compgrp'] = 'hrs';
    data['_comp'] = 'report';
    data['_action'] = 'daily_inex';
    data['identify_user_id'] = btoa(this.userData.identify_user_id);
    data['instance_server_id'] = btoa(this.userData.instance_server_id);
    data['instance_server_channel_id'] = btoa(this.userData.instance_server_channel_id);

    return this.http.httpPostServiceReport(data);
  }

  dailyInexReportFile(payload, type): Promise<any> {
    this.getUserData();
    let data = payload;
    delete data.month_no;
    data['_compgrp'] = 'hrs';
    data['_comp'] = 'report';
    data['_action'] = 'daily_inex';
    data['identify_user_id'] = btoa(this.userData.identify_user_id);
    data['instance_server_id'] = btoa(this.userData.instance_server_id);
    data['instance_server_channel_id'] = btoa(this.userData.instance_server_channel_id);

    if (type === 'pdf') {
      return this.http.httpPostServiceOpen(data);
    } else {
      return this.http.httpPostServiceDownloadReport(data, payload.filename);
    }

  }
  //Table
  timeTableReportJson(payload): Promise<any> {
    this.getUserData();
    let data = payload;
    delete data.month_no;
    data['_compgrp'] = 'hrs';
    data['_comp'] = 'report';
    data['_action'] = 'time_table';
    data['identify_user_id'] = btoa(this.userData.identify_user_id);
    data['instance_server_id'] = btoa(this.userData.instance_server_id);
    data['instance_server_channel_id'] = btoa(this.userData.instance_server_channel_id);

    // return this.http.httpPostFastifyService(data);
    return this.http.httpPostServiceReport(data);
  }
  //Table OT
  timeTableOTReportJson(payload): Promise<any> {
    this.getUserData();
    let data = payload;
    delete data.month_no;
    data['_compgrp'] = 'hrs';
    data['_comp'] = 'report';
    data['_action'] = 'time_table_ot';
    data['identify_user_id'] = btoa(this.userData.identify_user_id);
    data['instance_server_id'] = btoa(this.userData.instance_server_id);
    data['instance_server_channel_id'] = btoa(this.userData.instance_server_channel_id);

    return this.http.httpPostServiceReport(data);
  }
  //Table Worktime
  timeTableWorkTimeReportJson(payload): Promise<any> {
    this.getUserData();
    let data = payload;
    delete data.month_no;
    data['_compgrp'] = 'hrs';
    data['_comp'] = 'report';
    data['_action'] = 'time_table_worktime';
    data['identify_user_id'] = btoa(this.userData.identify_user_id);
    data['instance_server_id'] = btoa(this.userData.instance_server_id);
    data['instance_server_channel_id'] = btoa(this.userData.instance_server_channel_id);

    return this.http.httpPostServiceReport(data);
  }

  timeTableReportFile(payload, type): Promise<any> {
    this.getUserData();
    let data = payload;
    delete data.month_no;
    data['_compgrp'] = 'hrs';
    data['_comp'] = 'report';
    data['_action'] = 'time_table';
    data['identify_user_id'] = btoa(this.userData.identify_user_id);
    data['instance_server_id'] = btoa(this.userData.instance_server_id);
    data['instance_server_channel_id'] = btoa(this.userData.instance_server_channel_id);

    if (type === 'pdf') {
      // return this.http.httpPostFastifyServiceOpen(data);
      return this.http.httpPostServiceOpen(data);
    } else {
      // return this.http.httpPostFastifyServiceDownload(data, payload.filename);
      return this.http.httpPostServiceDownloadReport(data, payload.filename);
    }
  }
  //PDF OT
  timeTableOTReportFile(payload, type): Promise<any> {
    this.getUserData();
    let data = payload;
    delete data.month_no;
    data['_compgrp'] = 'hrs';
    data['_comp'] = 'report';
    data['_action'] = 'time_table_ot';
    data['identify_user_id'] = btoa(this.userData.identify_user_id);
    data['instance_server_id'] = btoa(this.userData.instance_server_id);
    data['instance_server_channel_id'] = btoa(this.userData.instance_server_channel_id);

    if (type === 'pdf') {
      return this.http.httpPostServiceOpen(data);
    } else {
      return this.http.httpPostServiceDownloadReport(data, payload.filename);
    }
  }
  //PDF OTEmployee
  timeTableOTEmployeeReportFile(payload): Promise<any> {
    this.getUserData();
    let data = payload;
    delete data.month_no;
    data['_compgrp'] = 'hrs';
    data['_comp'] = 'calculation_ot';
    data['_action'] = 'get_ot_round_detail';
    data['type'] = 'pdf_ot_round';
    data['identify_user_id'] = btoa(this.userData.identify_user_id);
    data['instance_server_id'] = btoa(this.userData.instance_server_id);
    data['instance_server_channel_id'] = btoa(this.userData.instance_server_channel_id);

    return this.http.httpPostServiceOpen(data);
  }
  //PDF WORK
  timeTableWorkTimeReportFile(payload, type): Promise<any> {
    this.getUserData();
    let data = payload;
    delete data.month_no;
    data['_compgrp'] = 'hrs';
    data['_comp'] = 'report';
    data['_action'] = 'time_table_worktime';
    data['identify_user_id'] = btoa(this.userData.identify_user_id);
    data['instance_server_id'] = btoa(this.userData.instance_server_id);
    data['instance_server_channel_id'] = btoa(this.userData.instance_server_channel_id);

    if (type === 'pdf') {
      return this.http.httpPostServiceOpen(data);
    } else {
      return this.http.httpPostServiceDownloadReport(data, payload.filename);
    }
  }
  //PDF WORKEmployee
  timeTableWorkTimeEmployeeReportFile(payload): Promise<any> {
    this.getUserData();
    let data = payload;
    data['_compgrp'] = 'hrs';
    data['_comp'] = 'calculation_worktime';
    data['_action'] = 'get_worktime_round_detail';
    data['type'] = 'pdf_worktime_round';
    data['identify_user_id'] = btoa(this.userData.identify_user_id);
    data['instance_server_id'] = btoa(this.userData.instance_server_id);
    data['instance_server_channel_id'] = btoa(this.userData.instance_server_channel_id);

    return this.http.httpPostServiceOpen(data);
  }

  


  timeTableDailyReportJson(payload): Promise<any> {
    this.getUserData();
    let data = payload;
    delete data.month_no;
    data['_compgrp'] = 'hrs';
    data['_comp'] = 'report';
    data['_action'] = 'time_table_daily';
    data['identify_user_id'] = btoa(this.userData.identify_user_id);
    data['instance_server_id'] = btoa(this.userData.instance_server_id);
    data['instance_server_channel_id'] = btoa(this.userData.instance_server_channel_id);

    return this.http.httpPostServiceReport(data);
  }
  timeTableDailyReportFile(payload, type): Promise<any> {
    this.getUserData();
    let data = payload;
    delete data.month_no;
    data['_compgrp'] = 'hrs';
    data['_comp'] = 'report';
    data['_action'] = 'time_table_daily';
    data['identify_user_id'] = btoa(this.userData.identify_user_id);
    data['instance_server_id'] = btoa(this.userData.instance_server_id);
    data['instance_server_channel_id'] = btoa(this.userData.instance_server_channel_id);

    if (type === 'pdf') {
      return this.http.httpPostServiceOpen(data);
    } else {
      return this.http.httpPostServiceDownloadReport(data, payload.filename);
    }
  }

  getTimeImproveReport(payload): Promise<any> {
    let data = payload;
    data['_compgrp'] = 'hrs';
    data['_comp'] = 'report';
    data['_action'] = 'export_time_attendance';
    data['identify_user_id'] = btoa(this.userData.identify_user_id);
    data['instance_server_id'] = btoa(this.userData.instance_server_id);
    data['instance_server_channel_id'] = btoa(this.userData.instance_server_channel_id);
    data['type'] = 'json';

    return this.http.httpPostFastifyService(data);
  }
  async downloadTimeImprove(payload, name) {
    let data = payload;
    data['_compgrp'] = 'hrs';
    data['_comp'] = 'report';
    data['_action'] = 'export_time_attendance';
    data['identify_user_id'] = btoa(this.userData.identify_user_id);
    data['instance_server_id'] = btoa(this.userData.instance_server_id);
    data['instance_server_channel_id'] = btoa(this.userData.instance_server_channel_id);

    // await this.http.httpPostFastifyServiceDownload(data, name);
    await this.http.httpPostServiceDownloadReport(data, name);
  }

  ssoReportJson(payload): Promise<any> {
    this.getUserData();
    let data = payload;
    delete data.month_no;
    data['_compgrp'] = 'hrs';
    data['_comp'] = 'report';
    data['_action'] = 'sso_month';
    data['identify_user_id'] = btoa(this.userData.identify_user_id);
    data['instance_server_id'] = btoa(this.userData.instance_server_id);
    data['instance_server_channel_id'] = btoa(this.userData.instance_server_channel_id);

    return this.http.httpPostServiceReport(data);
  }
  ssoReportFile(payload, type) {
    this.getUserData();
    let data = payload;
    delete data.month_no;
    data['_compgrp'] = 'hrs';
    data['_comp'] = 'report';
    data['_action'] = 'sso_month';
    data['identify_user_id'] = btoa(this.userData.identify_user_id);
    data['instance_server_id'] = btoa(this.userData.instance_server_id);
    data['instance_server_channel_id'] = btoa(this.userData.instance_server_channel_id);

    if (type === 'pdf') {
      // return this.http.httpPostServiceOpen(data);
      return this.http.httpPostServiceGetFullPathReport(data);
    } else {
      return this.http.httpPostServiceDownloadReport(data, payload.filename);
    }
  }

  compensationFundReportJson(payload): Promise<any> {
    this.getUserData();
    let data = payload;
    delete data.month_no;
    data['_compgrp'] = 'hrs';
    data['_comp'] = 'report';
    data['_action'] = 'compensation_fund';
    data['identify_user_id'] = btoa(this.userData.identify_user_id);
    data['instance_server_id'] = btoa(this.userData.instance_server_id);
    data['instance_server_channel_id'] = btoa(this.userData.instance_server_channel_id);

    return this.http.httpPostServiceReport(data);
  }
  compensationFundYearReportJson(payload, type): Promise<any> {
    this.getUserData();
    let data = payload;
    delete data.month_no;
    data['_compgrp'] = 'hrs';
    data['_comp'] = 'report';
    data['_action'] = 'compensation_fund_year';
    data['identify_user_id'] = btoa(this.userData.identify_user_id);
    data['instance_server_id'] = btoa(this.userData.instance_server_id);
    data['instance_server_channel_id'] = btoa(this.userData.instance_server_channel_id);
    if (type === 'excel') {
      return this.http.httpPostServiceDownloadReport(data, payload.filename);
    } else {
      return this.http.httpPostServiceReport(data);

    }

  }
  compensationFundReportFile(payload, type) {
    this.getUserData();
    let data = payload;
    delete data.month_no;
    data['_compgrp'] = 'hrs';
    data['_comp'] = 'report';
    data['_action'] = 'compensation_fund';
    data['identify_user_id'] = btoa(this.userData.identify_user_id);
    data['instance_server_id'] = btoa(this.userData.instance_server_id);
    data['instance_server_channel_id'] = btoa(this.userData.instance_server_channel_id);

    if (type === 'pdf') {
      // return this.http.httpPostServiceOpen(data);
      return this.http.httpPostServiceGetFullPathReport(data);
    } else {
      return this.http.httpPostServiceDownloadReport(data, payload.filename);
    }
  }

  ssoYearReportJson(payload): Promise<any> {
    this.getUserData();
    let data = payload;
    delete data.month_no;
    data['_compgrp'] = 'hrs';
    data['_comp'] = 'report';
    data['_action'] = 'sso_year';
    data['identify_user_id'] = btoa(this.userData.identify_user_id);
    data['instance_server_id'] = btoa(this.userData.instance_server_id);
    data['instance_server_channel_id'] = btoa(this.userData.instance_server_channel_id);

    return this.http.httpPostServiceReport(data);
  }
  ssoYearReportFile(payload, type) {
    this.getUserData();
    let data = payload;
    delete data.month_no;
    data['_compgrp'] = 'hrs';
    data['_comp'] = 'report';
    data['_action'] = 'sso_year';
    data['identify_user_id'] = btoa(this.userData.identify_user_id);
    data['instance_server_id'] = btoa(this.userData.instance_server_id);
    data['instance_server_channel_id'] = btoa(this.userData.instance_server_channel_id);

    if (type === 'pdf') {
      return this.http.httpPostServiceOpen(data);
    } else {
      return this.http.httpPostServiceDownloadReport(data, payload.filename);
    }
  }

  netTotalReportJson(payload): Promise<any> {
    this.getUserData();
    let data = payload;
    delete data.month_no;
    data['_compgrp'] = 'hrs';
    data['_comp'] = 'report';
    data['_action'] = 'net_total_month';
    data['identify_user_id'] = btoa(this.userData.identify_user_id);
    data['instance_server_id'] = btoa(this.userData.instance_server_id);
    data['instance_server_channel_id'] = btoa(this.userData.instance_server_channel_id);

    return this.http.httpPostServiceReport(data);
  }

  netTotalYearReportJson(payload): Promise<any> {
    this.getUserData();
    let data = payload;
    delete data.month_no;
    data['_compgrp'] = 'hrs';
    data['_comp'] = 'report';
    data['_action'] = 'net_total_year';
    data['identify_user_id'] = btoa(this.userData.identify_user_id);
    data['instance_server_id'] = btoa(this.userData.instance_server_id);
    data['instance_server_channel_id'] = btoa(this.userData.instance_server_channel_id);

    return this.http.httpPostServiceReport(data);
  }

  netTotalReportFile(payload, type): Promise<any> {
    this.getUserData();
    let data = payload;
    delete data.month_no;
    data['_compgrp'] = 'hrs';
    data['_comp'] = 'report';
    data['_action'] = 'net_total_month';
    data['identify_user_id'] = btoa(this.userData.identify_user_id);
    data['instance_server_id'] = btoa(this.userData.instance_server_id);
    data['instance_server_channel_id'] = btoa(this.userData.instance_server_channel_id);

    if (type === 'pdf') {
      // return this.http.httpPostServiceOpen(data);
      return this.http.httpPostServiceGetFullPathReport(data);
    } else {
      return this.http.httpPostServiceDownloadReport(data, payload.filename);
    }
  }

  netTotalMonthByGroupFile(payload, type): Promise<any> {
    this.getUserData();
    let data = payload;
    delete data.month_no;
    data['_compgrp'] = 'hrs';
    data['_comp'] = 'report';
    data['_action'] = 'net_total_month_by_group';
    data['identify_user_id'] = btoa(this.userData.identify_user_id);
    data['instance_server_id'] = btoa(this.userData.instance_server_id);
    data['instance_server_channel_id'] = btoa(this.userData.instance_server_channel_id);

    if (type === 'pdf') {
      // return this.http.httpPostServiceOpen(data);
      return this.http.httpPostServiceGetFullPathReport(data);
    } else {
      return this.http.httpPostServiceDownloadReport(data, payload.filename);
    }
  }

  netTotalYearReportFile(payload, type): Promise<any> {
    this.getUserData();
    let data = payload;
    delete data.month_no;
    data['_compgrp'] = 'hrs';
    data['_comp'] = 'report';
    data['_action'] = 'net_total_year';
    data['identify_user_id'] = btoa(this.userData.identify_user_id);
    data['instance_server_id'] = btoa(this.userData.instance_server_id);
    data['instance_server_channel_id'] = btoa(this.userData.instance_server_channel_id);

    if (type === 'pdf') {
      return this.http.httpPostServiceOpen(data);
    } else {
      return this.http.httpPostServiceDownloadReport(data, payload.filename);
    }
  }

  netTotalYearReportFile2(payload, type): Promise<any> {
    this.getUserData();
    let data = payload;
    delete data.month_no;
    data['_compgrp'] = 'hrs';
    data['_comp'] = 'report';
    data['_action'] = 'net_total_year2';
    data['identify_user_id'] = btoa(this.userData.identify_user_id);
    data['instance_server_id'] = btoa(this.userData.instance_server_id);
    data['instance_server_channel_id'] = btoa(this.userData.instance_server_channel_id);

    if (type === 'pdf') {
      return this.http.httpPostServiceOpen(data);
    } else {
      return this.http.httpPostServiceDownloadReport(data, payload.filename);
    }
  }

  netTotalGroupReportJson(payload): Promise<any> {
    this.getUserData();
    let data = payload;
    delete data.month_no;
    data['_compgrp'] = 'hrs';
    data['_comp'] = 'report';
    data['_action'] = 'net_total_group';
    data['identify_user_id'] = btoa(this.userData.identify_user_id);
    data['instance_server_id'] = btoa(this.userData.instance_server_id);
    data['instance_server_channel_id'] = btoa(this.userData.instance_server_channel_id);

    return this.http.httpPostServiceReport(data);
  }

  netTotalGroupReportFile(payload, type): Promise<any> {
    this.getUserData();
    let data = payload;
    delete data.month_no;
    data['_compgrp'] = 'hrs';
    data['_comp'] = 'report';
    data['_action'] = 'net_total_group';
    data['identify_user_id'] = btoa(this.userData.identify_user_id);
    data['instance_server_id'] = btoa(this.userData.instance_server_id);
    data['instance_server_channel_id'] = btoa(this.userData.instance_server_channel_id);

    if (type === 'pdf') {
      // return this.http.httpPostServiceOpen(data);
      return this.http.httpPostServiceGetFullPathReport(data);
    } else {
      return this.http.httpPostServiceDownloadReport(data, payload.filename);
    }
  }

  netTotalDepartmentReportJson(payload): Promise<any> {
    this.getUserData();
    let data = payload;
    delete data.month_no;
    data['_compgrp'] = 'hrs';
    data['_comp'] = 'report';
    data['_action'] = 'net_total_month_department';
    data['identify_user_id'] = btoa(this.userData.identify_user_id);
    data['instance_server_id'] = btoa(this.userData.instance_server_id);
    data['instance_server_channel_id'] = btoa(this.userData.instance_server_channel_id);

    return this.http.httpPostServiceReport(data);
  }
  netTotalMonthReportAccrevo(payload): Promise<any> {
    this.getUserData();
    let data = payload;
    delete data.month_no;
    data['_compgrp'] = 'hrs';
    data['_comp'] = 'report';
    data['_action'] = 'net_total_month_sso';
    data['identify_user_id'] = btoa(this.userData.identify_user_id);
    data['instance_server_id'] = btoa(this.userData.instance_server_id);
    data['instance_server_channel_id'] = btoa(this.userData.instance_server_channel_id);
    return this.http.httpPostService(data);
  }
  sendExpensesPeak(payload): Promise<any> {
    this.getUserData();
    let data = payload;
    delete data.month_no;
    data['_compgrp'] = 'hrs';
    data['_comp'] = 'partnership';
    data['_action'] = 'send_expenses_peak';
    data['identify_user_id'] = btoa(this.userData.identify_user_id);
    data['instance_server_id'] = btoa(this.userData.instance_server_id);
    data['instance_server_channel_id'] = btoa(this.userData.instance_server_channel_id);
    return this.http.httpPostService(data);
  }
  getListHistoryPartner(payload): Promise<any> {
    this.getUserData();
    let data = payload;
    delete data.month_no;
    data['_compgrp'] = 'hrs';
    data['_comp'] = 'partnership';
    data['_action'] = 'get_list_history_partnership';
    data['identify_user_id'] = btoa(this.userData.identify_user_id);
    data['instance_server_id'] = btoa(this.userData.instance_server_id);
    data['instance_server_channel_id'] = btoa(this.userData.instance_server_channel_id);
    return this.http.httpPostServiceReport(data);
  }
  updateHistoryPartner(payload): Promise<any> {
    this.getUserData();
    let data = payload;
    delete data.month_no;
    data['_compgrp'] = 'hrs';
    data['_comp'] = 'partnership';
    data['_action'] = 'update_disable_history_partnership';
    data['identify_user_id'] = btoa(this.userData.identify_user_id);
    data['instance_server_id'] = btoa(this.userData.instance_server_id);
    data['instance_server_channel_id'] = btoa(this.userData.instance_server_channel_id);
    return this.http.httpPostServiceReport(data);
  }

  netTotalDepartmentYearReportJson(payload): Promise<any> {
    this.getUserData();
    let data = payload;
    delete data.month_no;
    data['_compgrp'] = 'hrs';
    data['_comp'] = 'report';
    data['_action'] = 'net_total_year_department';
    data['identify_user_id'] = btoa(this.userData.identify_user_id);
    data['instance_server_id'] = btoa(this.userData.instance_server_id);
    data['instance_server_channel_id'] = btoa(this.userData.instance_server_channel_id);

    return this.http.httpPostServiceReport(data);
  }

  netTotalDepartmentReportFile(payload, type): Promise<any> {
    this.getUserData();
    let data = payload;
    delete data.month_no;
    data['_compgrp'] = 'hrs';
    data['_comp'] = 'report';
    data['_action'] = 'net_total_month_department';
    data['identify_user_id'] = btoa(this.userData.identify_user_id);
    data['instance_server_id'] = btoa(this.userData.instance_server_id);
    data['instance_server_channel_id'] = btoa(this.userData.instance_server_channel_id);

    if (type === 'pdf') {
      // return this.http.httpPostServiceOpen(data);
      return this.http.httpPostServiceGetFullPathReport(data);
    } else {
      return this.http.httpPostServiceDownloadReport(data, payload.filename);
    }
  }

  netTotalDepartmentYearReportFile(payload, type): Promise<any> {
    this.getUserData();
    let data = payload;
    delete data.month_no;
    data['_compgrp'] = 'hrs';
    data['_comp'] = 'report';
    data['_action'] = 'net_total_year_department';
    data['identify_user_id'] = btoa(this.userData.identify_user_id);
    data['instance_server_id'] = btoa(this.userData.instance_server_id);
    data['instance_server_channel_id'] = btoa(this.userData.instance_server_channel_id);

    if (type === 'pdf') {
      return this.http.httpPostServiceOpen(data);
    } else {
      return this.http.httpPostServiceDownloadReport(data, payload.filename);
    }
  }

  saveBankTransfer(payload) {
    this.getUserData();
    let data = payload;
    delete data.month_no;
    data['_compgrp'] = 'hrs';
    data['_comp'] = 'calculation_normal';
    data['_action'] = 'save_bank_transfer';
    data['identify_user_id'] = btoa(this.userData.identify_user_id);
    data['instance_server_id'] = btoa(this.userData.instance_server_id);
    data['instance_server_channel_id'] = btoa(this.userData.instance_server_channel_id);
    data['year_month'] = payload.year_month;

    if (payload.type == 'excel_normal') {
      data['bank_code'] = 'NORMAL';
    } else if (payload.type == 'excel_cash') {
      data['bank_code'] = 'CASH';
    } else if (payload.type == 'excel_transfer') {
      data['bank_code'] = 'TRANSFER';
    } else if (payload.type == 'excel_kbank' || payload.type == 'text_kbank' || payload.type == 'text_kbank2') {
      data['bank_code'] = 'KBANK';
    } else if (payload.type == 'excel_bbl' || payload.type == 'dat_bbl' || payload.type == 'dat_bbl2') {
      data['bank_code'] = 'BBL';
    } else if (payload.type == 'excel_ktb' || payload.type == 'text_ktb') {
      data['bank_code'] = 'KTB';
    } else if (payload.type == 'excel_scb' || payload.type == 'text_scb') {
      data['bank_code'] = 'SCB';
    } else if (payload.type == 'excel_tbank') {
      data['bank_code'] = 'TTB';
    } else if (payload.type == 'excel_tmb') {
      data['bank_code'] = 'TTB';
    } else if (payload.type == 'text_lah') {
      data['bank_code'] = 'LAH';
    } else {
      data['bank_code'] = 'NORMAL';
    }
    return this.http.httpPostServiceReport(data);

  }

  netTransferReportJson(payload): Promise<any> {
    this.getUserData();
    let data = payload;
    delete data.month_no;
    data['_compgrp'] = 'hrs';
    data['_comp'] = 'report';
    data['_action'] = 'net_transfer_month';
    data['identify_user_id'] = btoa(this.userData.identify_user_id);
    data['instance_server_id'] = btoa(this.userData.instance_server_id);
    data['instance_server_channel_id'] = btoa(this.userData.instance_server_channel_id);

    return this.http.httpPostServiceReport(data);
  }

  netTransferReportFile(payload, type) {
    this.getUserData();
    let data = payload;
    delete data.month_no;
    data['_compgrp'] = 'hrs';
    data['_comp'] = 'report';
    data['_action'] = 'net_transfer_month';
    data['identify_user_id'] = btoa(this.userData.identify_user_id);
    data['instance_server_id'] = btoa(this.userData.instance_server_id);
    data['instance_server_channel_id'] = btoa(this.userData.instance_server_channel_id);

    if (type === 'pdf') {
      return this.http.httpPostServiceOpen(data);
    } else {
      return this.http.httpPostServiceDownloadReport(data, payload.filename);
    }
  }

  netTransferReport(payload): Promise<any> {
    this.getUserData();
    let data = payload;
    data['_compgrp'] = 'hrs';
    data['_comp'] = 'report';
    data['identify_user_id'] = btoa(this.userData.identify_user_id);
    data['instance_server_id'] = btoa(this.userData.instance_server_id);
    data['instance_server_channel_id'] = btoa(this.userData.instance_server_channel_id);

    let name = payload._action.replace(/_/gi, " ");

    return this.http.httpPostServiceDownloadReport(data, payload.filename);
  }

  employeeReportJson(payload): Promise<any> {
    this.getUserData();
    let data = payload;
    delete data.month_no;
    data['_compgrp'] = 'hrs';
    data['_comp'] = 'report';
    data['_action'] = 'employee_lists';
    data['identify_user_id'] = btoa(this.userData.identify_user_id);
    data['instance_server_id'] = btoa(this.userData.instance_server_id);
    data['instance_server_channel_id'] = btoa(this.userData.instance_server_channel_id);

    // return this.http.httpPostFastifyService(data);
    return this.http.httpPostServiceReport(data);
  }

  employeeReportFile(payload, type) {
    this.getUserData();
    let data = payload;
    delete data.month_no;
    data['_compgrp'] = 'hrs';
    data['_comp'] = 'report';
    data['_action'] = 'employee_lists';
    data['identify_user_id'] = btoa(this.userData.identify_user_id);
    data['instance_server_id'] = btoa(this.userData.instance_server_id);
    data['instance_server_channel_id'] = btoa(this.userData.instance_server_channel_id);

    if (type === 'pdf') {
      // return this.http.httpPostFastifyServiceOpen(data);
      return this.http.httpPostServiceGetFullPathReport(data);
      // return this.http.httpPostServiceOpen(data);
    } else {
      // return this.http.httpPostFastifyServiceDownload(data, payload.filename);
      return this.http.httpPostServiceDownloadReport(data, payload.filename);
    }
  }

  timeReportJson(payload): Promise<any> {
    this.getUserData();
    let data = payload;
    delete data.start_dt;
    delete data.end_dt;
    data['_compgrp'] = 'hrs';
    data['_comp'] = 'report';
    data['_action'] = 'sumtime_month';
    data['identify_user_id'] = btoa(this.userData.identify_user_id);
    data['instance_server_id'] = btoa(this.userData.instance_server_id);
    data['instance_server_channel_id'] = btoa(this.userData.instance_server_channel_id);

    return this.http.httpPostServiceReport(data);
  }

  timeReportFile(payload, type) {
    this.getUserData();
    let data = payload;
    delete data.start_dt;
    delete data.end_dt;
    data['_compgrp'] = 'hrs';
    data['_comp'] = 'report';
    data['_action'] = 'sumtime_month';
    data['identify_user_id'] = btoa(this.userData.identify_user_id);
    data['instance_server_id'] = btoa(this.userData.instance_server_id);
    data['instance_server_channel_id'] = btoa(this.userData.instance_server_channel_id);

    if (type === 'pdf') {
      return this.http.httpPostServiceOpen(data);
    } else {
      return this.http.httpPostServiceDownloadReport(data, payload.filename);
    }
  }

  worktimeSumtimeReportJson(payload): Promise<any> {
    this.getUserData();
    let data = payload;
    delete data.start_dt;
    delete data.end_dt;
    data['_compgrp'] = 'hrs';
    data['_comp'] = 'report';
    data['_action'] = 'worktime_round_sumtime_month';
    data['identify_user_id'] = btoa(this.userData.identify_user_id);
    data['instance_server_id'] = btoa(this.userData.instance_server_id);
    data['instance_server_channel_id'] = btoa(this.userData.instance_server_channel_id);

    return this.http.httpPostServiceReport(data);
  }

  worktimeSumtimeReportFile(payload, type) {
    this.getUserData();
    let data = payload;
    delete data.start_dt;
    delete data.end_dt;
    data['_compgrp'] = 'hrs';
    data['_comp'] = 'report';
    data['_action'] = 'worktime_round_sumtime_month';
    data['identify_user_id'] = btoa(this.userData.identify_user_id);
    data['instance_server_id'] = btoa(this.userData.instance_server_id);
    data['instance_server_channel_id'] = btoa(this.userData.instance_server_channel_id);

    if (type === 'pdf') {
      return this.http.httpPostServiceOpen(data);
    } else {
      return this.http.httpPostServiceDownloadReport(data, payload.filename);
    }
  }

  worktimeNettotalReportJson(payload): Promise<any> {
    this.getUserData();
    let data = payload;
    delete data.start_dt;
    delete data.end_dt;
    data['_compgrp'] = 'hrs';
    data['_comp'] = 'report';
    data['_action'] = 'net_total_month_worktime_round';
    data['identify_user_id'] = btoa(this.userData.identify_user_id);
    data['instance_server_id'] = btoa(this.userData.instance_server_id);
    data['instance_server_channel_id'] = btoa(this.userData.instance_server_channel_id);

    return this.http.httpPostServiceReport(data);
  }

  worktimeNettotalReportFile(payload, type) {
    this.getUserData();
    let data = payload;
    delete data.start_dt;
    delete data.end_dt;
    data['_compgrp'] = 'hrs';
    data['_comp'] = 'report';
    data['_action'] = 'net_total_month_worktime_round';
    data['identify_user_id'] = btoa(this.userData.identify_user_id);
    data['instance_server_id'] = btoa(this.userData.instance_server_id);
    data['instance_server_channel_id'] = btoa(this.userData.instance_server_channel_id);

    if (type === 'pdf') {
      // return this.http.httpPostServiceOpen(data);
      return this.http.httpPostServiceGetFullPathReport(data);
    } else {
      return this.http.httpPostServiceDownloadReport(data, payload.filename);
    }
  }

  otSumtimeReportJson(payload): Promise<any> {
    this.getUserData();
    let data = payload;
    delete data.start_dt;
    delete data.end_dt;
    data['_compgrp'] = 'hrs';
    data['_comp'] = 'report';
    data['_action'] = 'ot_round_sumtime_month';
    data['identify_user_id'] = btoa(this.userData.identify_user_id);
    data['instance_server_id'] = btoa(this.userData.instance_server_id);
    data['instance_server_channel_id'] = btoa(this.userData.instance_server_channel_id);

    return this.http.httpPostServiceReport(data);
  }

  otSumtimeReportFile(payload, type) {
    this.getUserData();
    let data = payload;
    delete data.start_dt;
    delete data.end_dt;
    data['_compgrp'] = 'hrs';
    data['_comp'] = 'report';
    data['_action'] = 'ot_round_sumtime_month';
    data['identify_user_id'] = btoa(this.userData.identify_user_id);
    data['instance_server_id'] = btoa(this.userData.instance_server_id);
    data['instance_server_channel_id'] = btoa(this.userData.instance_server_channel_id);

    if (type === 'pdf') {
      return this.http.httpPostServiceOpen(data);
    } else {
      return this.http.httpPostServiceDownloadReport(data, payload.filename);
    }
  }

  otNettotalReportJson(payload): Promise<any> {
    this.getUserData();
    let data = payload;
    delete data.start_dt;
    delete data.end_dt;
    data['_compgrp'] = 'hrs';
    data['_comp'] = 'report';
    data['_action'] = 'net_total_month_ot_round';
    data['identify_user_id'] = btoa(this.userData.identify_user_id);
    data['instance_server_id'] = btoa(this.userData.instance_server_id);
    data['instance_server_channel_id'] = btoa(this.userData.instance_server_channel_id);

    return this.http.httpPostServiceReport(data);
  }

  otNettotalReportFile(payload, type) {
    this.getUserData();
    let data = payload;
    delete data.start_dt;
    delete data.end_dt;
    data['_compgrp'] = 'hrs';
    data['_comp'] = 'report';
    data['_action'] = 'net_total_month_ot_round';
    data['identify_user_id'] = btoa(this.userData.identify_user_id);
    data['instance_server_id'] = btoa(this.userData.instance_server_id);
    data['instance_server_channel_id'] = btoa(this.userData.instance_server_channel_id);

    if (type === 'pdf') {
      // return this.http.httpPostServiceOpen(data);
      return this.http.httpPostServiceGetFullPathReport(data);
    } else {
      return this.http.httpPostServiceDownloadReport(data, payload.filename);
    }
  }

  extraTotalReportJson(payload): Promise<any> {
    this.getUserData();
    let data = payload;
    delete data.month_no;
    delete data.extra_report;
    data['_compgrp'] = 'hrs';
    data['_comp'] = 'report';
    data['_action'] = 'net_xtra_month';
    data['identify_user_id'] = btoa(this.userData.identify_user_id);
    data['instance_server_id'] = btoa(this.userData.instance_server_id);
    data['instance_server_channel_id'] = btoa(this.userData.instance_server_channel_id);

    return this.http.httpPostServiceReport(data);
  }

  extraTotalReportFile(payload, type) {
    this.getUserData();
    let data = payload;
    delete data.month_no;
    data['_compgrp'] = 'hrs';
    data['_comp'] = 'report';
    data['_action'] = 'net_xtra_month';
    data['identify_user_id'] = btoa(this.userData.identify_user_id);
    data['instance_server_id'] = btoa(this.userData.instance_server_id);
    data['instance_server_channel_id'] = btoa(this.userData.instance_server_channel_id);

    if (type === 'pdf') {
      // return this.http.httpPostServiceOpen(data);
      return this.http.httpPostServiceGetFullPathReport(data);
    } else {
      return this.http.httpPostServiceDownloadReport(data, payload.filename);
    }
  }

  docRequestReportJson(payload): Promise<any> {
    this.getUserData();
    let data = payload;
    delete data.month_no;
    delete data.extra_report;
    data['_compgrp'] = 'hrs';
    data['_comp'] = 'report';
    data['_action'] = 'doc_request';
    data['identify_user_id'] = btoa(this.userData.identify_user_id);
    data['instance_server_id'] = btoa(this.userData.instance_server_id);
    data['instance_server_channel_id'] = btoa(this.userData.instance_server_channel_id);

    return this.http.httpPostServiceReport(data);
  }

  docRequestReportFile(payload, type) {
    this.getUserData();
    let data = payload;
    delete data.month_no;
    delete data.extra_report;
    data['_compgrp'] = 'hrs';
    data['_comp'] = 'report';
    data['_action'] = 'doc_request';
    data['identify_user_id'] = btoa(this.userData.identify_user_id);
    data['instance_server_id'] = btoa(this.userData.instance_server_id);
    data['instance_server_channel_id'] = btoa(this.userData.instance_server_channel_id);

    if (type === 'pdf') {
      // return this.http.httpPostServiceOpen(data);
      return this.http.httpPostServiceGetFullPathReport(data);
    } else {
      return this.http.httpPostServiceDownloadReport(data, payload.filename);
    }
  }

  debtReportJson(payload): Promise<any> {
    this.getUserData();
    let data = payload;
    data['_compgrp'] = 'hrs';
    data['_comp'] = 'report';
    data['_action'] = 'debt';
    data['identify_user_id'] = btoa(this.userData.identify_user_id);
    data['instance_server_id'] = btoa(this.userData.instance_server_id);
    data['instance_server_channel_id'] = btoa(this.userData.instance_server_channel_id);

    return this.http.httpPostServiceReport(data);
  }

  debtReportDownload(payload, name?) {
    this.getUserData();
    let data = payload;
    data['_compgrp'] = 'hrs';
    data['_comp'] = 'report';
    data['_action'] = 'debt';
    data['identify_user_id'] = btoa(this.userData.identify_user_id);
    data['instance_server_id'] = btoa(this.userData.instance_server_id);
    data['instance_server_channel_id'] = btoa(this.userData.instance_server_channel_id);

    if (payload['type'] === 'excel') {
      return this.http.httpPostServiceDownloadReport(data, name);
    } else if (payload['type'] === 'pdf') {
      // return this.http.httpPostServiceOpen(data);
      return this.http.httpPostServiceGetFullPathReport(data);
    }
  }
  slipMonthReportJson(payload): Promise<any> {
    this.getUserData();
    let data = payload;
    data['_compgrp'] = 'hrs';
    data['_comp'] = 'report';
    data['_action'] = 'slip_month';
    data['identify_user_id'] = btoa(this.userData.identify_user_id);
    data['instance_server_id'] = btoa(this.userData.instance_server_id);
    data['instance_server_channel_id'] = btoa(this.userData.instance_server_channel_id);

    return this.http.httpPostServiceReport(data);
  }
  slipMonthReportDownload(payload, name?, filter?): Promise<any> {
    this.getUserData();
    let data = { ...payload };
    data['_compgrp'] = 'hrs';

    //   if(filter.salary_round == '00'){
    //     data['_comp'] = 'report';
    //     data['_action'] = 'slip_month';


    // }else if(filter.salary_round == '01'){
    //   data['_comp'] = 'report';
    //   data['_action'] = 'slip_split_normal';

    // }else if(filter.salary_round == '02'){
    //   data['_comp'] = 'calculation_extra';
    //   data['_action'] = 'slip_extra_round';

    // }else if(filter.salary_round == '03'){
    //   data['_comp'] = 'calculation_ot';
    //   data['_action'] = 'slip_ot_round';

    // }else if(filter.salary_round == '04'){
    //   data['_comp'] = 'calculation_worktime';
    //   data['_action'] = 'slip_worktime_round';

    // }

    data['identify_user_id'] = btoa(this.userData.identify_user_id);
    data['instance_server_id'] = btoa(this.userData.instance_server_id);
    data['instance_server_channel_id'] = btoa(this.userData.instance_server_channel_id);

    if (payload['type'] === 'excel') {
      return this.http.httpPostServiceDownloadReport(data, name);
    } else if (payload['type'] === 'pdf') {
      if (payload?.slip_encryption == 'Y') {
        return this.http.httpPostServiceOpen(data);

      } else {
        return this.http.httpPostServiceGetFullPathReport(data);

      }
    }
  }
  getMonth(payload): Promise<any> {
    this.getUserData();
    let data = payload;
    data['_compgrp'] = 'hrs';
    data['_comp'] = 'report';
    data['_action'] = 'get_round_month';
    data['identify_user_id'] = btoa(this.userData.identify_user_id);
    data['instance_server_id'] = btoa(this.userData.instance_server_id);
    data['instance_server_channel_id'] = btoa(this.userData.instance_server_channel_id);

    return this.http.httpPostServiceReport(data);
  }

  getMonthByDateTime(payload): Promise<any> {
    this.getUserData();
    let data = payload;
    data['_compgrp'] = 'hrs';
    data['_comp'] = 'report';
    data['_action'] = 'get_round_month_by_date';
    data['identify_user_id'] = btoa(this.userData.identify_user_id);
    data['instance_server_id'] = btoa(this.userData.instance_server_id);
    data['instance_server_channel_id'] = btoa(this.userData.instance_server_channel_id);

    return this.http.httpPostServiceReport(data);
  }

  withdrawReportJson(payload): Promise<any> {
    this.getUserData();
    let data = payload;
    data['_compgrp'] = 'hrs';
    data['_comp'] = 'report';
    data['_action'] = 'withdraw';
    data['identify_user_id'] = btoa(this.userData.identify_user_id);
    data['instance_server_id'] = btoa(this.userData.instance_server_id);
    data['instance_server_channel_id'] = btoa(this.userData.instance_server_channel_id);

    return this.http.httpPostServiceReport(data);
  }

  withdrawReportDownload(payload, name?) {
    this.getUserData();
    let data = payload;
    data['_compgrp'] = 'hrs';
    data['_comp'] = 'report';
    data['_action'] = 'withdraw';
    data['identify_user_id'] = btoa(this.userData.identify_user_id);
    data['instance_server_id'] = btoa(this.userData.instance_server_id);
    data['instance_server_channel_id'] = btoa(this.userData.instance_server_channel_id);

    if (payload['type'] === 'pdf') {
      // return this.http.httpPostServiceOpen(data);
      return this.http.httpPostServiceGetFullPathReport(data);
    } else {
      return this.http.httpPostServiceDownloadReport(data, name);
    }
  }

  workInsuranceReportJson(payload): Promise<any> {
    this.getUserData();
    let data = payload;
    data['_compgrp'] = 'hrs';
    data['_comp'] = 'report';
    data['_action'] = 'work_insurance';
    data['identify_user_id'] = btoa(this.userData.identify_user_id);
    data['instance_server_id'] = btoa(this.userData.instance_server_id);
    data['instance_server_channel_id'] = btoa(this.userData.instance_server_channel_id);

    return this.http.httpPostServiceReport(data);
  }

  workInsuranceReportDownload(payload, name?) {
    this.getUserData();
    let data = payload;
    data['_compgrp'] = 'hrs';
    data['_comp'] = 'report';
    data['_action'] = 'work_insurance';
    data['identify_user_id'] = btoa(this.userData.identify_user_id);
    data['instance_server_id'] = btoa(this.userData.instance_server_id);
    data['instance_server_channel_id'] = btoa(this.userData.instance_server_channel_id);

    if (payload['type'] === 'excel') {
      return this.http.httpPostServiceDownloadReport(data, name);
    } else if (payload['type'] === 'pdf') {
      // return this.http.httpPostServiceOpen(data);
      return this.http.httpPostServiceGetFullPathReport(data);
    }
  }

  pettyCashReportJson(payload): Promise<any> {
    this.getUserData();
    let data = payload;
    data['_compgrp'] = 'hrs';
    data['_comp'] = 'report';
    data['_action'] = 'petty_cash';
    data['identify_user_id'] = btoa(this.userData.identify_user_id);
    data['instance_server_id'] = btoa(this.userData.instance_server_id);
    data['instance_server_channel_id'] = btoa(this.userData.instance_server_channel_id);

    return this.http.httpPostServiceReport(data);
  }

  pettyCashReportDownload(payload, name?) {
    this.getUserData();
    let data = payload;
    data['_compgrp'] = 'hrs';
    data['_comp'] = 'report';
    data['_action'] = 'petty_cash';
    data['identify_user_id'] = btoa(this.userData.identify_user_id);
    data['instance_server_id'] = btoa(this.userData.instance_server_id);
    data['instance_server_channel_id'] = btoa(this.userData.instance_server_channel_id);

    if (payload['type'] === 'excel') {
      return this.http.httpPostServiceDownloadReport(data, name);
    } else if (payload['type'] === 'pdf') {
      // return this.http.httpPostServiceOpen(data);
      return this.http.httpPostServiceGetFullPathReport(data);
    }
  }

  birthDateReportJson(payload): Promise<any> {
    this.getUserData();
    let data = payload;
    data['_compgrp'] = 'hrs';
    data['_comp'] = 'report';
    data['_action'] = 'birthday';
    data['identify_user_id'] = btoa(this.userData.identify_user_id);
    data['instance_server_id'] = btoa(this.userData.instance_server_id);
    data['instance_server_channel_id'] = btoa(this.userData.instance_server_channel_id);

    return this.http.httpPostServiceReport(data);
  }

  birthDateReportDownload(payload, name?) {
    this.getUserData();
    let data = payload;
    data['_compgrp'] = 'hrs';
    data['_comp'] = 'report';
    data['_action'] = 'birthday';
    data['identify_user_id'] = btoa(this.userData.identify_user_id);
    data['instance_server_id'] = btoa(this.userData.instance_server_id);
    data['instance_server_channel_id'] = btoa(this.userData.instance_server_channel_id);

    if (payload['type'] === 'excel') {
      return this.http.httpPostServiceDownloadReport(data, name);
    } else if (payload['type'] === 'pdf') {
      return this.http.httpPostServiceOpen(data);
    }
  }

  employeeTrialReportJson(payload): Promise<any> {
    this.getUserData();
    let data = payload;
    data['_compgrp'] = 'hrs';
    data['_comp'] = 'report';
    data['_action'] = 'employee_trial';
    data['identify_user_id'] = btoa(this.userData.identify_user_id);
    data['instance_server_id'] = btoa(this.userData.instance_server_id);
    data['instance_server_channel_id'] = btoa(this.userData.instance_server_channel_id);

    return this.http.httpPostServiceReport(data);
  }

  employeeTrialReportDownload(payload, name?) {
    this.getUserData();
    let data = payload;
    data['_compgrp'] = 'hrs';
    data['_comp'] = 'report';
    data['_action'] = 'employee_trial';
    data['identify_user_id'] = btoa(this.userData.identify_user_id);
    data['instance_server_id'] = btoa(this.userData.instance_server_id);
    data['instance_server_channel_id'] = btoa(this.userData.instance_server_channel_id);

    if (payload['type'] === 'excel') {
      return this.http.httpPostServiceDownloadReport(data, name);
    } else if (payload['type'] === 'pdf') {
      // return this.http.httpPostServiceOpen(data);
      return this.http.httpPostServiceGetFullPathReport(data);
    }
  }

  signoutListReportJson(payload): Promise<any> {
    this.getUserData();
    let data = payload;
    data['_compgrp'] = 'hrs';
    data['_comp'] = 'report';
    data['_action'] = 'sign_out_list';
    data['identify_user_id'] = btoa(this.userData.identify_user_id);
    data['instance_server_id'] = btoa(this.userData.instance_server_id);
    data['instance_server_channel_id'] = btoa(this.userData.instance_server_channel_id);

    return this.http.httpPostServiceReport(data);
  }

  fundReportJson(payload): Promise<any> {
    this.getUserData();
    let data = payload;
    data['_compgrp'] = 'hrs';
    data['_comp'] = 'report';
    data['_action'] = 'fund';
    data['identify_user_id'] = btoa(this.userData.identify_user_id);
    data['instance_server_id'] = btoa(this.userData.instance_server_id);
    data['instance_server_channel_id'] = btoa(this.userData.instance_server_channel_id);

    return this.http.httpPostServiceReport(data);
  }

  getListFund(payload): Promise<any> {
    this.getUserData();
    let data = payload;
    data['_compgrp'] = 'hrs';
    data['_comp'] = 'report';
    data['_action'] = 'get_list_fund';
    data['identify_user_id'] = btoa(this.userData.identify_user_id);
    data['instance_server_id'] = btoa(this.userData.instance_server_id);
    data['instance_server_channel_id'] = btoa(this.userData.instance_server_channel_id);

    return this.http.httpPostServiceReport(data);
  }

  fundReportDownload(payload, name?): Promise<any> {
    this.getUserData();
    let data = payload;
    data['_compgrp'] = 'hrs';
    data['_comp'] = 'report';
    data['_action'] = 'fund';
    data['identify_user_id'] = btoa(this.userData.identify_user_id);
    data['instance_server_id'] = btoa(this.userData.instance_server_id);
    data['instance_server_channel_id'] = btoa(this.userData.instance_server_channel_id);

    if (payload['type'] === 'excel') {
      return this.http.httpPostServiceDownloadReport(data, name);
    } else if (payload['type'] === 'pdf') {
      // return this.http.httpPostServiceOpen(data);
      return this.http.httpPostServiceGetFullPathReport(data);
    }
  }

  providentReportJson(payload): Promise<any> {
    this.getUserData();
    let data = payload;
    data['_compgrp'] = 'hrs';
    data['_comp'] = 'report';
    data['_action'] = 'provident';
    data['identify_user_id'] = btoa(this.userData.identify_user_id);
    data['instance_server_id'] = btoa(this.userData.instance_server_id);
    data['instance_server_channel_id'] = btoa(this.userData.instance_server_channel_id);

    return this.http.httpPostServiceReport(data);
  }

  providentReportYearJson(payload): Promise<any> {
    this.getUserData();
    let data = payload;
    data['_compgrp'] = 'hrs';
    data['_comp'] = 'report';
    data['_action'] = 'provident_year';
    data['identify_user_id'] = btoa(this.userData.identify_user_id);
    data['instance_server_id'] = btoa(this.userData.instance_server_id);
    data['instance_server_channel_id'] = btoa(this.userData.instance_server_channel_id);

    return this.http.httpPostServiceReport(data);
  }

  providentReportDownload(payload, name?) {
    this.getUserData();
    let data = payload;
    data['_compgrp'] = 'hrs';
    data['_comp'] = 'report';
    data['_action'] = 'provident';
    data['identify_user_id'] = btoa(this.userData.identify_user_id);
    data['instance_server_id'] = btoa(this.userData.instance_server_id);
    data['instance_server_channel_id'] = btoa(this.userData.instance_server_channel_id);

    // if (payload['type'] === 'excel' || payload['type'] === 'bbl' || payload['type'] === 'kbank') {
    //   return this.http.httpPostServiceDownloadReport(data, name);
    // } 
    if (payload['type'] === 'pdf') {
      // return this.http.httpPostServiceOpen(data);
      return this.http.httpPostServiceGetFullPathReport(data);
    } else {
      return this.http.httpPostServiceDownloadReport(data, name);
    }
  }

  providentReportYearDownload(payload, name?) {
    this.getUserData();
    let data = payload;
    data['_compgrp'] = 'hrs';
    data['_comp'] = 'report';
    data['_action'] = 'provident_year';
    data['identify_user_id'] = btoa(this.userData.identify_user_id);
    data['instance_server_id'] = btoa(this.userData.instance_server_id);
    data['instance_server_channel_id'] = btoa(this.userData.instance_server_channel_id);

    if (payload['type'] === 'excel' || payload['type'] === 'bbl') {
      return this.http.httpPostServiceDownloadReport(data, name);
    } else if (payload['type'] === 'pdf') {
      return this.http.httpPostServiceOpen(data);
    }
  }

  newByListReportJson(payload): Promise<any> {
    this.getUserData();
    let data = payload;
    data['_compgrp'] = 'hrs';
    data['_comp'] = 'report';
    data['_action'] = 'newby_list';
    data['identify_user_id'] = btoa(this.userData.identify_user_id);
    data['instance_server_id'] = btoa(this.userData.instance_server_id);
    data['instance_server_channel_id'] = btoa(this.userData.instance_server_channel_id);

    return this.http.httpPostServiceReport(data);
  }

  newByListReportDownload(payload) {
    this.getUserData();
    let data = payload;
    data['_compgrp'] = 'hrs';
    data['_comp'] = 'report';
    data['_action'] = 'newby_list';
    data['identify_user_id'] = btoa(this.userData.identify_user_id);
    data['instance_server_id'] = btoa(this.userData.instance_server_id);
    data['instance_server_channel_id'] = btoa(this.userData.instance_server_channel_id);

    if (payload['type'] === 'excel') {
      return this.http.httpPostServiceDownloadReport(data, payload.filename);
    } else if (payload['type'] === 'print') {
      // return this.http.httpPostServiceOpen(data);
      return this.http.httpPostServiceGetFullPathReport(data);
    }
  }

  signoutListReportDownload(payload, name?) {
    this.getUserData();
    let data = payload;
    data['_compgrp'] = 'hrs';
    data['_comp'] = 'report';
    data['_action'] = 'sign_out_list';
    data['identify_user_id'] = btoa(this.userData.identify_user_id);
    data['instance_server_id'] = btoa(this.userData.instance_server_id);
    data['instance_server_channel_id'] = btoa(this.userData.instance_server_channel_id);

    if (payload['type'] === 'excel') {
      return this.http.httpPostServiceDownloadReport(data, name);
    } else if (payload['type'] === 'print') {
      // return this.http.httpPostServiceOpen(data);
      return this.http.httpPostServiceGetFullPathReport(data);
    }
  }

  splitReportJson(payload): Promise<any> {
    this.getUserData();
    let data = payload;
    data['_compgrp'] = 'hrs';
    data['_comp'] = 'report';
    data['_action'] = 'net_total_split_month';
    data['identify_user_id'] = btoa(this.userData.identify_user_id);
    data['instance_server_id'] = btoa(this.userData.instance_server_id);
    data['instance_server_channel_id'] = btoa(this.userData.instance_server_channel_id);

    return this.http.httpPostServiceReport(data);
  }

  splitReportDownload(payload, name?) {
    this.getUserData();
    let data = payload;
    data['_compgrp'] = 'hrs';
    data['_comp'] = 'report';
    data['_action'] = 'net_total_split_month';
    data['identify_user_id'] = btoa(this.userData.identify_user_id);
    data['instance_server_id'] = btoa(this.userData.instance_server_id);
    data['instance_server_channel_id'] = btoa(this.userData.instance_server_channel_id);

    if (payload['type'] === 'excel' || payload['type'] === 'excel2') {
      return this.http.httpPostServiceDownloadReport(data, payload.filename);
    } else if (payload['type'] === 'pdf' || payload['type'] === 'pdf2' || payload['type'] === 'pdf3') {
      // return this.http.httpPostServiceOpen(data);
      return this.http.httpPostServiceGetFullPathReport(data);
    }
  }

  groupSplitReportDownload(payload, name?) {
    this.getUserData();
    let data = payload;
    data['_compgrp'] = 'hrs';
    data['_comp'] = 'report';
    data['_action'] = 'net_total_group_split_month';
    data['identify_user_id'] = btoa(this.userData.identify_user_id);
    data['instance_server_id'] = btoa(this.userData.instance_server_id);
    data['instance_server_channel_id'] = btoa(this.userData.instance_server_channel_id);

    if (payload['type'] === 'pdf') {
      // return this.http.httpPostServiceOpen(data);
      return this.http.httpPostServiceGetFullPathReport(data);
    }
  }


  splitNetTotalDepartmentReportJson(payload): Promise<any> {
    this.getUserData();
    let data = payload;
    delete data.month_no;
    data['_compgrp'] = 'hrs';
    data['_comp'] = 'report';
    data['_action'] = 'net_total_split_month_department';
    data['identify_user_id'] = btoa(this.userData.identify_user_id);
    data['instance_server_id'] = btoa(this.userData.instance_server_id);
    data['instance_server_channel_id'] = btoa(this.userData.instance_server_channel_id);

    return this.http.httpPostServiceReport(data);
  }

  splitNetTotalDepartmentReportFile(payload, type): Promise<any> {
    this.getUserData();
    let data = payload;
    delete data.month_no;
    data['_compgrp'] = 'hrs';
    data['_comp'] = 'report';
    data['_action'] = 'net_total_split_month_department';
    data['identify_user_id'] = btoa(this.userData.identify_user_id);
    data['instance_server_id'] = btoa(this.userData.instance_server_id);
    data['instance_server_channel_id'] = btoa(this.userData.instance_server_channel_id);

    if (type === 'pdf') {
      // return this.http.httpPostServiceOpen(data);
      return this.http.httpPostServiceGetFullPathReport(data);
    } else {
      return this.http.httpPostServiceDownloadReport(data, payload.filename);
    }
  }

  top10Report(payload): Promise<any> {
    this.getUserData();
    let data = { ...payload };
    data['_compgrp'] = 'hrs';
    data['_comp'] = 'report';
    data['_action'] = 'top10';
    data['identify_user_id'] = btoa(this.userData.identify_user_id);
    data['instance_server_id'] = btoa(this.userData.instance_server_id);
    data['instance_server_channel_id'] = btoa(this.userData.instance_server_channel_id);

    if (data['type'] === 'excel') {
      return this.http.httpPostServiceDownloadReport(data, 'top10');
    } else {
      return this.http.httpPostServiceReport(data);
    }
  }

  top10ExtraReport(payload): Promise<any> {
    this.getUserData();
    let data = { ...payload };
    data['_compgrp'] = 'hrs';
    data['_comp'] = 'report';
    data['_action'] = 'top10_extra';
    data['identify_user_id'] = btoa(this.userData.identify_user_id);
    data['instance_server_id'] = btoa(this.userData.instance_server_id);
    data['instance_server_channel_id'] = btoa(this.userData.instance_server_channel_id);

    if (data['type'] === 'excel') {
      return this.http.httpPostServiceDownloadReport(data, 'top10');
    } else {
      return this.http.httpPostServiceReport(data);
    }
  }

  documentExpireReport(payload) {
    this.getUserData();
    let data = payload;
    data['_compgrp'] = 'hrs';
    data['_comp'] = 'report';
    data['_action'] = 'document_expire';
    data['identify_user_id'] = btoa(this.userData.identify_user_id);
    data['instance_server_id'] = btoa(this.userData.instance_server_id);
    data['instance_server_channel_id'] = btoa(this.userData.instance_server_channel_id);

    return this.http.httpPostServiceReport(data);
  }

  documentExpireReportDownload(payload, name) {
    this.getUserData();
    let data = payload;
    data['_compgrp'] = 'hrs';
    data['_comp'] = 'report';
    data['_action'] = 'document_expire';
    data['identify_user_id'] = btoa(this.userData.identify_user_id);
    data['instance_server_id'] = btoa(this.userData.instance_server_id);
    data['instance_server_channel_id'] = btoa(this.userData.instance_server_channel_id);

    if (payload['type'] === 'excel') {
      return this.http.httpPostServiceDownloadReport(data, name);
    } else if (payload['type'] === 'pdf') {
      // return this.http.httpPostServiceOpen(data);
      return this.http.httpPostServiceGetFullPathReport(data);
    }
  }

  withdrawDocReportDownload(payload, name) {
    this.getUserData();
    let data = payload;
    data['_compgrp'] = 'hrs';
    data['_comp'] = 'report';
    data['_action'] = 'withdraw_doc';
    data['identify_user_id'] = btoa(this.userData.identify_user_id);
    data['instance_server_id'] = btoa(this.userData.instance_server_id);
    data['instance_server_channel_id'] = btoa(this.userData.instance_server_channel_id);

    if (payload['type'] === 'pdf') {
      return this.http.httpPostServiceOpen(data);
    } else {
      return this.http.httpPostServiceDownloadReport(data, name)
    }
  }

  holidayEmployeeReport(payload) {
    this.getUserData();
    let data = payload;
    data['_compgrp'] = 'hrs';
    data['_comp'] = 'report';
    data['_action'] = 'holiday_of_employee';
    data['identify_user_id'] = btoa(this.userData.identify_user_id);
    data['instance_server_id'] = btoa(this.userData.instance_server_id);
    data['instance_server_channel_id'] = btoa(this.userData.instance_server_channel_id);

    return this.http.httpPostServiceReport(data);
  }

  holidayEmployeeReportDownload(payload, name) {
    this.getUserData();
    let data = payload;
    data['_compgrp'] = 'hrs';
    data['_comp'] = 'report';
    data['_action'] = 'holiday_of_employee';
    data['identify_user_id'] = btoa(this.userData.identify_user_id);
    data['instance_server_id'] = btoa(this.userData.instance_server_id);
    data['instance_server_channel_id'] = btoa(this.userData.instance_server_channel_id);

    if (payload['type'] === 'excel') {
      return this.http.httpPostServiceDownloadReport(data, name);
    } else if (payload['type'] === 'pdf') {
      // return this.http.httpPostServiceOpen(data);
      return this.http.httpPostServiceGetFullPathReport(data);
    }
  }

  dayStatusReport(payload) {
    this.getUserData();
    let data = payload;
    data['_compgrp'] = 'hrs';
    data['_comp'] = 'report';
    data['_action'] = 'day_status';
    data['identify_user_id'] = btoa(this.userData.identify_user_id);
    data['instance_server_id'] = btoa(this.userData.instance_server_id);
    data['instance_server_channel_id'] = btoa(this.userData.instance_server_channel_id);

    return this.http.httpPostServiceReport(data);
  }

  dayStatusReportDownload(payload, name) {
    this.getUserData();
    let data = payload;
    data['_compgrp'] = 'hrs';
    data['_comp'] = 'report';
    data['_action'] = 'day_status';
    data['identify_user_id'] = btoa(this.userData.identify_user_id);
    data['instance_server_id'] = btoa(this.userData.instance_server_id);
    data['instance_server_channel_id'] = btoa(this.userData.instance_server_channel_id);

    if (payload['type'] === 'excel') {
      return this.http.httpPostServiceDownloadReport(data, name);
    } else if (payload['type'] === 'pdf') {
      return this.http.httpPostServiceOpen(data);
    }
  }

  WorkCyclePlanReportDownload(payload, name) {
    this.getUserData();
    let data = payload;
    data['_compgrp'] = 'hrs';
    data['_comp'] = 'report';
    data['_action'] = 'work_cycle_plan';
    data['identify_user_id'] = btoa(this.userData.identify_user_id);
    data['instance_server_id'] = btoa(this.userData.instance_server_id);
    data['instance_server_channel_id'] = btoa(this.userData.instance_server_channel_id);

    if (payload['type'] === 'excel') {
      return this.http.httpPostServiceDownloadReport(data, name);
    } else if (payload['type'] === 'pdf') {
      return this.http.httpPostServiceOpen(data);
    }
  }

  workHolidayReport(payload) {
    this.getUserData();
    let data = payload;
    data['_compgrp'] = 'hrs';
    data['_comp'] = 'report';
    data['_action'] = 'employee_work_holiday';
    data['identify_user_id'] = btoa(this.userData.identify_user_id);
    data['instance_server_id'] = btoa(this.userData.instance_server_id);
    data['instance_server_channel_id'] = btoa(this.userData.instance_server_channel_id);

    return this.http.httpPostServiceReport(data);
  }

  dworkHolidayReportDownload(payload, name) {
    this.getUserData();
    let data = payload;
    data['_compgrp'] = 'hrs';
    data['_comp'] = 'report';
    data['_action'] = 'employee_work_holiday';
    data['identify_user_id'] = btoa(this.userData.identify_user_id);
    data['instance_server_id'] = btoa(this.userData.instance_server_id);
    data['instance_server_channel_id'] = btoa(this.userData.instance_server_channel_id);

    if (payload['type'] === 'excel') {
      return this.http.httpPostServiceDownloadReport(data, name);
    } else if (payload['type'] === 'pdf') {
      return this.http.httpPostServiceOpen(data);
    }
  }

  todoListReport(payload) {
    this.getUserData();
    let data = payload;
    data['_compgrp'] = 'hrs';
    data['_comp'] = 'report';
    data['_action'] = 'todo_lists';
    data['identify_user_id'] = btoa(this.userData.identify_user_id);
    data['instance_server_id'] = btoa(this.userData.instance_server_id);
    data['instance_server_channel_id'] = btoa(this.userData.instance_server_channel_id);

    data['permission_id'] = btoa(this.userData.employee_id);

    return this.http.httpPostServiceReport(data);
  }

  todoListReportDownload(payload) {
    this.getUserData();
    let data = payload;
    data['_compgrp'] = 'hrs';
    data['_comp'] = 'report';
    data['_action'] = 'todo_lists';
    data['identify_user_id'] = btoa(this.userData.identify_user_id);
    data['instance_server_id'] = btoa(this.userData.instance_server_id);
    data['instance_server_channel_id'] = btoa(this.userData.instance_server_channel_id);

    if (payload['type'] === 'excel') {
      return this.http.httpPostServiceDownloadReport(data, payload.name);
    } else if (payload['type'] === 'pdf') {
      // return this.http.httpPostServiceOpen(data);
      return this.http.httpPostServiceGetFullPathReport(data);
    }
  }

  kpiProfileReport(payload) {
    this.getUserData();
    let data = payload;
    data['_compgrp'] = 'hrs';
    data['_comp'] = 'report';
    data['_action'] = 'kpi_profile_personal';
    data['identify_user_id'] = btoa(this.userData.identify_user_id);
    data['instance_server_id'] = btoa(this.userData.instance_server_id);
    data['instance_server_channel_id'] = btoa(this.userData.instance_server_channel_id);

    return this.http.httpPostServiceReport(data);
  }

  kpiProfileReportDownload(payload) {
    this.getUserData();
    let data = payload;
    data['_compgrp'] = 'hrs';
    data['_comp'] = 'report';
    data['_action'] = 'kpi_profile_personal';
    data['identify_user_id'] = btoa(this.userData.identify_user_id);
    data['instance_server_id'] = btoa(this.userData.instance_server_id);
    data['instance_server_channel_id'] = btoa(this.userData.instance_server_channel_id);

    return this.http.httpPostServiceDownloadReport(data, payload.filename);
  }

  logTrackingReport(payload) {
    this.getUserData();
    let data = payload;
    data['_compgrp'] = 'hrs';
    data['_comp'] = 'report';
    data['_action'] = 'log_tracking';
    data['identify_user_id'] = btoa(this.userData.identify_user_id);
    data['instance_server_id'] = btoa(this.userData.instance_server_id);
    data['instance_server_channel_id'] = btoa(this.userData.instance_server_channel_id);

    // data['employee_id'] = btoa(this.userData.employee_id);

    return this.http.httpPostServiceReport(data);
  }

  logTrackingReportDownload(payload): Promise<any> {
    this.getUserData();
    let data = payload;
    data['_compgrp'] = 'hrs';
    data['_comp'] = 'report';
    data['_action'] = 'log_tracking';
    data['identify_user_id'] = btoa(this.userData.identify_user_id);
    data['instance_server_id'] = btoa(this.userData.instance_server_id);
    data['instance_server_channel_id'] = btoa(this.userData.instance_server_channel_id);

    if (payload['type'] === 'excel') {
      return this.http.httpPostServiceDownloadReport(data, payload.filename);
    } else if (payload['type'] === 'print') {
      // return this.http.httpPostServiceOpen(data);
      return this.http.httpPostServiceGetFullPathReport(data);
    }
  }

  backlogReport(payload) {
    this.getUserData();
    let data = payload;
    data['_compgrp'] = 'hrs';
    data['_comp'] = 'report';
    data['_action'] = 'activity_backlog';
    data['identify_user_id'] = btoa(this.userData.identify_user_id);
    data['instance_server_id'] = btoa(this.userData.instance_server_id);
    data['instance_server_channel_id'] = btoa(this.userData.instance_server_channel_id);

    // data['employee_id'] = btoa(this.userData.employee_id);

    return this.http.httpPostServiceReport(data);
  }

  salaryAdjustReport(payload) {
    this.getUserData();
    let data = payload;
    data['_compgrp'] = 'hrs';
    data['_comp'] = 'report';
    data['_action'] = 'salary_adjust';
    data['identify_user_id'] = btoa(this.userData.identify_user_id);
    data['instance_server_id'] = btoa(this.userData.instance_server_id);
    data['instance_server_channel_id'] = btoa(this.userData.instance_server_channel_id);

    return this.http.httpPostServiceReport(data);
  }

  salaryAdjustReportDownload(payload): Promise<any> {
    this.getUserData();
    let data = payload;
    data['_compgrp'] = 'hrs';
    data['_comp'] = 'report';
    data['_action'] = 'salary_adjust';
    data['identify_user_id'] = btoa(this.userData.identify_user_id);
    data['instance_server_id'] = btoa(this.userData.instance_server_id);
    data['instance_server_channel_id'] = btoa(this.userData.instance_server_channel_id);

    if (payload['type'] === 'excel') {
      return this.http.httpPostServiceDownloadReport(data, payload.filename);
    } else if (payload['type'] === 'pdf') {
      return this.http.httpPostServiceOpen(data);
    }
  }

  salaryAdjustReportDownloadTmp(payload): Promise<any> {
    this.getUserData();
    let data = payload;
    data['_compgrp'] = 'hrs';
    data['_comp'] = 'report';
    data['_action'] = 'salary_adjust';
    data['identify_user_id'] = btoa(this.userData.identify_user_id);
    data['instance_server_id'] = btoa(this.userData.instance_server_id);
    data['instance_server_channel_id'] = btoa(this.userData.instance_server_channel_id);

    if (payload['type'] === 'excel') {
      return this.http.httpPostServiceDownloadReport(data, payload.filename);
    } else if (payload['type'] === 'pdf') {
      return this.http.httpPostServiceOpen(data);
    }
  }

  organizationAdjustReportDownload(payload): Promise<any> {
    this.getUserData();
    let data = payload;
    data['_compgrp'] = 'hrs';
    data['_comp'] = 'report';
    data['_action'] = 'organization_adjust';
    data['identify_user_id'] = btoa(this.userData.identify_user_id);
    data['instance_server_id'] = btoa(this.userData.instance_server_id);
    data['instance_server_channel_id'] = btoa(this.userData.instance_server_channel_id);

    if (payload['type'] === 'excel') {
      return this.http.httpPostServiceDownloadReport(data, payload.filename);
    } else if (payload['type'] === 'pdf') {
      return this.http.httpPostServiceOpen(data);
    }
  }

  listEmployeeAnnouncement(payload): Promise<any> {
    this.getUserData();
    let data = payload;
    data['_compgrp'] = 'hrs';
    data['_comp'] = 'report';
    data['_action'] = 'announcement_read';
    data['identify_user_id'] = btoa(this.userData.identify_user_id);
    data['instance_server_id'] = btoa(this.userData.instance_server_id);
    data['instance_server_channel_id'] = btoa(this.userData.instance_server_channel_id);

    return this.http.httpPostServiceReport(data);
  }

  listEmployeeAnnouncementDownload(payload): Promise<any> {
    this.getUserData();
    let data = payload;
    data['_compgrp'] = 'hrs';
    data['_comp'] = 'report';
    data['_action'] = 'announcement_read';
    data['identify_user_id'] = btoa(this.userData.identify_user_id);
    data['instance_server_id'] = btoa(this.userData.instance_server_id);
    data['instance_server_channel_id'] = btoa(this.userData.instance_server_channel_id);

    if (payload['type'] === 'excel') {
      return this.http.httpPostServiceDownloadReport(data, payload.filename);
    } else if (payload['type'] === 'pdf') {
      return this.http.httpPostServiceOpen(data);
    }
  }

  listProbationHistory(payload): Promise<any> {
    this.getUserData();
    let data = payload;
    data['_compgrp'] = 'hrs';
    data['_comp'] = 'report';
    data['_action'] = 'probation';
    data['identify_user_id'] = btoa(this.userData.identify_user_id);
    data['instance_server_id'] = btoa(this.userData.instance_server_id);
    data['instance_server_channel_id'] = btoa(this.userData.instance_server_channel_id);

    return this.http.httpPostServiceReport(data);
  }

  employeeBeginReportJson(payload): Promise<any> {
    this.getUserData();
    let data = payload;
    data['_compgrp'] = 'hrs';
    data['_comp'] = 'report';
    data['_action'] = 'employee_begin';
    data['identify_user_id'] = btoa(this.userData.identify_user_id);
    data['instance_server_id'] = btoa(this.userData.instance_server_id);
    data['instance_server_channel_id'] = btoa(this.userData.instance_server_channel_id);

    return this.http.httpPostServiceReport(data);
  }

  employeeBeginReportDownload(payload): Promise<any> {
    this.getUserData();
    let data = payload;
    data['_compgrp'] = 'hrs';
    data['_comp'] = 'report';
    data['_action'] = 'employee_begin';
    data['identify_user_id'] = btoa(this.userData.identify_user_id);
    data['instance_server_id'] = btoa(this.userData.instance_server_id);
    data['instance_server_channel_id'] = btoa(this.userData.instance_server_channel_id);

    if (payload['type'] === 'excel') {
      return this.http.httpPostServiceDownloadReport(data, payload.filename);
    } else if (payload['type'] === 'print') {
      // return this.http.httpPostServiceOpen(data);
      return this.http.httpPostServiceGetFullPathReport(data);
    }
  }

  warningLetterReportJson(payload): Promise<any> {
    this.getUserData();
    let data = payload;
    data['_compgrp'] = 'hrs';
    data['_comp'] = 'report';
    data['_action'] = 'warning_letter';
    data['identify_user_id'] = btoa(this.userData.identify_user_id);
    data['instance_server_id'] = btoa(this.userData.instance_server_id);
    data['instance_server_channel_id'] = btoa(this.userData.instance_server_channel_id);

    return this.http.httpPostServiceReport(data);
  }

  warningLetterReportDownload(payload): Promise<any> {
    this.getUserData();
    let data = payload;
    data['_compgrp'] = 'hrs';
    data['_comp'] = 'report';
    data['_action'] = 'warning_letter';
    data['identify_user_id'] = btoa(this.userData.identify_user_id);
    data['instance_server_id'] = btoa(this.userData.instance_server_id);
    data['instance_server_channel_id'] = btoa(this.userData.instance_server_channel_id);

    if (payload['type'] === 'excel') {
      return this.http.httpPostServiceDownloadReport(data, payload.file_name);
    } else if (payload['type'] === 'print') {
      // return this.http.httpPostServiceOpen(data);
      return this.http.httpPostServiceGetFullPathReport(data);
    }
  }

  listAssessment(payload): Promise<any> {
    this.getUserData();
    let data = payload;
    data['_compgrp'] = 'hrs';
    data['_comp'] = 'report';
    data['_action'] = 'assessment';
    data['identify_user_id'] = btoa(this.userData.identify_user_id);
    data['instance_server_id'] = btoa(this.userData.instance_server_id);
    data['instance_server_channel_id'] = btoa(this.userData.instance_server_channel_id);

    return this.http.httpPostServiceReport(data);
  }

  listAssessmentFile(payload, type): Promise<any> {
    this.getUserData();
    let data = payload;
    data['_compgrp'] = 'hrs';
    data['_comp'] = 'report';
    data['_action'] = 'assessment';
    data['identify_user_id'] = btoa(this.userData.identify_user_id);
    data['instance_server_id'] = btoa(this.userData.instance_server_id);
    data['instance_server_channel_id'] = btoa(this.userData.instance_server_channel_id);

    if (type === 'pdf') {
      // return this.http.httpPostServiceOpen(data);
      return this.http.httpPostServiceGetFullPathReport(data);
    } else {
      return this.http.httpPostServiceDownloadReport(data, payload.filename);
    }
  }

  trainingReportJson(payload): Promise<any> {
    this.getUserData;
    let data = payload;
    data['_compgrp'] = 'hrs';
    data['_comp'] = 'report';
    data['_action'] = 'training';
    data['identify_user_id'] = btoa(this.userData.identify_user_id);
    data['instance_server_id'] = btoa(this.userData.instance_server_id);
    data['instance_server_channel_id'] = btoa(this.userData.instance_server_channel_id);
    data['type'] = 'json';

    return this.http.httpPostServiceReport(data);
  }

  trainingReportDownload(payload, name?): Promise<any> {
    this.getUserData;
    let data = payload;
    data['_compgrp'] = 'hrs';
    data['_comp'] = 'report';
    data['_action'] = 'training';
    data['identify_user_id'] = btoa(this.userData.identify_user_id);
    data['instance_server_id'] = btoa(this.userData.instance_server_id);
    data['instance_server_channel_id'] = btoa(this.userData.instance_server_channel_id);

    return this.http.httpPostServiceDownloadReport(data, name);
  }

  thavornhotelsTrainingReportJson(payload): Promise<any> {
    this.getUserData;
    let data = payload;
    data['_compgrp'] = 'hrs';
    data['_comp'] = 'report';
    data['_action'] = 'thavornhotels_training';
    data['identify_user_id'] = btoa(this.userData.identify_user_id);
    data['instance_server_id'] = btoa(this.userData.instance_server_id);
    data['instance_server_channel_id'] = btoa(this.userData.instance_server_channel_id);
    data['type'] = 'json';

    return this.http.httpPostServiceReport(data);
  }

  thavornhotelsTrainingReportDownload(payload, name?): Promise<any> {
    this.getUserData;
    let data = payload;
    data['_compgrp'] = 'hrs';
    data['_comp'] = 'report';
    data['_action'] = 'thavornhotels_training';
    data['identify_user_id'] = btoa(this.userData.identify_user_id);
    data['instance_server_id'] = btoa(this.userData.instance_server_id);
    data['instance_server_channel_id'] = btoa(this.userData.instance_server_channel_id);

    return this.http.httpPostServiceDownloadReport(data, name);
  }

  quotaDetailReportJson(payload): Promise<any> {
    this.getUserData;
    let data = payload;
    data['_compgrp'] = 'hrs';
    data['_comp'] = 'report';
    data['_action'] = 'quota_detail';
    data['identify_user_id'] = btoa(this.userData.identify_user_id);
    data['instance_server_id'] = btoa(this.userData.instance_server_id);
    data['instance_server_channel_id'] = btoa(this.userData.instance_server_channel_id);
    data['type'] = 'json';

    return this.http.httpPostServiceReport(data);
  }

  quotaDetailReportDownload(payload, name?): Promise<any> {
    this.getUserData;
    let data = payload;
    data['_compgrp'] = 'hrs';
    data['_comp'] = 'report';
    data['_action'] = 'quota_detail';
    data['identify_user_id'] = btoa(this.userData.identify_user_id);
    data['instance_server_id'] = btoa(this.userData.instance_server_id);
    data['instance_server_channel_id'] = btoa(this.userData.instance_server_channel_id);

    if (payload['type'] === 'excel') {
      return this.http.httpPostServiceDownloadReport(data, name);
    } else if (payload['type'] === 'pdf') {
      // return this.http.httpPostServiceOpen(data);
      return this.http.httpPostServiceGetFullPathReport(data);
    }
  }

  quotaStatisticReportJson(payload): Promise<any> {
    this.getUserData;
    let data = payload;
    data['_compgrp'] = 'hrs';
    data['_comp'] = 'report';
    data['_action'] = 'quota_statistic';
    data['identify_user_id'] = btoa(this.userData.identify_user_id);
    data['instance_server_id'] = btoa(this.userData.instance_server_id);
    data['instance_server_channel_id'] = btoa(this.userData.instance_server_channel_id);
    data['type'] = 'json';

    return this.http.httpPostServiceReport(data);
  }

  quotaStatisticReportDownload(payload, name?): Promise<any> {
    this.getUserData;
    let data = payload;
    data['_compgrp'] = 'hrs';
    data['_comp'] = 'report';
    data['_action'] = 'quota_statistic';
    data['identify_user_id'] = btoa(this.userData.identify_user_id);
    data['instance_server_id'] = btoa(this.userData.instance_server_id);
    data['instance_server_channel_id'] = btoa(this.userData.instance_server_channel_id);

    if (payload['type'] === 'excel') {
      return this.http.httpPostServiceDownloadReport(data, name);
    } else if (payload['type'] === 'pdf') {
      // return this.http.httpPostServiceOpen(data);
      return this.http.httpPostServiceGetFullPathReport(data);
    }
  }

  salaryadjustReportDownload(payload, name?): Promise<any> {
    this.getUserData;
    let data = payload;
    data['_compgrp'] = 'hrs';
    data['_comp'] = 'report';
    data['_action'] = 'employee_salary_log';
    data['identify_user_id'] = btoa(this.userData.identify_user_id);
    data['instance_server_id'] = btoa(this.userData.instance_server_id);
    data['instance_server_channel_id'] = btoa(this.userData.instance_server_channel_id);

    return this.http.httpPostServiceDownloadReport(data, name);
  }

  employeeSalaryTypeDownload(payload, name?): Promise<any> {
    this.getUserData;
    let data = payload;
    data['_compgrp'] = 'hrs';
    data['_comp'] = 'report';
    data['_action'] = 'employee_salary_history_log';
    data['identify_user_id'] = btoa(this.userData.identify_user_id);
    data['instance_server_id'] = btoa(this.userData.instance_server_id);
    data['instance_server_channel_id'] = btoa(this.userData.instance_server_channel_id);

    return this.http.httpPostServiceDownloadReport(data, name);
  }
//(service new api)
  employeeSalaryType(payload) {
    this.getUserData();
    let data = payload;
    data['_compgrp'] = 'hrs';
    data['_comp'] = 'report';
    data['_action'] = 'employee_salary_history_log';
    data['identify_user_id'] = btoa(this.userData.identify_user_id);
    data['instance_server_id'] = btoa(this.userData.instance_server_id);
    data['instance_server_channel_id'] = btoa(this.userData.instance_server_channel_id);

    return this.http.httpPostServiceReport(data);
  }

  reportSalaryAdjust(payload) {
    this.getUserData();
    let data = payload;
    data['_compgrp'] = 'hrs';
    data['_comp'] = 'report';
    data['_action'] = 'employee_salary_log';
    data['identify_user_id'] = btoa(this.userData.identify_user_id);
    data['instance_server_id'] = btoa(this.userData.instance_server_id);
    data['instance_server_channel_id'] = btoa(this.userData.instance_server_channel_id);

    return this.http.httpPostServiceReport(data);
  }
  employeeTypeAdjustReportDownload(payload, name?): Promise<any> {
    this.getUserData;
    let data = payload;
    data['_compgrp'] = 'hrs';
    data['_comp'] = 'report';
    data['_action'] = 'employee_type_log';
    data['identify_user_id'] = btoa(this.userData.identify_user_id);
    data['instance_server_id'] = btoa(this.userData.instance_server_id);
    data['instance_server_channel_id'] = btoa(this.userData.instance_server_channel_id);

    return this.http.httpPostServiceDownloadReport(data, name);
  }
  employeeTypeAdjustReportDownload2(payload, name?): Promise<any> {
    this.getUserData;
    let data = payload;
    data['_compgrp'] = 'hrs';
    data['_comp'] = 'report';
    data['_action'] = 'employee_type_adjust';
    data['identify_user_id'] = btoa(this.userData.identify_user_id);
    data['instance_server_id'] = btoa(this.userData.instance_server_id);
    data['instance_server_channel_id'] = btoa(this.userData.instance_server_channel_id);
    if (payload['type'] === 'excel') {
      return this.http.httpPostServiceDownloadReport(data, payload.filename);
    } else if (payload['type'] === 'pdf') {
      return this.http.httpPostServiceOpen(data);
    }
    // return this.http.httpPostServiceDownloadReport(data, name);
  }

  reportEmployeeTypeAdjust(payload) {
    this.getUserData();
    let data = payload;
    data['_compgrp'] = 'hrs';
    data['_comp'] = 'report';
    data['_action'] = 'employee_type_log';
    data['identify_user_id'] = btoa(this.userData.identify_user_id);
    data['instance_server_id'] = btoa(this.userData.instance_server_id);
    data['instance_server_channel_id'] = btoa(this.userData.instance_server_channel_id);

    return this.http.httpPostServiceReport(data);
  }
  reportorganizatinAdjust(payload) {
    this.getUserData();
    let data = payload;
    data['_compgrp'] = 'hrs';
    data['_comp'] = 'report';
    data['_action'] = 'organization_adjust_log';
    data['identify_user_id'] = btoa(this.userData.identify_user_id);
    data['instance_server_id'] = btoa(this.userData.instance_server_id);
    data['instance_server_channel_id'] = btoa(this.userData.instance_server_channel_id);

    return this.http.httpPostServiceReport(data);
  }
  organizatinAdjustReportDownload(payload): Promise<any> {
    this.getUserData;
    let data = payload;
    data['_compgrp'] = 'hrs';
    data['_comp'] = 'report';
    data['_action'] = 'organization_adjust_log';
    data['identify_user_id'] = btoa(this.userData.identify_user_id);
    data['instance_server_id'] = btoa(this.userData.instance_server_id);
    data['instance_server_channel_id'] = btoa(this.userData.instance_server_channel_id);

    return this.http.httpPostServiceDownloadReport(data, payload.filename);
  }

  reportEmployeeComplaint(payload): Promise<any> {
    this.getUserData();
    let data = payload;
    data['_compgrp'] = 'hrs';
    data['_comp'] = 'report';
    data['_action'] = 'employee_complaint';

    data['identify_user_id'] = btoa(this.userData.identify_user_id);
    data['instance_server_id'] = btoa(this.userData.instance_server_id);
    data['instance_server_channel_id'] = btoa(this.userData.instance_server_channel_id);

    return this.http.httpPostServiceReport(data);
  }
  ComplaintReportDownload(payload, name?) {
    this.getUserData();
    let data = { ...payload };
    data['_compgrp'] = 'hrs';
    data['_comp'] = 'report';
    data['_action'] = 'employee_complaint';
    data['identify_user_id'] = btoa(this.userData.identify_user_id);
    data['instance_server_id'] = btoa(this.userData.instance_server_id);
    data['instance_server_channel_id'] = btoa(this.userData.instance_server_channel_id);

    if (payload['type'] === 'excel') {
      return this.http.httpPostServiceDownloadReport(data, name);
    } else if (payload['type'] === 'pdf') {
      // return this.http.httpPostServiceOpen(data);
      return this.http.httpPostServiceGetFullPathReport(data);
    }
  }

  elearningReport(payload) {
    this.getUserData();
    let data = payload;
    data['_compgrp'] = 'hrs';
    data['_comp'] = 'report';
    data['_action'] = 'e_learning';
    data['identify_user_id'] = btoa(this.userData.identify_user_id);
    data['instance_server_id'] = btoa(this.userData.instance_server_id);
    data['instance_server_channel_id'] = btoa(this.userData.instance_server_channel_id);

    return this.http.httpPostServiceReport(data);
  }

  elearningReportNew(payload) {
    this.getUserData();
    let data = payload;
    data['_compgrp'] = 'hrs';
    data['_comp'] = 'report';
    data['_action'] = 'e_learning_new';
    data['identify_user_id'] = btoa(this.userData.identify_user_id);
    data['instance_server_id'] = btoa(this.userData.instance_server_id);
    data['instance_server_channel_id'] = btoa(this.userData.instance_server_channel_id);

    return this.http.httpPostServiceReport(data);
  }

  elearningReportDownload(payload) {
    this.getUserData();
    let data = payload;
    data['_compgrp'] = 'hrs';
    data['_comp'] = 'report';
    data['_action'] = 'e_learning';
    data['identify_user_id'] = btoa(this.userData.identify_user_id);
    data['instance_server_id'] = btoa(this.userData.instance_server_id);
    data['instance_server_channel_id'] = btoa(this.userData.instance_server_channel_id);

    if (payload['type'] === 'excel') {
      return this.http.httpPostServiceDownloadReport(data, payload.name);
    } else if (payload['type'] === 'pdf') {
      return this.http.httpPostServiceOpen(data);
    }
  }

  elearningReportNewDownload(payload) {
    this.getUserData();
    let data = payload;
    data['_compgrp'] = 'hrs';
    data['_comp'] = 'report';
    data['_action'] = 'e_learning_new';
    data['identify_user_id'] = btoa(this.userData.identify_user_id);
    data['instance_server_id'] = btoa(this.userData.instance_server_id);
    data['instance_server_channel_id'] = btoa(this.userData.instance_server_channel_id);

    if (payload['type'] === 'excel') {
      return this.http.httpPostServiceDownloadReport(data, payload.name);
    } else if (payload['type'] === 'pdf') {
      return this.http.httpPostServiceOpen(data);
    }
  }

  getEmployeeReportWelfareJson(payload): Promise<any> {
    this.getUserData();
    let data = payload;
    data['_compgrp'] = 'hrs';
    data['_comp'] = 'report';
    data['_action'] = 'employee_welfare';
    data['identify_user_id'] = btoa(this.userData.identify_user_id);
    data['instance_server_id'] = btoa(this.userData.instance_server_id);
    data['instance_server_channel_id'] = btoa(this.userData.instance_server_channel_id);

    if (payload['type'] === 'excel') {
      return this.http.httpPostServiceDownloadReport(data, payload['name']);
    } else if (payload['type'] === 'pdf') {
      return this.http.httpPostServiceOpen(data);
    }
    else {
      return this.http.httpPostServiceReport(data);
    }
  }

  workforceSummaryJobReportDownload(payload) {
    this.getUserData();
    let data = { ...payload };
    data['_compgrp'] = 'hrs';
    data['_comp'] = 'report';
    data['_action'] = 'workforce_summary_job';
    data['identify_user_id'] = btoa(this.userData.identify_user_id);
    data['instance_server_id'] = btoa(this.userData.instance_server_id);
    data['instance_server_channel_id'] = btoa(this.userData.instance_server_channel_id);

    if (payload['type'] === 'excel') {
      return this.http.httpPostServiceDownloadReport(data, 'summary-job');
    } else if (payload['type'] === 'pdf') {
      return this.http.httpPostServiceOpen(data);
    }
  }

  workforceSummaryEmployeeReportDownload(payload) {
    this.getUserData();
    let data = { ...payload };
    data['_compgrp'] = 'hrs';
    data['_comp'] = 'report';
    data['_action'] = 'workforce_summary_employee';
    data['identify_user_id'] = btoa(this.userData.identify_user_id);
    data['instance_server_id'] = btoa(this.userData.instance_server_id);
    data['instance_server_channel_id'] = btoa(this.userData.instance_server_channel_id);

    if (payload['type'] === 'excel') {
      return this.http.httpPostServiceDownloadReport(data, 'summary-employee');
    } else if (payload['type'] === 'pdf') {
      return this.http.httpPostServiceOpen(data);
    }
  }
  workforceSummaryTimeframeReportDownload(payload) {
    this.getUserData();
    let data = { ...payload };
    data['_compgrp'] = 'hrs';
    data['_comp'] = 'report';
    data['_action'] = 'workforce_summary_timeframe';
    data['identify_user_id'] = btoa(this.userData.identify_user_id);
    data['instance_server_id'] = btoa(this.userData.instance_server_id);
    data['instance_server_channel_id'] = btoa(this.userData.instance_server_channel_id);

    if (payload['type'] === 'excel') {
      return this.http.httpPostServiceDownloadReport(data, 'summary-timeframe');
    } else if (payload['type'] === 'pdf') {
      return this.http.httpPostServiceOpen(data);
    }
  }

  chartOfAccountsReport(payload): Promise<any> {
    this.getUserData();
    let data = {...payload};
    data['_compgrp'] = 'hrs';
    data['_comp'] = 'report';
    data['_action'] = 'chart_of_accounts';
    data['identify_user_id'] = btoa(this.userData.identify_user_id);
    data['instance_server_id'] = btoa(this.userData.instance_server_id);
    data['instance_server_channel_id'] = btoa(this.userData.instance_server_channel_id);

    if (payload['type'] === 'excel' || payload['type'] === 'excel2') {
      return this.http.httpPostServiceDownloadReport(data, payload.filename);
    } else {
      return this.http.httpPostServiceReport(data);
    }
  }


  getAnnouncementPDF(payload, name){
    let data = payload;
    data['_compgrp'] = 'hrs';
    data['_comp'] = 'report';
    data['_action'] = 'get_annoucement';
    data['identify_user_id'] = btoa(this.userData.identify_user_id);
    data['instance_server_id'] = btoa(this.userData.instance_server_id);
    data['instance_server_channel_id'] = btoa(this.userData.instance_server_channel_id);
    data['type'] = 'pdf';
    return this.http.httpPostServiceGetFullPathReport(data);
  }

  getActivityDocsList(payload?): Promise<any> {
    let data = payload;
    data['_compgrp'] = 'hrs';
    data['_comp'] = 'report';
    data['_action'] = 'activity_docs';
    data['identify_user_id'] = btoa(this.userData.identify_user_id);
    data['instance_server_id'] = btoa(this.userData.instance_server_id);
    data['instance_server_channel_id'] = btoa(this.userData.instance_server_channel_id);

    if (payload['type'] === 'excel') {
      return this.http.httpPostServiceDownloadReport(data, 'เอกสารประวัติการแก้ไขเอกสาร');
    } else {
      return this.http.httpPostService(data);
    }
  }

  getActivityDocsById(payload): Promise<any> {
    let data = payload;
    data['_compgrp'] = 'hrs';
    data['_comp'] = 'report';
    data['_action'] = 'get_activity_action_step';
    data['identify_user_id'] = btoa(this.userData.identify_user_id);
    data['instance_server_id'] = btoa(this.userData.instance_server_id);
    data['instance_server_channel_id'] = btoa(this.userData.instance_server_channel_id);

    return this.http.httpPostService(data);
  }

  ssoAuditReportJson(payload): Promise<any> {
    this.getUserData();
    let data = payload;
    delete data.month_no;
    data['_compgrp'] = 'hrs';
    data['_comp'] = 'report';
    data['_action'] = 'sso_audit';
    data['identify_user_id'] = btoa(this.userData.identify_user_id);
    data['instance_server_id'] = btoa(this.userData.instance_server_id);
    data['instance_server_channel_id'] = btoa(this.userData.instance_server_channel_id);

    return this.http.httpPostServiceReport(data);
  }

  ssoAuditReportFile(payload, type): Promise<any> {
    this.getUserData();
    let data = payload;
    delete data.month_no;
    data['_compgrp'] = 'hrs';
    data['_comp'] = 'report';
    data['_action'] = 'sso_audit';
    data['identify_user_id'] = btoa(this.userData.identify_user_id);
    data['instance_server_id'] = btoa(this.userData.instance_server_id);
    data['instance_server_channel_id'] = btoa(this.userData.instance_server_channel_id);

    if (type === 'pdf') {
      return this.http.httpPostServiceGetFullPathReport(data);
    } else {
      return this.http.httpPostServiceDownloadReport(data, payload.filename);
    }
  }

  netTotalSectionReportJson(payload): Promise<any> {
    this.getUserData();
    let data = payload;
    delete data.month_no;
    data['_compgrp'] = 'hrs';
    data['_comp'] = 'report';
    data['_action'] = 'net_total_month_section_tnw';
    data['identify_user_id'] = btoa(this.userData.identify_user_id);
    data['instance_server_id'] = btoa(this.userData.instance_server_id);
    data['instance_server_channel_id'] = btoa(this.userData.instance_server_channel_id);

    return this.http.httpPostServiceReport(data);
  }

  netTotalSectionReportFile(payload, type): Promise<any> {
    this.getUserData();
    let data = payload;
    delete data.month_no;
    data['_compgrp'] = 'hrs';
    data['_comp'] = 'report';
    data['_action'] = 'net_total_month_section_tnw';
    data['identify_user_id'] = btoa(this.userData.identify_user_id);
    data['instance_server_id'] = btoa(this.userData.instance_server_id);
    data['instance_server_channel_id'] = btoa(this.userData.instance_server_channel_id);

    if (type === 'pdf') {
      // return this.http.httpPostServiceOpen(data);
      return this.http.httpPostServiceGetFullPathReport(data);
    } else {
      return this.http.httpPostServiceDownloadReport(data, payload.filename);
    }
  }

  ssoMonthAuditReportJson(payload): Promise<any> {

    this.getUserData();
    let data = payload;
    delete data.month_no;
    data['_compgrp'] = 'hrs';
    data['_comp'] = 'report';
    data['_action'] = 'sso_month_audit';
    data['identify_user_id'] = btoa(this.userData.identify_user_id);
    data['instance_server_id'] = btoa(this.userData.instance_server_id);
    data['instance_server_channel_id'] = btoa(this.userData.instance_server_channel_id);

    return this.http.httpPostServiceReport(data);
  }

  taxMonthAuditReportJson(payload): Promise<any> {
    this.getUserData();
    let data = payload;
    delete data.month_no;
    data['_compgrp'] = 'hrs';
    data['_comp'] = 'report';
    data['_action'] = 'tax_month_audit';
    data['identify_user_id'] = btoa(this.userData.identify_user_id);
    data['instance_server_id'] = btoa(this.userData.instance_server_id);
    data['instance_server_channel_id'] = btoa(this.userData.instance_server_channel_id);

    return this.http.httpPostServiceReport(data);
  }

  ssoMonthAuditReportFile(payload, type): Promise<any> {
    this.getUserData();
    let data = payload;
    delete data.month_no;
    data['_compgrp'] = 'hrs';
    data['_comp'] = 'report';
    data['_action'] = 'sso_month_audit';
    data['identify_user_id'] = btoa(this.userData.identify_user_id);
    data['instance_server_id'] = btoa(this.userData.instance_server_id);
    data['instance_server_channel_id'] = btoa(this.userData.instance_server_channel_id);

    if (type === 'pdf') {
      return this.http.httpPostServiceGetFullPathReport(data);
    } else {
      return this.http.httpPostServiceDownloadReport(data, payload.filename);
    }
  }

  taxMonthAuditReportFile(payload, type): Promise<any> {
    this.getUserData();
    let data = payload;
    delete data.month_no;
    data['_compgrp'] = 'hrs';
    data['_comp'] = 'report';
    data['_action'] = 'tax_month_audit';
    data['identify_user_id'] = btoa(this.userData.identify_user_id);
    data['instance_server_id'] = btoa(this.userData.instance_server_id);
    data['instance_server_channel_id'] = btoa(this.userData.instance_server_channel_id);

    if (type === 'pdf') {
      return this.http.httpPostServiceGetFullPathReport(data);
    } else {
      return this.http.httpPostServiceDownloadReport(data, payload.filename);
    }
  }
}

