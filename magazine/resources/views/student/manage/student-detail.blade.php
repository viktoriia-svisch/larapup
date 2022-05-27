@extends("layout.Student.student-layout")
@section('title', 'Update Information Student')
@push("custom-css")
@endpush
@section('breadcrumb')
    <div class="container">
        {{ Breadcrumbs::render('dashboard.profile', route('student.dashboard'), route('student.manageAccount',[$student->id])) }}
    </div>
@endsection
@section("student-content")
    <div class="container row col-md-12">
        @if(\Illuminate\Support\Facades\Session::has('updateStatus'))
            <div class="card col-12">
                @if(\Illuminate\Support\Facades\Session::get('updateStatus'))
                    <div class="card-body bg-danger">
                        Update Success
                    </div>
                @else
                    <div class="card-body bg-danger">
                        Update Failed
                    </div>
                @endif
            </div>
        @endif
        <div class="col-sm-2">
        </div>
        <div class="col-sm-3 p-4" style=" border: 1px solid #517777;">
            <div class="row">
                <div class="col-xl-2">
                </div>
                <img class="col-xl-8 " style="width: 320px; height: 200px"
                     src="https://i.pinimg.com/564x/40/3e/6d/403e6d4751905cca69e5a72015623f64.jpg">
                <div class="col-xl-2">
                </div>
            </div>
            <hr>
            <div class="row col-xl-12" style=" margin-top: 2vw">
                <h5 class="col-xl-12"
                    style="text-align: center"> {{$student->last_name}} {{$student->first_name}}</h5>
                <p class="col-xl-12">Gender:
                    @if($student->gender == 1)Male
                    @else Female
                    @endif
                </p>
                <p class="col-xl-12">Date of birth: {{$student->dateOfBirth}}</p>
            </div>
        </div>
        <div class="col-sm-5 p-4">
            <h3>Information of Student</h3>
            <hr>
            <form method="post" action="{{route('student.manageAccount_post', [$student->id])}}">
                {{csrf_field()}}
                <label style="color: #0b1011">First Name</label>
                <div>
                    <input name='first_name' class="form-control" type="text" placeholder="{{$student->first_name}}"
                           value="{{$student->first_name}}">
                </div>
                <div style="margin-top: 2vw">
                    <label style="color: #0b1011">Last Name</label>
                    <input name="last_name" class="form-control" type="text" placeholder="{{$student->last_name}}"
                           value="{{$student->last_name}}">
                </div>
                <div class="input-group input-group-alternative mt-3">
                    <div class="input-group-prepend">
                        <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                    </div>
                    <input class="form-control datepicker" id="dateOfBirth" name="dateOfBirth"
                           value="{{$student->dateOfBirth}}" placeholder="Date of Birth"
                           type="text">
                </div>
                <div class="row col-xl-12" style="margin-top: 2vw; margin-right: -1vw">
                    <h6 class="col-xl-12" style="color: #0b1011; margin-bottom: 2vw;">Gender</h6>
                    @if($student->gender == 1)
                        <div class="custom-control custom-radio col-6 d-flex justify-content-center align-items-center">
                            <input name="gender" value="{{GENDER['MALE']}}" class="custom-control-input" id="genderMale"
                                   type="radio">
                            <label class="custom-control-label" for="genderMale">Male</label>
                        </div>
                        <div class="custom-control custom-radio col-6 d-flex justify-content-center align-items-center">
                            <input name="gender" value="{{GENDER['FEMALE']}}" class="custom-control-input" id="genderFemale"
                                   checked="" type="radio">
                            <label class="custom-control-label" for="genderFemale">Female</label>
                        </div>
                    @else
                        <div class="custom-control custom-radio col-6 d-flex justify-content-center align-items-center">
                            <input name="gender" value="{{GENDER['MALE']}}" class="custom-control-input" id="genderMale"
                                   type="radio">
                            <label class="custom-control-label" for="genderMale">Male</label>
                        </div>
                        <div class="custom-control custom-radio col-6 d-flex justify-content-center align-items-center">
                            <input name="gender" value="{{GENDER['FEMALE']}}" class="custom-control-input" id="genderFemale"
                                   checked="" type="radio">
                            <label class="custom-control-label" for="genderFemale">Female</label>
                        </div>
                    @endif
                </div>
                <div style="margin-top: 2vw">
                    <label style="color: #0b1011">Email</label>
                    <input class="form-control" type="text" placeholder="{{$student->email}}"
                           value="{{$student->email}}" readonly>
                </div>
                <div style="margin-top: 2vw; margin-bottom: 3vw">
                    <label style="color: #0b1011">Old Password</label>
                    <input name="old_password" class="form-control" type="password" placeholder="Old Password">
                </div>
                <div style="margin-top: 2vw; margin-bottom: 3vw">
                    <label style="color: #0b1011">New Password</label>
                    <input name="new_password" class="form-control" type="password" placeholder="New Password">
                </div>
                <div style="margin-top: 2vw; margin-bottom: 3vw">
                    <label style="color: #0b1011">Confirm New Password</label>
                    <input name="confirm_password" class="form-control" type="password" placeholder="Retype new Password">
                </div>
                <hr>
                <button class="btn btn-danger col-sm-12" type="submit">Update Information</button>
            </form>
        </div>
    </div>
@endsection
@push("custom-js")
    <script>
        $(document).ready(function () {
            $('.datepicker').datepicker({
                format: 'dd/mm/yyyy',
                autoclose: true,
                // endDate: "today",
                // endDate: "today",
            });
        })
    </script>
@endpush
