@extends("layout.Admin.admin-layout")
@section('title', 'Update Information Coordinator')
@push("custom-css")
@endpush
@section("admin-content")
    <div class="container row col-md-12" style="margin-top: -2.2vw">
        <div class="col-sm-5 m-auto" style=" border-top: 1px solid; padding: 2%;">
            <h3>Information of Coordinator</h3>
            <hr>
            <div class="col-12">
                @include("layout.response.errors")
            </div>
            <form method="post" action="{{route('admin.updateGuest_post', [$guest->id])}}">
                {{csrf_field()}}
                <div class="form-group mb-4">
                    <label for="faculty" style="color: #0b1011">Faculty</label>
                    <input class="form-control" id="faculty" name="faculty"
                           value="{{$guest->faculty->name}}" type="text" readonly>
                </div>
                <div class="row form-group mb-4">
                    <h4 class="col-xl-12" style="color: #0b1011">Account
                        Status</h4>
                    <div class="custom-control custom-radio col-6 d-flex justify-content-center align-items-center">
                        <input name="status" value="{{GUEST_STATUS['ACTIVE']}}" class="custom-control-input"
                               id="statusStandby"
                               checked="" type="radio">
                        <label class="custom-control-label" for="statusStandby">Active</label>
                    </div>
                    <div class="custom-control custom-radio col-6 d-flex justify-content-center align-items-center">
                        <input name="status" value="{{GUEST_STATUS['DEACTIVATE']}}" class="custom-control-input"
                               id="statusOngoing"
                               type="radio">
                        <label class="custom-control-label" for="statusOngoing">Deactivate</label>
                    </div>
                </div>
                <div class="form-group mb-4">
                    <label for="email" style="color: #0b1011">Email</label>
                    <input class="form-control form-control-alternative" type="text" name="email" id="email"
                           placeholder="{{$guest->email}}"
                           value="{{$guest->email}}">
                </div>
                <div class="form-group mb-4">
                    <label for="new_password" style="color: #0b1011">New password</label>
                    <input class="form-control form-control-alternative" type="text" name="new_password"
                           id="new_password">
                    <small class="text-muted">You can leave this field empty if you don't want to update it</small>
                </div>
                <hr>
                <button class="btn btn-danger col-sm-12" type="submit">Update Account</button>
            </form>
        </div>
    </div>
@endsection
@push("custom-js")
    <script>
        $(document).ready(function () {
            var status = {{$guest->status}};
            $("input[name=status][value=" + status + "]").prop('checked', true);
        })
    </script>
@endpush
