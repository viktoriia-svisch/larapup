import {NgModule} from '@angular/core';
import {RouterModule, Routes} from '@angular/router';
import {LoginAdminComponent} from './login-admin/login-admin.component';
import {AddStudentComponent} from './add-student/add-student.component';
import {ChooseSemesterComponent} from './choose-semester/choose-semester.component';
const routes: Routes = [
    {
        path: '', children: [
            {path: 'login', component: LoginAdminComponent},
        ]
    },
    {
        path: '', children: [
            {path: 'add', component: AddStudentComponent},
            {path: 'choose', component: ChooseSemesterComponent},
        ]
    }
];
@NgModule({
    imports: [RouterModule.forChild(routes)],
    exports: [RouterModule]
})
export class AdminRoutingModule {
}
