import { async, ComponentFixture, TestBed } from '@angular/core/testing';
import { ChooseSemesterComponent } from './choose-semester.component';
describe('ChooseSemesterComponent', () => {
  let component: ChooseSemesterComponent;
  let fixture: ComponentFixture<ChooseSemesterComponent>;
  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ ChooseSemesterComponent ]
    })
    .compileComponents();
  }));
  beforeEach(() => {
    fixture = TestBed.createComponent(ChooseSemesterComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });
  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
