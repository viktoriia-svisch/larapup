@extends("layout.Admin.admin-layout")
@section('title', 'Add Coordinator to Faculty')
@push("custom-css")
@endpush
@section('breadcrumb')
    <div class="container">
        {{ Breadcrumbs::render('dashboard.faculty', route('admin.dashboard'), route('admin.faculty')) }}
    </div>
@endsection
@section("admin-content")
    <div class="container" style="margin-bottom: 10vw">
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
        <div class="row col-md-12 m-auto">
            <div class="row col-12">
                <div class="col-sm-6">
                    <select style="margin-top: 1vw" class="form-control" id="faculty" data-dependent="semester">
                        <option value="0">Select a faculty</option>
                        @foreach($faculties as $faculty)
                            <option value="{{$faculty->id}}">{{$faculty->name}}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-sm-6">
                    <select style="margin-top: 1vw" class="form-control" id="semester" data-dependent="faculty">
                        <option value="0">Select a semester</option>
                    </select>
                </div>
                {{csrf_field()}}
            </div>
            <hr>
            <div class="row col-12">
                <div class="col-sm-6">
                    <h4 style="text-align: center">List coordinator</h4>
                    <input style="margin-top: 1vw" class="form-control" type="text" placeholder="search">
                    <hr>
                    <div class="col-12" id="coordinator-available">
                    </div>
                </div>
                <div class="col-sm-6">
                    <h4 style="text-align: center">List coordinator</h4>
                    <input style="margin-top: 1vw" class="form-control" type="text" placeholder="search">
                    <hr>
                    <div class="col-12" id="coordinator-in-faculty">
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push("custom-js")
    <script>
        $(document).ready(function () {
            $('#faculty').change(function () {
                if($(this).val() != ''){
                    var value = $(this).val();
                    var _token = $('input[name="_token"]').val();
                    $.ajax({
                        url: "{{route('admin.addToFaculty.fetch')}}",
                        method: "POST",
                        data:{value: value, _token:_token},
                        success: function (results) {
                            $('#semester').html(results);
                        },
                        fail:function (results) {
                            console.log(results);
                        }
                    })
                }
            });
            $('#semester').change(function () {
                if($(this).val() != ''){
                    var semester = $(this).val();
                    var faculty = $('#faculty').val()
                    var _token = $('input[name="_token"]').val();
                    $.ajax({
                        url: "{{route('admin.addToFaculty.fetchCoor')}}",
                        method: "POST",
                        data:{semester: semester, faculty:faculty, _token:_token},
                        success: function (results) {
                            $('#coordinator-available').html(results['availableCoor']);
                            $('#coordinator-in-faculty').html(results['unavailableCoor']);
                        },
                        fail:function (results) {
                            console.log(results);
                        }
                    })
                }
            });
        });
    </script>
@endpush
