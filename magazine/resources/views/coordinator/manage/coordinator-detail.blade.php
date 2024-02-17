@extends("layout.Coordinator.coordinator-layout")
@section('title', 'Update Information Coordinator')
@push("custom-css")
@endpush
@section('breadcrumb')
    <div class="container">
        {{ Breadcrumbs::render('dashboard.profile', route('coordinator.dashboard'), route('coordinator.manageAccount',[$coordinator->id])) }}
    </div>
@endsection
@section("coordinator-content")
    <div class="container col-12 row">
        <br>
        <div class="col-sm-2"></div>
        <div class="col-sm-3 p-4" style=" border: 1px solid #517777; height: 50% ">
            <div class="row col-12 m-0">
                <div class="col-12 d-flex justify-content-center">
                    <img class="rounded-circle"
                         @if (!$coordinator->avatar_path)
                         src="http://getdrawings.com/images/anime-girls-drawing-34.jpg"
                         @endif
                         alt="Lights"
                         style="width:190px; height: 190px; object-fit: cover; object-position: center">
                </div>
                <div class="col-9 m-auto">
                    <button class="btn btn-google-plus col-sm-12 mt-3" type="submit">Update Avatar</button>
                </div>
            </div>
            <hr>
            <div class="col-xl-12" style=" margin-top: 2vw">
                <p class="col-xl-12" style="text-align: center; font-weight: bold ">
                    {{$coordinator->last_name}} {{$coordinator->first_name}}
                </p>
                <p class="col-xl-12" style="text-align: center">Gender:
                    @if($coordinator->gender == 1)Male
                    @else Female
                    @endif
                </p>
                <p class="col-xl-12" style="text-align: center">Date of birth: {{$coordinator->dateOfBirth}}</p>
            </div>
        </div>
        <div class="col-sm-5 p-4">
            <div class="col-12">
                @include('layout.response.errors')
            </div>
            <h1>Information of Coordinator</h1>
            <hr>
            <form method="post" action="{{route('coordinator.manageAccount_post', [$coordinator->id])}}">
                {{csrf_field()}}
                <div class="form-group mb-4">
                    <label for="first_name">First Name</label>
                    <input name='first_name' id="first_name" class="form-control form-control-alternative" type="text"
                           placeholder="{{$coordinator->first_name}}" value="{{$coordinator->first_name}}">
                </div>
                <div class="form-group mb-4">
                    <label for="last_name">Last Name</label>
                    <input name="last_name" id="last_name" class="form-control form-control-alternative" type="text"
                           placeholder="{{$coordinator->last_name}}" value="{{$coordinator->last_name}}">
                </div>
                <div class="form-group mb-4">
                    <label class="text-muted" for="dateOfBirth">Date of Birth</label>
                    <div class="input-group input-group-alternative">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                        </div>
                        <input class="form-control datepicker" id="dateOfBirth" name="dateOfBirth"
                               value="{{$coordinator->dateOfBirth}}" placeholder="Date of Birth"
                               type="text">
                    </div>
                </div>
                <p class="text-muted mt-3 pb-0 mb-1">Gender</p>
                <div class="form-group row col-12 m-0 mb-4">
                    @if($coordinator->gender == GENDER['MALE'])
                        <div class="custom-control custom-radio col-6 d-flex justify-content-center align-items-center">
                            <input name="gender" value="{{GENDER['MALE']}}" checked class="custom-control-input"
                                   id="genderMale" type="radio">
                            <label class="custom-control-label" for="genderMale">Male</label>
                        </div>
                        <div class="custom-control custom-radio col-6 d-flex justify-content-center align-items-center">
                            <input name="gender" value="{{GENDER['FEMALE']}}" class="custom-control-input"
                                   id="genderFemale" type="radio">
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
                                   id="genderFemale" checked="" type="radio">
                            <label class="custom-control-label" for="genderFemale">Female</label>
                        </div>
                    @endif
                </div>
                <div class="form-group mb-4">
                    <label for="email">Email</label>
                    <input class="form-control form-control-alternative" id="email" type="text"
                           placeholder="{{$coordinator->email}}" value="{{$coordinator->email}}" readonly>
                </div>
                <div class="form-group mb-4">
                    <label for="old_password">Old Password</label>
                    <input name="old_password" id="old_password" class="form-control form-control-alternative"
                           type="password" placeholder="Old Password" autocomplete="false">
                    <small class="text-muted">You can leave all password field empty if you wish for not update it
                    </small>
                </div>
                <div class="form-group mb-4">
                    <label for="new_password">New Password</label>
                    <input name="new_password" id="new_password" class="form-control form-control-alternative"
                           type="password" placeholder="New Password">
                </div>
                <div class="form-group mb-4">
                    <label for="confirm_password">Confirm New Password</label>
                    <input name="confirm_password" id="confirm_password" class="form-control form-control-alternative"
                           type="password" placeholder="Retype new Password">
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
