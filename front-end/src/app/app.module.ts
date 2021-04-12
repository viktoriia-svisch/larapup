import {BrowserModule} from '@angular/platform-browser';
import {NgModule} from '@angular/core';
import {FormsModule} from '@angular/forms';
import {NgbModule} from '@ng-bootstrap/ng-bootstrap';
import {RouterModule} from '@angular/router';
import {AppRoutingModule} from './app.routing';
import {AppComponent} from './app.component';
import {SignupComponent} from './prebuild/signup/signup.component';
import {LandingComponent} from './prebuild/landing/landing.component';
import {ProfileComponent} from './prebuild/profile/profile.component';
import {NavbarComponent} from './layout/guest/navbar/navbar.component';
import {FooterComponent} from './layout/guest/footer/footer.component';
import {HomeModule} from './prebuild/home/home.module';
import {DashboardComponent} from './modules/shared/dashboard/dashboard.component';
import {LoginComponent} from './modules/shared/login/login.component';
import {GuestComponent} from './layout/guest/guest.component';
import {HttpClientModule} from '@angular/common/http';
import {StorageService} from './servies/shared/storage.service';
import {AuthService} from './servies/modules/auth.service';
@NgModule({
    declarations: [
        AppComponent,
        SignupComponent,
        LandingComponent,
        ProfileComponent,
        NavbarComponent,
        FooterComponent,
        DashboardComponent,
        LoginComponent,
        GuestComponent
    ],
    imports: [
        BrowserModule,
        FormsModule,
        NgbModule,
        RouterModule,
        HttpClientModule,
        HomeModule,
        AppRoutingModule,
    ],
    providers: [
        AuthService,
        StorageService
    ],
    bootstrap: [AppComponent]
})
export class AppModule {
}
