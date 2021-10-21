import {NgModule} from '@angular/core';
import {RouterModule, Routes} from '@angular/router';
import {LoginAdminComponent} from './login-admin/login-admin.component';
import {AddStudentComponent} from './add-student/add-student.component';
const routes: Routes = [
    {
        path: '', children: [
            {path: 'login', component: LoginAdminComponent},
        ]
    },
    {
        path: '', children: [
            {path: 'add', component: AddStudentComponent},
        ]
    }
];
@NgModule({
    imports: [RouterModule.forChild(routes)],
    exports: [RouterModule]
})
export class AdminRoutingModule {
}
