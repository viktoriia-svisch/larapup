@extends("layout.Admin.admin-layout")
@section('title', 'Update Information Coordinator')
@push("custom-css")
@endpush
@section("admin-content")
    <div class="container row col-md-12" style="margin-top: -2.2vw">
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
        <div class="col-sm-3" style=" border: 1px solid #517777; padding: 3%; margin-bottom: 30vw">
            <div class="row">
                <div class="col-xl-2">
                </div>
                <img class="col-xl-12" style="width: 320px; height: 200px"
                     src="https://i.pinimg.com/564x/40/3e/6d/403e6d4751905cca69e5a72015623f64.jpg">
                <div class="col-xl-2">
                </div>
            </div>
            <hr>
            <div class="row col-xl-12" style=" margin-top: 2vw">
                <h5 class="col-xl-12"
                    style="text-align: center"> {{$coordinator->last_name}} {{$coordinator->first_name}}</h5>
                <p class="col-xl-12">Gender:
                    @if($coordinator->gender == 1)Male
                    @else Female
                    @endif
                </p>
                <p class="col-xl-12">Date of birth: {{$coordinator->dateOfBirth}}</p>
            </div>
        </div>
        <div class="col-sm-5" style=" border-top: 1px solid; padding: 2%;">
            <h3>Information of Coordinator</h3>
            <hr>
            <form method="post" action="{{route('admin.updateCoordinator_post', [$coordinator->id])}}">
                {{csrf_field()}}
                <label style="color: #0b1011">First Name</label>
                <div>
                    <input name='first_name' class="form-control" type="text" placeholder="{{$coordinator->first_name}}"
                           value="{{$coordinator->first_name}}">
                </div>
                <div style="margin-top: 2vw">
                    <label style="color: #0b1011">Last Name</label>
                    <input name="last_name" class="form-control" type="text" placeholder="{{$coordinator->last_name}}"
                           value="{{$coordinator->last_name}}">
                </div>
                <div class="input-group input-group-alternative mt-3">
                    <div class="input-group-prepend">
                        <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                    </div>
                    <input class="form-control datepicker" id="dateOfBirth" name="dateOfBirth"
                           value="{{$coordinator->dateOfBirth}}" placeholder="Date of Birth"
                           type="text">
                </div>
                <div class="row col-xl-12" style="margin-top: 2vw; margin-right: -1vw">
                    <h6 class="col-xl-12" style="color: #0b1011; margin-bottom: 2vw;">Gender</h6>
                    @if($coordinator->gender == 1)
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
                <div class="row col-xl-12" style="margin-top: 2vw; margin-right: -1vw">
                    <h6 class="col-xl-12" style="color: #0b1011; margin-bottom: 2vw;">Account Status</h6>
                    <div class="custom-control custom-radio col-6 d-flex justify-content-center align-items-center">
                        <input name="status" value="{{COORDINATOR_STATUS['ACTIVE']}}" class="custom-control-input" id="statusStandby"
                               checked="" type="radio">
                        <label class="custom-control-label" for="statusStandby">Active</label>
                    </div>
                    <div class="custom-control custom-radio col-6 d-flex justify-content-center align-items-center">
                        <input name="status" value="{{COORDINATOR_STATUS['DEACTIVATE']}}" class="custom-control-input" id="statusOngoing"
                               type="radio">
                        <label class="custom-control-label" for="statusOngoing">Deactivate</label>
                    </div>
                </div>
                <div style="margin-top: 2vw">
                    <label style="color: #0b1011">Email</label>
                    <input class="form-control" type="text" placeholder="{{$coordinator->email}}"
                           value="{{$coordinator->email}}" readonly>
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
            var status = {{$coordinator->status}};
            $("input[name=status][value=" + status + "]").prop('checked', true);
        })
    </script>
@endpush