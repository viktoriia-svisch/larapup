import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { LoginCoordinatorComponent } from './login-coordinator/login-coordinator.component';
import {CoordinatorRoutingModule} from './coordinator.routing';
@NgModule({
  declarations: [LoginCoordinatorComponent],
  imports: [
    CommonModule,
      CoordinatorRoutingModule
  ]
})
export class CoordinatorModule { }
