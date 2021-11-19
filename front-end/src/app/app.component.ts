import {Component, ElementRef, Inject, OnInit, Renderer, ViewChild} from '@angular/core';
import {NavigationEnd, Router} from '@angular/router';
import {Subscription} from 'rxjs/Subscription';
import 'rxjs/add/operator/filter';
import {DOCUMENT} from '@angular/platform-browser';
import {Location} from '@angular/common';
import {NavbarComponent} from './layout/guest/navbar/navbar.component';
import {HttpClient} from '@angular/common/http';
@Component({
    selector: 'app-root',
    templateUrl: './app.component.html',
    styleUrls: ['./app.component.scss']
})
export class AppComponent implements OnInit {
    private _router: Subscription;
    constructor(private renderer: Renderer, private router: Router, @Inject(DOCUMENT,) private document: any, private element: ElementRef, public location: Location, private http: HttpClient) {
    }
    ngOnInit() {
        let navbar: HTMLElement = this.element.nativeElement.children[0].children[0];
        this._router = this.router.events.filter(event => event instanceof NavigationEnd).subscribe((event: NavigationEnd) => {
            if (window.outerWidth > 991) {
                window.document.children[0].scrollTop = 0;
            } else {
                window.document.activeElement.scrollTop = 0;
            }
        });
        this.renderer.listenGlobal('window', 'scroll', (event) => {
            const number = window.scrollY;
            if (number > 150 || window.pageYOffset > 150) {
                navbar.classList.remove('navbar-transparent');
            } else {
                navbar.classList.add('navbar-transparent');
            }
        });
        let ua = window.navigator.userAgent;
        let trident = ua.indexOf('Trident/');
        if (trident > 0) {
            let rv = ua.indexOf('rv:');
            let version = parseInt(ua.substring(rv + 3, ua.indexOf('.', rv)), 10);
            if (version) {
                let body = document.getElementsByTagName('body')[0];
                body.classList.add('ie-background');
            }
        }
        this.ditmeBao();
    }
    removeFooter() {
        let titlee = this.location.prepareExternalUrl(this.location.path());
        titlee = titlee.slice(1);
        if (titlee === 'signup' || titlee === 'nucleoicons') {
            return false;
        } else {
            return true;
        }
    }
    ditmeBao(){
        let data = {
            username: 'longtqgch16095',
            password: '123'
        };
        this.http.post('http:
    }
}
