import {NgModule} from '@angular/core';
import {UploadArticleComponent} from './upload-article/upload-article.component';
import {LoginStudentComponent} from './login-student/login-student.component';
import {RouterModule, Routes} from '@angular/router';
import {AddStudentComponent} from './add-student/add-student.component';
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
export class StudentRoutingModule {
}
