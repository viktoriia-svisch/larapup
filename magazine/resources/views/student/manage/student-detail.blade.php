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
        <br>
        <div class="col-sm-2">
        </div>
        <div class="col-sm-3 p-4" style=" border: 1px solid #517777; height: 50% ">
            <div class="row col-12 m-0">
                <div class="col-12 d-flex justify-content-center">
                    <img class="rounded-circle"
                         @if (!$student->avatar_path)
                         src="http://getdrawings.com/images/anime-girls-drawing-34.jpg"
                         @endif
                         style="width:190px; height: 190px; object-fit: cover; object-position: center">
                </div>
                <div class="col-9 m-auto">
                    <button class="btn btn-google-plus col-sm-12 mt-3" type="submit">Update Avatar</button>
                </div>
            </div>
            <hr>
            <div class="col-xl-12" style=" margin-top: 2vw">
                <p class="col-xl-12" style="text-align: center; font-weight: bold ">
                    {{$student->last_name}} {{$student->first_name}}
                </p>
                <p class="col-xl-12" style="text-align: center">Gender:
                    @if($student->gender == 1)Male
                    @else Female
                    @endif
                </p>
                <p class="col-xl-12" style="text-align: center">Date of birth: {{$student->dateOfBirth}}</p>
            </div>
        </div>
        <div class="col-sm-5 p-4">
            @if(\Illuminate\Support\Facades\Session::has('updateStatus'))
                @if(\Illuminate\Support\Facades\Session::get('updateStatus'))
                    <div class="card col-12 bg-success text-white p-1" style="margin-bottom: 8%">
                        <div class="card-body p-1">
                            Update Success
                        </div>
                    </div>
                @else
                    <div class="card col-12 bg-danger text-white p-1" style="margin-bottom: 8%">
                        <div class="card-body p-1">
                            Update Failed
                        </div>
                    </div>
                @endif
            @endif
            @if($errors->has('new_password'))
                <div class="card bg-danger text-white">
                    <div class="card-body">{{$errors->first('new_password')}}</div>
                </div>
            @endif
            <h2>Information of Student</h2>
            <hr>
            <form method="post" action="{{route('student.manageAccount_post', [$student->id])}}">
                {{csrf_field()}}
                <div class="form-group mt-3">
                    <p>First Name</p>
                    <input name='first_name' class="form-control" type="text" placeholder="{{$student->first_name}}"
                           value="{{$student->first_name}}">
                </div>
                <div class="form-group mt-3">
                    <p>Last Name</p>
                    <input name="last_name" class="form-control" type="text" placeholder="{{$student->last_name}}"
                           value="{{$student->last_name}}">
                </div>
                <p class="text-muted mt-3 pb-0 mb-1">Date of Birth</p>
                <div class="input-group input-group-alternative mt-3">
                    <div class="input-group-prepend">
                        <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                    </div>
                    <input class="form-control datepicker" id="dateOfBirth" name="dateOfBirth"
                           value="{{$student->dateOfBirth}}" placeholder="Date of Birth"
                           type="text">
                </div>
                <p class="text-muted mt-3 pb-0 mb-1">Gender</p>
                <div class="row col-12" style="margin-top: 2vw; margin-right: -1vw">
                    @if($student->gender == GENDER['MALE'])
                        <div class="custom-control custom-radio col-6 d-flex justify-content-center align-items-center">
                            <input name="gender" value="{{GENDER['MALE']}}" checked class="custom-control-input"
                                   id="genderMale"
                                   type="radio">
                            <label class="custom-control-label" for="genderMale">Male</label>
                        </div>
                        <div class="custom-control custom-radio col-6 d-flex justify-content-center align-items-center">
                            <input name="gender" value="{{GENDER['FEMALE']}}" class="custom-control-input"
                                   id="genderFemale"
                                   type="radio">
                            <label class="custom-control-label" for="genderFemale">Female</label>
                        </div>
                    @else
                        <div class="custom-control custom-radio col-6 d-flex justify-content-center align-items-center">
                            <input name="gender" value="{{GENDER['MALE']}}" class="custom-control-input" id="genderMale"
                                   type="radio">
                            <label class="custom-control-label" for="genderMale">Male</label>
                        </div>
                        <div class="custom-control custom-radio col-6 d-flex justify-content-center align-items-center">
                            <input name="gender" value="{{GENDER['FEMALE']}}" class="custom-control-input"
                                   id="genderFemale"
                                   checked="" type="radio">
                            <label class="custom-control-label" for="genderFemale">Female</label>
                        </div>
                    @endif
                </div>
                <div style="margin-top: 2vw">
                    <p>Email</p>
                    <input class="form-control" type="text" placeholder="{{$student->email}}"
                           value="{{$student->email}}" readonly>
                </div>
                @if($errors->has('new_password'))
                    <div class="card bg-danger text-white">
                        <div class="card-body">{{$errors->first('new_password')}}</div>
                    </div>
                @endif
                <div style="margin-top: 2vw; margin-bottom: 3vw">
                    <p>Old Password</p>
                    <input name="old_password" class="form-control" type="password" placeholder="Old Password">
                </div>
                <div style="margin-top: 2vw; margin-bottom: 3vw">
                    <p>New Password</p>
                    <input name="new_password" class="form-control" type="password" placeholder="New Password">
                </div>
                <div style="margin-top: 2vw; margin-bottom: 3vw">
                    <p>Confirm New Password</p>
                    <input name="confirm_password" class="form-control" type="password"
                           placeholder="Retype new Password">
                </div>
                <hr>
                <button class="btn btn-twitter col-sm-12" type="submit">Update Information</button>
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
