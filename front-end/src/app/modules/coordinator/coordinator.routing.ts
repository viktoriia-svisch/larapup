import {NgModule} from '@angular/core';
import {RouterModule, Routes} from '@angular/router';
import {LoginCoordinatorComponent} from './login-coordinator/login-coordinator.component';
const routes: Routes = [
    {
        path: '', children: [
            {path: 'login', component: LoginCoordinatorComponent},
        ]
    },
];
@NgModule({
    imports: [RouterModule.forChild(routes)],
    exports: [RouterModule]
})
export class CoordinatorRoutingModule {
}
