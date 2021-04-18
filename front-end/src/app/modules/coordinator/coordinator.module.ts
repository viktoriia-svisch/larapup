import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { LoginCoordinatorComponent } from './login-coordinator/login-coordinator.component';
import {CoordinatorRoutingModule} from './coordinator.routing';
import { CreateFacultyComponent } from './create-faculty/create-faculty.component';
@NgModule({
  declarations: [LoginCoordinatorComponent, CreateFacultyComponent],
  imports: [
    CommonModule,
      CoordinatorRoutingModule
  ]
})
export class CoordinatorModule { }
