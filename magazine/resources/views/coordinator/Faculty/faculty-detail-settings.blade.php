@extends("coordinator.Faculty.faculty-detail")
@section('title', 'Faculty '.$facultySemester->faculty->name.' - Settings')
@push("custom-css")
@endpush
@section("faculty-detail")
    <div class="container row col-md-12">
        <div class="col-6">
            <small class="text-muted mb-0 pb-0">Faculty Information</small>
            <h1 class="mt-0 pt-0" style="line-height: 1.625rem;">{{$facultySemester->semester->name}}</h1>
            <hr>
            @include("layout.response.errors")
            <form method="post"
                  action="{{route('coordinator.faculty.settingPost', [$facultySemester->faculty_id, $facultySemester->semester_id])}}">
                {{csrf_field()}}
                @if($errors->has('first_deadline'))
                    <div class="card bg-danger text-white rounded-0">
                        <div class="card-body p-1 rounded-0">
                            {{$errors->first('first_deadline')}}
                        </div>
                    </div>
                @endif
                <div class="card-body p-1 rounded-0">
                    <label>First-Deadline<span style="color: red">*</span></label>
                    <input class="form-control datepicker" type="text" name="first_deadline"
                           placeholder="{{\App\Helpers\DateTimeHelper::formatDateInput($facultySemester->first_deadline)}}"
                           value="{{\App\Helpers\DateTimeHelper::formatDateInput($facultySemester->first_deadline)}}">
                </div>
                @if($errors->has('second_deadline'))
                    <div class="card bg-danger text-white rounded-0">
                        <div class="card-body p-1 rounded-0">
                            {{$errors->first('second_deadline')}}
                        </div>
                    </div>
                @endif
                <div class="card-body p-1 rounded-0" style="margin-top: 2vw">
                    <label>Second-Deadline<span style="color: red">*</span></label>
                    <input class="form-control datepicker" type="text" name="second_deadline"
                           placeholder="{{\App\Helpers\DateTimeHelper::formatDateInput($facultySemester->second_deadline)}}"
                           value="{{\App\Helpers\DateTimeHelper::formatDateInput($facultySemester->second_deadline)}}">
                </div>
                @if($errors->has('description'))
                    <div class="card bg-danger text-white rounded-0">
                        <div class="card-body p-1 rounded-0">
                            {{$errors->first('description')}}
                        </div>
                    </div>
                @endif
                <div class="card-body p-1 rounded-0" style="margin-top: 2vw">
                    <label>Description</label>
                    <input class="form-control form-control-alternative" type="text" name="description"
                           placeholder="{{$facultySemester->description}}"
                           value="{{$facultySemester->description}}">
                </div>
                <hr>
                <input type="hidden" name="semester_id" value="{{$facultySemester->semester_id}}">
                <input type="hidden" name="faculty_id" value="{{$facultySemester->faculty_id}}">
                <input class="btn btn-twitter col-sm-12" type="submit" value="Update Faculty"/>
            </form>
        </div>
        <div class="col-6">
            <small class="text-muted mb-0 pb-0">Belongs To Semester</small>
            <h1 class="mt-0 pt-0" style="line-height: 1.625rem;">{{$facultySemester->semester->name}}</h1>
            <div class="card-body p-1 rounded-0">
                <label>End date: </label>
                <h4 class="mt-0 pt-0" style="line-height: 1.625rem;">{{\App\Helpers\DateTimeHelper::formatDateInput($facultySemester->semester->end_date)}}</h4>
            </div>
        </div>
    </div>
@endsection
@push("custom-js")
    <script>
        $(document).ready(function () {
            let endDate = Date.createFromMysql('{{$facultySemester->semester->end_date}}');
            let startDate = Date.createFromMysql('{{$facultySemester->semester->start_date}}');
            $('.datepicker').datepicker({
                format: 'yyyy-mm-dd',
                autoclose: true,
                endDate: endDate,
                startDate: startDate
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
