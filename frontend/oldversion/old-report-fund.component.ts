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
import { OrganizationFunction } from 'app/untils/oranization';

@Component({
  selector: 'app-report-fund',
  templateUrl: './report-fund.component.html',
  styleUrls: ['./report-fund.component.scss'],
  encapsulation: ViewEncapsulation.None,
  animations: [fuseAnimations, CustomAnimation],
})
export class ReportFundComponent implements OnInit, OnDestroy {

  lang = Functions.getLang();

  posValue?: [];
  posNodes = [];

  orgValue?: string;
  orgsNodes = [];

  filterData = {
    company_lists: [],
    branch_lists: [],
    department_lists: [],

    type: 'json',

    year_month: new Date(),

    division_lists:[],
    section_lists:[],
    section_lists_lv01: [],
    section_lists_lv02: [],
    section_lists_lv03: [],
    section_lists_lv04: [],
    section_lists_lv05: [],
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
      console.log(payload)
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
        let valType = type == 'company' ? 'branch' : (type == 'branch' ? 'department' : (type == 'department' ? 'division' : (type == 'division' ? 'section' : (type == 'section' ? 'section_lv01': (type == 'section_lv01' ? 'section_lv02': (type == 'section_lv02' ? 'section_lv03' : (type == 'section_lv03' ? 'section_lv04' : 'section_lv05')))))));
        org.children = this.createOrganizationData(val.children, valType);
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
    this.filterData.division_lists.splice(0, this.filterData.division_lists.length);
    this.filterData.section_lists.splice(0, this.filterData.section_lists.length);
    this.filterData.section_lists_lv01.splice(0, this.filterData.section_lists_lv01.length);
    this.filterData.section_lists_lv02.splice(0, this.filterData.section_lists_lv02.length);
    this.filterData.section_lists_lv03.splice(0, this.filterData.section_lists_lv03.length);
    this.filterData.section_lists_lv04.splice(0, this.filterData.section_lists_lv04.length);
    this.filterData.section_lists_lv05.splice(0, this.filterData.section_lists_lv05.length);
    
    this.fundList = [];
      if (event) {
        let orgTmp = {
          company_lists: this.filterData.company_lists,
          branch_lists: this.filterData.branch_lists,
          department_lists: this.filterData.department_lists,
          division_lists: this.filterData.division_lists,
          section_lists: this.filterData.section_lists,
          section_lists_lv01: this.filterData.section_lists_lv01,
          section_lists_lv02: this.filterData.section_lists_lv02,
          section_lists_lv03: this.filterData.section_lists_lv03,
          section_lists_lv04: this.filterData.section_lists_lv04,
          section_lists_lv05: this.filterData.section_lists_lv05,
        };    
        OrganizationFunction.getOrgID([event], this.orgsNodes, orgTmp, false).then(value => {
          if(value.company_lists) {
            this.getFundData(value.company_lists[0])
          }
          this.filterData.company_lists = value.company_lists;
          this.filterData.branch_lists = value.branch_lists;
          this.filterData.department_lists = value.department_lists;
          this.filterData.division_lists = value.division_lists;
          this.filterData.section_lists = value.section_lists;
          this.filterData.section_lists_lv01 = value.section_lists_lv01;
          this.filterData.section_lists_lv02 = value.section_lists_lv02;
          this.filterData.section_lists_lv03 = value.section_lists_lv03;
          this.filterData.section_lists_lv04 = value.section_lists_lv04;
          this.filterData.section_lists_lv05 = value.section_lists_lv05;
        });
        // this.getType(event);
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
  getData() {
    AlertService.progressAlert('');
    let payload: any = { ...this.filterData };
    payload['year_month'] = moment(this.filterData.year_month).format('YYYY-MM');
    payload['type'] = 'json'
    payload['fund_id'] = btoa(this.fund_id)

    if (payload['company_lists']?.length == 0 && payload['branch_lists']?.length == 0 && payload['department_lists']?.length == 0 && payload['division_lists']?.length == 0 && payload['section_lists']?.length == 0 && this.requireBranch) {
      AlertService.warningAlert('กรุณาเลือกสาขา', 'please select branch');
    } else {
      this.reportService.fundReportJson(payload).then(resp => {
        if (resp.code === '200') {
          if (resp.payload) {
            this.columns = resp.payload.column;
            this.rows = resp.payload.row;
          }
          AlertService.closeAlert();
        } else {
          console.log('Get signout report error', resp);
          AlertService.errorAlert('ไม่สามารถโหลดข้อมูลรายงานได้', 'Load Report Data Error');
        }
      }).catch(async (error) => {
        if (error.status == 413) {
          this.requireBranch = true;
          await this.getNewOrg();
          AlertService.remarkAlert('เนื่องจากพนักงานมีจำนวนมาก กรุณาเลือกทีละสาขา', 'Employee is too many, please select by branch');
        } else {
          console.log('Get net total report catch', error);
          AlertService.errorAlert('ไม่สามารถโหลดข้อมูลรายงานได้', 'Load Report Data Error');
        }
      });
    } 
  }

  async getNewOrg() {
    this.orgValue = null;
    await this.getOrganization();
  }

  downloadFile(type, name?) {
    AlertService.progressAlert('');
    let payload: any = { ...this.filterData };
    payload['type'] = type;
    payload['year_month'] = moment(this.filterData.year_month).format('YYYY-MM');
    payload['fund_id'] = btoa(this.fund_id);

    this.reportService.fundReportDownload(payload, name).then(resp => {
      AlertService.closeAlert();
      if(type == 'pdf'){
        const url = this._router.serializeUrl(this._router.createUrlTree(['view-pdf'], { 
          queryParams: {
            path:Functions.encrypt(resp),
          },
        }));
        window.open(url, '_blank');
      }
    }).catch((err) => {
      if (err.status == 413) {
        if (payload.type.includes('excel')) {
          AlertService.confirmAlert('เนื่องจากข้อมูลของท่านมีจำนวนมาก ระบบจะทำการดาวน์โหลดข้อมูลแยกตามสาขา ท่านต้องการดำเนินการต่อหรือไม่', 'Your information is too many, The system will download information separated by branch. Do you want to continue?').then(async (resp) => {
            if (resp.isConfirmed) {
              await this.splitPayload(payload, type);
            }
          });
        } else {
          AlertService.remarkAlert('เนื่องจากพนักงานมีจำนวนมาก กรุณาใช้รูปแบบ excel', 'Employee is too many, please use the excel format');
        }
      } else {
        AlertService.errorAlert('ไม่สามารถโหลดไฟล์ได้', 'Download File Error');
      }
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
      newPayload['section_lists_lv01'] = [];
      newPayload['section_lists_lv02'] = [];
      newPayload['section_lists_lv03'] = [];
      newPayload['section_lists_lv04'] = [];
      newPayload['section_lists_lv05'] = [];
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
}
