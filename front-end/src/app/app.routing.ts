import {NgModule} from '@angular/core';
import {RouterModule, Routes} from '@angular/router';
import {AppComponent} from './app.component';
import {DashboardComponent} from './modules/shared/dashboard/dashboard.component';
import {LoginComponent} from './modules/shared/login/login.component';
import {GuestComponent} from './layout/guest/guest.component';
import {ViewArticleComponent} from './modules/shared/view-article/view-article.component';
const routes: Routes = [
    {
        path: '', component: AppComponent, children: [
            {
                path: '', component: GuestComponent, children: [
                    {path: '', pathMatch: 'full', redirectTo: 'news'},
                    {path: 'news', component: DashboardComponent},
                    {path: 'login', component: LoginComponent},
                    {path: 'view-article', component: ViewArticleComponent},
                    {
                        path: 'student',
                        loadChildren: './modules/student/student.module#StudentModule'
                    },
                    {
                        path: 'admin',
                        loadChildren: './modules/admin/admin.module#AdminModule'
                    },
                    {
                        path: 'coordinator',
                        loadChildren: './modules/coordinator/coordinator.module#CoordinatorModule'
                    },
                ]
            },
        ]
    },
    {path: '**', redirectTo: 'home'}
];
@NgModule({
    imports: [RouterModule.forRoot(routes)],
    exports: [RouterModule]
})
export class AppRoutingModule {
}
