import { TestBed } from '@angular/core/testing';
import { Storage } from './storage.service';
describe('Storage', () => {
  beforeEach(() => TestBed.configureTestingModule({}));
  it('should be created', () => {
    const service: Storage = TestBed.get(Storage);
    expect(service).toBeTruthy();
  });
});
