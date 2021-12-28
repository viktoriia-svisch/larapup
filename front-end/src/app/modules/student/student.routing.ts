import {NgModule} from '@angular/core';
import {UploadArticleComponent} from './upload-article/upload-article.component';
import {LoginStudentComponent} from './login-student/login-student.component';
import {RouterModule, Routes} from '@angular/router';
import {UpdateInformationComponent} from './update-information/update-information.component';
const routes: Routes = [
    {
        path: '', children: [
            {path: 'login', component: LoginStudentComponent}
        ]
    },
    {
        path: '', children: [
            {path: 'upload', component: UploadArticleComponent},
            {path: 'update-info', component: UpdateInformationComponent}
        ]
    }
];
@NgModule({
    imports: [RouterModule.forChild(routes)],
    exports: [RouterModule]
})
export class StudentRoutingModule {
}
