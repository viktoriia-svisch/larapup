import { async, ComponentFixture, TestBed } from '@angular/core/testing';
import { LoginCoordinatorComponent } from './login-coordinator.component';
describe('LoginCoordinatorComponent', () => {
  let component: LoginCoordinatorComponent;
  let fixture: ComponentFixture<LoginCoordinatorComponent>;
  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ LoginCoordinatorComponent ]
    })
    .compileComponents();
  }));
  beforeEach(() => {
    fixture = TestBed.createComponent(LoginCoordinatorComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });
  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
