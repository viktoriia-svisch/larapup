import { Injectable } from '@angular/core';
import {HttpClient} from '@angular/common/http';
import {StorageService} from '../shared/storage.service';
@Injectable({
  providedIn: 'root'
})
export class AuthService {
  constructor(
      private http: HttpClient,
      private ls: StorageService
  ) { }
  login(){
  }
  me(){}
  logout(){}
  refresh(){}
}
