@extends("admin.Semester.semester-detail")
@section('title',  $currentSemester->name . ' - About')
@push("custom-css")
@endpush
@section("semester-detail")
    <div class="row m-0 p-0">
        <form method="post" class="col-12 col-md-8"
              action="{{route('admin.updateSemesterPost',[$currentSemester->id])}}">
            {{csrf_field()}}
            <h2>Update information</h2>
            <div class="mb-3">
                <small class="text-muted">
                    <strong>This action cannot be undone.</strong> You cannot update passed semester!
                </small>
            </div>
            @if (!\App\Helpers\DateTimeHelper::isNowPassedDate($currentSemester->start_date))
                <label for="name">Semester name</label>
                <div class="form-group">
                    <input type="text" class="form-control" id="name" name="name" value="{{$currentSemester->name}}">
                </div>
            @else
                <label>Semester name</label>
                <p>{{$currentSemester->name}}</p>
                <br>
            @endif
            @if (!\App\Helpers\DateTimeHelper::isNowPassedDate($currentSemester->end_date))
                <label for="description">Semester description</label>
                <div class="form-group">
                <textarea name="description" id="description" rows="10"
                          class="form-control form-control-alternative">{{$currentSemester->description}}</textarea>
                </div>
            @else
                <label>Semester description</label>
                <p>{{$currentSemester->description}}</p>
                <br>
            @endif
            @if (!\App\Helpers\DateTimeHelper::isNowPassedDate($currentSemester->start_date))
                <label for="start_date">Semester start date</label>
                <div class="form-group">
                    <input type="text" class="form-control datepicker" id="start_date" name="start_date"
                           value="{{\App\Helpers\DateTimeHelper::formatDateInput($currentSemester->start_date)}}">
                </div>
            @else
                <label>Semester starting date</label>
                <p>{{\App\Helpers\DateTimeHelper::formatDate($currentSemester->start_date)}}</p>
                <br>
            @endif
            @if (!\App\Helpers\DateTimeHelper::isNowPassedDate($currentSemester->end_date))
                <label for="end_date">Semester end date</label>
                <div class="form-group">
                    <input type="text" class="form-control datepicker" id="end_date" name="end_date"
                           value="{{\App\Helpers\DateTimeHelper::formatDateInput($currentSemester->end_date)}}">
                </div>
            @else
                <label>Semester ending date</label>
                <p>{{\App\Helpers\DateTimeHelper::formatDate($currentSemester->end_date)}}</p>
                <br>
            @endif
            @if (!\App\Helpers\DateTimeHelper::isNowPassedDate($currentSemester->end_date))
                <button class="btn btn-twitter col-sm-12" type="submit">Update Information</button>
            @endif
        </form>
        @if (\App\Helpers\DateTimeHelper::isNowPassedDate($currentSemester->start_date))
            <div class="col-12 col-md-4">
                <h2>Delete this semester</h2>
                <span class="text-muted">This semester was already activated and cannot be deleted.</span>
            </div>
        @else
            <div class="col-12 col-md-4">
                <h2>Delete this semester</h2>
                <div class="mb-3">
                    <small class="text-muted">
                        <strong>This action cannot be undone.</strong> You can only delete the future, non-activated
                        semester!
                    </small>
                </div>
                <a href="{{route('admin.deleteSemester', [$currentSemester->id])}}" class="btn btn-block btn-danger">
                    Confirm Delete
                </a>
            </div>
        @endif
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
