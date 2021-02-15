import {NgModule} from '@angular/core';
import {UploadArticleComponent} from './upload-article/upload-article.component';
import {LoginStudentComponent} from './login-student/login-student.component';
import {RouterModule, Routes} from '@angular/router';
const routes: Routes = [
    {
        path: '', children: [
            {path: 'login', component: LoginStudentComponent}
        ]
    },
    {
        path: '', children: [
            {path: 'upload', component: UploadArticleComponent},
        ]
    }
];
@NgModule({
    imports: [RouterModule.forChild(routes)],
    exports: [RouterModule]
})
export class StudentRoutingModule {
}
