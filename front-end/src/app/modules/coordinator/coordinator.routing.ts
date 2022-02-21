import {NgModule} from '@angular/core';
import {RouterModule, Routes} from '@angular/router';
import {LoginCoordinatorComponent} from './login-coordinator/login-coordinator.component';
import {CreateFacultyComponent} from './create-faculty/create-faculty.component';
import {CreateCoordinatorComponent} from './create-coordinator/create-coordinator.component';
const routes: Routes = [
    {
        path: '', children: [
            {path: 'login', component: LoginCoordinatorComponent},
        ]
    },
    {
        path: '', children: [
            {path: 'create-faculty', component: CreateFacultyComponent},
            {path: 'create-coordinator', component: CreateCoordinatorComponent}
        ]
    },
];
@NgModule({
    imports: [RouterModule.forChild(routes)],
    exports: [RouterModule]
})
export class CoordinatorRoutingModule {
}
