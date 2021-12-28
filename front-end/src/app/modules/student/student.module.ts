import {NgModule} from '@angular/core';
import {CommonModule} from '@angular/common';
import {UploadArticleComponent} from './upload-article/upload-article.component';
import {LoginStudentComponent} from './login-student/login-student.component';
import {StudentRoutingModule} from './student.routing';
import { UpdateInformationComponent } from './update-information/update-information.component';
@NgModule({
    declarations: [
        UploadArticleComponent,
        LoginStudentComponent,
        UpdateInformationComponent,
    ],
    imports: [
        CommonModule,
        StudentRoutingModule,
    ]
})
export class StudentModule {
}
