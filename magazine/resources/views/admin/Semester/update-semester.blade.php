@extends("layout.Admin.admin-layout")
@section('title', 'Static Information')
@push("custom-css")
@endpush
@section("admin-content")
    <div class="container">
        @include("layout.response.errors")
        <form method="post" action="{{route('admin.updateSemesterPost',[$currentSemester->id])}}">
            {{csrf_field()}}
            <div class="row">
                <div class="col-md-6 center">
                    <label><h3>Semester name</h3></label>
                    <div class="form-group">
                        <input type="text" class="form-control" id="name" name="name"
                               value="{{$currentSemester->name}}">
                    </div>
                    <label><h3>Semester description</h3></label>
                    <div class="form-group">
                        <input type="text" class="form-control" id="description" name="description"
                               value="{{$currentSemester->description}}">
                    </div>
                    <label><h3>Semester start date</h3></label>
                    <div class="form-group">
                        <input type="text" class="form-control datepicker" id="start_date" name="start_date"
                               value="{{\App\Helpers\DateTimeHelper::formatDateInput($currentSemester->start_date)}}">
                    </div>
                    <label><h3>Semester end date</h3></label>
                    <div class="form-group">
                        <input type="text" class="form-control datepicker" id="end_date" name="end_date"
                               value="{{\App\Helpers\DateTimeHelper::formatDateInput($currentSemester->end_date)}}">
                    </div>
                    <input hidden name="semester_id" value="{{$currentSemester->id}}">
                    <button class="btn btn-twitter col-sm-12" type="submit">Update Information</button>
                </div>
            </div>
        </form>
    </div>
@endsection
@push("custom-js")
    <script>
        $(document).ready(function () {
            $('.datepicker').datepicker({
                format: 'yyyy-mm-dd',
                autoclose: true,
            });
        });
        Date.createFromMysql = function (mysql_string) {
            let t, result = null;
            if (typeof mysql_string === 'string') {
                t = mysql_string.split(/[- :]/);
                //when t[3], t[4] and t[5] are missing they defaults to zero
                result = new Date(t[0], t[1] - 1, t[2], t[3] || 0, t[4] || 0, t[5] || 0);
                result.setDate(+t[2] + 1);
            }
            return result;
        };
    </script>
@endpush
