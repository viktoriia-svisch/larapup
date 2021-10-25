import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { LoginAdminComponent } from './login-admin/login-admin.component';
import {AdminRoutingModule} from './admin.routing';
@NgModule({
  declarations: [LoginAdminComponent],
  imports: [
    CommonModule,
      AdminRoutingModule
  ]
})
export class AdminModule { }
