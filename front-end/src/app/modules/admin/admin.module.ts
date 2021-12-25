import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { LoginAdminComponent } from './login-admin/login-admin.component';
import {AdminRoutingModule} from './admin.routing';
import { AddStudentComponent } from './add-student/add-student.component';
import { ChooseSemesterComponent } from './choose-semester/choose-semester.component';
@NgModule({
  declarations: [LoginAdminComponent, AddStudentComponent, ChooseSemesterComponent],
  imports: [
    CommonModule,
      AdminRoutingModule
  ]
})
export class AdminModule { }
