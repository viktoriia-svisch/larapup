@extends("layout.Admin.admin-layout")
@section('title', 'Dashboard')
@push("custom-css")
@endpush
@section('breadcrumb')
    <div class="container">
        {{ Breadcrumbs::render('dashboard.guest.create', route('admin.dashboard'), route('admin.guest'), route('admin.createGuest')) }}
    </div>
@endsection
@section("admin-content")
    <div class="container row col-md-12" style="margin-bottom: 10vw">
        <div class="col-sm-5 m-auto">
            @if (Session::has('action_response') || sizeof($errors->all()) > 0)
                @if (Session::get('action_response')['status_ok'])
                    <div class="col-12 m-0 p-0">
                        <div class="card bg-success text-white">
                            <div class="card-body" style="padding: 1rem;">
                                {{Session::get('action_response')['status_message']}}
                            </div>
                        </div>
                    </div>
                @else
                    @if ($errors->first())
                        <div class="col-12 m-0 p-0">
                            <div class="card bg-danger text-white">
                                <div class="card-body" style="padding: 1rem;">
                                    {{$errors->first()}}
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="col-12 m-0 p-0">
                            <div class="card bg-danger text-white">
                                <div class="card-body" style="padding: 1rem;">
                                    {{Session::get('action_response')['status_message']}}
                                </div>
                            </div>
                        </div>
                    @endif
                @endif
                <br>
            @endif
            <h1 class="title">Create Guest</h1>
            <hr>
            <form action="{{route('admin.createGuest_post')}}" method="post">
                {{csrf_field()}}
                @if($errors->has('email'))
                    <p class="col-12 text-danger">
                        {{$errors->first('email')}}
                    </p>
                @endif
                <div style="margin-top: 2vw">
                    <label style="color: #0b1011">Email</label>
                    <input class="form-control" type="text" placeholder="Email" name="email" id="email" required>
                </div>
                @if($errors->has('password'))
                    <p class="col-12 text-danger">
                        {{$errors->first('password')}}
                    </p>
                @endif
                <div style="margin-top: 2vw; margin-bottom: 3vw">
                    <label style="color: #0b1011">Password</label>
                    <input class="form-control" name="password" id="password" value="123456" type="password" placeholder="Password" required>
                </div>
                @if($errors->has('faculty_id'))
                    <p class="col-12 text-danger">
                        {{$errors->first('faculty_id')}}
                    </p>
                @endif
                <div style="margin-top: 2vw; margin-bottom: 3vw">
                    <label style="color: #0b1011">Password</label>
                    <select class="form-control" name="faculty_id" id="faculty" required>
                        <option value="-1">Select a faculty</option>
                        @foreach($faculties as $faculty)
                            <option value="{{$faculty->id}}">{{$faculty->name}}</option>
                        @endforeach
                    </select>
                </div>
                <hr>
                <button class="btn btn-danger col-sm-12">Create</button>
            </form>
        </div>
    </div>
@endsection
@push("custom-js")
    <script>
    </script>
@endpush
