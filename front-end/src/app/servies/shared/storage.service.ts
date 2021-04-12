import {Injectable} from '@angular/core';
@Injectable({
    providedIn: 'root'
})
export class StorageService {
    constructor() {
    }
    static store(key: string, value: string) {
        if (!!key && value !== null) {
            localStorage.setItem(key, value);
            return true;
        }
        return false;
    }
    static get(key: string) {
        return localStorage.getItem(key);
    }
    static flush() {
        localStorage.clear();
    }
    setAuthorize(token: string, subject: string = 'student') {
        let res = false;
        if (!!token && !!subject) {
            switch (subject) {
                case 'student':
                    res = StorageService.store('token_st', token);
                    break;
                case 'coordinator':
                    res = StorageService.store('token_cr', token);
                    break;
                case 'admin':
                    res = StorageService.store('token_ad', token);
                    break;
                default:
                    res = StorageService.store('token', token);
            }
            return token;
        }
    }
    getAuthorize(guard: string = 'student') {
        let token = null;
        switch (guard) {
            case 'student':
                token = StorageService.get('token_st');
                break;
            case 'coordinator':
                token = StorageService.get('token_cr');
                break;
            case 'admin':
                token = StorageService.get('token_ad');
                break;
            default:
                token = StorageService.get('token');
        }
        return token;
    }
}
