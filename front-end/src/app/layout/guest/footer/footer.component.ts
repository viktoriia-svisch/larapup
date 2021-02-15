import { Component, OnInit } from '@angular/core';
@Component({
    selector: 'all-footer',
    templateUrl: './footer.component.html',
    styleUrls: ['./footer.component.scss']
})
export class FooterComponent implements OnInit {
    test : Date = new Date();
    constructor() { }
    ngOnInit() {}
}
