import {Component, OnInit} from '@angular/core';
import {Router} from '@angular/router';
@Component({
    selector: 'app-choose-semester',
    templateUrl: './choose-semester.component.html',
    styleUrls: ['./choose-semester.component.scss']
})
export class ChooseSemesterComponent implements OnInit {
    constructor(private router: Router) {
    }
    ngOnInit() {
    }
    gotoAddStudent() {
        this.router.navigateByUrl('coordinator/create')
    }
}
