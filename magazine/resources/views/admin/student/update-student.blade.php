@extends("layout.Admin.admin-layout")
@section('title', 'Update Information Student')
@push("custom-css")
@endpush
@section("admin-content")
    <div class="container row pt-5">
        <div class="col-12 col-md-4 ml-auto mr-auto mt-0 pt-0">
            <img class="img-center img-fluid" style="width: 200px; height: 200px"
                 src="https://i.pinimg.com/564x/40/3e/6d/403e6d4751905cca69e5a72015623f64.jpg">
            <hr>
            <div class="col-12 m-auto">
                <h5 style="text-align: center"> {{$student->last_name}} {{$student->first_name}}</h5>
                <p class="text-center">Gender: @if($student->gender == 1) Male @else Female @endif</p>
                <p class="text-center">Date of birth: {{$student->dateOfBirth}}</p>
            </div>
        </div>
        <div class="col-12 col-md-6 m-auto">
            <h3>Information of Student</h3>
            <hr>
            @include("layout.response.errors")
            <form method="post" action="{{route('admin.updateStudent_post', [$student->id])}}">
                {{csrf_field()}}
                @if($errors->has('first_name'))
                    <div class="card bg-danger text-white rounded-0">
                        <div class="card-body p-1 rounded-0">
                            {{$errors->first('first_name')}}
                        </div>
                    </div>
                @endif
                <label style="color: #0b1011">First Name</label>
                <div>
                    <input name='first_name' class="form-control" type="text" placeholder="{{$student->first_name}}"
                           value="{{$student->first_name}}">
                </div>
                @if($errors->has('last_name'))
                    <div class="card bg-danger text-white rounded-0">
                        <div class="card-body p-1 rounded-0">
                            {{$errors->first('last_name')}}
                        </div>
                    </div>
                @endif
                <div style="margin-top: 2vw">
                    <label style="color: #0b1011">Last Name</label>
                    <input name="last_name" class="form-control" type="text" placeholder="{{$student->last_name}}"
                           value="{{$student->last_name}}">
                </div>
                @if($errors->has('dateOfBirth'))
                    <div class="card bg-danger text-white rounded-0">
                        <div class="card-body p-1 rounded-0">
                            {{$errors->first('dateOfBirth')}}
                        </div>
                    </div>
                @endif
                <div class="input-group input-group-alternative mt-3">
                    <div class="input-group-prepend">
                        <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                    </div>
                    <input class="form-control datepicker" id="dateOfBirth" name="dateOfBirth"
                           value="{{$student->dateOfBirth}}" placeholder="Date of Birth"
                           type="text">
                </div>
                @if($errors->has('gender'))
                    <div class="card bg-danger text-white rounded-0">
                        <div class="card-body p-1 rounded-0">
                            {{$errors->first('gender')}}
                        </div>
                    </div>
                @endif
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
                @if($errors->has('status'))
                    <div class="card bg-danger text-white rounded-0">
                        <div class="card-body p-1 rounded-0">
                            {{$errors->first('status')}}
                        </div>
                    </div>
                @endif
                <div class="row col-xl-12" style="margin-top: 2vw; margin-right: -1vw">
                    <h6 class="col-xl-12" style="color: #0b1011; margin-bottom: 2vw;">Account Status</h6>
                    <div class="custom-control custom-radio col-3 d-flex justify-content-center align-items-center">
                        <input name="status" value="{{STUDENT_STATUS['STANDBY']}}" class="custom-control-input" id="statusStandby"
                               checked="" type="radio">
                        <label class="custom-control-label" for="statusStandby">Standby</label>
                    </div>
                    <div class="custom-control custom-radio col-3 d-flex justify-content-center align-items-center">
                        <input name="status" value="{{STUDENT_STATUS['ONGOING']}}" class="custom-control-input" id="statusOngoing"
                               type="radio">
                        <label class="custom-control-label" for="statusOngoing">Ongoing</label>
                    </div>
                    <div class="custom-control custom-radio col-3 d-flex justify-content-center align-items-center">
                        <input name="status" value="{{STUDENT_STATUS['FINISHED']}}" class="custom-control-input" id="statusFinished"
                               checked="" type="radio">
                        <label class="custom-control-label" for="statusFinished">Finish</label>
                    </div>
                    <div class="custom-control custom-radio col-3 d-flex justify-content-center align-items-center">
                        <input name="status" value="{{STUDENT_STATUS['LEFT']}}" class="custom-control-input" id="statusLeft"
                               type="radio">
                        <label class="custom-control-label" for="statusLeft">Left</label>
                    </div>
                </div>
                @if($errors->has('email'))
                    <div class="card bg-danger text-white rounded-0">
                        <div class="card-body p-1 rounded-0">
                            {{$errors->first('email')}}
                        </div>
                    </div>
                @endif
                <div style="margin-top: 2vw">
                    <label style="color: #0b1011">Email</label>
                    <input class="form-control" type="text" id="email" name="email" placeholder="{{$student->email}}"
                           value="{{$student->email}}">
                </div>
                <div style="margin-top: 2vw; margin-bottom: 3vw">
                    <label style="color: #0b1011">Password</label>
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
            var status = {{$student->status}};
            var gender = {{$student->gender}};
            $("input[name=status][value=" + status + "]").prop('checked', true);
            $("input[name=gender][value=" + gender + "]").prop('checked', true);
        })
    </script>
@endpush
