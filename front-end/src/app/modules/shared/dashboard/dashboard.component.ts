import {Component, OnInit} from '@angular/core';
@Component({
    selector: 'app-dashboard',
    templateUrl: './dashboard.component.html',
    styleUrls: ['./dashboard.component.scss']
})
export class DashboardComponent implements OnInit {
    page1 = 1;
    constructor() {
    }
    ngOnInit() {
    }
    changePaginatePage(page: number) {
        console.log(page)
    }
}
