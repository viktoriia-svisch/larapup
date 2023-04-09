@extends("layout.Coordinator.coordinator-layout")
@section('title', 'Update Information Student')
@push("custom-css")
@endpush
@section("coordinator-content")
    <div class="container row col-md-12">
        <div class="col-6 m-auto">
            <h1>Update</h1>
            <hr>
                <form method="post" action="{{route('coordinator.faculty.settingPost', [$facultyUpdate->faculty_id, $facultyUpdate->semester_id])}}">
                    {{csrf_field()}}
                    <div class="card-body p-1 rounded-0">
                        <label>First-Deadline<span style="color: red">*</span></label>
                        <input class="form-control datepicker" type="text" name="first_deadline" placeholder="{{$facultyUpdate->first_deadline}}"
                               value="{{$facultyUpdate->first_deadline}}" required>
                    </div>
                    <div class="card-body p-1 rounded-0" style="margin-top: 2vw">
                        <label>Second-Deadline<span style="color: red">*</span></label>
                        <input class="form-control datepicker" type="text" name="second_deadline" placeholder="{{$facultyUpdate->second_deadline}}"
                               value="{{$facultyUpdate->second_deadline}}" required>
                    </div>
                    <div class="card-body p-1 rounded-0" style="margin-top: 2vw">
                        <label>Description</label>
                        <input class="form-control" type="text" name="description" placeholder="{{$facultyUpdate->description}}"
                               value="{{$facultyUpdate->description}}" required>
                    </div>
                    <hr>
                    <input class="btn btn-twitter col-sm-12" type="submit" value="Update Faculty"/>
               </form>
        </div>
    </div>
@endsection
@push("custom-js")
    <script>
        $(document).ready(function () {
            $('.datepicker').datepicker({
                format: 'yyyy-mm-dd',
                autoclose: true,
            });
        })
    </script>
@endpush
