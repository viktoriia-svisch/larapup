import {NgModule} from '@angular/core';
import {RouterModule, Routes} from '@angular/router';
import {LoginAdminComponent} from './login-admin/login-admin.component';
const routes: Routes = [
    {
        path: '', children: [
            {path: 'login', component: LoginAdminComponent},
        ]
    },
];
@NgModule({
    imports: [RouterModule.forChild(routes)],
    exports: [RouterModule]
})
export class AdminRoutingModule {
}
