import { Component, LOCALE_ID, OnDestroy, OnInit, ViewEncapsulation, ViewChild } from '@angular/core';
import { fuseAnimations } from '@fuse/animations';
import { CustomAnimation } from 'app/main/animations';
import { Functions } from 'app/untils/functions';

import * as moment from 'moment';
import { Organization, Position } from 'app/untils/model';
import { OrganizationService } from 'app/services/organization.service';
import { ReportService } from 'app/services/report.service';
import { AlertService } from 'app/untils/alert-service';

import { registerLocaleData } from '@angular/common';
import localeTh from '@angular/common/locales/th';
import localeEN from '@angular/common/locales/en';
import { th_TH, en_US, NzI18nService } from 'ng-zorro-antd/i18n';
registerLocaleData(localeTh, localeEN);

import { locale as english } from 'i18n/report/en';
import { locale as thai } from 'i18n/report/th';
import { FuseTranslationLoaderService } from '@fuse/services/translation-loader.service';
import { TranslateService } from '@ngx-translate/core';
import { takeUntil } from 'rxjs/operators';
import { Subject } from 'rxjs';

import { FuseSidebarService } from '@fuse/components/sidebar/sidebar.service';
import { TooltipService } from 'app/services/tooltip.service';
import { Router } from '@angular/router';
import { cloneDeep } from 'lodash';
import { NzNotificationService } from 'ng-zorro-antd/notification';
import { HttpClient } from '@angular/common/http';


export interface FundReport {
  report_title: string;
  company_name: string;
  branch_name: string;
  start_month: string;
  end_month: string;
  funds: Fund[];
  report_summary: ReportSummary;
}

export interface EmployeeRow {
  employee_code: string;
  employee_name: string;
  employee_last_name: string;
  date: string;
  account_number: string;
  employee_contribution: number;
  company_contribution: number;
}

export interface FundSummary {
  total_employees: number;
  total_employee_contribution: number;
  total_company_contribution: number;
}

export interface ReportSummary {
  total_all_employees: number;
  total_all_employee_contribution: number;
  total_all_company_contribution: number;
}

// สร้าง interface สำหรับข้อมูลที่ได้จาก API
interface ApiResponse {
  code: string;
  message: string;
  payload: {
    months: Month[];
    fund_Lists: Fund[];
    employee_data: {
      [fundId: string]: {
        [employeeId: string]: EmployeeContribution;
      }
    };
    report_array: {
      [companyId: string]: Company;
    }
  }
}

interface Month {
  master_salary_month: string;
  salary_report_start_dt: string;
  salary_report_end_dt: string;
}

interface Fund {
  fund_id: string;
  fund_name: string;
  salary_type_name: string;
  salary_type_name_en: string;
}

interface EmployeeContribution {
  employee_code: string;
  employee_name: string;
  date: string;
  employee_contribution: string;
  company_contribution: string;
}

interface Company {
  name: string;
  branches: {
    [branchId: string]: Branch;
  }
}

interface Branch {
  name: string;
  departments: {
    [departmentId: string]: Department;
  }
}

interface Department {
  name: string;
  employees: {
    [employeeId: string]: Employee;
  }
}

interface Employee {
  employee_id: string;
  employee_code: string;
  employee_name: string;
  employee_last_name: string;
}

@Component({
  selector: 'app-report-fund',
  templateUrl: './report-fund.component.html',
  styleUrls: ['./report-fund.component.scss'],
  encapsulation: ViewEncapsulation.None,
  animations: [fuseAnimations, CustomAnimation],
})
export class ReportFundComponent implements OnInit, OnDestroy {

  lang = Functions.getLang();
  isLoading = false;
  reportData: FundReport | null = null;

  posValue?: [];
  posNodes = [];

  orgValue?: string;
  orgsNodes = [];

  selectedFundIds: string[] = [];
  filterData = {
    company_lists: [],
    branch_lists: [],
    department_lists: [],
    type: 'json',
    start_month: null,
    end_month: null
  }

  columns = [];
  rows = [];
  fundList = [];
  fund_id;

  private _unsubscribeAll: Subject<any>;

  constructor(
    private _fuseSidebarService: FuseSidebarService,
    private _tooltip:TooltipService,
    private orgService: OrganizationService,
    private reportService: ReportService,
    private i18n: NzI18nService,
    private translationLoader: FuseTranslationLoaderService,
    public translate: TranslateService,
    private _router: Router,
    private notification: NzNotificationService,
    private http: HttpClient
  ) {
    if (this.lang === 'TH') {
      this.i18n.setLocale(th_TH);
    } else {
      this.i18n.setLocale(en_US);
    }
    this.translationLoader.loadTranslations(english, thai);
    this._unsubscribeAll = new Subject();
  }

  ngOnInit(): void {
    this.translate.use(this.lang.toLowerCase());

    this.translate.onLangChange
      .pipe(takeUntil(this._unsubscribeAll))
      .subscribe(lang => {
        this.translate.use(lang.lang);
        if (lang.lang === 'th') {
          this.i18n.setLocale(th_TH);
        } else {
          this.i18n.setLocale(en_US);
        }
      })

    this.getOrganization();
    // this.getData();
  }

  ngOnDestroy(): void {
    this._unsubscribeAll.next();
    this._unsubscribeAll.complete();
  }


  getFundData(company_id) {
    let payload = {
      company_id: btoa(company_id),
    }

    this.orgService.getListFund(payload).then(resp => {
      if (resp.code === '200') {
        if (resp.payload) {
          this.fundList = this.createData(resp.payload);
        } else {
          this.fundList = [];
        }
      } else {
        console.log('Get fund error', resp);
      }
    }).catch(error => {
      console.log('Get fund catch', error);
    });
  }

  createData(data) {
    return data.reduce((acc, val) => {
      let tmp = {};
      tmp['title'] = val.salary_type_name;
      tmp['key'] = val.fund_id;
      tmp['isLeaf'] = true;
      return acc.concat(tmp);
    }, [])
  }
  createPositionData(data) {
    return data.reduce((acc, val) => {
      const org = new Position();
      org.title = this.lang == 'TH'?val.position_name:val.position_name_en;
      org.key = val.position_id;

      if (val.childs) {
        org.children = this.createPositionData(val.childs);
      } else {
        org.isLeaf = true;
      }

      return acc.concat(org);
    }, [])
  }


  getOrganization() {
    this.orgService.getOrganizationStructure().then(resp => {
      if (resp.code === '200') {
        this.orgsNodes = this.createOrganizationData(resp.payload, 'company');
      } else {
        console.log('error', resp);
      }
    }).catch(error => {
      console.log('error', error);
    });
  }

  createOrganizationData(data, type) {
    return data.reduce((acc, val) => {
      const org = new Organization();
      org.title = val.name;
      org.key = val.id;
      org.type = type;

      if (this.requireBranch) {
        if (type == 'company') {
          org.disabled = true;
        }
  
        if (type == 'branch' && !this.orgValue) {
          this.orgValue = org.key;
          this.orgChange(this.orgValue);
        }
      }

      if (val.children) {
        org.children = this.createOrganizationData(val.children, type == 'company' ? 'branch' : 'department');
      } else {
        org.isLeaf = true;
      }
      return acc.concat(org);
    }, []);
  }

  orgChange(event): void {
    this.filterData.company_lists.splice(0, this.filterData.company_lists.length);
    this.filterData.branch_lists.splice(0, this.filterData.branch_lists.length);
    this.filterData.department_lists.splice(0, this.filterData.department_lists.length);
    this.fundList = [];
    if (event) {
      this.getType(event);
    }
  }

  getType(value) {
    for (let i = 0; i < this.orgsNodes.length; i++) {
      if (this.orgsNodes[i].key === value) {
        this.getFundData(value)
        this.filterData.company_lists.push({ id: btoa(value) });
      } else {
        if (this.orgsNodes[i].children) {
          for (let j = 0; j < this.orgsNodes[i].children.length; j++) {
            if (this.orgsNodes[i].children[j].key === value) {
              this.filterData.branch_lists.push({ id: btoa(value) });
            } else {
              if (this.orgsNodes[i].children[j].children) {
                for (let k = 0; k < this.orgsNodes[i].children[j].children.length; k++) {
                  if (this.orgsNodes[i].children[j].children[k].key === value) {
                    this.filterData.department_lists.push({ id: btoa(value) });
                  }
                }
              }
            }
          }
        }
      }
    }
  }

  requireBranch: boolean = false;
  getData(): void {
    if (!this.selectedFundIds.length) {
      this.notification.warning('กรุณาเลือกกองทุน', '');
      return;
    }

    if (!this.filterData.start_month || !this.filterData.end_month) {
      this.notification.warning('กรุณาเลือกช่วงวันที่', '');
      return;
    }

    this.isLoading = true;

    const payload = {
      fund_ids: this.selectedFundIds.map(id => btoa(id)),
      start_month: moment(this.filterData.start_month).format('YYYY-MM'),
      end_month: moment(this.filterData.end_month).format('YYYY-MM'),
      company_lists: this.filterData.company_lists,
      branch_lists: this.filterData.branch_lists,
      department_lists: this.filterData.department_lists
    };

    this.reportService.fundReportJson(payload).then(response => {
      if (response.code === '200' && response.payload) {
        this.reportData = this.transformApiData(response);
      } else {
        this.notification.error('ไม่พบข้อมูล', '');
        this.reportData = null;
      }
      this.isLoading = false;
    }).catch(error => {
      console.error('Error fetching report:', error);
      this.notification.error('เกิดข้อผิดพลาดในการดึงข้อมูล', '');
      this.reportData = null;
      this.isLoading = false;
    });
  }

  downloadFile(type: 'pdf' | 'excel'): void {
    if (!this.selectedFundIds.length || !this.filterData.start_month || !this.filterData.end_month) {
      this.notification.warning('กรุณาเลือกกองทุนและช่วงวันที่', '');
      return;
    }

    this.isLoading = true;

    const payload = {
      fund_ids: this.selectedFundIds.map(id => btoa(id)),
      start_month: moment(this.filterData.start_month).format('YYYY-MM'),
      end_month: moment(this.filterData.end_month).format('YYYY-MM'),
      company_lists: this.filterData.company_lists,
      branch_lists: this.filterData.branch_lists,
      department_lists: this.filterData.department_lists,
      type: type
    };

    this.reportService.fundReportDownload(payload).then(response => {
      if (response.code === '200') {
        if (type === 'pdf') {
          const url = this._router.serializeUrl(this._router.createUrlTree(['view-pdf'], { 
            queryParams: {
              path: Functions.encrypt(response),
            },
          }));
          window.open(url, '_blank');
        } else {
          const blob = new Blob([response.payload], { 
            type: 'application/vnd.ms-excel'
          });
          const url = window.URL.createObjectURL(blob);
          const link = document.createElement('a');
          link.href = url;
          link.download = `fund_report_${moment().format('YYYYMMDD_HHmmss')}.${type}`;
          link.click();
          window.URL.revokeObjectURL(url);
        }
      } else {
        this.notification.error('Error', response.message || 'Failed to download file');
      }
      this.isLoading = false;
    }).catch(error => {
      console.error('Error downloading report:', error);
      this.notification.error('เกิดข้อผิดพลาดในการดาวน์โหลดรายงาน', '');
      this.isLoading = false;
    });
  }

  branchesInOrg: any = [];
  async splitPayload(payload, type) {
    this.branchesInOrg = this.orgsNodes.reduce((acc, val) => {
      if (val.type === 'company' && val.children) {
        let branch = val.children.reduce((acc2, val2) => {
          return acc2.concat({ id: val2.key, name: val2.title });
        }, []);

        return acc.concat(branch);
      }
    }, []);
    
    let tmpPayload = cloneDeep(payload);
    let i = 0;
    for await (let branch of this.branchesInOrg) {
      let newPayload = cloneDeep(tmpPayload);
      newPayload['company_lists'] = [];
      newPayload['branch_lists'] = [{ id: btoa(branch.id) }];
      newPayload['department_lists'] = [];
      newPayload['division_lists'] = [];
      newPayload['section_lists'] = [];
      newPayload['position_lists'] = [];
      newPayload['bypass'] = true;
      newPayload['filename'] = `${tmpPayload['filename']} ${branch['name'].replace('.', '')}`;
      this.progress.name = branch['name'];
      this.progress.curr = i + 1;
      this.progress.percentage = Number(((100 / this.branchesInOrg.length) * i).toFixed());
      this.createBasicNotification();
      await this.reportService.fundReportJson(newPayload).then(() => {
      }).catch(() => {
        AlertService.errorAlert('ไม่สามารถดาวน์โหลดไฟล์ได้');
      });
      i++;
    }
    this.progress.percentage = 100;
    this.createBasicNotification();
  }

  progress = {
    percentage: 0,
    name: '',
    fail: 0,
    success: 0,
    curr: 0,
    fail_lists: [],
  }
  notificationType = 'full';
  @ViewChild('downloadExcelNoti', { static: false }) template?;
  minimizeNotification(value) {
    this.notificationType = value;
    this.createBasicNotification();
  }

  createBasicNotification(): void {
    this.notification.template(this.template!,
      {
        nzDuration: 0,
        nzPlacement: 'bottomRight',
        nzKey: 'downloadExcelNoti',
        nzCloseIcon: '',
        nzStyle: this.notificationType === 'mini' ? {
          width: '40px',
          height: '40px',
          background: 'transparent',
          padding: '0',
          boxShadow: '0 0 rgba(0, 0, 0, 0)',
          position: 'absolute',
          bottom: '0',
          right: '0',
          margin: '0',
        } : {}
      });
  }

          /**
     * Toggle sidebar open
     *
     * @param key
     */
           toggleSidebarOpen(key, code): void {
            this._fuseSidebarService.getSidebar(key).toggleOpen();
            this._tooltip.setTooltip({code: code, title:"คำนวณเงินเดือนรายบุคคล",desc:"เมนูคำนวนเงินเดือนรายบุคคล คือ \nการเลือกพนักงาน"})
        }

  // Add method to handle month range change
  onMonthRangeChange(dates: Date[]): void {
    if (dates && dates.length === 2) {
      const startDate = moment(dates[0]);
      const endDate = moment(dates[1]);
      const monthDiff = endDate.diff(startDate, 'months');
      
      if (monthDiff > 12) {
        AlertService.warningAlert('ไม่สามารถเลือกช่วงเดือนเกิน 12 เดือน', 'Month range cannot exceed 12 months');
        return;
      }
      
      this.filterData.start_month = dates[0];
      this.filterData.end_month = dates[1];
    }
  }

  // Add method to validate date range
  disabledDate = (current: Date): boolean => {
    if (!this.filterData.start_month) {
      return false;
    }
    const startDate = moment(this.filterData.start_month);
    const currentDate = moment(current);
    const monthDiff = currentDate.diff(startDate, 'months');
    return monthDiff > 12;
  }

  async downloadFileByBranch(type: 'pdf' | 'excel'): Promise<void> {
    if (!this.selectedFundIds.length || !this.filterData.start_month || !this.filterData.end_month) {
      this.notification.warning('กรุณาเลือกกองทุนและช่วงวันที่', '');
      return;
    }

    this.isLoading = true;
    this.progress.percentage = 0;

    for (let i = 0; i < this.branchesInOrg.length; i++) {
      const branch = this.branchesInOrg[i];
      const newPayload = {
        ...this.filterData,
        fund_ids: this.selectedFundIds,
        start_month: moment(this.filterData.start_month).format('YYYY-MM'),
        end_month: moment(this.filterData.end_month).format('YYYY-MM'),
        branch_lists: [branch.branch_id],
        type: type
      };

      this.progress.percentage = Number(((100 / this.branchesInOrg.length) * i).toFixed());
      this.createBasicNotification();

      try {
        const response = await this.reportService.fundReportJson(newPayload);
        if (response.code === '200') {
          // Handle successful response
        } else {
          this.notification.error('ไม่สามารถดาวน์โหลดไฟล์ได้', '');
        }
      } catch (error) {
        console.error('Error:', error);
        this.notification.error('ไม่สามารถดาวน์โหลดไฟล์ได้', '');
      }
    }

    this.isLoading = false;
    this.progress.percentage = 100;
  }

  transformApiData(apiData: ApiResponse): FundReport {
    const reportData: FundReport = {
      report_title: 'รายงานกองทุน',
      company_name: Object.values(apiData.payload.report_array)[0]?.name || '',
      branch_name: Object.values(Object.values(apiData.payload.report_array)[0]?.branches || {})[0]?.name || '',
      start_month: apiData.payload.months[0]?.master_salary_month || '',
      end_month: apiData.payload.months[apiData.payload.months.length - 1]?.master_salary_month || '',
      funds: [],
      report_summary: {
        total_all_employees: 0,
        total_all_employee_contribution: 0,
        total_all_company_contribution: 0
      }
    };

    // แปลงข้อมูลกองทุน
    apiData.payload.fund_Lists.forEach(fund => {
      const fundData = {
        fund_name: fund.fund_name,
        columns: ['รหัสพนักงาน', 'ชื่อ-นามสกุล', 'วันที่ลงทะเบียน', 'เลขที่บัญชี', 'เงินสมทบพนักงาน', 'เงินสมทบบริษัท'],
        rows: [] as EmployeeRow[],
        summary: {
          total_employees: 0,
          total_employee_contribution: 0,
          total_company_contribution: 0
        }
      };

      // เพิ่มข้อมูลพนักงานในแต่ละกองทุน
      const employeeData = apiData.payload.employee_data[fund.fund_id] || {};
      Object.entries(employeeData).forEach(([employeeId, emp]) => {
        // หาข้อมูลพนักงานจาก report_array
        let employeeInfo = null;
        for (const company of Object.values(apiData.payload.report_array)) {
          for (const branch of Object.values(company.branches)) {
            for (const dept of Object.values(branch.departments)) {
              if (dept.employees[employeeId]) {
                employeeInfo = dept.employees[employeeId];
                break;
              }
            }
          }
        }

        fundData.rows.push({
          employee_code: emp.employee_code,
          employee_name: emp.employee_name,
          employee_last_name: employeeInfo?.employee_last_name || '',
          date: emp.date || '-',
          account_number: '-',
          employee_contribution: parseFloat(emp.employee_contribution),
          company_contribution: parseFloat(emp.company_contribution)
        });
      });

      // คำนวณสรุปข้อมูลกองทุน
      fundData.summary = {
        total_employees: fundData.rows.length,
        total_employee_contribution: fundData.rows.reduce((sum, row) => sum + row.employee_contribution, 0),
        total_company_contribution: fundData.rows.reduce((sum, row) => sum + row.company_contribution, 0)
      };

      reportData.funds.push(fundData);
    });

    // คำนวณสรุปรายงานทั้งหมด
    reportData.report_summary = {
      total_all_employees: reportData.funds.reduce((sum, fund) => sum + fund.summary.total_employees, 0),
      total_all_employee_contribution: reportData.funds.reduce((sum, fund) => sum + fund.summary.total_employee_contribution, 0),
      total_all_company_contribution: reportData.funds.reduce((sum, fund) => sum + fund.summary.total_company_contribution, 0)
    };

    return reportData;
  }
}
