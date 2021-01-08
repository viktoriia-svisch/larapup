import {NgModule} from '@angular/core';
import {RouterModule, Routes} from '@angular/router';
import {HomeComponent} from './prebuild/home/home.component';
import {AppComponent} from './app.component';
import {DashboardComponent} from './modules/shared/dashboard/dashboard.component';
import {LoginComponent} from './modules/shared/login/login.component';
import {GuestComponent} from './layout/guest/guest.component';
import {NucleoiconsComponent} from './components/nucleoicons/nucleoicons.component';
const routes: Routes = [
    {
        path: '', component: AppComponent, children: [
            {
                path: '', component: GuestComponent, children: [
                    {path: '', pathMatch: 'full', redirectTo: 'news'},
                    {path: 'news', component: DashboardComponent},
                    {path: 'login', component: LoginComponent}
                ]
            },
        ]
    },
    {path: 'home', component: HomeComponent},
    {path: 'nucleoicons', component: NucleoiconsComponent},
    {path: '**', redirectTo: 'home'}
];
@NgModule({
    imports: [RouterModule.forRoot(routes)],
    exports: [RouterModule]
})
export class AppRoutingModule {
}
