import { Component, OnInit, OnDestroy, ViewChild } from '@angular/core';
import { FuseSidebarService } from '@fuse/components/sidebar/sidebar.service';
import { TooltipService } from '@fuse/services/tooltip.service';
import { OrganizationService } from 'app/services/organization.service';
import { ReportService } from 'app/services/report.service';
import { NzI18nService } from 'ng-zorro-antd/i18n';
import { FuseTranslationLoaderService } from '@fuse/services/translation-loader.service';
import { TranslateService } from '@ngx-translate/core';
import { Router } from '@angular/router';
import { NzNotificationService } from 'ng-zorro-antd/notification';
import { HttpClient } from '@angular/common/http';
import { Subject } from 'rxjs';
import { takeUntil } from 'rxjs/operators';
import { Functions } from 'app/utils/functions';

// Interfaces for the API response
interface ApiResponse {
    header_jwt_verify: {
        api_id: string;
        jwt_verify: string;
    };
    code: string;
    message: string;
    payload: {
        column: ColumnData[][];
        row: RowData[][];
    };
}

interface ColumnData {
    data: string;
    align: string;
    colspan: string | number;
    rowspan?: string | number;
}

interface RowData {
    data: string | number;
    align: string;
    colspan: string | number;
    color?: string;
    setdate?: string;
    setint?: string;
}

interface FundReport {
    columns: ColumnData[][];
    rows: RowData[][];
    summary: {
        total_employees: number;
        total_employee_contribution: number;
        total_company_contribution: number;
    };
}

@Component({
    selector: 'app-new-report-fund',
    templateUrl: './new-report-fund.component.html',
    styleUrls: ['./new-report-fund.component.scss']
})
export class NewReportFundComponent implements OnInit, OnDestroy {
    lang = Functions.getLang();
    isLoading = false;
    reportData: FundReport | null = null;

    // Organization data
    orgValue?: string;
    orgsNodes = [];

    // Fund selection
    selectedFundIds: string[] = [];
    fundList = [];

    // Date range
    filterData = {
        start_month: null,
        end_month: null
    };

    private _unsubscribeAll: Subject<any>;

    constructor(
        private _fuseSidebarService: FuseSidebarService,
        private _tooltip: TooltipService,
        private orgService: OrganizationService,
        private reportService: ReportService,
        private i18n: NzI18nService,
        private translationLoader: FuseTranslationLoaderService,
        public translate: TranslateService,
        private _router: Router,
        private notification: NzNotificationService,
        private http: HttpClient
    ) {
        this._unsubscribeAll = new Subject();
    }

    ngOnInit(): void {
        this.getOrganization();
        this.getFundData();
    }

    ngOnDestroy(): void {
        this._unsubscribeAll.next();
        this._unsubscribeAll.complete();
    }

    getFundData(): void {
        const payload = {
            _compgrp: 'hrs',
            _comp: 'report',
            _action: 'get_list_fund'
        };

        this.reportService.getListFund(payload)
            .then((response) => {
                if (response && response.payload) {
                    this.fundList = this.transformFundData(response.payload);
                }
            })
            .catch((error) => {
                console.error('Error fetching fund data:', error);
                this.notification.error(
                    this.translate.instant('ERROR'),
                    this.translate.instant('ERROR_FETCHING_FUND_DATA')
                );
            });
    }

    getOrganization(): void {
        this.orgService.getOrganization()
            .pipe(takeUntil(this._unsubscribeAll))
            .subscribe(
                (response) => {
                    if (response && response.payload) {
                        this.orgsNodes = this.createOrganizationData(response.payload);
                    }
                },
                (error) => {
                    console.error('Error fetching organization data:', error);
                }
            );
    }

    createOrganizationData(data: any): any[] {
        return data.map(org => ({
            title: org.name,
            key: org.id,
            value: org.id,
            children: org.branches ? org.branches.map(branch => ({
                title: branch.name,
                key: branch.id,
                value: branch.id,
                isLeaf: true
            })) : []
        }));
    }

    transformFundData(data: any): any[] {
        return data.map(fund => ({
            title: this.lang === 'TH' ? fund.salary_type_name : fund.salary_type_name_en,
            key: fund.fund_id,
            value: fund.fund_id,
            isLeaf: true
        }));
    }

    orgChange(event: string): void {
        this.orgValue = event;
    }

    getData(): void {
        if (!this.validateInputs()) {
            return;
        }

        this.isLoading = true;
        const params = {
            server_id: this.orgValue,
            start_month: this.filterData.start_month,
            end_month: this.filterData.end_month,
            fund_ids: this.selectedFundIds
        };

        this.reportService.getNewFundReport(params)
            .pipe(takeUntil(this._unsubscribeAll))
            .subscribe(
                (response: ApiResponse) => {
                    if (response.code === '200' && response.payload) {
                        this.reportData = {
                            columns: response.payload.column,
                            rows: response.payload.row,
                            summary: this.calculateSummary(response.payload.row)
                        };
                    } else {
                        this.notification.error(
                            this.translate.instant('ERROR'),
                            response.message || this.translate.instant('NO_DATA_FOUND')
                        );
                    }
                    this.isLoading = false;
                },
                (error) => {
                    console.error('Error fetching report data:', error);
                    this.notification.error(
                        this.translate.instant('ERROR'),
                        this.translate.instant('ERROR_FETCHING_DATA')
                    );
                    this.isLoading = false;
                }
            );
    }

    validateInputs(): boolean {
        if (!this.orgValue) {
            this.notification.warning(
                this.translate.instant('WARNING'),
                this.translate.instant('PLEASE_SELECT_COMPANY')
            );
            return false;
        }

        if (!this.selectedFundIds || this.selectedFundIds.length === 0) {
            this.notification.warning(
                this.translate.instant('WARNING'),
                this.translate.instant('PLEASE_SELECT_FUND')
            );
            return false;
        }

        if (!this.filterData.start_month || !this.filterData.end_month) {
            this.notification.warning(
                this.translate.instant('WARNING'),
                this.translate.instant('PLEASE_SELECT_DATE_RANGE')
            );
            return false;
        }

        return true;
    }

    calculateSummary(rows: RowData[][]): any {
        let totalEmployees = 0;
        let totalEmployeeContribution = 0;
        let totalCompanyContribution = 0;

        rows.forEach(row => {
            row.forEach(cell => {
                if (cell.setint === 'Y') {
                    if (cell.align === 'right') {
                        if (row[0].data.toString().includes('รวมจำนวน')) {
                            totalEmployees += parseInt(cell.data.toString());
                        } else if (row[0].data.toString().includes('หักพนักงาน')) {
                            totalEmployeeContribution += parseFloat(cell.data.toString());
                        } else if (row[0].data.toString().includes('บริษัทสมทบ')) {
                            totalCompanyContribution += parseFloat(cell.data.toString());
                        }
                    }
                }
            });
        });

        return {
            total_employees: totalEmployees,
            total_employee_contribution: totalEmployeeContribution,
            total_company_contribution: totalCompanyContribution
        };
    }

    onMonthRangeChange(dates: Date[]): void {
        if (dates && dates.length === 2) {
            this.filterData.start_month = dates[0].toISOString().slice(0, 7);
            this.filterData.end_month = dates[1].toISOString().slice(0, 7);
        } else {
            this.filterData.start_month = null;
            this.filterData.end_month = null;
        }
    }

    disabledDate = (current: Date): boolean => {
        return current > new Date();
    };

    toggleSidebarOpen(key: string, code: string): void {
        this._tooltip.toggleSidebarOpen(key, code);
    }

    downloadFile(type: 'pdf' | 'excel'): void {
        if (!this.reportData) {
            return;
        }

        const params = {
            server_id: this.orgValue,
            start_month: this.filterData.start_month,
            end_month: this.filterData.end_month,
            fund_ids: this.selectedFundIds,
            type: type
        };

        this.reportService.downloadNewFundReport(params)
            .pipe(takeUntil(this._unsubscribeAll))
            .subscribe(
                (response: Blob) => {
                    const url = window.URL.createObjectURL(response);
                    const link = document.createElement('a');
                    link.href = url;
                    link.download = `fund_report_${new Date().getTime()}.${type}`;
                    link.click();
                    window.URL.revokeObjectURL(url);
                },
                (error) => {
                    console.error('Error downloading file:', error);
                    this.notification.error(
                        this.translate.instant('ERROR'),
                        this.translate.instant('ERROR_DOWNLOADING_FILE')
                    );
                }
            );
    }
} 