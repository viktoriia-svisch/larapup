import {NgModule} from '@angular/core';
import {CommonModule} from '@angular/common';
import {UploadArticleComponent} from './upload-article/upload-article.component';
import {LoginStudentComponent} from './login-student/login-student.component';
import {StudentRoutingModule} from './student.routing';
@NgModule({
    declarations: [
        UploadArticleComponent,
        LoginStudentComponent,
    ],
    imports: [
        CommonModule,
        StudentRoutingModule,
    ]
})
export class StudentModule {
}
