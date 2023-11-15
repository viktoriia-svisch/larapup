@extends("layout.Admin.admin-layout")
@section('title', 'Update Information Coordinator')
@push("custom-css")
@endpush
@section("admin-content")
    <div class="container row col-md-12" style="margin-top: -2.2vw">
        @if(\Illuminate\Support\Facades\Session::has('updateStatus'))
            <div class="card col-12">
                @if(\Illuminate\Support\Facades\Session::get('updateStatus'))
                    <div class="card-body bg-success">
                        Update Success
                    </div>
                @else
                    <div class="card-body bg-danger">
                        Update Failed
                    </div>
                @endif
            </div>
        @endif
        <div class="col-sm-5 m-auto" style=" border-top: 1px solid; padding: 2%;">
            <h3>Information of Coordinator</h3>
            <hr>
            <form method="post" action="{{route('admin.updateGuest_post', [$guest->id])}}">
                {{csrf_field()}}
                <div>
                    <label style="color: #0b1011">Faculty</label>
                    <input class="form-control" id="faculty" name="faculty"
                           value="{{$guest->faculty->name}}" type="text" readonly>
                </div>
                <div class="row col-xl-12" style="margin-top: 2vw; margin-right: -1vw">
                    <h4 class="col-xl-12" style="color: #0b1011; margin-bottom: 2vw; margin-left: -1vw">Account Status</h4>
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
                <div style="margin-top: 2vw">
                    <label style="color: #0b1011">Email</label>
                    <input class="form-control" type="text" placeholder="{{$guest->email}}"
                           value="{{$guest->email}}" readonly>
                </div>
                <hr>
                <button class="btn btn-danger col-sm-12" type="submit">Activate Account</button>
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
